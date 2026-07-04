<?php

namespace App\Core;

/**
 * Base controller with shared rendering, redirect, and request helpers.
 */
abstract class Controller
{
    protected array $config;
    protected array $request;

    public function __construct(array $config = [], array $request = [])
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Render a view and send it to the browser.
     */
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        echo View::render($view, array_merge($data, ['app' => $this->config]), $layout);
    }

    /**
     * Redirect the request to another URL and stop execution.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Redirect to a configured application path.
     */
    protected function redirectTo(string $path): void
    {
        $baseUrl = rtrim($this->config['base_url'] ?? '', '/');
        $target = $baseUrl . '/' . ltrim($path, '/');

        $this->redirect($target);
    }

    /**
     * Read a request input value with an optional fallback.
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * Return all request data for repopulating safe form fields.
     */
    protected function request(): array
    {
        return $this->request;
    }
}
