## Summary

<!-- What changed and why, in 1-3 bullets. Lead with the outcome, not the process. -->

-

## Spec / task reference

<!-- e.g. .specs/features/admin-panel/tasks.md T13, requirement ADMIN-08 -->

## Spec gaps or deviations

<!-- Anything the PR does differently from design.md/tasks.md, and why. "None" if none. -->

## Test plan

<!-- Check only what actually ran. Tests are the spec (AD-010) - "N/A" needs a one-line reason. -->

- [ ] `vendor/bin/pint --test` clean
- [ ] `php artisan test` - all tests pass (GIVEN/WHEN/THEN named, per AD-010)
- [ ] Coverage meets this layer's Test Coverage Matrix expectation (or is explicitly `none` per the task)
- [ ] `php artisan migrate --pretend` (or `migrate:fresh`) clean, for PRs touching migrations
- [ ] CI green

## Checklist

- [ ] Commits follow Conventional Commits, one task per commit (AD-011)
- [ ] No task/ticket-referencing comments in code (AD-014) - rationale lives in `docs/backend/*.md` or here in the PR description
- [ ] Clean Architecture layering respected: no `Illuminate\*`/Eloquent imports in `app/Domain/**` or `app/Application/**` (AD-012)
- [ ] No magic numbers/strings introduced without a named constant (AD-013)
- [ ] `php-laravel-code-reviewer` has run on this PR and its findings are addressed or explicitly deferred with a reason

<!-- Per AD-011: this PR merges only once CI is fully green after the review round above - not on either alone. -->
