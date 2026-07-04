<?php

namespace App\Services;

/**
 * Builds canonical public URLs for published static website projects.
 */
class PublicSiteUrlService
{
    /**
     * Return the app-relative public site path.
     */
    public function relativePath(int $projectId, string $slug): string
    {
        return 'sites/' . $projectId . '/' . rawurlencode($slug) . '/';
    }

    /**
     * Return the absolute public site URL using the configured app base URL.
     */
    public function absoluteUrl(string $baseUrl, int $projectId, string $slug): string
    {
        return rtrim($baseUrl, '/') . '/' . $this->relativePath($projectId, $slug);
    }

    /**
     * Add a canonical public_url value to a website row.
     */
    public function withPublicUrl(array $website, string $baseUrl): array
    {
        $website['public_url'] = $this->absoluteUrl(
            $baseUrl,
            (int) ($website['id'] ?? 0),
            (string) ($website['slug'] ?? '')
        );

        return $website;
    }
}
