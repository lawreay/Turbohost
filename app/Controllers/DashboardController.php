<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ClientDashboardRepository;
use App\Models\User;
use App\Models\Website;
use App\Services\AuthService;
use App\Services\MailerService;
use App\Services\PlanService;
use App\Services\PublicSiteUrlService;

/**
 * Displays the authenticated user dashboard.
 */
class DashboardController extends Controller
{
    /**
     * Show the client dashboard for signed-in users.
     */
    public function index(): void
    {
        if (!AuthService::check()) {
            $this->redirectTo('/login');
        }

        $userId = AuthService::id();
        $repository = new ClientDashboardRepository();
        $websiteModel = new Website();
        $urlService = new PublicSiteUrlService();
        $websites = array_map(
            fn (array $website): array => $urlService->withPublicUrl($website, (string) ($this->config['base_url'] ?? '')),
            $repository->websites($userId)
        );

        $subscription = $repository->subscription($userId);
        $plan = $websiteModel->userPlan($userId);
        $planService = new PlanService();
        $storageLimit = $plan === 'premium' ? $planService->premiumStorageBytes() : $planService->freeStorageBytes();

        $this->view('dashboard/index', [
            'title' => 'Client Dashboard',
            'user' => $repository->user($userId),
            'stats' => $repository->stats($userId),
            'subscription' => $subscription,
            'plan' => $plan,
            'planDetails' => $planService->plans(),
            'freeStorageBytes' => $planService->freeStorageBytes(),
            'storageLimitBytes' => $storageLimit,
            'websites' => $websites,
            'recentFiles' => $repository->recentFiles($userId),
            'notifications' => $repository->notifications($userId),
            'isAdmin' => AuthService::isAdmin(),
            'displayName' => Session::get('user_name', 'there'),
            'active' => 'dashboard',
        ], 'layouts/dashboard');
    }

    public function profile(): void
    {
        if (!AuthService::check()) {
            $this->redirectTo('/login');
        }

        $userId = AuthService::id();
        $repository = new ClientDashboardRepository();
        $user = $repository->user($userId);

        if (!$user) {
            Session::flash('error', 'Unable to load profile information.');
            $this->redirectTo('/dashboard');
        }

        if (AuthService::isAdmin()) {
            $this->view('admin/profile', [
                'title' => 'My Profile',
                'user' => $user,
                'active' => 'profile',
            ], 'layouts/admin');
            return;
        }

        $this->view('dashboard/profile', [
            'title' => 'My Profile',
            'user' => $user,
            'isAdmin' => false,
            'displayName' => Session::get('user_name', 'there'),
            'active' => 'profile',
        ], 'layouts/dashboard');
    }

    /**
     * Show the notifications page for the signed-in user.
     */
    public function notifications(): void
    {
        if (!AuthService::check()) {
            $this->redirectTo('/login');
        }

        $userId = AuthService::id();
        $repository = new ClientDashboardRepository();
        $user = $repository->user($userId);

        if (!$user) {
            Session::flash('error', 'Unable to load notifications.');
            $this->redirectTo('/dashboard');
            return;
        }

        $this->view('dashboard/notifications', [
            'title' => 'Notifications',
            'user' => $user,
            'notifications' => $repository->notifications($userId, 20),
            'displayName' => Session::get('user_name', 'there'),
            'isAdmin' => false,
            'active' => 'notifications',
        ], 'layouts/dashboard');
    }

    /**
     * Handle profile updates (name, phone, country, bio, avatar).
     */
    public function updateProfile(): void
    {
        if (!AuthService::check()) {
            $this->redirectTo('/login');
        }

        if (!Csrf::validate($this->input('_csrf'))) {
            Session::flash('error', 'Your session expired. Please try again.');
            $this->redirectTo('/profile');
        }

        $userId = AuthService::id();
        $validator = new Validator($this->request());
        $validator->validate([
            'fullname' => 'required|min:3|max:150',
            'phone' => 'max:30',
            'country' => 'max:100',
            'bio' => 'max:500',
        ]);

        if ($validator->errors() !== []) {
            Session::flash('error', implode(' ', $validator->messages()));
            $this->redirectTo('/profile');
            return;
        }

        $userModel = new User();

        // Handle avatar upload if present
        if (!empty($_FILES['avatar']['name'])) {
            $file = $_FILES['avatar'];
            $filename = basename($file['name']);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];

            if (!in_array($extension, $allowed, true)) {
                Session::flash('error', 'Avatar file type is not allowed.');
                $this->redirectTo('/profile');
                return;
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                Session::flash('error', 'Avatar is too large (max 2MB).');
                $this->redirectTo('/profile');
                return;
            }

            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', pathinfo($filename, PATHINFO_FILENAME));
            $outputExtension = 'webp';
            $finalName = $safeName . '.' . $outputExtension;
            $avatarsPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';

            if (!is_dir($avatarsPath)) {
                mkdir($avatarsPath, 0755, true);
            }

            $targetPath = $avatarsPath . DIRECTORY_SEPARATOR . $finalName;

            // Convert to WebP if possible
            if (!$this->convertImageToWebp($file['tmp_name'], $targetPath, $extension)) {
                Session::flash('error', 'Unable to process avatar image.');
                $this->redirectTo('/profile');
                return;
            }

            $userModel->updateProfile($userId, ['avatar' => 'uploads/avatars/' . $finalName]);
            Session::put('user_name', $this->input('fullname') ?: Session::get('user_name'));
        }

