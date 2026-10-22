# L’isola di Milo

Sezione autenticata `/giochi/milo`, raggiungibile dal menu Strumenti. La scena
`/giochi/milo/scena` richiede anch’essa il login e usa una risposta privata non
memorizzabile. Un iframe same-origin separa il gioco dagli stili e dagli eventi
dell’app; sostituendo la shell AJAX, il browser chiude il documento del gioco.
Il gioco rilascia animazioni, sintesi vocale e risorse WebGL su pagehide e gestisce
il ritorno dalla back-forward cache.

## Percorso

Dodici capitoli indipendenti, rigiocabili e senza blocchi, timer obbligatori,
classifiche o serie giornaliere:

1. Il ponte delle carote — osservare il risultato e precisare la larghezza.
2. Una casetta per Pip — colore e grandezza.
3. Quale fiore? — chiedere chiarimenti e individuare il destinatario.
4. Tre mele nel cestino — specificare e controllare una quantità fino a tre.
5. Prima laviamo, poi mangiamo — ordinare due azioni.
6. Un riparo dalla pioggia — spiegare lo scopo.
7. La scatola misteriosa — distinguere ipotesi e osservazione.
8. Il colore che vediamo — confrontare una risposta con un oggetto visibile.
9. Un sentiero da scoprire — dire quando mancano informazioni e chiedere aiuto.
10. Il nome della casetta — nomi inventati e informazioni personali.
11. Due dettagli per il ponte — combinare colore e larghezza.
12. La festa delle buone domande — destinatari, quantità e verifica finale.

Ogni capitolo ha tre passaggi con due scelte concrete e conseguenze diverse:
osservare un bisogno, scegliere un’istruzione, controllare il risultato. Nessun
passaggio didattico è un singolo pulsante per continuare. Un tentativo non adatto
resta sullo stesso passaggio e spiega con calma cosa osservare, senza punteggi o
penalità. Le posizioni delle due opzioni variano tra le partite e restano stabili
mentre si riprova. La sintesi vocale legge entrambe nell’ordine mostrato.

Ogni capitolo mantiene cambiamenti visibili nella scena e un’attività
finale da fare fuori dallo schermo con un adulto. L’obiettivo è una sessione di
circa cinque minuti comprensiva dell’attività reale, senza prolungare artificialmente
il gioco. Le scelte sono accompagnate da simboli e dalla lettura facoltativa di
consegne e opzioni; un adulto guida il bambino quando serve. Non sono promessi
risultati educativi futuri o un apprendimento autonomo a quattro anni.

## Ambientazioni e scoperte

Ogni capitolo ha un luogo 3D distinto: ruscello, villaggio, giardino, frutteto,
cucina nel bosco, radura piovosa, osservatorio notturno, atelier, sentiero delle
lucciole, villaggio delle nuvole, gole dei ponti e prato della festa. Cambiano
terreno, costruzioni, vegetazione, cielo, luce e inquadratura; il ponte compare
soltanto nelle due missioni che lo richiedono.

Tre oggetti per luogo si possono toccare nella scena o scegliere dal pulsante
“Esplora”, disponibile anche con tastiera. Le scoperte animano piccoli dettagli;
quando un oggetto corrisponde alla scelta corrente (ad esempio il secchio blu o
il rubinetto), attiva lo stesso effetto delle opzioni della missione. Nessuna
raccolta obbligatoria: le scoperte restano temporanee e non aggiungono salvataggi.
Le animazioni ambientali rispettano `prefers-reduced-motion`. Il cambio capitolo
rilascia geometrie e materiali della scena precedente.

## Comandi e progressi

Interfaccia con due grandi scelte illustrate, testo delle opzioni da 17–20 px,
simboli separati e comandi principali da 48–56 px. In vista restano voce, schermo
intero e menu; capitoli, esplorazione, pausa e informazioni sono nel menu. La scheda
missione resta leggibile e scorre sugli schermi piccoli o in orizzontale.

