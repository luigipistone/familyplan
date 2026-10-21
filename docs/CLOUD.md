# Continuare FamilyPlan dal cloud

## Stato verificato il 21 ottobre 2026

GitHub main conteneva una vecchia applicazione PHP, diversa da quella Laravel online.
Il ramo codex/cloud-ready-laravel contiene il codice locale attuale, confrontato con
Plesk per app, config, routes, resources, migration, asset JS/CSS e lockfile.
Unica differenza sostanziale trovata: UserFields.vue locale include etichetta nuova
password e autocomplete=new-password, assenti nel sorgente Plesk. Si conserva la
versione locale aggiornata. Non e' stato modificato il sito durante la preparazione.

## Architettura e funzioni

Laravel 12, PHP 8.3, MySQL, Vue 3, Vite, PWA. Calendario familiare, spesa, note,
promemoria, figli, contatti, carte fedelta', password cifrate, backup, monete,
costi 3D manuali, meteo, calcio, TV, traduttore, cambio valuta, soundboard e film.
Login con nome; utenti creati dall'admin. config.local.php contiene i segreti del
server, .env contiene anche APP_KEY. Entrambi sono esclusi dal repository.

Film: TmdbService / MovieController, raccolta personale e condivisa, voti,
preferiti, suggerimenti, carosello per genere e provider italiani. La selezione
genere e' conservata in sessionStorage. I modali bloccano lo scroll del body.
I suggerimenti sono euristiche attuali, da perfezionare con test mirati.

## Ambiente Codex Cloud

1. Collegare luigipistone/familyplan nell'ambiente Codex Cloud.
2. Selezionare questo ramo per la verifica, oppure main dopo il merge della PR.
3. Disponibilita' runtime: PHP 8.3 con pdo_sqlite, pdo_mysql, mbstring, xml, curl,
   zip, Composer 2 e Node 22 con npm.
4. Setup: bash scripts/cloud-setup.sh.
5. Non caricare database, .env, config.local.php o password di produzione.
6. Per test API reali, configurare TMDB_API_KEY e GOOGLE_WEATHER_API_KEY tramite
   le impostazioni riservate dell'ambiente; non sono necessarie ai test automatici.

Lo script prepara un database SQLite vuoto e non crea utenti reali. Le variabili
per avviare successivamente il server devono essere presenti nell'ambiente:
APP_ENV=local, APP_URL=http://localhost:8000, DB_CONNECTION=sqlite,
DB_DATABASE=<percorso assoluto checkout>/database/database.sqlite,
SESSION_SECURE_COOKIE=false, CACHE_STORE=file, SESSION_DRIVER=file,
QUEUE_CONNECTION=sync, MAIL_MAILER=log.

## Verifica e sviluppo

    vendor/bin/phpunit
    npm run build
    php artisan serve --host=0.0.0.0 --port=8000

Modifiche in public/js e public/css non richiedono Vite. Modifiche in resources/js
richiedono npm run build. Il layout versiona gli asset pubblici con filemtime.

Media Soundboard: manifest incluso; audio e WebP su Plesk esclusi da Git.
Nel cloud la riproduzione richiede una copia autorizzata di questi file.
Le immagini contatti/profili e i backup sono dati di produzione e restano sul server.

## Deploy Plesk

Document root: public. Usare PHP 8.3 di Plesk. Pubblicare solo codice e asset
compilati, conservando .env, config.local.php, storage e public/media.
Prima di migration fare un backup. Installare dipendenze Composer con --no-dev,
applicare php artisan migrate --force, poi optimize:clear, config:cache,
route:cache e view:cache. Non eseguire seeders per aggiornamenti ordinari.
Nessun deploy automatico o credenziale SSH e' stato configurato in GitHub.

## Proseguire da un altro dispositivo

Aprire Codex Cloud con lo stesso account, scegliere repository e ramo e avviare
un'attivita'. Primo messaggio suggerito: "Continuiamo FamilyPlan. Leggi AGENTS.md
e docs/CLOUD.md, verifica il ramo attuale e poi applica questa modifica: ...".
Questa documentazione trasferisce il contesto tecnico, non l'intera vecchia chat.
