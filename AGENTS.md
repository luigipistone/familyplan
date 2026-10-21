# FamilyPlan

Read docs/CLOUD.md before working on this project.

- Laravel 12 / PHP 8.3, MySQL in production; Vue 3 components with Vite.
- Italian mobile-first interface. Preserve the existing theme, spacing, icons and modal conventions.
- public/js/app.js handles persistent AJAX navigation and delegated events. New behavior must survive shell replacement and repeated actions.
- Vue components live in resources/js/components. Run npm run build when editing them.
- Keep dialog body scroll locking and internal scrolling working on mobile.
- Run vendor/bin/phpunit and npm run build for relevant changes. There is no artisan test command installed.
- Never commit config.local.php, .env, credentials, uploaded files, backups or production data.
- Use SQLite and synthetic data in development. Never run migrate:fresh, db:wipe or seeders against production.
- Production requires its existing APP_KEY for encrypted password entries. Never regenerate it.
- Soundboard media are hosted on Plesk and intentionally excluded from Git. Do not remove them during deployment.
- The previous main branch was a legacy PHP implementation. This branch replaces it with the current Laravel application; do not reintroduce legacy api.php or src/bootstrap.php.
- Keep changes scoped and explain results concisely in Italian.
