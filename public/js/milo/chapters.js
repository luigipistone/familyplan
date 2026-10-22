/* Prepared, age-appropriate stories: no model requests or child text input. */
window.MiloChapters = [
  {
    id: "ponte",
    title: "Il ponte delle carote",
    skill: "Osservare e migliorare",
    scene: "bridge",
    icon: "🌉",
    activity:
      "Con un adulto, costruisci un ponte di mattoncini per un pupazzo. Come gli spiegheresti quanto deve essere largo?",
    parent:
      "Una richiesta si può migliorare dopo aver osservato il risultato. Il primo ponte non è un errore della bambina: manca soltanto un dettaglio.",
    steps: [
      {
        title: "Una carota dall’altra parte.",
        text: "Pip vorrebbe raggiungere le carote, ma c’è il ruscello. Come possiamo aiutarlo?",
        choices: [
          {
            label: "Costruiamo un ponte",
            icon: "🌉",
            effect: "bridge-build",
            to: 1,
          },
        ],
      },
      {
        title: "Pip ha bisogno di più spazio.",
        text: "Il ponte è stretto per Pip. Quale dettaglio possiamo aggiungere alla richiesta?",
        choices: [
          {
            label: "Un ponte più largo",
            icon: "↔",
            effect: "bridge-widen",
            to: 2,
          },
          {
            label: "Un ponte blu",
            icon: "🔵",
            effect: "bridge-blue",
            feedback:
              "Ora è blu! Il colore è cambiato. Pip ha ancora bisogno di più spazio per passare.",
          },
        ],
      },
      {
        title: "La richiesta è diventata più precisa.",
        text: "“Milo, costruisci un ponte largo per Pip.” Hai spiegato cosa serve e per chi.",
        choices: [
          {
            label: "Guardiamo Pip attraversare",
            icon: "🐰",
            effect: "cross",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "casetta",
    title: "Una casetta per Pip",
    skill: "Dire colore e grandezza",
    scene: "house",
    icon: "🏡",
    activity:
      "Cerca un oggetto blu e uno grande. Poi descrivi a un adulto una casa per il tuo pupazzo.",
    parent:
      "Colore e dimensione sono dettagli concreti. Mostrare il risultato aiuta a collegare le parole alle caratteristiche degli oggetti.",
    steps: [
      {
        title: "Pip sogna una casetta blu.",
        text: "Aiutiamo Milo a capire quale colore scegliere. Guarda la casetta mentre cambia.",
        choices: [
          { label: "Una casetta blu", icon: "🔵", effect: "house-blue", to: 1 },
          {
            label: "Proviamo il rosso",
            icon: "🔴",
            effect: "house-red",
            feedback:
              "Ecco il rosso. Pip ci ha chiesto il blu: quale cerchio scegliamo?",
          },
        ],
      },
      {
        title: "C’è spazio per le orecchie?",
        text: "La casetta è blu, ma piccolina. Pip ha due belle orecchie lunghe. Come possiamo aiutarlo?",
        choices: [
          {
            label: "Facciamola più grande",
            icon: "🏡",
            effect: "house-large",
            to: 2,
          },
        ],
      },
      {
        title: "Due dettagli, una richiesta.",
        text: "“Milo, fai una casetta blu e grande per Pip.” Colore, grandezza e destinatario sono chiari.",
        choices: [
          {
            label: "Guardiamo la nuova casetta",
            icon: "🐰",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "fiore",
    title: "Quale fiore?",
    skill: "Chiedere un chiarimento",
    scene: "flowers",
    icon: "🌼",
    activity:
      "Di’ a un adulto “prendi quella cosa”. Poi aggiungi un dettaglio che gli faccia capire proprio quale oggetto vuoi.",
    parent:
      "Milo fa una domanda quando non sa quale oggetto si intende. Chiedere chiarimenti è un comportamento utile anche per un assistente AI.",
    steps: [
      {
        title: "“Annaffia quel fiore.”",
        text: "Ci sono due fiori. Milo non sa quale intendi. Che domanda può farti?",
        choices: [
          {
            label: "Quale dei due fiori?",
            icon: "❓",
            effect: "point-flowers",
            to: 1,
          },
        ],
      },
      {
        title: "Il fiore giallo ha sete.",
        text: "Ora abbiamo un dettaglio: il colore. Aiutiamo Milo a trovare il fiore giusto.",
        choices: [
          {
            label: "Annaffia il fiore giallo",
            icon: "🌼",
            effect: "water-flower",
            to: 2,
          },
          {
            label: "Guarda quello rosa",
            icon: "🌸",
            effect: "point-pink",
            feedback:
              "Quello è rosa. La richiesta riguarda il fiore giallo: guardiamolo insieme.",
          },
        ],
      },
      {
        title: "Una domanda in più aiuta.",
        text: "Il fiore ha ricevuto l’acqua. Prima abbiamo chiarito quale fiore, poi abbiamo agito.",
        choices: [
          {
            label: "Salutiamo il giardino",
            icon: "🌱",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "mele",
    title: "Tre mele nel cestino",
    skill: "Specificare una quantità",
    scene: "apples",
    icon: "🍎",
    activity:
      "Conta insieme a un adulto tre mattoncini. Poi chiedigli di passarti proprio tre oggetti, uno alla volta.",
    parent:
      "Si contano fino a tre oggetti visibili. Se è troppo difficile, l’adulto indica ogni mela mentre conta insieme alla bambina.",
    steps: [
      {
        title: "Quante mele ci servono?",
        text: "Per il picnic vogliamo tre mele. Milo ne ha messa una. Contiamola insieme: uno.",
        choices: [
          {
            label: "Contiamo fino a tre",
            icon: "🍎",
            effect: "apples-one",
            to: 1,
          },
        ],
      },
      {
        title: "Aggiungiamo la quantità.",
        text: "“Milo, metti tre mele nel cestino.” Guardiamo comparire le mele e contiamole.",
        choices: [
          { label: "Tre mele", icon: "③", effect: "apples-three", to: 2 },
        ],
      },
      {
        title: "Uno, due, tre.",
        text: "Controlliamo il risultato insieme. La richiesta diceva tre e ora vediamo tre mele.",
        choices: [
          {
            label: "Il cestino è pronto",
            icon: "🧺",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "merenda",
    title: "Prima laviamo, poi mangiamo",
    skill: "Mettere in ordine due azioni",
    scene: "wash",
    icon: "💧",
    activity:
      "Prima lava le mani con un adulto, poi prepara la merenda. Racconta le due azioni nell’ordine in cui le fate.",
    parent:
      "Due azioni collegate rendono concreta l’idea di una sequenza di istruzioni. Non si richiede di leggere né di ricordare liste lunghe.",
    steps: [
      {
        title: "Prepariamo la merenda.",
        text: "Abbiamo raccolto una mela. Prima la laviamo con l’acqua, poi la mettiamo nel piatto.",
        choices: [
          {
            label: "Prima laviamo la mela",
            icon: "💧",
            effect: "wash-apple",
            to: 1,
          },
          {
            label: "Il piatto è qui",
            icon: "🍽",
            feedback:
              "Il piatto è pronto. Prima di servirla, laviamo insieme la mela.",
          },
        ],
      },
      {
        title: "Ora la mela è lavata.",
        text: "Abbiamo fatto la prima azione. Qual è la prossima?",
        choices: [
          {
            label: "Mettiamola nel piatto",
            icon: "🍽",
            effect: "serve-apple",
            to: 2,
          },
        ],
      },
      {
        title: "Prima e poi.",
        text: "“Milo, prima lava la mela e poi mettila nel piatto.” Abbiamo dato due istruzioni in ordine.",
        choices: [
          {
            label: "La merenda è pronta",
            icon: "🍎",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "pioggia",
    title: "Un riparo dalla pioggia",
    skill: "Spiegare lo scopo",
    scene: "rain",
    icon: "☂️",
    activity:
      "Cerca con un adulto un oggetto che aiuta quando piove. Spiega a che cosa serve, con parole tue.",
    parent:
      "Dire lo scopo della richiesta aiuta a scegliere una soluzione adatta. Il gioco permette di osservare l’effetto della scelta.",
    steps: [
      {
        title: "Sta piovendo sull’isola.",
        text: "Pip vorrebbe restare asciutto. Diciamo a Milo non solo cosa vogliamo, ma anche a cosa deve servire.",
        choices: [
          {
            label: "Un riparo per Pip",
            icon: "🐰",
            effect: "rain-start",
            to: 1,
          },
        ],
      },
      {
        title: "Che cosa ferma le gocce?",
        text: "Un ombrello aperto può riparare Pip. Un palloncino è bello per una festa, ma non copre le sue orecchie.",
        choices: [
          {
            label: "Apriamo l’ombrello",
            icon: "☂️",
            effect: "umbrella-open",
            to: 2,
          },
        ],
      },
      {
        title: "Lo scopo era restare asciutti.",
        text: "“Milo, apri un ombrello per riparare Pip dalla pioggia.” Abbiamo spiegato il bisogno.",
        choices: [
          {
            label: "Pip è al riparo",
            icon: "💛",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "scatola",
    title: "La scatola misteriosa",
    skill: "Distinguere un’ipotesi da una prova",
    scene: "box",
    icon: "📦",
    activity:
      "Un adulto nasconde un oggetto in una scatola. Prima fate un’ipotesi, poi aprite e guardate che cosa c’è davvero.",
    parent:
      "Milo dice chiaramente “forse”. La bambina può osservare una prova accessibile e correggere una risposta senza rimproveri.",
    steps: [
      {
        title: "Cosa c’è nella scatola?",
        text: "Milo dice: “Forse c’è una pallina!”. La scatola è chiusa: è soltanto un’ipotesi.",
        choices: [
          {
            label: "Apriamo e guardiamo",
            icon: "🔎",
            effect: "box-open",
            to: 1,
          },
        ],
      },
      {
        title: "Dentro c’è una stella!",
        text: "Ora abbiamo guardato. Possiamo dire a Milo quello che vediamo.",
        choices: [
          { label: "Vedo una stella", icon: "⭐", effect: "show-star", to: 2 },
        ],
      },
      {
        title: "Possiamo correggerci insieme.",
        text: "“Grazie, avevo fatto un’ipotesi”, dice Milo. Una risposta si può controllare.",
        choices: [
          {
            label: "Abbiamo una prova",
            icon: "💛",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "pallina",
    title: "Il colore che vediamo",
    skill: "Confrontare parole e risultato",
    scene: "ball",
    icon: "🔴",
    activity:
      "Mostra a un adulto un oggetto rosso. Se lui dice per gioco “è blu”, racconta con calma che cosa vedi.",
    parent:
      "Una risposta sicura può essere sbagliata. Conta il confronto con l’oggetto visibile, non l’obbedienza al personaggio.",
    steps: [
      {
        title: "Milo ha confuso un colore.",
        text: "La pallina è rossa. Milo dice: “Ecco una pallina blu!”. Guardiamola insieme.",
        choices: [
          { label: "Guardiamo bene", icon: "🔎", effect: "ball-show", to: 1 },
        ],
      },
      {
        title: "Che colore vedi?",
        text: "Puoi aiutare Milo a correggersi. Scegli il colore della pallina.",
        choices: [
          { label: "È rossa", icon: "🔴", effect: "ball-correct", to: 2 },
          {
            label: "Confrontiamo con il blu",
            icon: "🔵",
            effect: "ball-compare",
            feedback:
              "Ora vedi anche una piccola pallina blu. Quella grande è rossa: quale colore descrive la grande?",
          },
        ],
      },
      {
        title: "Grazie per aver controllato.",
        text: "Milo si corregge: “La pallina è rossa”. Hai confrontato le parole con quello che vedi.",
        choices: [
          {
            label: "Ora siamo d’accordo",
            icon: "🤝",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "sentiero",
    title: "Un sentiero da scoprire",
    skill: "Dire quando non si sa",
    scene: "sign",
    icon: "🧭",
    activity:
      "Chiedi a un adulto qualcosa che ancora non sai. Potete rispondere “non lo so ancora” e cercare insieme.",
    parent:
      "Si normalizza l’incertezza: Milo non inventa una strada quando gli mancano informazioni. L’adulto resta il riferimento.",
    steps: [
      {
        title: "Dove porta questo sentiero?",
        text: "Milo non lo sa ancora. Il cartello è girato e non abbiamo informazioni sufficienti.",
        choices: [
          {
            label: "Chiediamo un indizio",
            icon: "❓",
            effect: "sign-question",
            to: 1,
          },
        ],
      },
      {
        title: "Guardiamo il cartello insieme.",
        text: "Un adulto ci aiuta a girarlo. Ora la freccia indica la casetta.",
        choices: [
          {
            label: "Giriamo il cartello",
            icon: "➡",
            effect: "sign-reveal",
            to: 2,
          },
        ],
      },
      {
        title: "Ora abbiamo un’informazione.",
        text: "Prima non sapevamo. Abbiamo cercato un indizio con un adulto, invece di inventare.",
        choices: [
          {
            label: "Salutiamo il sentiero",
            icon: "🏡",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "cartello",
    title: "Il nome della casetta",
    skill: "Tenere private le informazioni personali",
    scene: "privacy",
    icon: "🔐",
    activity:
      "Con un adulto, inventa un nome per la casa di un pupazzo. Le informazioni vere di casa si gestiscono insieme ai grandi.",
    parent:
      "Nessun indirizzo, nome reale o dato personale viene chiesto o inserito. Si usa un nome di fantasia e si ricorda di chiedere a un adulto prima di condividere informazioni.",
    steps: [
      {
        title: "Una casetta di fantasia.",
        text: "Prepariamo un cartello per la casa di Pip. Possiamo inventare un nome, senza scrivere dove abitiamo davvero.",
        choices: [
          {
            label: "Inventiamo un nome",
            icon: "✨",
            effect: "sign-private",
            to: 1,
          },
          {
            label: "Chiediamo a un adulto",
            icon: "🤝",
            effect: "sign-private",
            to: 1,
          },
        ],
      },
      {
        title: "La Casa delle Nuvole.",
        text: "È un nome inventato per il gioco. Il nostro vero indirizzo resta una cosa da gestire con un adulto.",
        choices: [
          {
            label: "Casa delle Nuvole",
            icon: "☁️",
            effect: "sign-fantasy",
            to: 2,
          },
        ],
      },
      {
        title: "Fantasia e informazioni vere.",
        text: "Possiamo giocare con i nomi inventati. Prima di condividere cose vere su di noi, chiediamo a un adulto.",
        choices: [
          {
            label: "Il cartello è pronto",
            icon: "🏡",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "dettagli",
    title: "Due dettagli per il ponte",
    skill: "Unire due caratteristiche",
    scene: "bridge-two",
    icon: "🎨",
    activity:
      "Chiedi a un adulto un oggetto con due caratteristiche, per esempio un mattoncino grande e blu. Poi controllate insieme.",
    parent:
      "Si combinano due proprietà già conosciute: colore e larghezza. L’adulto può ricordare la prima mentre si sceglie la seconda.",
    steps: [
      {
        title: "Un ponte largo e blu.",
        text: "Pip vuole attraversare. Questa volta vorrebbe anche un ponte blu. Facciamo una cosa alla volta.",
        choices: [
          {
            label: "Prima lo coloriamo di blu",
            icon: "🔵",
            effect: "bridge-blue",
            to: 1,
          },
        ],
      },
      {
        title: "È blu. C’è abbastanza spazio?",
        text: "Abbiamo scelto il colore. Aggiungiamo anche la larghezza per far passare Pip.",
        choices: [
          {
            label: "Ora rendiamolo largo",
            icon: "↔",
            effect: "bridge-widen",
            to: 2,
          },
        ],
      },
      {
        title: "Largo e blu, proprio così.",
        text: "“Milo, fai un ponte largo e blu per Pip.” Guardiamo se entrambe le richieste sono rispettate.",
        choices: [
          {
            label: "Facciamo passare Pip",
            icon: "🐰",
            effect: "cross",
            to: "done",
          },
        ],
      },
    ],
  },
  {
    id: "festa",
    title: "La festa delle buone domande",
    skill: "Chiedere, osservare e controllare",
    scene: "party",
    icon: "🎈",
    activity:
      "Prepara con un adulto una piccola festa per due pupazzi. Spiega cosa serve, per chi e quanti oggetti vuoi. Poi guardate il risultato.",
    parent:
      "Una missione conclusiva riunisce destinatario, quantità e controllo. Le competenze vengono riprese nel gioco e nelle attività reali, senza attribuire al percorso risultati futuri garantiti.",
    steps: [
      {
        title: "Un picnic per due amici.",
        text: "Prepariamo un tavolino per Milo e Pip. Sono due: scegliamo due posti.",
        choices: [
          {
            label: "Due posti per i due amici",
            icon: "②",
            effect: "party-setup",
            to: 1,
          },
        ],
      },
      {
        title: "Una mela per ciascuno.",
        text: "Ci servono due mele, una per ogni posto. Diciamolo chiaramente a Milo.",
        choices: [
          {
            label: "Metti due mele sul tavolo",
            icon: "🍎",
            effect: "party-two",
            to: 2,
          },
        ],
      },
      {
        title: "Guardiamo insieme il risultato.",
        text: "Due posti e due mele. C’è quello che avevamo chiesto? Prima abbiamo spiegato, poi abbiamo controllato.",
        choices: [
          {
            label: "Sì, il picnic è pronto",
            icon: "🎉",
            effect: "celebrate",
            to: "done",
          },
        ],
      },
    ],
  },
];
