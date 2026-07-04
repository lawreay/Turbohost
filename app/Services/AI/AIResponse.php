<?php

namespace App\Services\AI;

class AIResponse
{
    public function __construct(
        private bool $success,
        private string $provider,
        private string $content = '',
        private array $data = [],
        private string $error = ''
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'provider' => $this->provider,
            'content' => $this->content,
            'data' => $this->data,
            'error' => $this->error,
        ];
    }
}
