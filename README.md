# LaraDesk

AI-powered multi-tenant support ticket system. Companies sign up, get an isolated database, and every ticket is automatically categorized, prioritized, and drafted for reply by Claude before an agent ever opens it.

## What It Does

A customer submits a ticket. Within seconds, Claude has categorized it (Billing, Technical, General), scored its sentiment (Frustrated, Neutral, Satisfied), assigned a priority (Low, Medium, High), and written a draft reply. The agent opens the ticket to an AI-suggested answer already waiting — they edit and send, or write their own from scratch.

Every company using LaraDesk gets a fully isolated database. No shared tables, no `tenant_id` filtering, no risk of one tenant's data leaking into another's query.

## Tech Stack

| Layer | Choice |
|---|---|
| Language / Framework | PHP 8.4, Laravel 13 |
| Multi-tenancy | Spatie Laravel Multitenancy — isolated DB per tenant |
| Admin panel | Filament 3.x |
| Auth | Laravel Fortify (web/admin) + Sanctum (API tokens) |
| AI | Anthropic Claude API |
| Queues | Laravel Horizon + Redis |
| Permissions | Spatie Laravel Permission |
| Testing | Pest (Feature + Unit) |
| Local dev | Docker via Laravel Sail |
| CI | GitHub Actions |

## Architecture

Service Layer + Action Classes. Controllers call Actions and nothing else. Actions call Services when an external integration is involved. Jobs handle anything async.

Controller → Action → Service (external API) → Job (queued work)

**Why Actions instead of fat Services:** each ticket operation — create, assign, close, reply — is one discrete business process. An Action does exactly one thing, which makes it independently testable and easy to reason about in isolation.

**Why Contracts only on Services, not Repositories:** Eloquent is the data layer here; nothing ever swaps out from under it, so a Repository layer on top would just be indirection with no payoff. `AIServiceContract` and `TenantServiceContract` exist for a real reason — they let tests mock external calls instead of hitting the Anthropic API in CI.

**Why isolated databases per tenant instead of a shared table with `tenant_id`:** one tenant's data, load, or bad query never touches another tenant's. Costs more in provisioning complexity, buys real isolation — the right tradeoff for a tool that may hold sensitive support data.

**Why the AI layer never throws:** `AIService` retries three times with backoff, and if Claude is unreachable after that, the ticket still gets created — it just falls back to `category: General`, `priority: Medium`. A customer's ticket should never fail to submit because of a downstream AI outage.

## Folder Structure

```
app/Actions/Tickets/       CreateTicket, AssignTicket, CloseTicket, ReplyToTicket
app/Actions/AI/            ProcessTicketWithAI
app/Actions/Tenants/       CreateTenant, ProvisionTenantDatabase
app/Contracts/             AIServiceContract, TenantServiceContract
app/DTOs/                  AIResponseDTO, CreateTicketDTO
app/Services/              AIService, TenantService
app/Jobs/                  ProcessTicketWithAI, SendTicketNotification
app/Events/                TicketCreated, TicketAssigned, TicketClosed
app/Listeners/             DispatchAIProcessing, NotifyAgentOnAssignment
app/Http/Controllers/Api/  TicketController
app/Http/Requests/         CreateTicketRequest, ReplyToTicketRequest
app/Filament/Resources/    TicketResource, AgentResource, TenantResource
```

## Ticket Lifecycle

1. Customer submits a ticket (web form or API)
2. `CreateTicket` Action persists it and fires `TicketCreated`
3. `DispatchAIProcessing` listener queues `ProcessTicketWithAI`
4. `AIService` calls Claude, returns a typed `AIResponseDTO`
5. Ticket updated with category, sentiment, priority, and draft reply
6. Assigned agent notified by email
7. Agent reviews the AI draft in Filament, edits, sends

## Roles

- **SuperAdmin** — manages tenants and platform-wide settings
- **TenantAdmin** — manages agents and settings for their own company
- **Agent** — works and replies to tickets
- **Customer** — submits tickets, tracks status, views replies

## API

Authenticated via Sanctum bearer tokens. All requests scoped automatically to the authenticated user's tenant.

### Create a ticket

```
POST /api/tickets
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Unable to update billing address",
  "description": "I tried updating my billing address three times and it keeps reverting to the old one.",
  "customer_id": 42
}
```

Response `201 Created`:

```json
{
  "data": {
    "id": 118,
    "title": "Unable to update billing address",
    "status": "open",
    "category": "Billing",
    "sentiment": "Frustrated",
    "priority": "High",
    "ai_draft_reply": "Hi, sorry for the trouble with your billing address...",
    "assigned_agent_id": null,
    "created_at": "2026-07-07T10:15:00Z"
  }
}
```

Note: `category`, `sentiment`, `priority`, and `ai_draft_reply` are `null` immediately after creation and populate a few seconds later once the queued AI job completes.

### List tickets

```
GET /api/tickets?status=open&priority=high
Authorization: Bearer {token}
```

### View a single ticket

```
GET /api/tickets/{id}
Authorization: Bearer {token}
```

### Reply to a ticket

```
POST /api/tickets/{id}/reply
Authorization: Bearer {token}
Content-Type: application/json

{
  "body": "Your billing address has been updated. Let us know if this happens again."
}
```

Response `200 OK`:

```json
{
  "data": {
    "id": 118,
    "status": "resolved",
    "replies": [
      {
        "id": 301,
        "body": "Your billing address has been updated. Let us know if this happens again.",
        "is_ai_draft": false,
        "created_at": "2026-07-07T10:22:00Z"
      }
    ]
  }
}
```

## Running Locally

Requires Docker. No local PHP, MySQL, or Redis needed — everything runs in containers.

```bash
git clone https://github.com/abdul-moiz-06/laradesk.git
cd laradesk
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

- App: `http://localhost`
- Admin panel: `http://localhost/admin`
- Horizon (queue dashboard): `http://localhost/horizon`

### Environment

```env
ANTHROPIC_API_KEY=your_key_here
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=laradesk
QUEUE_CONNECTION=redis
REDIS_HOST=redis
```

## Tests

```bash
./vendor/bin/sail test
```

Covers the full ticket lifecycle, tenant isolation, role-based access, and `AIService` retry/fallback behavior. `AIService` is mocked in every test — no real Anthropic calls run in CI.

## CI

GitHub Actions runs on every push to `main`: install dependencies, run migrations, run the full Pest suite, check code style with Pint.

## Roadmap

- Webhook support for ticket status changes
- SLA tracking with breach alerts
- Multi-language ticket support via AI translation

## License

MIT
