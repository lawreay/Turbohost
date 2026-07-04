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
}
