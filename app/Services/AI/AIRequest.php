<?php

namespace App\Services\AI;

class AIRequest
{
    public function __construct(
        public string $prompt,
        public string $provider = 'groq',
        public string $model = '',
        public string $systemPrompt = '',
        public array $metadata = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'prompt' => $this->prompt,
            'provider' => $this->provider,
            'model' => $this->model,
            'system_prompt' => $this->systemPrompt,
            'metadata' => $this->metadata,
        ];
    }
}
