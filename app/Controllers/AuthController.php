<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;
use App\Services\AuthService;
use App\Services\EmailVerificationService;
use App\Services\MailerService;
use App\Services\NotificationManager;
use App\Services\PasswordResetService;
use App\Services\TwoFactorService;
use App\Models\Setting;
use Throwable;

/**
 * Handles registration, login, logout, email verification, and password reset flows.
 */
class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): void
    {
        $this->guestOnly();
        $this->view('auth/login', ['title' => 'Login']);
    }

    /**
     * Authenticate a user and create a session.
     */
    public function login(): void
    {
        $this->guestOnly();
        $this->validateCsrf('/login');

        $validator = new Validator($this->request());
        $validator->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $userModel = new User();
        $user = $userModel->findByLogin(trim((string) $this->input('login')));

        if (!$user || !password_verify((string) $this->input('password'), $user['password'])) {
            $validator->add('login', 'The login details are incorrect.');
        } elseif (in_array($user['account_status'], ['banned', 'suspended'], true)) {
            $validator->add('login', 'This account is not allowed to sign in.');
        }

        if ($validator->errors() !== []) {
            $this->view('auth/login', [
                'title' => 'Login',
                'errors' => $validator->errors(),
                'old' => ['login' => $this->input('login')],
            ]);
            return;
        }

        $settings = (new Setting())->all();

        if (($settings['two_factor_enabled'] ?? '0') === '1') {
            $code = (new TwoFactorService(Database::connection()))->createCode((int) $user['id']);
            try {
                $this->mailerService()->sendTwoFactorCodeEmail(
                    $user['email'],
                    $user['fullname'],
                    $code
                );
            } catch (Throwable) {
                Session::flash('warning', 'Verification code could not be sent. Check SMTP settings.');
            }

            Session::put('two_factor_user_id', (int) $user['id']);
            Session::put('two_factor_remember', $this->input('remember') === '1' ? '1' : '0');
            Session::flash('success', 'A verification code has been sent to your email.');
            $this->redirectTo('/two-factor');
            return;
        }

        AuthService::login($user, $this->input('remember') === '1');

        if (AuthService::isAdmin()) {
            if (($settings['admin_login_alerts_enabled'] ?? '0') === '1') {
                try {
                    $this->mailerService()->sendAdminLoginAlert($user['fullname'], $_SERVER['REMOTE_ADDR'] ?? 'unknown');
                } catch (Throwable) {
                    // Ignore alert failures so login still succeeds.
                }
            }

            $this->redirectTo('/admin');
        }

        $this->redirectTo('/dashboard');
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): void
    {
        $this->guestOnly();
        $settings = (new Setting())->all();
        $registrationEnabled = ($settings['allow_registration'] ?? '1') === '1';

        $this->view('auth/register', [
            'title' => 'Create account',
            'registrationEnabled' => $registrationEnabled,
        ]);
    }

    /**
     * Create a user account and send verification email.
     */
    public function register(): void
    {
        $this->guestOnly();
        $this->validateCsrf('/register');
        $settings = (new Setting())->all();
        $registrationEnabled = ($settings['allow_registration'] ?? '1') === '1';

        if (!$registrationEnabled) {
            Session::flash('warning', 'Public registration is currently disabled.');
            $this->view('auth/register', [
                'title' => 'Create account',
                'registrationEnabled' => false,
            ]);
            return;
        }

        $validator = new Validator($this->request());
        $validator->validate([
            'fullname' => 'required|min:3|max:150',
            'username' => 'required|username|min:3|max:50',
            'email' => 'required|email|max:150',
            'phone' => 'required|max:30',
            'country' => 'required|max:100',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        $userModel = new User();

        if ($userModel->findByEmail(trim((string) $this->input('email')))) {
            $validator->add('email', 'This email address is already registered.');
        }

        if ($userModel->findByUsername(trim((string) $this->input('username')))) {
            $validator->add('username', 'This username is already taken.');
        }

        if ($validator->errors() !== []) {
            $this->view('auth/register', [
                'title' => 'Create account',
                'errors' => $validator->errors(),
                'old' => $this->safeOldInput(),
            ]);
            return;
        }

        $userId = $userModel->create([
            'fullname' => trim((string) $this->input('fullname')),
            'username' => trim((string) $this->input('username')),
            'email' => trim((string) $this->input('email')),
            'phone' => trim((string) $this->input('phone')),
            'country' => trim((string) $this->input('country')),
            'password' => (string) $this->input('password'),
        ]);

        $token = (new EmailVerificationService(Database::connection()))->createToken($userId);
        $notificationManager = new NotificationManager($this->config);

        try {
            $this->mailerService()->sendVerificationEmail((string) $this->input('email'), (string) $this->input('fullname'), $token);
            Session::flash('success', 'Account created. Check your email to verify your account.');
        } catch (Throwable) {
            Session::flash('warning', 'Account created, but verification email could not be sent. Check SMTP settings.');
        }

        $notificationManager->trigger('user.registered', [
            'user_id' => $userId,
            'fullname' => trim((string) $this->input('fullname')),
            'email' => trim((string) $this->input('email')),
        ]);

        $this->redirectTo('/login');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): void
    {
        $this->guestOnly();
        $this->view('auth/forgot-password', ['title' => 'Forgot password']);
    }

    /**
     * Send a password reset email when the account exists.
     */
    public function sendResetLink(): void
    {
        $this->guestOnly();
        $this->validateCsrf('/forgot-password');

        $validator = new Validator($this->request());
        $validator->validate(['email' => 'required|email']);

        if ($validator->errors() !== []) {
            $this->view('auth/forgot-password', [
                'title' => 'Forgot password',
                'errors' => $validator->errors(),
                'old' => ['email' => $this->input('email')],
            ]);
            return;
        }

        $user = (new User())->findByEmail(trim((string) $this->input('email')));

        if ($user) {
            $token = (new PasswordResetService(Database::connection()))->createToken((int) $user['id']);
            $notificationManager = new NotificationManager($this->config);

            try {
                $this->mailerService()->sendPasswordResetEmail($user['email'], $user['fullname'], $token);
            } catch (Throwable) {
                Session::flash('warning', 'Reset link created, but email could not be sent. Check SMTP settings.');
            }

            $notificationManager->trigger('password.reset_requested', [
                'user_id' => (int) $user['id'],
            ]);
        }

        Session::flash('success', 'If that email exists, a password reset link has been sent.');
        $this->redirectTo('/forgot-password');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(): void
    {
        $this->guestOnly();
        $this->view('auth/reset-password', [
            'title' => 'Reset password',
            'token' => $this->input('token'),
        ]);
    }

    /**
     * Show the two-factor verification form.
     */
    public function showTwoFactor(): void
    {
        $this->guestOnly();

        if (!Session::get('two_factor_user_id')) {
            $this->redirectTo('/login');
            return;
        }

        $this->view('auth/two-factor', [
            'title' => 'Two-factor verification',
        ]);
    }

    /**
     * Verify the two-factor code and complete login.
     */
    public function verifyTwoFactor(): void
    {
        $this->guestOnly();
        $this->validateCsrf('/two-factor');

        $userId = Session::get('two_factor_user_id');
        $code = trim((string) $this->input('two_factor_code'));

        if (!$userId || $code === '') {
            Session::flash('error', 'Enter the verification code.');
            $this->redirectTo('/two-factor');
            return;
        }

        $twoFactor = new TwoFactorService(Database::connection());

        if (!$twoFactor->verifyCode((int) $userId, $code)) {
            Session::flash('error', 'The code is invalid or has expired.');
            $this->redirectTo('/two-factor');
            return;
        }

        $user = (new User())->find((int) $userId);

        if (!$user) {
            Session::flash('error', 'Unable to complete sign in.');
            $this->redirectTo('/login');
            return;
        }

        $remember = Session::get('two_factor_remember') === '1';
        Session::forget('two_factor_user_id');
        Session::forget('two_factor_remember');

        AuthService::login($user, $remember);

        if (AuthService::isAdmin()) {
            $this->redirectTo('/admin');
            return;
        }

        $this->redirectTo('/dashboard');
    }

    /**
     * Helper to build the configured mailer for platform SMTP settings.
     */
    private function mailerService(): MailerService
    {
        return new MailerService($this->resolveMailConfig(), $this->config);
    }

    /**
     * Load SMTP settings from platform configuration, with env fallback.
     */
    private function resolveMailConfig(): array
    {
        $default = require APP_PATH . '/Config/mail.php';
        $settings = (new Setting())->all();

        return [
            'host' => $settings['mail_host'] ?? $default['host'],
            'port' => (int) ($settings['mail_port'] ?? $default['port']),
            'username' => $settings['mail_username'] ?? $default['username'],
            'password' => $settings['mail_password'] ?? $default['password'],
            'encryption' => $settings['mail_encryption'] ?? $default['encryption'],
            'from_address' => $settings['mail_from_address'] ?? $default['from_address'],
            'from_name' => $settings['mail_from_name'] ?? $default['from_name'],
            'admin_login_notification_address' => $settings['admin_login_notification_email'] ?? $default['admin_login_notification_address'],
            'admin_login_notification_message' => $settings['admin_login_notification_message'] ?? $default['admin_login_notification_message'],
        ];
    }

    public function resetPassword(): void
    {
        $this->guestOnly();
        $this->validateCsrf('/forgot-password');

        $validator = new Validator($this->request());
        $validator->validate([
            'token' => 'required',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        $resetService = new PasswordResetService(Database::connection());
        $reset = $resetService->findValidToken((string) $this->input('token'));

        if (!$reset) {
            $validator->add('token', 'This reset link is invalid or expired.');
        }

        if ($validator->errors() !== []) {
            $this->view('auth/reset-password', [
                'title' => 'Reset password',
                'errors' => $validator->errors(),
                'token' => $this->input('token'),
            ]);
            return;
        }

        (new User())->updatePassword((int) $reset['user_id'], (string) $this->input('password'));
        $resetService->markUsed((int) $reset['id']);

        (new NotificationManager($this->config))->sendUserNotificationWithEmail(
            (int) $reset['user_id'],
            'Password changed',
            'Your Instaweb password was updated successfully.',
            'Password changed',
            'password-changed',
            ['message' => 'Your Instaweb password was updated successfully.'],
            [
                'category' => 'security',
                'icon' => 'shield-check',
                'target_url' => '/profile',
            ]
        );

        Session::flash('success', 'Password updated. You can now sign in.');
        $this->redirectTo('/login');
    }

    /**
     * Verify a user's email address.
     */
    public function verifyEmail(): void
    {
        $verified = false;
        $token = (string) $this->input('token', '');

        if ($token !== '') {
            $verifiedUserId = (new EmailVerificationService(Database::connection()))->verifyToken($token);
            $verified = $verifiedUserId !== false;

            if ($verified && $verifiedUserId !== false) {
                (new NotificationManager($this->config))->sendUserNotificationWithEmail(
                    $verifiedUserId,
                    'Email verified',
                    'Your email address is now verified, and your account is active.',
                    'Email verified',
                    'verify-email',
                    ['message' => 'Your email address is now verified, and your account is active.'],
                    [
                        'category' => 'security',
                        'icon' => 'mail-check',
                        'target_url' => '/dashboard',
                    ]
                );
            }
        }

        $this->view('auth/verify-email', [
            'title' => 'Email verification',
            'verified' => $verified,
        ]);
    }

    /**
     * Log out the current user.
     */
    public function logout(): void
    {
        $this->validateCsrf('/login');
        AuthService::logout();
        Session::flash('success', 'You have been logged out.');
        $this->redirectTo('/login');
    }

    /**
     * Redirect signed-in users away from guest-only pages.
     */
    private function guestOnly(): void
    {
        if (AuthService::check()) {
            $this->redirectTo('/dashboard');
        }
    }

    /**
     * Validate CSRF token or redirect with an error message.
     */
    private function validateCsrf(string $redirectPath): void
    {
        if (!Csrf::validate($this->input('_csrf'))) {
            Session::flash('error', 'Your session expired. Please try again.');
            $this->redirectTo($redirectPath);
        }
    }

    /**
     * Return safe fields for form repopulation.
     */
    private function safeOldInput(): array
    {
        return [
            'fullname' => $this->input('fullname'),
            'username' => $this->input('username'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'country' => $this->input('country'),
        ];
    }
}
