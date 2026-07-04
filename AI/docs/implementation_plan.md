# TurboHost AI Implementation Plan

## 1. Goal
Turn the current AI folder from a scaffold into a working, modular AI subsystem for TurboHost.

## 2. Current State
The existing AI folder contains:
- documentation and architecture notes
- a sample Laravel controller stub
- a Node.js example backend
- a simple demo UI

What is missing is the real runtime layer:
- provider manager
- provider adapters
- secure configuration storage
- BYOK support
- memory layer
- orchestration engine
- logging and monitoring

## 3. Recommended Architecture

### Core components
- AI Manager: single backend entry point for all AI requests
- Provider Adapter Layer: abstract provider-specific logic
- Provider Settings Store: admin-managed settings per provider
- User Credentials Store: encrypted user-provided API keys
- Memory Layer: short-term and long-term context storage
- Tool Registry: callable tools and workflows
- Orchestration Engine: decides whether to answer, call a tool, or fetch memory

## 4. Phased Implementation Plan

### Phase 0 — Architecture foundation
- Define the AI request contract
- Define provider abstraction
- Define security rules
- Finalize provider support list

### Phase 1 — MVP AI core
- Build one backend endpoint for AI generation
- Implement Groq as the first provider
- Normalize provider responses into one format
- Add request logging

### Phase 2 — Provider configuration
- Add admin settings for provider enable/disable
- Add default provider selection
- Add provider model, token, and temperature settings
- Add fallback priority

### Phase 3 — BYOK support
- Allow users to save provider API keys securely
- Encrypt credentials in storage
- Use the user key only for that user’s requests
- Restrict exposure to the browser

### Phase 4 — Memory and context
- Store short-term conversation history
- Store long-term user memory
- Retrieve relevant context when building prompts

### Phase 5 — Tools and orchestration
- Add tool registry
- Allow the AI to call tools when appropriate
- Add permission checks
- Add workflow routing logic

### Phase 6 — Production hardening
- Add retries and fallback
- Add rate limiting and usage quotas
- Add health checks and error reporting
- Add test coverage

## 5. Suggested Provider Modes
- Server Managed: use platform API keys
- BYOK: user provides their own API key
- Unavailable: disabled or hidden

## 6. Suggested Database Objects
- ai_providers
- ai_provider_settings
- ai_requests
- ai_user_credentials
- ai_memory
- ai_tool_logs

## 7. MVP Milestone
The first useful milestone should be:
- one working AI endpoint
- one provider working end-to-end
- provider settings configurable in admin
- request logging enabled
- basic UI integration

## 8. Security Rules
- Never expose API keys in the browser
- Encrypt stored user keys
- Validate provider credentials before use
- Rate limit requests per user
- Log AI usage per provider

## 9. Recommended Implementation Order
1. AI Manager core
2. Groq adapter
3. Provider settings storage
4. Admin UI controls
5. BYOK support
6. Memory layer
7. Tool orchestration
8. Production hardening
