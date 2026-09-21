<?php

namespace App\Core;

use App\Services\AuthService;
use App\Services\SitemapGenerator;

/**
 * Application bootstrapper responsible for loading configuration and routing.
 */
class App
{
    /**
     * Start the application and dispatch the current request.
     */
    public function run(): void
    {
        self::sendSecurityHeaders();
        $config = require __DIR__ . '/../Config/app.php';
        $routes = require __DIR__ . '/../Config/routes.php';

        Session::start();
        AuthService::loginFromRememberCookie();

        try {
            (new SitemapGenerator($config))->ensureFileExists();
        } catch (\Throwable $exception) {
            // Sitemap creation should not prevent the application from running.
        }

        Router::dispatch(
            $_SERVER['REQUEST_URI'] ?? '/',
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $routes,
            $config
        );
    }

    /**
     * Prevent authenticated and generated pages from being cached by browsers or proxies.
     */
    private static function sendSecurityHeaders(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        if (Session::isSecureRequest()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
