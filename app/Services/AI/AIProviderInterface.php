<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    public function supports(string $provider): bool;

    public function generate(AIRequest $request, array $config = []): AIResponse;
}