La vista iniziale è più ravvicinata e centrata nello spazio libero tra comandi e
missione. Trascinamento con un dito o tasto sinistro del mouse per ruotare;
pizzico con due dita o rotella per lo zoom; due dita insieme o tasto destro per
spostare l’inquadratura. Zoom e inclinazione hanno limiti; “Rimetti la vista” nel
menu ripristina l’inquadratura senza riavviare la missione. Il trascinamento non
attiva scelte o oggetti: solo un tocco breve lo fa. Gesti annullati e interruzioni
non vengono interpretati come tocchi. La vista non viene salvata e si ripristina
al cambio capitolo. Nel menu restano pulsanti di zoom utilizzabili con tastiera.

Tocco/clic sul prato per muovere Milo. Su desktop, dopo aver dato il focus alla
scena, frecce o WASD. Pulsanti per voce, pausa, capitoli, informazioni e schermo
intero. Il pulsante sopra il gioco e quello nella scena mantengono lo stesso
iframe: avanzamento e Prova adulto non si azzerano. Nei browser con Fullscreen API
si usa lo schermo intero nativo; negli altri (in particolare Safari su alcuni
iPhone) la scena occupa tutto il viewport e nasconde l’interfaccia FamilyPlan.
Le barre del browser restano sotto il controllo del browser stesso. Il pulsante
× esce dalla modalità; anche Escape funziona su desktop. Rotazione e altezza
dinamica del viewport aggiornano la scena senza ricaricarla. I dialoghi e la scheda missione possono scorrere anche
su schermi bassi; i dialoghi nativi mantengono il focus al loro interno.

I capitoli completati, l’ultimo capitolo selezionato e la preferenza della voce
sono salvati in localStorage con chiave `familyplan.milo.v1.user-<id>`. Sono
separati per utente FamilyPlan sullo stesso browser, senza sincronizzazione tra
dispositivi o profili individuali per i bambini. Una missione interrotta riparte
dal suo primo passaggio; il completamento viene salvato solo alla fine.
La rimozione dei dati del sito elimina questi progressi. Se lo storage non è
disponibile, il gioco resta utilizzabile e lo segnala.

## Prova adulto

Il collegamento sopra il gioco apre `/giochi/milo?prova=1`. Il parametro viene
propagato alla scena autenticata. In questa modalità il gioco non legge né scrive
localStorage: completamenti, ultimo capitolo e voce restano soltanto in memoria.
Tutti i capitoli si possono scegliere dal sentiero; quelli provati hanno una
marcatura temporanea. Ricaricando o riaprendo la prova si riparte da zero.

Il comando “Esci dalla prova” torna alla modalità normale, che rilegge i suoi
progressi precedenti. Entrata e uscita usano la navigazione AJAX di FamilyPlan.
È una modalità di esplorazione senza salvataggi, non un controllo parentale o
un ruolo diverso: entrambi i percorsi richiedono sempre l’autenticazione.

La voce usa SpeechSynthesis del browser e si può disattivare. Disponibilità,
pronuncia e implementazione locale/remota dipendono dal dispositivo; il testo
letto è esclusivamente quello predisposto nel gioco. Nessun microfono, input
libero, richiesta a modelli AI, pubblicità o servizio esterno viene usato dal codice.

## File e verifiche

- `public/js/milo/chapters.js`: testi, scelte, riferimenti ai passaggi ed effetti.
- `public/js/milo/worlds.js`: luoghi procedurali, scoperte e animazioni ambientali.
- `public/js/milo/game.js`: scena Three.js, effetti, stato, input e ciclo di vita.
- `public/css/milo.css`: stili interni della scena, separati da quelli FamilyPlan.
- `public/vendor/three/`: Three.js 0.160.0 self-hosted e relativa licenza MIT.
- `resources/views/milo/`: contenitore FamilyPlan e documento del gioco.

Nessuna migration o dipendenza server aggiuntiva. Vite non è necessario per
questi asset pubblici; i file del gioco sono versionati con filemtime e la libreria
con un nome contenente la versione. Non usare media o database di produzione.

Verifiche automatiche: `vendor/bin/phpunit` e
`node --test tests/Browser/milo-chapters.test.cjs`. I test del percorso verificano
riferimenti e conclusione raggiungibile per tutti i capitoli. Per una modifica
alla scena, verificare inoltre un browser WebGL con sessione locale sintetica:
missioni, tentativi alternativi, pausa, rientro AJAX, progressi, dialoghi, desktop,
telefono verticale e orizzontale. Il controllo `/up` del deploy non copre il WebGL.
