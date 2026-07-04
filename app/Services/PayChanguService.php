<?php

namespace App\Services;

use RuntimeException;

/**
 * Small API client for PayChangu Standard Checkout and transaction verification.
 */
class PayChanguService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct(string $secretKey, string $baseUrl = 'https://api.paychangu.com')
    {
        $this->secretKey = trim($secretKey);
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Create a hosted checkout session and return PayChangu's decoded response.
     */
    public function initiatePayment(array $payload): array
    {
        return $this->request('POST', '/payment', $payload);
    }

    /**
     * Verify a transaction by the generated transaction reference.
     */
    public function verifyPayment(string $txRef): array
    {
        return $this->request('GET', '/verify-payment/' . rawurlencode($txRef));
    }

    /**
     * Send an authenticated JSON request to PayChangu.
     */
    private function resolveCaBundlePath(): ?string
    {
        $candidates = [];

        foreach (['SSL_CERT_FILE', 'CURL_CA_BUNDLE'] as $envKey) {
            $envValue = getenv($envKey);
            if (is_string($envValue) && trim($envValue) !== '') {
                $candidates[] = trim($envValue);
            }
        }

        $phpCainfo = trim((string) ini_get('curl.cainfo'));
        if ($phpCainfo !== '') {
            $candidates[] = $phpCainfo;
        }

        $phpOpensslCafile = trim((string) ini_get('openssl.cafile'));
        if ($phpOpensslCafile !== '') {
            $candidates[] = $phpOpensslCafile;
        }

        $baseDir = dirname(__DIR__, 2);
        $candidates[] = $baseDir . '/vendor/composer/ca-bundle/res/cacert.pem';
        $candidates[] = $baseDir . '/.venv/Lib/site-packages/pip/_vendor/certifi/cacert.pem';
        $candidates[] = $baseDir . '/.venv/Lib/site-packages/pip-26.1.2.dist-info/licenses/src/pip/_vendor/certifi/cacert.pem';

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        if ($this->secretKey === '') {
            throw new RuntimeException('PayChangu secret key is not configured.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for PayChangu payments.');
        }

        $ch = curl_init($this->baseUrl . $path);
        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->secretKey,
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $caBundle = $this->resolveCaBundlePath();
        if ($caBundle !== null) {
            curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
        }

        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
        }

        $response = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new RuntimeException('Unable to reach PayChangu: ' . $error);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('PayChangu returned an invalid response.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = is_string($decoded['message'] ?? null) ? $decoded['message'] : 'PayChangu request failed.';
            throw new RuntimeException($message);
        }

        return $decoded;
    }
}
