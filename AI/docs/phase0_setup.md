# Phase 0 — AI Foundation Setup

## Objective
Establish the initial AI foundation inside TurboHost by creating a backend entry point, a provider abstraction layer, and a normalized response flow.

## What Was Added
- AI manager service for centralized request handling
- request and response DTOs for consistent AI communication
- a Groq provider adapter as the first implementation
- an AI controller endpoint for incoming requests
- route registration for the new generation endpoint at `/api/ai/generate`

## Request Format
Send a JSON payload like this:

```json
{
  "prompt": "Write a short welcome message for a hosting platform",
  "provider": "groq",
  "model": "llama-3.3-70b",
  "system_prompt": "You are a helpful assistant."
}
```

## Expected Behavior
- If the Groq API key is configured, the request is forwarded to Groq.
- If the key is missing, the system returns a structured error response.
- If the provider fails, the system returns a safe, structured error without exposing raw provider details to the client.
- All responses are normalized through the AI response object.

### Example: Missing API Key

```json
{
  "success": false,
  "error": "GROQ_API_KEY_NOT_CONFIGURED"
}
```

### Example: Provider Failure
- return a structured error response
- never expose raw API errors to the client

---

## Design Principles

- Providers are replaceable modules
- The AI Manager is the only entry point
- All responses must be normalized
- Controllers must not call providers directly
- The system must fail safely and predictably

---

## Phase 1 Preview

### Provider Expansion
- admin-configurable providers
- model selection UI
- default provider fallback support

### Logging Layer
- store every AI request
- track latency and usage

### BYOK Preparation
- encrypted key storage
- user-level provider switching

---

## Summary
Phase 0 lays the groundwork for a modular, secure, and scalable AI subsystem in TurboHost by introducing the core request flow, provider abstraction, and safe error handling.
