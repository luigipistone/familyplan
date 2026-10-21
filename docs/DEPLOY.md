# Deploy GitHub Actions → Plesk

## Stato e funzionamento

L'attivazione richiede ancora la configurazione di GitHub e dell'accesso SSH Plesk.
Il codice da solo non abilita il deploy. Il collegamento GitHub usato da Codex
non dispone attualmente dei permessi per amministrare Secrets e Variables (403).

Dopo l'attivazione, ogni push o merge su `main` esegue test e build e pubblica
su Plesk. Le PR eseguono solo la verifica. Da GitHub → Actions → Verify Laravel →
Run workflow, selezionare `main` e `check` per verificare SSH e prerequisiti senza
modificare il sito, oppure `deploy` per pubblicare. Le modifiche ancora soltanto
nel checkout Cloud non vengono pubblicate: devono essere committate e unite in main.

## Configurazione iniziale

In Plesk, Siti web e domini → Hosting e DNS → Accesso hosting, individuare
l'utente di sistema e abilitare una shell SSH compatibile. In alcune versioni
la voce si chiama Accesso FTP/SSH. Usare l'IP/nome del server come host; la porta
abituale e' 22, da confermare con il provider. Il dominio pubblico puo' essere
dietro un proxy e non coincidere con l'host SSH.

Da File o dal terminale Plesk trovare il percorso assoluto della cartella con
`artisan`, `.env` e `config.local.php`. La document root deve puntare a `public`.
Non spostare questi file e non cambiare APP_KEY. Sono richiesti Bash, rsync, tar,
flock, mysqldump, PHP 8.3 con pdo_mysql, mbstring, xml, curl e zip. Il binario
abituale e' `/opt/plesk/php/8.3/bin/php`; con una shell chroot va verificato.
Il server non ha bisogno di Node, npm o Composer per il deploy.

Creare una chiave SSH Ed25519 dedicata alle Actions, senza passphrase, e aggiungere
la sola chiave pubblica a `~/.ssh/authorized_keys` dell'utente Plesk. Non usare
root. Conservare la privata esclusivamente nei Secrets GitHub. La chiave host
del server deve essere verificata tramite il provider/terminale attendibile:
`ssh-keyscan` da solo non ne dimostra l'identita'.

In repository → Settings → Secrets and variables → Actions creare:

| Tipo | Nome | Contenuto |
| --- | --- | --- |
| Secret | `PLESK_SSH_PRIVATE_KEY` | Chiave privata dedicata, completa di intestazioni |
| Secret | `PLESK_SSH_KNOWN_HOSTS` | Riga known_hosts verificata; con porta diversa da 22 usare `[host]:porta` |
| Variable | `PLESK_HOST` | Host/IP SSH |
| Variable | `PLESK_PORT` | Porta SSH, predefinita 22 |
| Variable | `PLESK_USER` | Utente di sistema del sito |
| Variable | `PLESK_PATH` | Percorso assoluto della cartella Laravel |
| Variable | `PLESK_PHP` | Percorso assoluto PHP 8.3, opzionale se quello abituale |
| Variable | `PLESK_DEPLOY_ENABLED` | `true` per abilitare il job; altrimenti disabilitato |

I percorsi configurati devono contenere solo lettere, numeri, `/`, `.`, `_` e `-`.
Creare l'environment `production` in Settings → Environments e consentire soltanto
main. Eventuali required reviewers richiederanno approvazione nel browser a ogni
deploy. Le variabili di collegamento e i Secrets possono essere in `production`;
`PLESK_DEPLOY_ENABLED` deve essere a livello repository per la condizione del job.

Configurare tutti i valori prima di abilitare il job, quindi eseguire manualmente
`check`. Dopo la verifica eseguire il primo `deploy` e controllare login, password
salvate, media soundboard e foto. Il workflow controlla automaticamente `/up`.
Il provider deve consentire connessioni SSH dai runner GitHub ospitati.

## Conservazione dei dati e guasti

Lo script `scripts/plesk-deploy.sh` verifica ambiente production, APP_KEY esistente
e connessione MySQL. In manutenzione crea un backup database/upload con il
BackupManager esistente e una copia codice/configurazione. Se il backup fallisce
non sostituisce il codice e non avvia migrazioni. Le copie sono sul server in
`~/familyplan-deploy-backups/<data>-<pid>/`, fuori dalla document root, accessibili
solo all'utente. L'archivio codice contiene i segreti di produzione: non caricarlo
in GitHub. Controllare spazio disponibile e gestire la conservazione dei backup;
non sono cancellati automaticamente. Una copia dati resta anche in storage/app/backups.

La sincronizzazione conserva `.env`, `config.local.php`, `storage`, `public/storage`
e `public/media`. Elimina file obsoleti soltanto nelle directory di codice app,
config, routes, resources, vendor e public/build. Non esegue key:generate, seeders
o comandi di azzeramento del database. Verifica che i due file di configurazione
non siano cambiati, esegue migrazioni e ricrea le cache prima di riaprire il sito.
E' previsto un breve periodo di manutenzione; non e' un deploy atomico.

Se il backup fallisce prima della copia del codice, lo script richiede la rimozione
della manutenzione: codice e schema non sono stati modificati. Dopo l'inizio
della sincronizzazione, se un passaggio fallisce, il sito resta in manutenzione per
evitare di servire codice/database incoerenti. Consultare il log Actions e quello
Laravel. Correggere e rilanciare richiede prima di risolvere la manutenzione:
lo script rifiuta di intervenire su un sito gia' in manutenzione. Per recuperare,
da terminale Plesk ripristinare il codice dal backup se necessario; valutare le
migrazioni applicate e l'eventuale ripristino del database da `data.zip` (non
automatico, puo' perdere scritture successive). Conservare sempre APP_KEY. Dopo
il recupero eseguire con PHP 8.3 `artisan optimize:clear`, ricreare le cache e
`artisan up`. Non eseguire automaticamente `migrate:rollback`.

Se fallisce solo il controllo HTTP finale, l'app e' gia' uscita dalla manutenzione:
verificare Plesk, PHP-FPM, log e `/up`. Per disabilitare i deploy impostare
`PLESK_DEPLOY_ENABLED=false`. Le modifiche Cloud possono comunque continuare via PR.
