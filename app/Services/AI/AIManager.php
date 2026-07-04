<?php

namespace App\Services\AI;

use App\Services\AI\Providers\GroqProvider;

class AIManager
{
    /** @var array<string, AIProviderInterface> */
    private array $providers;

    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->providers = [
            'groq' => new GroqProvider(),
        ];
    }

    public function generate(array $input): AIResponse
    {
        $providerName = strtolower((string) ($input['provider'] ?? $this->config['default_provider'] ?? 'groq'));

        if (!isset($this->providers[$providerName])) {
            return new AIResponse(false, $providerName, '', [], 'Provider is not implemented yet.');
        }

        $request = new AIRequest(
            prompt: (string) ($input['prompt'] ?? ''),
            provider: $providerName,
            model: (string) ($input['model'] ?? $this->config['providers'][$providerName]['model'] ?? ''),
            systemPrompt: (string) ($input['system_prompt'] ?? ''),
            metadata: $input['metadata'] ?? []
        );

        $providerConfig = $this->config['providers'][$providerName] ?? [];

        return $this->providers[$providerName]->generate($request, $providerConfig);
    }

    public function availableProviders(): array
    {
        return array_keys($this->providers);
    }
}
