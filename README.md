# FamilyPlan

Webapp mobile-first in PHP, JavaScript/AJAX e MySQL per gestire calendario familiare, liste spesa, profili famiglia/figli, promemoria e note.

## Funzionalità

- Login con nome e password; creazione utenti riservata all'admin.
- Telefono come chiave utente per i familiari adulti; profili `figlio` associabili a un genitore anche senza telefono.
- Ruoli `admin` e `familiare`, categorie familiari, profilo personale, tema light/dark/system.
- Recupero password via email con token hashato e scadenza; in locale il link viene anche scritto nel file configurato.
- Dashboard a widget aggiungibili/rimuovibili per Calendario, Lista spesa, Famiglia, Promemoria, Note, Profilo, Impostazioni e Utenti.
- PWA installabile con manifest, service worker, cache offline base e supporto a notifiche web push lato service worker.
- Notifiche applicative per creazioni/condivisioni/archiviazioni.
- Query PDO preparate, password hashate, cookie sessione HttpOnly/SameSite, CSRF token sugli endpoint mutativi.

## Installazione

1. Crea il database MySQL importando `database/schema.sql`.
2. Copia `config.sample.php` in `config.local.php` e inserisci le credenziali reali del database. Il file locale è ignorato da Git.
3. Pubblica la cartella `public/` come document root o avvia in sviluppo:

```bash
php -S localhost:8000 -t public
```

## Account admin iniziale

- Nome: `Luigi`
- Telefono: `3497591581`
- Password: `#M1l4n1899!`

Cambia la password dopo il primo accesso in un ambiente reale.

## Note sicurezza produzione

- Usa sempre HTTPS per proteggere sessioni, credenziali e dati sensibili sugli spostamenti familiari.
- Mantieni `config.local.php` fuori dal repository e limita i permessi del file.
- Configura SMTP affidabile per il recupero password; `mail()`/log locale è solo un fallback semplice.
- Per notifiche push complete serve aggiungere una coppia VAPID e salvare le subscription browser; il service worker è già predisposto a mostrare push ricevuti.
