# FamilyPlan

Webapp Laravel mobile-first per calendario familiare, liste spesa, gestione figli, promemoria, note, PWA e gestione utenti admin.

## Installazione su Plesk

1. Carica il progetto nella directory dell'applicazione.
2. In Plesk imposta la document root su `public`.
3. Assicurati che Plesk usi PHP 8.3.31 o superiore. Il lockfile Composer è generato per PHP 8.3.31.
4. Esegui Composer dal pannello Plesk o da SSH:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

5. Crea/aggiorna le tabelle del database:

   ```bash
   php artisan migrate --seed --force
   ```

6. Ottimizza l'app:

   ```bash
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   ```

7. Verifica che `config.local.php` sia presente sul server e non venga pubblicato su repository.
8. Configura un SMTP reale in `.env` per il recupero password via email.

## Database e aggiornamenti

Il database non si carica con un file SQL manuale: lo crea Laravel tramite le migrazioni in `database/migrations`.

Primo avvio su Plesk:

```bash
php artisan migrate --seed --force
```

Aggiornamenti futuri:

1. Aggiungi una nuova migration per ogni modifica allo schema.
2. Carica i file aggiornati sul server.
3. Esegui:

   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   ```

Laravel registra le migration già eseguite nella tabella `migrations`, quindi a ogni aggiornamento applica solo quelle nuove.

Prima di ogni aggiornamento importante fai un backup MySQL da Plesk. In produzione non usare mai `php artisan migrate:fresh`: cancella e ricrea tutte le tabelle.

## Sicurezza

- Registrazione pubblica disabilitata: gli utenti vengono creati solo dall'admin.
- Login basato su nome e password.
- Sessioni cifrate, cookie `HttpOnly`, `Secure`, `SameSite=Lax`.
- Header di sicurezza applicati a ogni risposta web.
- Credenziali DB e admin in `config.local.php`, escluso da git.
- Password salvate con hashing Laravel.

## Admin iniziale

L'account admin viene creato dal seeder leggendo `config.local.php`.

## Sviluppo cloud

Leggere [docs/CLOUD.md](docs/CLOUD.md) e [AGENTS.md](AGENTS.md).
Setup: `bash scripts/cloud-setup.sh`. Test: `vendor/bin/phpunit`.
Non eseguire il setup cloud nella directory di produzione.
