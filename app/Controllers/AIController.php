<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AI\AIManager;

class AIController extends Controller
{
    public function generate(): void
    {
        $input = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $payload = is_array($input) ? $input : [];

        $manager = new AIManager([
            'default_provider' => 'groq',
            'providers' => [
                'groq' => [
                    'model' => 'llama-3.3-70b',
                    'api_key' => env('GROQ_API_KEY', ''),
                ],
            ],
        ]);

        $response = $manager->generate($payload);

        header('Content-Type: application/json');
        echo json_encode($response->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
