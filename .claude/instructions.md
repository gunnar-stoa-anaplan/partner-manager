# Project Conventions

## Feature Flags (Pennant + ModuleRegistry)

When adding a new feature flag to the application, always include ALL of the following:

1. **Pennant Feature Class** — `app/Features/{PascalCaseName}.php` with `#[Name('snake_case_slug')]`, returning `false` from `resolve()`.
2. **Module Config Entry** — `config/modules.php` with `name`, `description`, `icon`, and `enabled` (referencing an env var defaulting to `true`).
3. **Activation Path Definition** — `database/seeders/ActivationPathSeeder.php` entry in `pathDefinitions()` with Tell/Show/Do phased steps. Admin paths auto-assign to admins+coes; user paths auto-assign to admins+psms+coes.
4. **Route Middleware** — Gate routes with `middleware('module:slug')` using the `EnsureModuleEnabled` middleware.
5. **Navigation Gating** — Gate nav items in `resources/views/layouts/partials/generated-navigation.blade.php` with `$registry->isAccessible('slug')`.
6. **View Gating** — Gate feature-specific UI panels with `app(\App\Services\ModuleRegistry::class)->isEnabled('slug')`.

Use `isEnabled()` for full feature access checks (config + Pennant + activation path) and `isAccessible()` for Tell-phase training access (allows viewing even before full activation).
