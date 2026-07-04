
# TurboHost AI Integration System (Free AI Providers)

## 1. Overview
This document explains how to integrate multiple FREE AI providers into TurboHost using a unified AI engine.

Supported providers:
- Groq (OpenAI-compatible)
- OpenRouter (OpenAI-compatible)
- Google Gemini (separate SDK)
- Hugging Face Inference API (REST)
- Together AI (OpenAI-compatible)

---

## 2. System Architecture

```
Client UI
   |
   v
TurboHost Backend (AI Manager)
   |
   +----------------------+
   |                      |
OpenAI-Compatible     Custom SDK
(Groq, OpenRouter)    (Gemini)
   |
   v
AI Providers
```

---

## 3. Core Idea

All AI requests go through ONE function:

```php
generateAI(provider, model, prompt)
```

Backend decides how to route request.

---

## 4. Database Design (JSON Style)

```json
{
  "providers": {
    "groq": {
      "type": "openai",
      "base_url": "https://api.groq.com/openai/v1",
      "enabled": true
    },
    "openrouter": {
      "type": "openai",
      "base_url": "https://openrouter.ai/api/v1",
      "enabled": true
    },
    "gemini": {
      "type": "google",
      "enabled": true
    }
  }
}
```

---

## 5. AI Manager (Core Logic)

```php
class AIManager {

    public static function generate($provider, $model, $prompt) {

        switch ($provider) {

            case "groq":
            case "openrouter":
            case "together":

                return self::openAICompatible($provider, $model, $prompt);

            case "gemini":
                return self::gemini($model, $prompt);

            case "huggingface":
                return self::huggingFace($model, $prompt);

            default:
                throw new Exception("Unknown provider");
        }
    }
}
```

---

## 6. OpenAI-Compatible Integration (Groq / OpenRouter)

### Groq Example (Node.js)

```javascript
import OpenAI from "openai";

const client = new OpenAI({
  apiKey: process.env.GROQ_API_KEY,
  baseURL: "https://api.groq.com/openai/v1"
});

const response = await client.chat.completions.create({
  model: "llama-3.3-70b",
  messages: [
    { role: "user", content: "Generate a login page" }
  ]
});

console.log(response.choices[0].message.content);
```

---

### OpenRouter (fetch)

```typescript
fetch("https://openrouter.ai/api/v1/chat/completions", {
  method: "POST",
  headers: {
    Authorization: "Bearer YOUR_KEY",
    "Content-Type": "application/json"
  },
  body: JSON.stringify({
    model: "openai/gpt-4o",
    messages: [
      { role: "user", content: "Explain AI" }
    ]
  })
});
```

---

## 7. Gemini Integration

```javascript
import { GoogleGenAI } from "@google/genai";

const ai = new GoogleGenAI({
  apiKey: process.env.GEMINI_API_KEY
});

const result = await ai.models.generateContent({
  model: "gemini-1.5-flash",
  contents: "Create a PHP login system"
});

console.log(result.text);
```

---

## 8. Hugging Face Integration

```php
$apiKey = "hf_xxx";

$data = [
  "model" => "Qwen/Qwen2.5-Coder-7B-Instruct",
  "messages" => [
    ["role" => "user", "content" => "Write a login page"]
  ]
];

$ch = curl_init("https://router.huggingface.co/v1/chat/completions");

curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json"
  ],
  CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

---

## 9. Provider Modes

| Mode | Meaning |
|------|--------|
| Server Managed | You pay & control API key |
| BYOK | User provides API key |
| Disabled | Hidden or blocked |

---

## 10. Request Flow

```
User Prompt
   |
   v
AI Manager
   |
   +--> Groq (fast free)
   +--> OpenRouter (fallback)
   +--> Gemini (backup)
```

---

## 11. Security Rules

- Never expose API keys in frontend
- Always encrypt BYOK keys
- Store keys in environment variables
- Rate limit requests per user
- Log AI usage per provider

---

## 12. Recommended Stack

Best FREE stack:

- Groq (primary)
- OpenRouter (secondary)
- Gemini (advanced reasoning)

---

## 13. Future Expansion

You can later add:
- Image generation APIs
- Voice AI
- Code execution sandbox
- Auto website builder

---

## 14. Folder Structure

```
/ai
  AIManager.php
  providers/
    Groq.php
    OpenRouter.php
    Gemini.php
    HuggingFace.php
  config.json
```

---

## 15. Final Idea

TurboHost becomes:

"AI-powered hosting + AI code generator + BYOK platform"

