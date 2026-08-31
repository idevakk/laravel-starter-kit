# PRD — "Anvil": A Rock-Solid Laravel 13 + Livewire 4 + Filament 5 Starter Kit

**Status:** Draft v1.0
**Date:** August 31, 2026
**Working codename:** *Anvil* (rename freely — used only so the doc has something concrete to refer to)
**Intended consumer of this document:** a human dev, or an AI coding agent (Claude Code, Cursor, etc.) building the repo step by step.

---

## 0. Why this document exists

As of August 2026, no single open-source starter kit combines all of the following at once:

- Laravel 13 + a real Livewire 4 customer-facing frontend (not just Filament's internal engine)
- Filament 5 as a separate admin backend
- 100% enforced type coverage and 100% enforced test coverage
- A full multi-agent AI guideline system, managed through Laravel Boost
- A documented design/code "taste" layer
- A genuinely bloat-free dependency list

The closest real kits are `nunomaduro/laravel-starter-kit` (best-in-class for strict typing/coverage/AI guidelines, but Blade-only, no Filament) and `CodeWithDennis/larament` (best-in-class for Filament 5 + Boost + bloat-free, but no coverage enforcement and no standalone Livewire frontend). This PRD specifies the kit that sits at the intersection of both.

---

## 1. Vision

A `composer create-project` template that gives a solo developer or small team a production-grade Laravel 13 application in under 10 minutes: a Livewire 4 customer-facing app, a Filament 5 admin panel, a testing/static-analysis gate that cannot silently regress, and an AI-agent guideline system (via Laravel Boost) that keeps Claude Code/Cursor/Copilot/etc. writing code that matches the codebase's actual conventions instead of generic Laravel tutorials.

## 2. Goals

1. Zero-to-running in one command, zero manual config beyond `.env` secrets.
2. `composer review` (single command) enforces: 100% type coverage, 100% line coverage, PHPStan/Larastan at max level, zero Pint diffs, zero Rector diffs.
3. Filament 5 admin panel and Livewire 4 app coexist in one repo without Tailwind/asset conflicts (see Section 17, Risk R1).
4. Every supported AI coding agent gets correct, current, project-specific instructions with no manual duplication — Boost + one canonical source file.
5. A documented, enforced "design taste" so an AI agent (or a new hire) produces UI and code that looks like it was written by the same person who wrote the rest of the app.
6. Stay genuinely small: every dependency in `composer.json`/`package.json` must be justified in Section 3's ledger.

## 3. Non-Goals / Bloat Guardrails

Explicitly **not** included by default (documented as an opt-in upgrade path instead, so the base template stays small):

- Multi-tenancy, teams, billing/Stripe, blog/CMS, SaaS marketing pages
- Spatie `laravel-permission` + Filament Shield (granular RBAC) — ships with a minimal role-enum gate instead; upgrade path documented in Section 10.3
- Queues beyond the default sync/database driver, Horizon, Reverb/websockets
- A second JS framework (no Vue/React/Svelte islands) — this is a Livewire-first app
- Bun as a hard requirement — Vite + npm stays the default; Bun is mentioned as an opt-in speed upgrade only

**Rule of thumb:** a package earns a place in `composer.json` only if removing it breaks a *default* feature listed in Sections 10–11, not a hypothetical future one.

## 4. Target Users

- Solo developers / small teams starting a new internal tool, SaaS MVP, or client project on the newest Laravel/Livewire/Filament majors
- Teams that plan to lean on AI coding agents daily and want the codebase itself to constrain and guide those agents, not just prompt them

## 5. Core Tech Stack (pinned)

| Layer | Package | Version constraint | Purpose |
|---|---|---|---|
| Framework | `laravel/framework` | `^13.0` | Base framework |
| Runtime | PHP | `^8.3`, 8.4+ recommended | Match Larament's floor; Xdebug/PCOV needed locally for coverage |
| Frontend reactivity | `livewire/livewire` | `^4.1` | Customer-facing app |
| Component kit | `livewire/flux` (+ `flux-pro` optional) | `^2.0` | Matches the official Livewire starter kit's convention |
| Admin panel | `filament/filament` | `^5.0` | Requires Livewire 4 under the hood — this is where the two halves of the app share an engine |
| Sane defaults | `nunomaduro/essentials` | `^1.2` | Strict models, auto eager loading, immutable dates, forced HTTPS, safe console |
| AI tooling | `laravel/boost` | `^2.0` (dev) | MCP server, AI guidelines, Project Rules, docs API |
| Static analysis | `larastan/larastan` | `^3.0` (dev) | PHPStan wrapper for Laravel, run at level 9 |
| Refactoring | `rector/rector` + `driftingly/rector-laravel` | `^2.0` (dev) | Automated modernization, dry-run gate in CI |
| Code style | `laravel/pint` | `^1.13+` (dev) | Strict preset, zero-diff gate in CI |
| Testing | `pestphp/pest` | `^4.3+` (dev) | Test runner |
| Type coverage | `pestphp/pest-plugin-type-coverage` | `^4.0` (dev) | **The plugin Larament is missing** — this is what makes "100% typed" enforceable, not aspirational |
| Livewire/Filament testing | `pestphp/pest-plugin-livewire` | `^4.0` (dev) | `Livewire::test()` for both the app and Filament resources |
| Browser/E2E | Pest v4 native browser testing (Playwright) | — | Smoke-level E2E only, see Section 12 |
| IDE support | `barryvdh/laravel-ide-helper` | (dev) | Autocompletion |
| Local dev insight | `fruitcake/laravel-debugbar` | (dev, local-only) | Never shipped to prod |

## 6. Repository Layout

```
app/
  Actions/                # single-purpose, invokable business logic
  Enums/
  Filament/
    Resources/
    Pages/
    Widgets/
  Livewire/
    Pages/                # full-page Livewire 4 components (routed)
    Forms/
  Models/
  Policies/
  Providers/
resources/
  views/
    livewire/
    components/
  css/  js/
routes/
  web.php
tests/
  Unit/  Feature/  Browser/
  Pest.php
.ai/
  guidelines/              # project-specific, composable — layered on top of Boost's package guidelines
docs/
  TASTE.md                 # design + code taste (Section 9)
  ARCHITECTURE.md
AGENTS.md                  # canonical AI-agent source of truth
CLAUDE.md                  # generated/mirrored from AGENTS.md via Boost install
GEMINI.md
.cursor/rules/
.junie/
.github/workflows/
  tests.yml  phpstan.yml  pint.yml  browser.yml
boost.json
phpstan.neon
pint.json
rector.php
composer.json
```

## 7. Quality Bar — Definition of "Rock Solid"

| Metric | Tool | Threshold | Gate |
|---|---|---|---|
| Type coverage | Pest Type Coverage plugin | 100% (`pest --type-coverage --min=100`) | CI, blocks merge |
| Line/test coverage | Pest + Xdebug or PCOV | 100% (`pest --coverage --min=100`) | CI, blocks merge |
| Static analysis | Larastan (PHPStan) | Level 9 (max), zero baseline-ignored errors | CI, blocks merge |
| Code style | Pint, strict preset | Zero diff (`pint --test`) | CI, blocks merge |
| Refactor rules | Rector + rector-laravel | Zero diff (`rector --dry-run`) | CI, blocks merge |
| Browser smoke | Pest browser group (Playwright) | Critical paths only: auth flow, one Livewire flow, one Filament CRUD flow | CI, separate job, non-blocking on first release |

**Honesty note for the PRD:** 100% coverage is a floor, not a quality proxy — it stops *untyped* and *untested* code from merging, it does not guarantee the tests assert anything meaningful. Section 17 (Risks) covers the mitigation.

## 8. AI Guidelines & Laravel Boost System

### 8.1 Boost install and Project Rules
Run `composer require laravel/boost --dev` then `php artisan boost:install`, selecting every agent the team actually uses (Claude Code, Cursor, Copilot, Junie, Gemini, at minimum). Boost auto-detects IDEs/agents and generates `.mcp.json` plus per-agent guideline files. Laravel 13's Boost adds **Project Rules** — a mechanism to record and infer your application's own conventions rather than only shipping generic package guidelines. Use this as the live, evolving half of your "design taste" system; re-run it after major architectural decisions so it doesn't go stale.

### 8.2 AGENTS.md as the single canonical source
Every other agent file (`CLAUDE.md`, `GEMINI.md`, `.cursor/rules/*`, `.junie/guidelines.md`, GitHub Copilot instructions) either symlinks to or is generated from `AGENTS.md`. Never hand-edit two files with the same content — pick the canonical one and mirror it in CI (a cheap `diff` check in the lint workflow catches drift).

### 8.3 Custom project guideline files (`.ai/guidelines/*.md`)
Layered on top of Boost's own package guidelines. At minimum, ship:
- `architecture.md` — Actions pattern, when to extract a class vs. keep it inline, no service-layer-for-its-own-sake
- `livewire.md` — component conventions, when a full page vs. a nested component, `wire:navigate` usage
- `filament.md` — resource conventions, and the hard rule that **every generated Filament resource must ship with a Pest smoke test** (list loads, create, edit, delete) before it's considered done
- `testing.md` — what "done" means: type coverage + line coverage + at least one meaningful assertion per branch, not just "make coverage green"

### 8.4 Multi-agent file matrix

| File / folder | Agent | Source |
|---|---|---|
| `AGENTS.md` | Generic / fallback standard | Hand-authored, canonical |
| `CLAUDE.md` | Claude Code | Mirrors AGENTS.md |
| `.claude/skills/` | Claude Code skills | Boost-generated + project additions |
| `GEMINI.md` | Gemini | Mirrors AGENTS.md |
| `.cursor/rules/` | Cursor | Boost-generated |
| `.junie/` | Junie (JetBrains) | Boost-generated |
| `.github/copilot-instructions.md` | GitHub Copilot | Mirrors AGENTS.md |
| `boost.json` / `.mcp.json` | MCP-compatible editors | Boost-generated |

## 9. Design Principles / "Taste" Doc (`docs/TASTE.md`)

This is the piece none of the researched kits document explicitly — it's what turns "AI guidelines" from lint rules into actual judgment calls. Required sections:

**Code taste**
- Prefer explicit over clever; no magic, no unnecessary abstraction layers
- Actions over services: one invokable class per business operation
- Immutable by default (`readonly` properties, `CarbonImmutable` via Essentials)
- A method that needs a comment to explain *what* it does should be renamed, not commented

**UI/UX taste**
- Spacing scale, type scale, and color tokens defined once in Tailwind config, referenced everywhere — no ad hoc `px-[13px]`
- Every list view has a defined empty state and loading state — not optional, not an afterthought
- Every destructive action (delete, revoke, etc.) requires confirmation, styled consistently between the Livewire app and the Filament panel
- Flux UI components are the default; a custom component is justified only when Flux genuinely can't do it

This file is read by AI agents via `.ai/guidelines/` (Section 8.3) and by humans as onboarding.

## 10. Filament 5 Admin Panel Spec

- Panel ID `admin`, path `/admin`, SPA mode enabled
- Custom theme sharing the same Tailwind design tokens as `docs/TASTE.md`
- MFA (App Authentication / TOTP) mandatory for all admin users
- Custom login page, autofilled credentials in local environment only

### 10.3 RBAC — default vs. upgrade path
Default: a simple `role` enum column (`admin` / `user`) plus a couple of Gate definitions — enough for a single-admin-tier app and zero extra dependencies. Documented upgrade path for teams that need per-resource, per-action permissions: add `bezhansalleh/filament-shield` + `spatie/laravel-permission` (confirm current Filament 5 compatibility before adding — verify against the plugin's own changelog, since both Filament 5 and Livewire 4 are new enough that some third-party plugins may still be catching up).

### 10.4 Testing requirement
Every Filament Resource ships with a Pest smoke test covering list, create, edit, and delete before it's considered mergeable — this mirrors Filament's own published AI guideline for Boost-driven resource generation.

## 11. Livewire 4 Application Spec

- Auth as full-page Livewire components (login, register, forgot/reset password, email verification), matching the official starter kit's pattern, navigated via `wire:navigate`
- Guest layout + authenticated app layout, shared nav component
- Dark mode toggle, cookie-persisted
- Check the current Livewire 4 documentation/official starter kit source for the exact single-file-component syntax before scaffolding — Livewire 4 introduced a new view-based component format, and this PRD intentionally doesn't hardcode syntax that may already have shifted since this document was written

## 12. Testing Strategy

- **Unit** — pure PHP: Actions, Enums, value objects
- **Feature** — HTTP + `Livewire::test()` for both the app and Filament components
- **Browser** — Pest v4 + Playwright, kept intentionally small: the auth flow, one representative Livewire page, one Filament CRUD flow. This is a smoke net, not full E2E coverage of every screen — keep CI fast
- Type coverage and line coverage both gate merges (Section 7); browser tests gate merges once the suite is stable, non-blocking during initial bring-up

## 13. CI/CD Pipeline (GitHub Actions)

Separate jobs so failures are easy to diagnose: `lint` (Pint), `refactor` (Rector dry-run), `types` (Larastan), `type-coverage` (Pest), `tests` (Pest, coverage, sharded/parallel), `browser` (Playwright, separate job so it doesn't slow down the fast feedback loop). Cache Composer and npm. Run on every PR and on push to `main`.

```yaml
# .github/workflows/tests.yml (skeleton)
name: tests
on: [push, pull_request]
jobs:
  types:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install --no-interaction
      - run: ./vendor/bin/phpstan analyse
  type-coverage:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install --no-interaction
      - run: ./vendor/bin/pest --type-coverage --min=100
  unit-feature:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install --no-interaction
      - run: ./vendor/bin/pest --parallel --coverage --min=100
```

## 14. Composer Scripts Reference

| Script | Purpose |
|---|---|
| `composer dev` | Concurrently run serve, queue listener, Pail logs, Vite |
| `composer setup` | Fresh-clone bootstrap: install, `.env`, key, migrate, seed, build |
| `composer lint` | Pint, dry-run |
| `composer refactor` | Rector, apply |
| `composer types` | Larastan analyse |
| `composer test:type-coverage` | Pest `--type-coverage --min=100` |
| `composer test:unit` | Pest `--coverage --min=100 --parallel` |
| `composer test:browser` | Pest browser group only |
| `composer review` | Everything above — the one command to run before opening a PR |
| `php artisan boost:install` | Generate/refresh AI agent configuration |

## 15. Security & Sane Defaults

Delivered via `nunomaduro/essentials`: strict Eloquent models (fail on unfilled/undefined attribute access), automatic eager-load prevention, immutable dates by default, forced HTTPS in production, a "safe console" guard against destructive commands (`migrate:fresh`, etc.) running in production, and asset prefetching. Add sane rate limits on auth routes and a baseline security-headers middleware (HSTS/CSP starter — expect to tune the CSP per app).

## 16. Build Plan (phased, agent-executable checklist)

**Phase 0 — Bootstrap**
- [ ] `laravel new anvil --no-interaction`, PHP 8.3+ target, SQLite for local/test DB
- [ ] Init git, `.editorconfig`, `.gitattributes`

**Phase 1 — Quality tooling core**
- [ ] Require `nunomaduro/essentials`, `larastan/larastan`, `rector/rector` + `driftingly/rector-laravel`, `laravel/pint`
- [ ] Add `phpstan.neon` (level 9), `rector.php`, `pint.json` (strict preset)
- [ ] Require `pestphp/pest` v4, `pest-plugin-laravel`, `pest-plugin-type-coverage`, `pest-plugin-livewire`
- [ ] Configure `Pest.php` and coverage thresholds

**Phase 2 — Laravel Boost & AI guidelines**
- [ ] `composer require laravel/boost --dev` → `php artisan boost:install`
- [ ] Author `AGENTS.md`; confirm mirrored files match
- [ ] Add `.ai/guidelines/*.md` (Section 8.3) and `docs/TASTE.md` (Section 9)

**Phase 3 — Livewire 4 frontend**
- [ ] Require `livewire/livewire` v4, `livewire/flux`
- [ ] Scaffold auth as full-page Livewire components
- [ ] Guest + app layouts, dark mode toggle

**Phase 4 — Filament 5 admin**
- [ ] `composer require filament/filament:"^5.0"` → `php artisan filament:install --panels`
- [ ] SPA mode, custom theme, MFA
- [ ] Role-enum gate (Section 10.3); User resource + one example resource, each with a smoke test
- [ ] **Verify Tailwind/asset pipeline compiles cleanly with both the Livewire app and the Filament panel in the same build** (Risk R1) before continuing

**Phase 5 — Testing & CI**
- [ ] Write baseline tests to reach 100/100 on both coverage metrics
- [ ] Author the four GitHub Actions workflows (Section 13)
- [ ] Confirm the gate actually gates: intentionally break a type hint or delete a test on a throwaway branch and confirm CI fails

**Phase 6 — DX polish**
- [ ] Finalize `composer.json` scripts (Section 14), IDE helper, Debugbar (local-only)
- [ ] README: setup, philosophy, badges for the quality-bar checks

**Phase 7 — Release**
- [ ] Tag `v1.0.0`, publish as a `composer create-project` template on Packagist
- [ ] `CONTRIBUTING.md`, `LICENSE` (MIT recommended, matches every reference kit researched)

## 17. Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **R1 — Livewire 4 app + Filament 5 admin in one repo hit asset/Tailwind conflicts.** Real precedent: teams have hit exactly this with older Filament + the official Livewire kit. | Do Phase 4's asset-pipeline check *before* building out either half further. Keep Filament's theme and the app's Tailwind config on the same Tailwind major from day one. |
| **R2 — Livewire 4 / Filament 5 are ~7 months old as of this PRD; third-party plugins (e.g. Filament Shield) may lag.** | Verify any plugin's Filament-5/Livewire-4 compatibility before adding it, not after. Default RBAC (Section 10.3) has zero such dependency. |
| **R3 — 100% coverage becomes a box-ticking exercise as the app grows (tests that hit lines without asserting behavior).** | `.ai/guidelines/testing.md` explicitly bans coverage-only tests in code review; consider Pest mutation testing as a stretch goal once the suite matures. |
| **R4 — AI guideline files drift from actual codebase conventions over time.** | Re-run Boost's Project Rules after significant architectural changes; the mirrored-file diff check in CI (Section 8.2) catches manual edits that only touched one agent's file. |

## 18. Acceptance Criteria — Definition of Done for the template itself

- [ ] Fresh `composer create-project` completes with zero manual steps beyond `.env`/DB choice
- [ ] `composer review` passes: 100% type coverage, 100% line coverage, 0 Larastan errors at level 9, 0 Pint diff, 0 Rector diff
- [ ] `php artisan boost:install` succeeds and produces working MCP config for at least Claude Code and Cursor
- [ ] Filament admin panel reachable; login, MFA enrollment, and one full resource CRUD all work end to end
- [ ] Livewire app: register → verify email → login → dashboard works end to end
- [ ] CI is green on a clean PR, and demonstrably fails when a type hint or a test is deliberately removed
- [ ] README links to `AGENTS.md` and `docs/TASTE.md` as the philosophy/onboarding entry points

---

## Appendix A — Faster bootstrap path (merge existing kits instead of building from zero)

If a from-scratch build is more rigor than you need right now:

1. `composer create-project codewithdennis/larament anvil` — gives you Laravel 13 + Filament 5 + Boost + bloat-free baseline immediately.
2. Manually port the Livewire-4 auth scaffolding, Flux layouts, and `wire:navigate` setup from `laravel/livewire-starter-kit`'s current `main` branch.
3. Port `phpstan.neon`, `rector.php`, `pint.json`, and the full multi-agent guideline set (`AGENTS.md`, `CLAUDE.md`, `.cursor/`, `.claude/skills/`, etc.) from `nunomaduro/laravel-starter-kit` — it's MIT-licensed, so direct reuse is fine.
4. Add `pestphp/pest-plugin-type-coverage`, wire `--min=100` into both the type-coverage and unit-test scripts, and raise the suite to 100% manually — neither source kit ships this combination today.

This gets you ~80% of the way in hours instead of the multi-phase build in Section 16, at the cost of inheriting two different authors' opinions instead of one coherent set.

## Appendix B — References (verified August 2026)

- Laravel 13 docs — Starter Kits: https://laravel.com/docs/13.x/starter-kits
- Laravel 13 docs — Boost: https://laravel.com/docs/13.x/boost
- Laravel 13 docs — AI Assisted Development: https://laravel.com/docs/13.x/ai
- Filament 5 docs — AI-assisted development: https://filamentphp.com/docs/5.x/introduction/ai
- https://github.com/laravel/livewire-starter-kit
- https://github.com/CodeWithDennis/larament
- https://github.com/nunomaduro/laravel-starter-kit
- https://github.com/nunomaduro/laravel-starter-kit-inertia-vue
- https://github.com/nunomaduro/laravel-starter-kit-inertia-react
