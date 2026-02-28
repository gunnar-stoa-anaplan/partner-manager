# Rename Project: Partner-Manager-3 → partner-manager

## Context

The project has evolved beyond its "version 3" suffix. The user wants to rename the repo, directory, and all internal references from `Partner-Manager-3` / `partner-manager-3` to simply `partner-manager`.

## Steps

### 1. Rename GitHub repo
```bash
gh repo rename partner-manager
```
This updates the remote URL automatically.

### 2. Update Docker references
- `docker-compose.yml:80` — image name `partner-manager-3-pm-agent` → `partner-manager-pm-agent`
- `docker/agent/config.yaml:12` — container name `partner-manager-3-laravel.test-1` → `partner-manager-laravel.test-1`

### 3. Update documentation
- `SAIL_QUICK_START.md` — Replace all `Partner-Manager-3` / `partner_manager_3` references
- `MIGRATION_CHECKLIST.md` — Replace all `Partner-Manager-3` / `partner_manager_3` references
- `create_views.sh:2` — Update hardcoded path

### 4. Update local config
- `.claude/settings.local.json` — Update hardcoded paths in permissions (all `/Partner-Manager-3/` → `/partner-manager/`)

### 5. Rename local directory
This must be done last, outside the current working directory:
```bash
mv ~/Library/CloudStorage/OneDrive-Anaplan/Desktop/Partner-Manager-3 \
   ~/Library/CloudStorage/OneDrive-Anaplan/Desktop/partner-manager
```

**Note:** This will end the current Claude Code session since the working directory changes. The user will need to reopen from the new path.

### 6. Post-rename: Update .env
After the directory rename, update `.env`:
- `DB_DATABASE=partner_manager_3` → `DB_DATABASE=partner_manager` (if desired — can keep existing DB name to avoid MySQL migration)

## Files to modify
- `docker-compose.yml`
- `docker/agent/config.yaml`
- `SAIL_QUICK_START.md`
- `MIGRATION_CHECKLIST.md`
- `create_views.sh`
- `.claude/settings.local.json`

## Verification
- `gh repo view` confirms new repo name
- `vendor/bin/sail up -d` starts correctly with new container names
- `vendor/bin/sail artisan test --compact` passes