        // Update other fields
        $userModel->updateProfile($userId, [
            'fullname' => trim((string) $this->input('fullname')),
            'phone' => trim((string) $this->input('phone')),
            'country' => trim((string) $this->input('country')),
            'bio' => trim((string) $this->input('bio')),
        ]);

        Session::flash('success', 'Profile updated.');
        $this->redirectTo('/profile');
    }

    /**
     * Change the signed-in user's password.
     */
    public function changePassword(): void
    {
        if (!AuthService::check()) {
            $this->redirectTo('/login');
        }

        if (!Csrf::validate($this->input('_csrf'))) {
            Session::flash('error', 'Your session expired. Please try again.');
            $this->redirectTo('/profile');
        }

        $userId = AuthService::id();
        $current = (string) $this->input('current_password');
        $new = (string) $this->input('new_password');
        $confirm = (string) $this->input('confirm_password');

        if ($new === '' || strlen($new) < 8 || $new !== $confirm) {
            Session::flash('error', 'Password validation failed. Use at least 8 characters and ensure confirmation matches.');
            $this->redirectTo('/profile');
            return;
        }

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user || !password_verify($current, $user['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            $this->redirectTo('/profile');
            return;
        }

        $userModel->updatePassword($userId, $new);
        Session::flash('success', 'Password updated.');
        $this->redirectTo('/profile');
    }

    /**
     * Start an email change flow: verify current password, send confirmation to new address.
     */
    public function changeEmail(): void
    {
        if (!AuthService::check()) {
            $this->redirectTo('/login');
        }

        if (!Csrf::validate($this->input('_csrf'))) {
            Session::flash('error', 'Your session expired. Please try again.');
            $this->redirectTo('/profile');
        }

        $userId = AuthService::id();
        $current = (string) $this->input('current_password');
        $newEmail = trim((string) $this->input('new_email'));

        if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Enter a valid email address.');
            $this->redirectTo('/profile');
            return;
        }

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user || !password_verify($current, $user['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            $this->redirectTo('/profile');
            return;
        }

        // Ensure new email not used
        if ($userModel->findByEmail($newEmail)) {
            Session::flash('error', 'That email is already in use.');
            $this->redirectTo('/profile');
            return;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new \DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO email_change_requests (user_id, new_email, token_hash, expires_at) VALUES (:user_id, :new_email, :token_hash, :expires_at)');
        $stmt->execute(['user_id' => $userId, 'new_email' => $newEmail, 'token_hash' => $tokenHash, 'expires_at' => $expiresAt]);

        try {
            $mailer = new MailerService((require APP_PATH . '/Config/mail.php'), $this->config);
            $confirmUrl = rtrim(($this->config['base_url'] ?? ''), '/') . '/profile/confirm-email-change?token=' . urlencode($token);
            $body = "<p>Please confirm your requested email change by clicking the link below:</p><p><a href=\"{$confirmUrl}\">Confirm email change</a></p>";
            $mailer->send($newEmail, $user['fullname'], 'Confirm your email change', $body, strip_tags($body));
            Session::flash('success', 'A confirmation message has been sent to the new email address.');
        } catch (\Throwable) {
            Session::flash('warning', 'Email created but confirmation could not be sent.');
        }

        $this->redirectTo('/profile');
    }

    /**
     * Confirm email change using token in query string.
     */
    public function confirmEmailChange(): void
    {
        $token = (string) $this->input('token', '');
        $verified = false;

        if ($token !== '') {
            $tokenHash = hash('sha256', $token);
            $db = Database::connection();
            $stmt = $db->prepare('SELECT id, user_id, new_email FROM email_change_requests WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
            $stmt->execute(['token_hash' => $tokenHash]);
            $req = $stmt->fetch();

            if ($req) {
                $db->beginTransaction();
                $update = $db->prepare('UPDATE users SET email = :email, email_verified = TRUE WHERE id = :id');
                $update->execute(['email' => $req['new_email'], 'id' => $req['user_id']]);

                $mark = $db->prepare('UPDATE email_change_requests SET used_at = NOW() WHERE id = :id');
                $mark->execute(['id' => $req['id']]);
                $db->commit();
                $verified = true;
            }
        }

        $this->view('auth/verify-email', [
            'title' => 'Email change',
            'verified' => $verified,
        ]);
    }

    /**
     * Start account deactivation flow by sending confirmation to current email.
     */
    public function requestDeactivation(): void
    {
        if (!AuthService::check()) {
            $this->redirectTo('/login');
        }

        if (!Csrf::validate($this->input('_csrf'))) {
            Session::flash('error', 'Your session expired. Please try again.');
            $this->redirectTo('/profile');
        }

        $userId = AuthService::id();
        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user) {
            Session::flash('error', 'Unable to find your account.');
            $this->redirectTo('/profile');
            return;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new \DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO deactivation_requests (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)');
        $stmt->execute(['user_id' => $userId, 'token_hash' => $tokenHash, 'expires_at' => $expiresAt]);

        try {
            $mailer = new MailerService((require APP_PATH . '/Config/mail.php'), $this->config);
            $confirmUrl = rtrim(($this->config['base_url'] ?? ''), '/') . '/profile/confirm-deactivation?token=' . urlencode($token);
            $body = "<p>Please confirm account deactivation by clicking the link below:</p><p><a href=\"{$confirmUrl}\">Deactivate account</a></p>";
            $mailer->send($user['email'], $user['fullname'], 'Confirm account deactivation', $body, strip_tags($body));
            Session::flash('success', 'A confirmation link has been sent to your email.');
        } catch (\Throwable) {
            Session::flash('warning', 'Request recorded but email could not be sent.');
        }

        $this->redirectTo('/profile');
    }

    /**
     * Confirm account deactivation and suspend the account.
     */
    public function confirmDeactivation(): void
    {
        $token = (string) $this->input('token', '');
        $done = false;

        if ($token !== '') {
            $tokenHash = hash('sha256', $token);
            $db = Database::connection();
            $stmt = $db->prepare('SELECT id, user_id FROM deactivation_requests WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
            $stmt->execute(['token_hash' => $tokenHash]);
            $req = $stmt->fetch();

            if ($req) {
                $db->beginTransaction();
                $update = $db->prepare("UPDATE users SET account_status = 'suspended' WHERE id = :id");
                $update->execute(['id' => $req['user_id']]);

                $mark = $db->prepare('UPDATE deactivation_requests SET used_at = NOW() WHERE id = :id');
                $mark->execute(['id' => $req['id']]);
                $db->commit();

                // If the currently signed-in user is the same, log them out
                if (AuthService::id() === (int) $req['user_id']) {
                    AuthService::logout();
                }

                $done = true;
            }
        }

        $this->view('auth/verify-email', [
            'title' => 'Account deactivation',
            'verified' => $done,
        ]);
    }

    /**
     * Image conversion helper copied for avatar handling.
     */
    private function convertImageToWebp(string $sourcePath, string $targetPath, string $extension): bool
    {
        if (extension_loaded('imagick')) {
            try {
                $image = new \Imagick($sourcePath);
                $image->setImageFormat('webp');
                $image->setImageCompressionQuality(100);
                $image->setOption('webp:lossless', 'true');
                $image->writeImage($targetPath);
                $image->clear();
                $image->destroy();
                return is_file($targetPath);
            } catch (\Throwable $e) {
                return false;
            }
        }

        if (!function_exists('imagewebp')) {
            return false;
        }

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $resource = @imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $resource = @imagecreatefrompng($sourcePath);
                if ($resource) {
                    imagealphablending($resource, false);
                    imagesavealpha($resource, true);
                }
                break;
            case 'gif':
                $resource = @imagecreatefromgif($sourcePath);
                if ($resource) {
                    imagealphablending($resource, false);
                    imagesavealpha($resource, true);
                }
                break;
            case 'bmp':
                $resource = @imagecreatefrombmp($sourcePath);
                break;
            default:
                $resource = null;
        }

        if (!$resource) {
            return false;
        }

        $success = imagewebp($resource, $targetPath, 100);
        imagedestroy($resource);
        return $success && is_file($targetPath);
    }
}
