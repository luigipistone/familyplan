/* Prepared stories: two meaningful choices at every step, with gentle retries. */
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
        title: "Come arriva Pip alle carote?",
        text: "Guarda il ruscello tra Pip e le carote. Cosa chiediamo a Milo?",
        choices: [
          {
            label: "Un ponte per passare",
            icon: "🌉",
            effect: "bridge-build",
            to: 1,
          },
          {
            label: "Una bandierina per decorare",
            icon: "🚩",
            feedback:
              "La bandierina decora la riva, ma resta l’acqua in mezzo. Cosa può unire le due rive?",
          },
        ],
      },
      {
        title: "Il ponte è stretto.",
        text: "Pip ha provato, ma non riesce a passare. Cosa cambiamo?",
        choices: [
          {
            label: "Facciamolo più largo",
            icon: "↔",
            effect: "bridge-widen",
            to: 2,
          },
          {
            label: "Coloriamolo di blu",
            icon: "🔵",
            feedback:
              "È diventato blu, ma lo spazio è lo stesso. Guarda Pip: gli serve un ponte più largo.",
            effect: "bridge-blue",
          },
        ],
      },
      {
        title: "Adesso Pip può passare?",
        text: "Guarda quanto spazio c’è sul ponte. Quale richiesta ha aiutato Pip?",
        choices: [
          {
            label: "Un ponte largo per Pip",
            icon: "↔",
            effect: "cross",
            to: "done",
          },
          {
            label: "Un ponte solo colorato",
            icon: "🎨",
            feedback:
              "Il colore cambia l’aspetto. La larghezza dà a Pip lo spazio per passare. Quale dettaglio ci serve?",
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
        title: "Pip desidera una casetta blu.",
        text: "Quale colore chiediamo a Milo per la casetta?",
        choices: [
          {
            label: "Blu",
            icon: "🔵",
            effect: "house-blue",
            to: 1,
          },
          {
            label: "Rosso",
            icon: "🔴",
            feedback:
              "Ecco il rosso. Pip desidera il blu: confrontiamo i due cerchi.",
            effect: "house-red",
          },
        ],
      },
      {
        title: "C’è spazio per le orecchie?",
        text: "Guarda Pip vicino alla piccola casa. Cosa chiediamo di cambiare?",
        choices: [
          {
            label: "Una casa più grande",
            icon: "🏡",
            effect: "house-large",
            to: 2,
          },
          {
            label: "Un altro colore",
            icon: "🎨",
            feedback:
              "Cambiare colore lascia la casa piccola. Le orecchie di Pip hanno bisogno di più spazio.",
          },
        ],
      },
      {
        title: "Controlliamo la casetta.",
        text: "Pip voleva il blu e spazio per le orecchie. Quale richiesta descrive la casa che gli serve?",
        choices: [
          {
            label: "Blu e grande",
            icon: "🔵",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Blu e piccolina",
            icon: "🔹",
            feedback:
              "Il colore va bene. Ma per le orecchie serve anche una casa grande. Guarda la casa e riprova.",
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
        text: "Ci sono due fiori. Milo non sa quale intendi. Come può capirlo?",
        choices: [
          {
            label: "Chiede: quale fiore?",
            icon: "❓",
            effect: "point-flowers",
            to: 1,
          },
          {
            label: "Sceglie senza chiedere",
            icon: "🤔",
            feedback:
              "Potrebbe scegliere un fiore diverso da quello che intendi. Una piccola domanda ci aiuta a capirci.",
          },
        ],
      },
      {
        title: "Il fiore giallo ha sete.",
        text: "Guarda i due fiori. Quale indichiamo a Milo?",
        choices: [
          {
            label: "Il fiore giallo",
            icon: "🌼",
            effect: "water-flower",
            to: 2,
          },
          {
            label: "Il fiore rosa",
            icon: "🌸",
            feedback:
              "Hai indicato il rosa. Il fiore che ha sete è giallo: confrontiamo i colori.",
            effect: "point-pink",
          },
        ],
      },
      {
        title: "Come chiediamo proprio quel fiore?",
        text: "Ci sono ancora due fiori. Quale frase aiuta Milo a scegliere quello giallo?",
        choices: [
          {
            label: "Annaffia il fiore giallo",
            icon: "🌼",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Annaffia quel fiore",
            icon: "🌱",
            feedback:
              "“Quel fiore” non dice quale dei due. Il colore è un indizio che possiamo aggiungere.",
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
        title: "Contiamo quello che vediamo.",
        text: "Guarda il cestino, non gli alberi. Quante mele ci sono adesso?",
        choices: [
          {
            label: "Una mela",
            icon: "🍎",
            effect: "apples-one",
            to: 1,
          },
          {
            label: "Tre mele",
            icon: "🍎 🍎 🍎",
            feedback:
              "Indichiamo le mele nel cestino una alla volta: per ora ce n’è una. Le altre sono ancora fuori.",
          },
        ],
      },
      {
        title: "Per il picnic servono tre mele.",
        text: "Quale richiesta dice a Milo quante mele mettere nel cestino?",
        choices: [
          {
            label: "Metti tre mele",
            icon: "🍎 🍎 🍎",
            effect: "apples-three",
            to: 2,
          },
          {
            label: "Metti una mela",
            icon: "🍎",
            feedback:
              "Una è meno di tre. Nel cestino ne vogliamo tre: contiamo uno, due, tre.",
            effect: "apples-one",
          },
        ],
      },
      {
        title: "Controlliamo il risultato.",
        text: "Indica ogni mela nel cestino mentre conti con un adulto. Quante ne vedi?",
        choices: [
          {
            label: "Tre mele",
            icon: "🍎 🍎 🍎",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Una mela",
            icon: "🍎",
            feedback:
              "Proviamo insieme: una, due, tre. Ogni mela si conta una volta.",
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
        title: "Una mela appena raccolta.",
        text: "Prima di metterla nel piatto, quale azione chiediamo a Milo?",
        choices: [
          {
            label: "Lava la mela",
            icon: "💧",
            effect: "wash-apple",
            to: 1,
          },
          {
            label: "Mettila subito nel piatto",
            icon: "🍽",
            feedback:
              "La mela è appena raccolta. Prima la laviamo con l’acqua, poi la serviamo.",
          },
        ],
      },
      {
        title: "Ora la mela è lavata.",
        text: "Abbiamo usato l’acqua. Qual è il prossimo passo per preparare la merenda?",
        choices: [
          {
            label: "Metterla nel piatto",
            icon: "🍽",
            effect: "serve-apple",
            to: 2,
          },
          {
            label: "Lavarla ancora",
            icon: "💧",
            feedback:
              "L’abbiamo già lavata. Per finire la merenda manca il piatto: cosa facciamo adesso?",
            effect: "wash-apple",
          },
        ],
      },
      {
        title: "Aiutiamo Milo a ricordare l’ordine.",
        text: "Quale richiesta racconta le due azioni nell’ordine giusto?",
        choices: [
          {
            label: "Prima acqua, poi piatto",
            icon: "💧 → 🍽",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Prima piatto, poi acqua",
            icon: "🍽 → 💧",
            feedback:
              "Prima laviamo la mela. Solo dopo la mettiamo nel piatto: acqua, poi piatto.",
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
        title: "Sta piovendo su Pip.",
        text: "Guarda le gocce. Quale bisogno spieghiamo a Milo?",
        choices: [
          {
            label: "Pip vuole restare asciutto",
            icon: "☂️",
            effect: "rain-start",
            to: 1,
          },
          {
            label: "Pip vuole fare una festa",
            icon: "🎈",
            feedback:
              "Una festa può aspettare. Guarda le gocce sulle orecchie: adesso Pip cerca un riparo.",
          },
        ],
      },
      {
        title: "Scegliamo un aiuto.",
        text: "Quale oggetto può coprire Pip e fermare le gocce?",
        choices: [
          {
            label: "Un ombrello aperto",
            icon: "☂️",
            effect: "umbrella-open",
            to: 2,
          },
          {
            label: "Un palloncino",
            icon: "🎈",
            feedback:
              "Il palloncino è piccolo e vola. Non copre Pip dalla pioggia. Quale oggetto si apre sopra di lui?",
          },
        ],
      },
      {
        title: "Perché abbiamo aperto l’ombrello?",
        text: "Guarda Pip sotto il riparo. Quale frase spiega a Milo a cosa serve?",
        choices: [
          {
            label: "Per tenere Pip asciutto",
            icon: "🐰",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Per cambiare il colore di Pip",
            icon: "🎨",
            feedback:
              "Il colore di Pip non cambia. L’ombrello ferma le gocce: quale bisogno abbiamo risolto?",
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
        title: "Milo dice: “Forse c’è una pallina”.",
        text: "La scatola è chiusa. Come scopriamo cosa c’è davvero?",
        choices: [
          {
            label: "Apriamo e guardiamo",
            icon: "🔎",
            effect: "box-open",
            to: 1,
          },
          {
            label: "Diciamo che è una pallina",
            icon: "⚽",
            feedback:
              "“Forse” vuol dire che non sappiamo ancora. Per scoprirlo possiamo aprire e guardare.",
          },
        ],
      },
      {
        title: "Che cosa vedi nella scatola?",
        text: "Guarda l’oggetto uscito dalla scatola. Quale forma ha?",
        choices: [
          {
            label: "Una stella",
            icon: "⭐",
            effect: "show-star",
            to: 2,
          },
          {
            label: "Una pallina",
            icon: "⚽",
            feedback:
              "La pallina è rotonda. Questo oggetto ha le punte: osserviamolo insieme.",
          },
        ],
      },
      {
        title: "Come abbiamo scoperto la stella?",
        text: "Milo prima aveva un’idea diversa. Cosa ci ha aiutato a sapere?",
        choices: [
          {
            label: "Abbiamo aperto e guardato",
            icon: "👀",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Abbiamo indovinato senza guardare",
            icon: "🤔",
            feedback:
              "Prima era soltanto un’idea. Aprendo la scatola abbiamo visto la stella con i nostri occhi.",
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
        title: "Milo dice: “La pallina è blu”.",
        text: "Guarda la pallina grande. Cosa possiamo fare prima di fidarci della risposta?",
        choices: [
          {
            label: "Guardare il suo colore",
            icon: "👀",
            effect: "ball-show",
            to: 1,
          },
          {
            label: "Ripetere senza guardare",
            icon: "💬",
            feedback:
              "Anche Milo può confondersi. La pallina è qui: guardarla ci aiuta a controllare.",
          },
        ],
      },
      {
        title: "Che colore vedi davvero?",
        text: "Osserva la pallina grande. Quale colore diciamo a Milo?",
        choices: [
          {
            label: "Rossa",
            icon: "🔴",
            effect: "ball-correct",
            to: 2,
          },
          {
            label: "Blu",
            icon: "🔵",
            feedback:
              "Ora c’è anche una pallina blu più piccola per confrontare. Quella grande è diversa: guardiamola.",
            effect: "ball-compare",
          },
        ],
      },
      {
        title: "Aiutiamo Milo a correggere la frase.",
        text: "Quale richiesta descrive la pallina grande che vediamo?",
        choices: [
          {
            label: "La pallina grande è rossa",
            icon: "🔴",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "La pallina grande è blu",
            icon: "🔵",
            feedback:
              "Confrontiamo la frase con la pallina grande. È rossa: possiamo dirlo anche se prima Milo ha detto blu.",
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
        text: "Il cartello è girato. Non abbiamo ancora un indizio. Cosa diciamo?",
        choices: [
          {
            label: "Non lo sappiamo ancora",
            icon: "❓",
            effect: "sign-question",
            to: 1,
          },
          {
            label: "Sicuramente al picnic",
            icon: "🧺",
            feedback:
              "Potrebbe, ma non abbiamo guardato nessun indizio. Possiamo dire “non lo so ancora”.",
          },
        ],
      },
      {
        title: "Cerchiamo un indizio con un adulto.",
        text: "Come possiamo scoprire dove porta il sentiero?",
        choices: [
          {
            label: "Guardiamo il cartello",
            icon: "➡",
            effect: "sign-reveal",
            to: 2,
          },
          {
            label: "Inventiamo una destinazione",
            icon: "✨",
            feedback:
              "Inventare è bello nelle storie. Qui cerchiamo dove porta davvero il sentiero: il cartello può aiutarci.",
          },
        ],
      },
      {
        title: "Ora guarda la freccia.",
        text: "Quale luogo è comparso vicino al cartello?",
        choices: [
          {
            label: "Una casetta",
            icon: "🏡",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Un tavolo da picnic",
            icon: "🧺",
            feedback:
              "Cerca il piccolo tetto e le pareti vicino alla freccia: è una casetta.",
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
        title: "Un cartello per la casa di Pip.",
        text: "Stiamo giocando. Quale nome possiamo chiedere a Milo senza dire dove abitiamo?",
        choices: [
          {
            label: "Un nome inventato",
            icon: "✨",
            effect: "sign-private",
            to: 1,
          },
          {
            label: "Il nostro vero indirizzo",
            icon: "🏠",
            feedback:
              "L’indirizzo vero è un’informazione personale. Per il gioco inventiamo un nome; delle informazioni vere parliamo con un adulto.",
          },
        ],
      },
      {
        title: "Scegliamo un nome di fantasia.",
        text: "Quale nome è inventato per la casetta del gioco?",
        choices: [
          {
            label: "Casa delle Nuvole",
            icon: "☁️",
            effect: "sign-fantasy",
            to: 2,
          },
          {
            label: "La via e il numero di casa mia",
            icon: "🏠",
            feedback:
              "La via e il numero dicono dove abitiamo davvero. La casa di Pip può avere un nome di fantasia.",
          },
        ],
      },
      {
        title: "E se ci chiedono dove abitiamo?",
        text: "Prima di condividere informazioni vere su di noi, cosa facciamo?",
        choices: [
          {
            label: "Chiediamo a un adulto",
            icon: "🤝",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "Le diciamo subito",
            icon: "💬",
            feedback:
              "Prima ci fermiamo e chiediamo a un adulto di fiducia. Nel gioco possiamo continuare con nomi inventati.",
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
        title: "Pip chiede un ponte blu e largo.",
        text: "Cominciamo dal colore. Quale scegliamo?",
        choices: [
          {
            label: "Blu",
            icon: "🔵",
            effect: "bridge-blue",
            to: 1,
          },
          {
            label: "Lasciamo il colore del legno",
            icon: "🟤",
            feedback:
              "Il legno è marrone. Pip ha chiesto il blu: quale cerchio gli corrisponde?",
          },
        ],
      },
      {
        title: "Il colore è giusto. E lo spazio?",
        text: "Guarda il ponte stretto e Pip. Quale altro dettaglio aggiungiamo alla richiesta?",
        choices: [
          {
            label: "Più largo per Pip",
            icon: "↔",
            effect: "bridge-widen",
            to: 2,
          },
          {
            label: "Ancora più blu",
            icon: "🔵",
            feedback:
              "Il blu c’è già. Ora manca lo spazio per passare: dobbiamo cambiare la larghezza.",
            effect: "bridge-blue",
          },
        ],
      },
      {
        title: "Controlliamo le due richieste.",
        text: "Guarda colore e larghezza. Quale frase dice tutto quello che serve a Pip?",
        choices: [
          {
            label: "Un ponte blu e largo",
            icon: "🔵 ↔",
            effect: "cross",
            to: "done",
          },
          {
            label: "Un ponte blu",
            icon: "🔵",
            feedback:
              "Il colore è giusto, ma manca un dettaglio nella frase: per Pip serve anche lo spazio.",
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
        title: "Un picnic per Milo e Pip.",
        text: "Conta gli amici: Milo e Pip. Quanti posti chiediamo?",
        choices: [
          {
            label: "Due posti",
            icon: "🪑 🪑",
            effect: "party-setup",
            to: 1,
          },
          {
            label: "Un posto",
            icon: "🪑",
            feedback:
              "Con un posto uno dei due amici resta senza. Indichiamoli insieme: Milo, Pip.",
          },
        ],
      },
      {
        title: "Una mela per ogni amico.",
        text: "Ci sono due posti. Quante mele chiediamo, per darne una a ciascuno?",
        choices: [
          {
            label: "Due mele",
            icon: "🍎 🍎",
            effect: "party-two",
            to: 2,
          },
          {
            label: "Una mela",
            icon: "🍎",
            feedback:
              "Con una mela uno dei due amici resta senza. Un amico, una mela; due amici, due mele.",
          },
        ],
      },
      {
        title: "Controlliamo il picnic.",
        text: "Guarda i posti e le mele. Ogni amico può avere una mela?",
        choices: [
          {
            label: "Sì: due amici e due mele",
            icon: "🍎 🍎",
            effect: "celebrate",
            to: "done",
          },
          {
            label: "No: manca una mela",
            icon: "🍎",
            feedback:
              "Indichiamo le due mele: una per Milo e una per Pip. Ora bastano per entrambi.",
          },
        ],
      },
    ],
  },
];
