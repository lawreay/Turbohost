<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AIProviderInterface;
use App\Services\AI\AIRequest;
use App\Services\AI\AIResponse;

class GroqProvider implements AIProviderInterface
{
    public function supports(string $provider): bool
    {
        return strtolower($provider) === 'groq';
    }

    public function generate(AIRequest $request, array $config = []): AIResponse
    {
        $apiKey = (string) ($config['api_key'] ?? env('GROQ_API_KEY', ''));

        if ($apiKey === '') {
            return new AIResponse(false, $request->provider, '', [], 'Groq API key is not configured.');
        }

        $payload = [
            'model' => $request->model ?: ($config['model'] ?? 'llama-3.3-70b'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $request->systemPrompt ?: 'You are a helpful assistant.'
                ],
                [
                    'role' => 'user',
                    'content' => $request->prompt
                ]
            ],
        ];

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false || $httpCode >= 400) {
            return new AIResponse(false, $request->provider, '', [
                'http_code' => $httpCode,
                'curl_error' => $curlError,
            ], 'Groq request failed.');
        }

        $decoded = json_decode($responseBody, true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';

        return new AIResponse(true, $request->provider, (string) $content, $decoded);
    }
}
