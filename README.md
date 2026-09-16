<a name="top"></a>
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-latest-FF2D20)](https://laravel.com/)
[![Database](https://img.shields.io/badge/database-PostgreSQL-336791)](https://www.postgresql.org/)
[![Auth](https://img.shields.io/badge/auth-Sanctum-1D76DB)](https://laravel.com/docs/sanctum)
[![Realtime](https://img.shields.io/badge/realtime-Reverb-8250DF)](https://laravel.com/docs/reverb)
[![Status](https://img.shields.io/badge/status-pre--implementation-lightgrey)](#-project-status)

**The single Laravel API that powers every QOR Novo (Qual o Rock) surface** — the consumer web app, the consumer mobile app, the organizer/venue admin panel, and the plans landing page all talk to this one backend rather than each shipping their own.

## Table of Contents
- [About](#-about)
- [Architecture](#-architecture)
- [Getting Started](#-getting-started)
- [Documentation](#-documentation)
- [Project Status](#-project-status)
- [Contributing](#-contributing)
- [License](#-license)

## 🚀 About

This is the API for **Qual o Rock**, an event-discovery and organizer platform. Consumers browse, follow, and socialize around live events; organizers create and manage events, venues, and promoters, subject to plan-tier limits and Super Admin approval. This repository holds only the backend — see the root [QOR Novo](https://github.com/derlandyb/qualorock) repository for the full platform (each app is its own repository, wired together as git submodules).

- **Auth**: Laravel Sanctum with two distinct guards/user models — one for consumers, one for organizers — rather than a single account with a role flag.
- **Realtime**: Laravel Reverb (self-hosted WebSockets) for live updates (e.g. engagement stats, event status changes).
- **Storage**: S3-compatible object storage via Laravel's Flysystem abstraction (MinIO locally).
- **Data**: PostgreSQL, accessed through a least-privilege application database user — never the superuser.

## 🏗 Architecture

The codebase follows Clean Architecture, applied uniformly rather than as a thin frontend-only convention:

```
app/
├── Domain/          # Entities + repository contracts. Zero framework dependency.
├── Application/     # Use-cases, services, policies. Orchestrates Domain via its contracts.
├── Infrastructure/  # Eloquent models + concrete repository implementations.
└── Presentation/     # Controllers. Thin — call Application use-cases only, no direct
                       # Eloquent queries or business rules.
```

Every persisted entity has a matching `Domain/Contracts/<Entity>RepositoryInterface.php`; entities carrying real business rules (e.g. an organizer's approval-state transitions, an event's status transitions, append-only plan pricing) additionally get an explicit `Domain/Entities/<Entity>.php`. Simple CRUD entities keep a thin contract with no separate entity class, deliberately, per YAGNI.

Security and compliance guardrails apply across every endpoint: all database access goes through Eloquent/the query builder (no raw SQL), every endpoint touching a user- or organizer-owned resource is gated by a Policy (IDOR protection), and self-service data export/deletion is a first-class requirement, not an afterthought — see the platform's [`.specs/STATE.md`](https://github.com/derlandyb/qualorock/blob/main/.specs/STATE.md) for the full architecture-decision record (AD-004, AD-008, AD-012).

## ⚡ Getting Started

This service runs via the root project's Docker Compose setup rather than standalone:

```shell
# From the root QOR Novo repository
cp api/.env.example api/.env   # fill in real local values
make up                        # builds every service, boots it, runs migrations + seeders
```

`make up` builds and starts this API alongside Postgres, Reverb, MinIO, Mailhog, and pgAdmin, then seeds the database. See the root repository's `docs/development.md` for the full local-dev walkthrough and every other `make` target (`down`, `logs`, `ps`, `seed`, `test-e2e`).

## 📚 Documentation

- `docs/backend/architecture.md` — this stack's Clean Architecture layering and conventions in full (added alongside the first implementation tasks, in this repository).
- [`.specs/STATE.md`](https://github.com/derlandyb/qualorock/blob/main/.specs/STATE.md) — every cross-feature architecture decision (AD-001..) this API implements against (root repository).
- [`.specs/features/`](https://github.com/derlandyb/qualorock/tree/main/.specs/features) — the requirements and task breakdowns for each consuming feature (admin-panel, web-app, landing-page-plans, mobile-app) (root repository).

## 📍 Project Status

No application code exists yet — this repository currently holds only local-dev infrastructure (`Dockerfile`, `.env.example`) committed at the root project level, plus this README. Implementation is starting with **admin-panel Phase 1** (data models & migrations for Organizer, Venue, Event, Promoter, and Plan Pricing), executed task-by-task against `.specs/features/admin-panel/tasks.md`.

## 🤝 Contributing

This project follows Conventional Commits (one commit per task) and Conventional branch naming, with one branch per phase/milestone off `main`. Once a milestone branch's development completes, a stack-specific code-review pass reviews its PR and leaves comments; those are addressed with fix commits before the PR is updated. A PR only merges once CI is fully green — lint (Pint, PHPStan), the full test suite (Pest, ≥80% coverage), all required checks.

## 📃 License

TBD — no license has been chosen for this project yet.

[Back to top](#top)
