<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Setting;
use App\Services\PlanService;

/**
 * Displays public marketing pages for TurboHostMw.
 */
class MarketingController extends Controller
{
    /**
     * Show the landing page.
     */
    public function home(): void
    {
        $settings = (new Setting())->all();
        $pageTitle = trim((string) ($settings['browser_title'] ?? '')) ?: ($settings['homepage_hero_title'] ?? 'TurboHostMw - Website Hosting Made Simple');

        $this->view('marketing/home', [
            'title' => $pageTitle,
            'settings' => $settings,
        ]);
    }

    /**
     * Show the feature overview page.
     */
    public function features(): void
    {
        $this->view('marketing/features', ['title' => 'Features']);
    }

    /**
     * Show pricing plans.
     */
    public function pricing(): void
    {
        $settings = (new Setting())->all();
        $planService = new PlanService($settings);

        $this->view('marketing/pricing', [
            'title' => 'Pricing',
            'settings' => $settings,
            'planDetails' => $planService->plans(),
        ]);
    }

    /**
     * Show company information.
     */
    public function about(): void
    {
        $this->view('marketing/about', ['title' => 'About TurboHostMw']);
    }

    /**
     * Show contact information and inquiry form.
     */
    public function contact(): void
    {
        $this->view('marketing/contact', ['title' => 'Contact']);
    }

    /**
     * Handle contact form submissions from the public site.
     */
    public function sendContact(): void
    {
        $name = trim((string) $this->input('name'));
        $email = trim((string) $this->input('email'));
        $subject = trim((string) $this->input('subject')) ?: 'Website inquiry';
        $message = trim((string) $this->input('message'));

        if (!\App\Core\Csrf::validate($this->input('_csrf'))) {
            Session::flash('error', 'Session expired, please try again.');
            $this->redirectTo('/contact');
        }

        if ($name === '' || $email === '' || $message === '') {
            Session::flash('error', 'Please provide your name, email and a message.');
            $this->redirectTo('/contact');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please provide a valid email address.');
            $this->redirectTo('/contact');
        }

        $settings = (new Setting())->all();
        $adminEmail = $settings['admin_login_notification_email'] ?? $settings['mail_from_address'] ?? 'no-reply@turbohostmw.com';

        $mailer = new \App\Services\MailerService([ // mail config will be resolved inside NotificationService normally
            'host' => $settings['mail_host'] ?? '',
            'port' => (int) ($settings['mail_port'] ?? 587),
            'username' => $settings['mail_username'] ?? '',
            'password' => $settings['mail_password'] ?? '',
            'encryption' => $settings['mail_encryption'] ?? '',
            'from_address' => $settings['mail_from_address'] ?? '',
            'from_name' => $settings['mail_from_name'] ?? '',
        ], $this->config);

        $html = '<h2>Website contact form</h2>' .
            '<p><strong>From:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ' &lt;' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '&gt;</p>' .
            '<p><strong>Subject:</strong> ' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>' .
            '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>';

        $text = "From: {$name} <{$email}>\nSubject: {$subject}\n\n" . $message;

        try {
            $sent = $mailer->send($adminEmail, 'Site admin', $subject, $html, $text);
        } catch (\Throwable $e) {
            $sent = false;
        }

        if ($sent) {
            Session::flash('success', 'Your message was sent — we will reply as soon as we can.');
        } else {
            Session::flash('error', 'Unable to send message at this time. Please try again later.');
        }

        $this->redirectTo('/contact');
    }

    /**
     * Show frequently asked questions.
     */
    public function faq(): void
    {
        $this->view('marketing/faq', ['title' => 'FAQ']);
    }
}
