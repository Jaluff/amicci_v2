# AGENTS.md

## Project Overview
Enterprise PHP application built with:
- PHP 8.3+
- Laravel 11
- Laravel Sail
- MySQL
- Redis
- Docker-based development

This is a large long-lived codebase.
Prioritize stability, predictability, maintainability, and minimal changes.

---

# CORE PRINCIPLES

## 1. Minimal Changes
Only modify what is necessary for the requested task.

Avoid:
- unnecessary refactors
- renaming files/classes
- formatting unrelated files
- moving folders
- architecture rewrites

Preserve existing patterns unless explicitly instructed otherwise.

---

## 2. Read Before Writing
Before making changes:
1. Read all related files
2. Understand existing conventions
3. Search for similar implementations
4. Reuse patterns already present

Never invent new architecture if the project already has one.

---

## 3. Controllers Must Stay Thin
Controllers should:
- validate requests
- authorize actions
- delegate logic to Services/Actions

Avoid:
- business logic in controllers
- large query blocks
- data transformation logic

---

## 4. Business Logic
Business logic belongs in:
- Services
- Actions
- Domain classes

Avoid duplication.

If similar logic exists elsewhere:
- reuse it
- extract carefully
- do not create parallel implementations

---

## 5. Database Rules

### Never:
- modify old migrations
- rename columns casually
- change production schemas without migration
- introduce N+1 queries

### Always:
- use eager loading when appropriate
- prefer transactions for critical writes
- keep queries readable
- analyze performance impact

---

## 6. Eloquent Conventions

Prefer:
- query scopes
- relationships
- repositories only when complexity justifies them

Avoid:
- raw SQL unless necessary
- duplicated query logic
- fat models with unrelated responsibilities

---

## 7. Validation
Use:
- Form Requests
- DTOs when payloads are large/complex

Never trust frontend validation alone.

---

## 8. Frontend/API Rules

For APIs:
- preserve response formats
- avoid breaking existing clients
- maintain backwards compatibility

If changing response shape:
- explain impact clearly

---

## 9. Error Handling

Never silently swallow exceptions.

Prefer:
- domain-specific exceptions
- clear logging
- actionable error messages

---

## 10. Testing Requirements

Before considering work complete:
- run relevant tests
- create tests for bug fixes
- avoid changing unrelated snapshots/tests

Prefer targeted tests over full-suite runs unless necessary.

---

# DEVELOPMENT WORKFLOW

## Required Process

### Step 1 — Analyze
Explain:
- what is happening
- root cause
- affected files

### Step 2 — Plan
Provide concise implementation plan before editing.

### Step 3 — Implement
Apply minimal safe changes.

### Step 4 — Verify
Explain:
- what was changed
- why
- possible side effects
- how it was validated

---

# DOCKER / SAIL RULES

All commands must run through Sail.

Use:
./vendor/bin/sail artisan
./vendor/bin/sail test
./vendor/bin/sail composer

Never assume PHP/composer exists on host machine.

---

# FORBIDDEN ACTIONS

Do NOT:
- delete files without explicit confirmation
- run destructive commands automatically
- rewrite large sections unnecessarily
- update dependencies casually
- introduce new frameworks/libraries without approval
- modify .env values automatically
- expose secrets/tokens
- bypass tests

---

# PERFORMANCE

Prioritize:
- readability first
- then performance

Optimize only when:
- bottleneck is identified
- evidence exists

Avoid premature optimization.

---

# SECURITY

Always consider:
- SQL injection
- mass assignment
- authorization
- authentication
- file upload risks
- unsafe deserialization
- rate limiting

Never expose internal stack traces in production code.

---

# CODE STYLE

Follow existing project style first.

Prefer:
- explicit naming
- short methods
- early returns
- typed signatures
- small focused classes

Avoid:
- magic behavior
- hidden side effects
- deeply nested conditionals

---

# LEGACY CODE RULES

When touching legacy code:
- minimize blast radius
- preserve behavior
- add characterization tests if possible
- avoid opportunistic rewrites

---

# WHEN UNSURE

Stop and ask instead of assuming.

Incorrect assumptions are more expensive than clarification.
