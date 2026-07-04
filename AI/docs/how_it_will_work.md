# How It Will Work

## Overview
The AI system for TurboHost will work as a centralized backend service that handles all AI requests from the platform.

## Flow
1. The user sends a prompt from the UI.
2. The request goes to the TurboHost backend.
3. The backend passes the request to an AI manager.
4. The AI manager selects the correct provider.
5. The provider sends the request to the AI model.
6. The response is returned to the user through the app.

## Main Components
- UI: where the user enters prompts
- Backend API: receives requests from the UI
- AI Manager: decides which provider to use
- Provider Adapters: connect to Groq, Gemini, OpenAI, OpenRouter, and others
- Settings Layer: controls enabled providers, default provider, and modes
- Logging Layer: records requests, errors, and usage

## Provider Modes
The system will support three modes:

- Server Managed: the platform uses its own API key
- BYOK: the user provides their own API key
- Disabled: the provider is unavailable

## Security
- API keys will never be exposed in the browser
- User keys will be encrypted
- Requests will be handled server-side
- Usage will be logged and rate-limited

## Example Logic
When a user submits a prompt:
- the system checks the selected provider
- if the provider is enabled, it sends the request
- if the provider fails, it can fall back to another provider
- the final response is returned to the user

## Result
This makes the AI subsystem modular, secure, and scalable for TurboHost.
