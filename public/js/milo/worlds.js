/* The locations are procedural and self-hosted. Discoveries are ephemeral. */
(() => {
  const definitions = {
    ponte: {
      name: "Il ruscello delle carote",
      kind: "river",
      shape: "round",
      sky: "#dceee7",
      ground: "#aacb91",
      base: "#d8bc94",
      camera: [10, 12, 15],
      river: true,
      discoveries: [
        [
          "ripple",
          "💧",
          "Le onde del ruscello",
          "Toccando l’acqua, piccoli cerchi si allargano.",
        ],
        [
          "boat",
          "⛵",
          "La barchetta",
          "La barchetta dondola piano sull’acqua.",
        ],
        [
          "timber",
          "🪵",
          "Le assi per il ponte",
          "Con queste assi possiamo costruire un ponte.",
          ["bridge-build"],
        ],
      ],
    },
    casetta: {
      name: "Il villaggio delle casette",
      kind: "village",
      shape: "square",
      sky: "#f1e9d7",
      ground: "#d9d6b3",
      base: "#d2b697",
      camera: [8, 11, 15],
      discoveries: [
        [
          "well",
          "🪣",
          "Il pozzo del villaggio",
          "Il secchio sale e scende: nel pozzo c’è dell’acqua.",
        ],
        [
          "blue-paint",
          "🔵",
          "Il secchio blu",
          "Questo è il colore blu della casetta di Pip.",
          ["house-blue"],
        ],
        [
          "red-paint",
          "🔴",
          "Il secchio rosso",
          "Questo è il colore rosso. Possiamo confrontare i due colori.",
          ["house-red"],
        ],
      ],
    },
    fiore: {
      name: "Il giardino delle farfalle",
      kind: "garden",
      shape: "oval",
      sky: "#e9eedb",
      ground: "#bdce9a",
      base: "#dbc4a9",
      camera: [7, 12, 16],
      discoveries: [
        [
          "butterfly",
          "🦋",
          "La farfalla",
          "La farfalla si alza e cerca un altro fiore.",
        ],
        [
          "fountain",
          "⛲",
          "La fontanella",
          "La fontanella ha una piccola goccia azzurra.",
        ],
        [
          "watering-can",
          "💦",
          "L’annaffiatoio",
          "L’annaffiatoio ci aiuta a dare acqua ai fiori.",
        ],
      ],
    },
    mele: {
      name: "Il frutteto d’autunno",
      kind: "orchard",
      shape: "hex",
      sky: "#f2e3cb",
      ground: "#d5bb7d",
      base: "#bb9772",
      camera: [-7, 11, 16],
      discoveries: [
        [
          "apple-tree",
          "🌳",
          "Il melo",
          "Il melo dondola: quante mele riusciamo a vedere?",
        ],
        [
          "leaves",
          "🍂",
          "Le foglie d’autunno",
          "Le foglie si sollevano in una piccola danza.",
        ],
        [
          "fruit-crate",
          "🧺",
          "La cassetta delle mele",
          "Possiamo contare le mele una alla volta.",
          ["apples-one", "apples-three"],
        ],
      ],
    },
    merenda: {
      name: "La cucina nel bosco",
      kind: "kitchen",
      shape: "square",
      sky: "#e8e9d9",
      ground: "#d7bf9c",
      base: "#b99878",
      camera: [9, 12, 15],
      discoveries: [
        [
          "tap",
          "🚰",
          "Il rubinetto",
          "Il rubinetto ci aiuta a lavare la mela prima della merenda.",
          ["wash-apple"],
        ],
        [
          "jar",
          "🥄",
          "Il barattolo dei cucchiai",
          "I cucchiai si muovono per gioco, senza fretta.",
        ],
        [
          "plate",
          "🍽",
          "Il piatto della merenda",
          "Dopo averla lavata, possiamo servire la mela.",
          ["serve-apple"],
        ],
      ],
    },
    pioggia: {
      name: "La radura sotto la pioggia",
      kind: "rain",
      shape: "round",
      sky: "#b9cdd2",
      ground: "#8fb49a",
      base: "#9bafa0",
      camera: [9, 12, 16],
      discoveries: [
        [
          "puddle",
          "💧",
          "La pozzanghera",
          "Le gocce disegnano cerchi nella pozzanghera.",
        ],
        [
          "mushroom",
          "🍄",
          "Il fungo del bosco",
          "Sotto il cappello del fungo si ripara una piccola coccinella.",
        ],
        [
          "umbrella",
          "☂️",
          "L’ombrello chiuso",
          "Possiamo aprirlo per dare riparo a Pip.",
          ["umbrella-open"],
        ],
      ],
    },
    scatola: {
      name: "L’osservatorio delle stelle",
      kind: "observatory",
      shape: "round",
      sky: "#303e58",
      ground: "#858da9",
      base: "#65718b",
      camera: [8, 12, 16],
      night: true,
      discoveries: [
        [
          "telescope",
          "🔭",
          "Il telescopio",
          "Il telescopio si inclina verso una stella luminosa.",
        ],
        [
          "moon",
          "🌙",
          "La luna gentile",
          "La luna illumina piano il piccolo osservatorio.",
        ],
        [
          "constellation",
          "✨",
          "Una costellazione",
          "Tre stelle si accendono una dopo l’altra.",
        ],
      ],
    },
    pallina: {
      name: "L’atelier dei colori",
      kind: "studio",
      shape: "square",
      sky: "#eee4df",
      ground: "#e6d4b4",
      base: "#c2a68e",
      camera: [7, 11, 16],
      discoveries: [
        [
          "easel",
          "🎨",
          "Il cavalletto",
          "Un pennello lascia una traccia sul foglio.",
        ],
        [
          "red-pot",
          "🔴",
          "Il colore rosso",
          "Confrontiamo questo rosso con la pallina grande.",
          ["ball-correct"],
        ],
        [
          "blue-pot",
          "🔵",
          "Il colore blu",
          "Questo è il blu: guardiamo come è diverso dal rosso.",
          ["ball-compare"],
        ],
      ],
    },
    sentiero: {
      name: "Il sentiero delle lucciole",
      kind: "fireflies",
      shape: "oval",
      sky: "#506c72",
      ground: "#769d8b",
      base: "#698779",
      camera: [-7, 12, 16],
      night: true,
      discoveries: [
        [
          "lantern",
          "🏮",
          "La lanterna",
          "La lanterna illumina un pezzetto del sentiero.",
        ],
        [
          "fireflies",
          "✨",
          "Le lucciole",
          "Le lucciole si avvicinano e poi tornano a brillare nel bosco.",
        ],
        [
          "trail-sign",
          "🧭",
          "Un cartello lungo la via",
          "Possiamo chiedere un indizio e poi guardare il cartello.",
          ["sign-question", "sign-reveal"],
        ],
      ],
    },
    cartello: {
      name: "Il villaggio delle nuvole",
      kind: "clouds",
      shape: "cloud",
      sky: "#e5e3f0",
      ground: "#d6d4e5",
      base: "#c4bfd8",
      camera: [8, 12, 16],
      discoveries: [
        [
          "cloud",
          "☁️",
          "La nuvola soffice",
          "La nuvola si allunga e cambia forma lentamente.",
        ],
        [
          "pinwheel",
          "🌬",
          "La girandola",
          "Un piccolo soffio mette in movimento la girandola.",
        ],
        [
          "mailbox",
          "📮",
          "La cassetta di fantasia",
          "Qui usiamo soltanto nomi inventati per la casa di Pip.",
          ["sign-private", "sign-fantasy"],
        ],
      ],
    },
    dettagli: {
      name: "Le gole dei ponti",
      kind: "canyon",
      shape: "split",
      sky: "#d7e5ed",
      ground: "#b7bca8",
      base: "#a2a49a",
      camera: [11, 13, 15],
      river: true,
      discoveries: [
        [
          "waterfall",
          "🌊",
          "La piccola cascata",
          "L’acqua scende tra le rocce e forma piccole onde.",
        ],
        [
          "flag",
          "🚩",
          "La bandierina",
          "La bandierina ci mostra da che parte soffia il vento.",
        ],
        [
          "paint-bucket",
          "🔵",
          "Il colore del ponte",
          "Un dettaglio della richiesta è il colore blu.",
          ["bridge-blue"],
        ],
      ],
    },
    festa: {
      name: "Il prato della festa",
      kind: "party",
      shape: "oval",
      sky: "#f2e5d4",
      ground: "#c2d09a",
      base: "#d2b38f",
      camera: [8, 12, 16],
      discoveries: [
        [
          "balloons",
          "🎈",
          "I palloncini",
          "I palloncini salutano i due amici.",
        ],
        [
          "bunting",
          "🎏",
          "Le bandierine",
          "Le bandierine danzano piano sopra il prato.",
        ],
        [
          "picnic-basket",
          "🧺",
          "Il cestino del picnic",
          "Prepariamo i posti e le mele per i due amici.",
          ["party-setup", "party-two"],
        ],
      ],
    },
  };

  function create(THREE, id) {
    const spec = definitions[id];
    if (!spec) throw new Error("Unknown Milo location: " + id);
    const root = new THREE.Group(),
      targets = [],
      materials = new Set(),
      animations = [],
      cached = new Map(),
      items = new Map();
    let time = 0;
    function mat(color, options = {}) {
      const key = color + JSON.stringify(options);
      if (cached.has(key)) return cached.get(key);
      const m = new THREE.MeshStandardMaterial({
        color,
        roughness: 0.88,
        ...options,
      });
      materials.add(m);
      cached.set(key, m);
      return m;
    }
    const ground = mat(spec.ground),
      soil = mat(spec.base),
      bark = mat("#a68c73"),
      cream = mat("#fff2d5"),
      green = mat("#7ba781"),
      yellow = mat("#e9c47b"),
      wood = mat("#cda476");
    function mesh(g, m, parent = root) {
      const o = new THREE.Mesh(g, m);
      o.castShadow = true;
      o.receiveShadow = true;
      parent.add(o);
      return o;
    }
    function group(x = 0, y = 0, z = 0, parent = root) {
      const g = new THREE.Group();
      g.position.set(x, y, z);
      parent.add(g);
      return g;
    }
    function ball(r, m, x, y, z, parent = root, scale = [1, 1, 1]) {
      const o = mesh(new THREE.SphereGeometry(r, 16, 10), m, parent);
      o.position.set(x, y, z);
      o.scale.set(...scale);
      return o;
    }
    function box(w, h, d, m, x, y, z, parent = root) {
      const o = mesh(new THREE.BoxGeometry(w, h, d), m, parent);
      o.position.set(x, y, z);
      return o;
    }
    function cylinder(rt, rb, h, m, x, y, z, parent = root, sides = 16) {
      const o = mesh(new THREE.CylinderGeometry(rt, rb, h, sides), m, parent);
      o.position.set(x, y, z);
      return o;
    }
    function lineBetween(a, b, m, r = 0.025, parent = root) {
      const av = new THREE.Vector3(...a),
        bv = new THREE.Vector3(...b),
        delta = bv.clone().sub(av);
      const o = cylinder(r, r, delta.length(), m, 0, 0, 0, parent, 8);
      o.position.copy(av.add(bv).multiplyScalar(0.5));
      o.quaternion.setFromUnitVectors(
        new THREE.Vector3(0, 1, 0),
        delta.normalize(),
      );
      return o;
    }
    function register(g, actionId, callback, radius = 0.5) {
      const info = spec.discoveries.find((d) => d[0] === actionId);
      g.userData.worldAction = actionId;
      const proxy = mesh(
        new THREE.SphereGeometry(radius, 8, 6),
        mat("#ffffff", { transparent: true, opacity: 0, depthWrite: false }),
        g,
      );
      // The proxy makes small objects easier to tap without changing their appearance.
      proxy.position.y = 0.4;
      proxy.castShadow = false;
      proxy.receiveShadow = false;
      targets.push(g);
      items.set(actionId, {
        g,
        info,
        callback,
        tapped: -100,
        baseZ: g.rotation.z,
        baseScale: g.scale.clone(),
      });
      return g;
    }
    function tree(x, z, size = 1, type = "round", color = "#79a47f") {
      const g = group(x, 0.25, z);
      g.scale.setScalar(size);
      cylinder(0.09, 0.14, 1.05, bark, 0, 0.52, 0, g, 8);
      if (type === "pine") {
        for (let i = 0; i < 3; i++)
          cylinder(
            0,
            0.7 - i * 0.13,
            0.9,
            mat(color),
            0,
            0.9 + i * 0.42,
            0,
            g,
            9,
          );
      } else {
        ball(0.61, mat(color), 0, 1.43, 0, g, [0.9, 1.1, 0.9]);
        ball(0.46, mat(color), -0.28, 1.16, 0.1, g);
        ball(0.43, green, 0.28, 1.13, 0.08, g);
      }
      return g;
    }
    function cottage(x, z, color, size = 1) {
      const g = group(x, 0.25, z);
      g.scale.setScalar(size);
      box(1.15, 0.9, 0.95, mat(color), 0, 0.45, 0, g);
      const roof = cylinder(0, 0.95, 0.6, mat("#7c9a8d"), 0, 1.2, 0, g, 4);
      roof.rotation.y = Math.PI / 4;
      box(0.26, 0.5, 0.025, mat("#586957"), 0.13, 0.25, 0.49, g);
      box(0.23, 0.23, 0.03, yellow, -0.3, 0.57, 0.5, g);
      return g;
    }
    function cloud(x, y, z, size = 1) {
      const g = group(x, y, z);
      for (let i = 0; i < 4; i++)
        ball(
          0.48,
          mat("#f2eee9"),
          (i - 1.5) * 0.4,
          Math.sin(i) * 0.1,
          0,
          g,
          [1.15, 0.75, 1],
        );
      g.scale.setScalar(size);
      return g;
    }
    function puddle(x, z, size = 1) {
      const g = group(x, 0.27, z);
      const p = cylinder(
        0.65,
        0.65,
        0.012,
        mat("#8cbec5", { roughness: 0.3 }),
        0,
        0,
        0,
        g,
        32,
      );
      p.scale.z = 0.65;
      g.scale.setScalar(size);
      const ripple = mesh(
        new THREE.RingGeometry(0.12, 0.15, 32),
        mat("#e6f2d6", {
          transparent: true,
          opacity: 0,
          side: THREE.DoubleSide,
        }),
        g,
      );
      ripple.rotation.x = -Math.PI / 2;
      ripple.position.y = 0.012;
      ripple.castShadow = false;
      return { g, ripple };
    }
    function paintPot(x, z, color) {
      const g = group(x, 0.25, z);
      cylinder(0.22, 0.18, 0.32, wood, 0, 0.16, 0, g);
      cylinder(0.19, 0.19, 0.015, mat(color), 0, 0.33, 0, g);
      lineBetween([-0.22, 0.3, 0], [0, 0.6, 0], bark, 0.025, g);
      lineBetween([0, 0.6, 0], [0.22, 0.3, 0], bark, 0.025, g);
      return g;
    }
    function fence(x, z, length = 3) {
      const g = group(x, 0.25, z);
      for (let i = 0; i <= length; i++)
        box(0.09, 0.55, 0.09, cream, i * 0.65, 0.275, 0, g);
      box(length * 0.65, 0.07, 0.07, cream, length * 0.325, 0.22, 0, g);
      box(length * 0.65, 0.07, 0.07, cream, length * 0.325, 0.45, 0, g);
      return g;
    }
    function mushroom(x, z, size = 1) {
      const g = group(x, 0.25, z);
      cylinder(0.11, 0.15, 0.4, cream, 0, 0.2, 0, g);
      const cap = mesh(
        new THREE.SphereGeometry(0.4, 16, 10, 0, Math.PI * 2, 0, Math.PI / 2),
        mat("#c78d83"),
        g,
      );
      cap.position.y = 0.38;
      cap.scale.y = 0.65;
      g.scale.setScalar(size);
      return g;
    }
    function leafPatch(x, z) {
      const g = group(x, 0.28, z);
      for (let i = 0; i < 7; i++) {
        const leaf = ball(
          0.13,
          mat(i % 2 ? "#c68f59" : "#ddb76c"),
          Math.sin(i * 2) * 0.4,
          0.02,
          Math.cos(i * 2) * 0.35,
          g,
          [1.6, 0.15, 1],
        );
        leaf.rotation.y = i;
      }
      return g;
    }
    function poolRipples(item) {
      animations.push((t) => {
        const age = t - item.tapped;
        const ripple = item.ripple;
        if (age >= 0 && age < 2.5) {
          ripple.scale.setScalar(0.5 + age * 1.4);
          ripple.material.opacity = (1 - age / 2.5) * 0.8;
        } else ripple.material.opacity = 0;
      });
    }
    // Each location has different terrain and framing, not just a new palette.
    if (spec.shape === "split") {
      for (const side of [-1, 1]) {
        box(4.1, 1.2, 7.4, soil, side * 3.05, -0.45, 0);
        box(4.1, 0.14, 7.4, ground, side * 3.05, 0.18, 0);
        for (let i = 0; i < 4; i++)
          ball(
            0.8,
            soil,
            side * 4.5,
            -0.15,
            -2.8 + i * 1.8,
            root,
            [0.8, 1.4, 1],
          );
      }
      box(1.95, 0.08, 7.4, mat("#8ebdc6"), 0, -0.65, 0);
    } else if (spec.shape === "square") {
      box(9.1, 0.65, 7.5, soil, 0, -0.15, 0);
      box(9.15, 0.14, 7.55, ground, 0, 0.18, 0);
    } else {
      const segments = spec.shape === "hex" ? 8 : 64;
      const base = cylinder(5.05, 4.8, 0.65, soil, 0, -0.15, 0, root, segments);
      const top = cylinder(
        5.09,
        5.05,
        0.14,
        ground,
        0,
        0.18,
        0,
        root,
        segments,
      );
      if (spec.shape === "oval") {
        base.scale.z = 0.77;
        top.scale.z = 0.77;
      }
    }
    if (spec.kind === "river") {
      const shape = new THREE.Shape();
      for (let i = 0; i <= 20; i++) {
        const x = -0.94 + (1.88 * i) / 20,
          z = Math.sqrt(5.04 ** 2 - x * x);
        i ? shape.lineTo(x, z) : shape.moveTo(x, z);
      }
      for (let i = 20; i >= 0; i--) {
        const x = -0.94 + (1.88 * i) / 20;
        shape.lineTo(x, -Math.sqrt(5.04 ** 2 - x * x));
      }
      shape.closePath();
      const river = mesh(
        new THREE.ShapeGeometry(shape),
        mat("#88c1bf", { roughness: 0.35 }),
      );
      river.rotation.x = -Math.PI / 2;
      river.position.y = 0.263;
      for (let side of [-1, 1])
        for (let i = 0; i < 6; i++) {
          const z = -3.5 + i * 1.35;
          if (Math.abs(z) < 0.9) continue;
          ball(0.18, mat("#cdd5ac"), side * 1.09, 0.3, z, root, [1.2, 0.65, 1]);
        }
    }
    if (
      !["kitchen", "studio", "observatory", "clouds", "canyon"].includes(
        spec.kind,
      )
    ) {
      for (const [x, z, s] of [
        [-3.4, -2.2, 1.2],
        [-1.9, -3, 0.9],
        [2, -2.8, 1.1],
        [3.7, -1.3, 0.85],
        [-4, 1.4, 0.6],
      ])
        tree(
          x,
          z,
          s,
          spec.kind === "fireflies" ? "pine" : "round",
          spec.kind === "orchard"
            ? "#b1a464"
            : spec.kind === "rain"
              ? "#639b81"
              : "#86b17f",
        );
    }
    if (spec.kind === "river") {
      tree(3.6, 1.9, 0.65);
      const p = puddle(-0.3, 2.5, 0.8);
      p.g.position.y = 0.274;
      const item = { ...p, tapped: -100 };
      poolRipples(item);
      register(
        p.g,
        "ripple",
        () => {
          item.tapped = time;
        },
        0.7,
      );
      const boat = group(0.15, 0.3, -2.25);
      box(0.5, 0.16, 0.25, wood, 0, 0, 0, boat);
      lineBetween([0, 0, 0], [0, 0.7, 0], bark, 0.02, boat);
      const sail = mesh(new THREE.ConeGeometry(0.25, 0.45, 3), cream, boat);
      sail.position.set(0.07, 0.4, 0);
      sail.scale.z = 0.1;
      register(boat, "boat");
      animations.push((t) => {
        boat.position.y = 0.31 + Math.sin(t * 2) * 0.025;
        boat.rotation.z = Math.sin(t * 1.6) * 0.06;
      });
      const timber = group(-2.5, 0.3, -1);
      for (let i = 0; i < 4; i++)
        box(0.9, 0.08, 0.18, wood, 0, 0.08 + i * 0.08, 0, timber);
      register(timber, "timber");
      for (let i = 0; i < 5; i++) {
        let c = cylinder(
          0,
          0.095,
          0.3,
          mat("#e8a269"),
          2.1 + (i % 3) * 0.32,
          0.4,
          -0.2 + Math.floor(i / 3) * 0.4,
        );
        c.rotation.z = 0.12;
        ball(
          0.065,
          green,
          c.position.x,
          0.61,
          c.position.z,
          root,
          [0.5, 1.9, 0.5],
        );
      }
    }
    if (spec.kind === "village") {
      cottage(-2.9, -2, "#d5a999", 1.25);
      cottage(0.2, -2.65, "#e2c991", 0.9);
      cottage(3.2, -2.25, "#a9c2ba", 0.9);
      fence(-3.8, 2.35, 4);
      fence(1.5, -3.2, 3);
      for (let i = 0; i < 9; i++) {
        const p = cylinder(
          0.2,
          0.22,
          0.035,
          cream,
          -2.8 + i * 0.6,
          0.272,
          0.9 - Math.sin(i * 0.4) * 0.45,
        );
        p.scale.z = 0.7;
      }
      const well = group(-0.6, 0.25, -1.8);
      cylinder(0.45, 0.48, 0.55, mat("#b7b9a7"), 0, 0.28, 0, well);
      for (let x of [-0.4, 0.4]) box(0.07, 1.4, 0.07, bark, x, 0.8, 0, well);
      const roof = cylinder(0, 0.7, 0.35, mat("#b09582"), 0, 1.65, 0, well, 4);
      roof.rotation.y = Math.PI / 4;
      const bucket = cylinder(0.15, 0.12, 0.22, wood, 0, 0.85, 0.1, well);
      register(well, "well");
      animations.push((t) => {
        const tap = items.get("well").tapped;
        bucket.position.y =
          0.85 + (t - tap < 3 ? Math.sin((t - tap) * 4) * 0.25 : 0);
      });
      register(paintPot(-3, 1, "#6e9fc4"), "blue-paint");
      register(paintPot(-1.7, 1, "#ce7d70"), "red-paint");
    }
    if (spec.kind === "garden") {
      for (let side of [-1, 1]) {
        box(1.4, 0.18, 2.1, wood, side * 3, 0.3, -0.9);
        for (let i = 0; i < 8; i++) {
          const x = side * 3 + Math.sin(i * 4) * 0.45,
            z = -1.5 + (i % 4) * 0.4;
          lineBetween([x, 0.4, z], [x, 0.8, z], green, 0.02);
          ball(0.13, mat(i % 2 ? "#dba0ae" : "#e8c77d"), x, 0.85, z);
        }
      }
      const arch = group(0, 0.25, -2.75);
      for (let x of [-0.85, 0.85]) box(0.1, 1.6, 0.1, cream, x, 0.8, 0, arch);
      const arc = mesh(
        new THREE.TorusGeometry(0.85, 0.055, 8, 30, Math.PI),
        cream,
        arch,
      );
      arc.position.y = 1.6;
      for (let i = 0; i < 7; i++)
        ball(
          0.12,
          mat("#dfb2c0"),
          Math.cos((i * Math.PI) / 6) * 0.9,
          1.6 + Math.sin((i * Math.PI) / 6) * 0.9,
          0.02,
          arch,
        );
      const butterfly = group(-1.6, 1.1, -1.4);
      ball(0.06, bark, 0, 0, 0, butterfly, [0.6, 1.3, 0.7]);
      const wings = [];
      for (let side of [-1, 1])
        wings.push(
          ball(
            0.22,
            mat("#ddc786"),
            side * 0.18,
            0,
            0,
            butterfly,
            [1, 0.8, 0.15],
          ),
        );
      register(butterfly, "butterfly", null, 0.55);
      animations.push((t) => {
        const boosted = t - items.get("butterfly").tapped < 3;
        butterfly.position.y =
          1.15 + Math.sin(t * 1.5) * 0.2 + (boosted ? 0.4 : 0);
        butterfly.position.x = -1.6 + Math.sin(t * 0.8) * (boosted ? 0.8 : 0.2);
        wings.forEach(
          (w, i) => (w.rotation.y = Math.sin(t * 8) * (i ? 1 : -1) * 0.6),
        );
      });
      const fountain = group(-3, 0.25, 1.3);
      cylinder(0.5, 0.55, 0.15, cream, 0, 0.1, 0, fountain);
      cylinder(0.16, 0.22, 0.6, wood, 0, 0.4, 0, fountain);
      ball(0.16, mat("#93c5d1"), 0, 0.8, 0, fountain);
      register(fountain, "fountain");
      const can = group(-0.5, 0.25, 2);
      cylinder(0.22, 0.2, 0.32, mat("#8eb6a1"), 0, 0.18, 0, can);
      lineBetween([0.15, 0.2, 0], [0.5, 0.38, 0], green, 0.045, can);
      register(can, "watering-can");
    }
    if (spec.kind === "orchard") {
      for (const [x, z] of [
        [-3, -1.5],
        [0.3, -2.5],
        [3, -2],
      ]) {
        const t = tree(x, z, 1.1, "round", "#b7ad70");
        for (let i = 0; i < 4; i++)
          ball(
            0.11,
            mat("#cb7257"),
            Math.sin(i * 2) * 0.42,
            1.25 + Math.cos(i * 2) * 0.3,
            0.3,
            t,
          );
        if (x === -3) register(t, "apple-tree", null, 0.7);
      }
      const leaves = leafPatch(-0.8, 1);
      register(leaves, "leaves");
      const angles = leaves.children.map((o, i) => ({
        o,
        x: o.position.x,
        y: o.position.y,
        z: o.position.z,
        i,
      }));
      animations.push((t) => {
        const age = t - items.get("leaves").tapped;
        for (const l of angles) {
          l.o.position.y =
            l.y +
            (age < 2 ? Math.sin((age * Math.PI) / 2) * (0.3 + l.i * 0.04) : 0);
          l.o.rotation.y = l.i + (age < 2 ? age * 2 : 0);
        }
      });
      const crate = group(-3.2, 0.25, 1.3);
      box(0.8, 0.35, 0.6, wood, 0, 0.18, 0, crate);
      for (let i = 0; i < 3; i++)
        ball(0.13, mat("#cf7958"), -0.23 + i * 0.23, 0.42, 0, crate);
      register(crate, "fruit-crate");
      for (let i = 0; i < 6; i++) leafPatch(-3.6 + i * 1.3, 2.5);
    }
    if (spec.kind === "kitchen") {
      for (let i = 0; i < 14; i++)
        box(
          0.64,
          0.025,
          7.45,
          mat(i % 2 ? "#d5b895" : "#dec6a4"),
          -4.2 + i * 0.65,
          0.264,
          0,
        );
      box(6.5, 1.5, 0.15, mat("#b0c5b0"), 0, 1, -2.6);
      box(6.7, 0.15, 1.25, wood, 0, 0.95, -2);
      for (let x of [-2.5, 2.5]) box(0.2, 0.85, 0.8, wood, x, 0.5, -2);
      const sink = group(-2, 0.26, -1.8);
      box(1, 0.08, 0.7, cream, 0, 0.78, 0, sink);
      box(0.75, 0.08, 0.5, mat("#9cbdc2"), 0, 0.83, 0, sink);
      lineBetween([0.25, 0.85, -0.15], [0.25, 1.3, -0.15], wood, 0.04, sink);
      lineBetween([0.25, 1.3, -0.15], [0, 1.3, -0.15], wood, 0.04, sink);
      register(sink, "tap");
      const jar = group(-3, 0.25, 0.7);
      cylinder(0.22, 0.2, 0.4, mat("#c1d1b6"), 0, 0.2, 0, jar);
      for (let i = 0; i < 3; i++) {
        lineBetween([0, 0.3, 0], [(i - 1) * 0.1, 0.8, 0], bark, 0.02, jar);
        ball(0.06, wood, (i - 1) * 0.1, 0.83, 0, jar, [1, 1.4, 0.45]);
      }
      register(jar, "jar");
      const plate = group(-0.6, 0.25, 1.9);
      cylinder(0.4, 0.4, 0.04, cream, 0, 0.03, 0, plate);
      register(plate, "plate");
      tree(-3.7, -3.2, 0.8);
      tree(3.8, -3.1, 0.8);
    }
    if (spec.kind === "rain") {
      const pudd = puddle(-1.2, 1.4, 1.3);
      const item = { ...pudd, tapped: -100 };
      poolRipples(item);
      register(
        pudd.g,
        "puddle",
        () => {
          item.tapped = time;
        },
        0.8,
      );
      const mush = mushroom(-3, -0.7, 1.5);
      register(mush, "mushroom");
      ball(0.06, mat("#b56352"), 0.1, 0.15, 0.2, mush);
      const umbrella = group(-0.3, 0.25, -1.3);
      lineBetween([0, 0, 0], [0, 1.3, 0], bark, 0.025, umbrella);
      const folded = cylinder(0, 0.14, 0.9, yellow, 0, 1, 0, umbrella, 9);
      folded.rotation.z = 0.12;
      register(umbrella, "umbrella");
      for (let i = 0; i < 3; i++) cloud(-3 + i * 2.7, 3.1, -2.2, 0.9);
      const positions = new Float32Array(90 * 3);
      for (let i = 0; i < 90; i++) {
        positions[i * 3] = Math.sin(i * 12.3) * 4.3;
        positions[i * 3 + 1] = 0.4 + (i % 17) * 0.19;
        positions[i * 3 + 2] = Math.cos(i * 5.7) * 3.6;
      }
      const geo = new THREE.BufferGeometry();
      geo.setAttribute("position", new THREE.BufferAttribute(positions, 3));
      const rainMat = new THREE.PointsMaterial({
        color: "#d8edf1",
        size: 0.045,
        transparent: true,
        opacity: 0.8,
      });
      materials.add(rainMat);
      const rain = new THREE.Points(geo, rainMat);
      root.add(rain);
      animations.push((t) => {
        for (let i = 0; i < 90; i++)
          positions[i * 3 + 1] =
            0.3 + ((((3.2 - t * 1.3 + i * 0.19) % 3.2) + 3.2) % 3.2);
        geo.attributes.position.needsUpdate = true;
      });
    }
    if (spec.kind === "observatory") {
      cylinder(2.1, 2.15, 0.06, mat("#a6a5ba"), -1.8, 0.29, -1.5);
      const dome = group(-2.8, 0.3, -1.7);
      cylinder(0.8, 0.85, 0.75, mat("#c6c3ce"), 0, 0.38, 0, dome);
      const cap = mesh(
        new THREE.SphereGeometry(0.82, 22, 12, 0, Math.PI * 2, 0, Math.PI / 2),
        mat("#8aa4ae"),
        dome,
      );
      cap.position.y = 0.74;
      box(0.19, 0.5, 0.03, yellow, 0, 0.33, 0.85, dome);
      const telescope = group(-0.6, 0.3, -2);
      for (let i = 0; i < 3; i++)
        lineBetween(
          [0, 0.9, 0],
          [Math.sin(i * 2.1) * 0.4, 0, Math.cos(i * 2.1) * 0.4],
          wood,
          0.035,
          telescope,
        );
      const tube = cylinder(
        0.13,
        0.17,
        1.05,
        mat("#9db8bf"),
        0,
        1.05,
        0,
        telescope,
      );
      tube.rotation.z = -0.8;
      register(telescope, "telescope");
      animations.push((t) => {
        const age = t - items.get("telescope").tapped;
        tube.rotation.z =
          -0.8 + (age < 2 ? Math.sin((age * Math.PI) / 2) * 0.35 : 0);
      });
      const moon = group(2.5, 3, -2.8);
      const crescent = mesh(
        new THREE.TorusGeometry(0.48, 0.12, 10, 36, Math.PI * 1.5),
        mat("#efda9e", { emissive: "#b59552", emissiveIntensity: 0.3 }),
        moon,
      );
      crescent.rotation.z = -Math.PI / 3;
      register(moon, "moon", null, 0.65);
      const constellation = group(-2.4, 2.8, 0.3);
      for (let i = 0; i < 3; i++)
        ball(
          0.075,
          mat("#e9dca8", { emissive: "#d3bc73", emissiveIntensity: 0.6 }),
          i * 0.4,
          Math.sin(i * 2) * 0.2,
          0,
          constellation,
        );
      const stars = [...constellation.children];
      register(constellation, "constellation", null, 0.65);
      animations.push((t) => {
        const age = t - items.get("constellation").tapped;
        stars.forEach((star, i) =>
          star.scale.setScalar(
            age >= i * 0.35 && age < i * 0.35 + 0.6 ? 1.8 : 1,
          ),
        );
      });
      for (let i = 0; i < 26; i++)
        ball(
          0.025,
          mat("#dce8d6", { emissive: "#dce8d6", emissiveIntensity: 0.5 }),
          Math.sin(i * 7) * 4,
          2.6 + (i % 5) * 0.25,
          -2 + Math.cos(i * 4),
          root,
        );
      const warm = new THREE.PointLight("#f2dca4", 2, 7);
      warm.position.set(-2, 2, 0);
      root.add(warm);
    }
    if (spec.kind === "studio") {
      box(8, 2.3, 0.12, mat("#d8c9c5"), 0, 1.35, -2.8);
      for (let i = 0; i < 5; i++)
        box(
          1.5,
          0.03,
          7.4,
          mat(i % 2 ? "#e6d4b7" : "#eedec4"),
          -3.5 + i * 1.75,
          0.26,
          0,
        );
      const easel = group(-2.5, 0.25, -1.2);
      for (let side of [-1, 1])
        lineBetween(
          [side * 0.45, 0, 0],
          [side * 0.2, 1.6, 0],
          wood,
          0.045,
          easel,
        );
      box(1.1, 1.1, 0.08, cream, 0, 1.2, 0.06, easel);
      const paint = box(0.55, 0.16, 0.02, mat("#a3bba8"), 0, 1.2, 0.12, easel);
      register(easel, "easel", () => {
        paint.rotation.z += 0.25;
      });
      register(paintPot(-2.9, 1.3, "#d45f58"), "red-pot");
      register(paintPot(-1.4, 1.3, "#6b9fc5"), "blue-pot");
      for (let i = 0; i < 5; i++)
        ball(
          0.13,
          mat(["#ce8f85", "#d8c478", "#9abc99", "#91b6c0", "#ba9cb8"][i]),
          -0.8 + i * 0.8,
          1.65,
          -2.65,
        );
    }
    if (spec.kind === "fireflies") {
      const path = group();
      for (let i = 0; i < 12; i++) {
        const x = Math.sin(i * 0.35) * 1.5,
          z = 2.9 - i * 0.55;
        const stone = cylinder(
          0.26,
          0.27,
          0.04,
          mat("#a4b5a2"),
          x,
          0.28,
          z,
          path,
          10,
        );
        stone.scale.z = 0.7;
      }
      const lantern = group(-2.5, 0.25, 0.9);
      cylinder(0.05, 0.07, 1.2, wood, 0, 0.6, 0, lantern);
      box(
        0.35,
        0.5,
        0.35,
        mat("#e7cc8c", { emissive: "#d7a351", emissiveIntensity: 0.5 }),
        0,
        1.4,
        0,
        lantern,
      );
      register(lantern, "lantern");
      const glow = new THREE.PointLight("#f2d794", 2, 5);
      glow.position.set(-2.5, 1.8, 0.9);
      root.add(glow);
      const fireflies = group(-0.3, 1.1, -1);
      for (let i = 0; i < 9; i++)
        ball(
          0.035,
          mat("#e3dfa1", { emissive: "#e3dfa1", emissiveIntensity: 0.7 }),
          Math.sin(i * 5) * 0.6,
          Math.cos(i * 3) * 0.3,
          Math.cos(i) * 0.5,
          fireflies,
        );
      register(fireflies, "fireflies", null, 0.7);
      animations.push((t) => {
        fireflies.rotation.y = t * 0.3;
        fireflies.position.y = 1.1 + Math.sin(t) * 0.1;
      });
      const sign = group(-3.3, 0.25, -1.5);
      box(0.06, 1.1, 0.06, wood, 0, 0.55, 0, sign);
      box(0.7, 0.25, 0.08, cream, 0, 1, 0, sign);
      register(sign, "trail-sign");
      mushroom(3, 1.4, 1.2);
    }
    if (spec.kind === "clouds") {
      for (let i = 0; i < 8; i++)
        cloud(Math.sin(i * 2) * 4, -0.2, Math.cos(i * 2) * 3.6, 1.25);
      cottage(-2.6, -1.8, "#c2bad6", 0.9);
      cottage(2.4, -2, "#d9c6c2", 0.85);
      const puff = cloud(-1, 1, -0.9, 0.75);
      register(puff, "cloud", null, 0.65);
      animations.push((t) => {
        puff.scale.x = 0.75 + Math.sin(t) * 0.08;
      });
      const pinwheel = group(-2.8, 0.25, 1.3);
      cylinder(0.025, 0.025, 0.9, wood, 0, 0.45, 0, pinwheel);
      const wheel = group(0, 0.95, 0.04, pinwheel);
      for (let i = 0; i < 4; i++) {
        let blade = box(
          0.28,
          0.13,
          0.035,
          mat(i % 2 ? "#c4b6d7" : "#e0c49b"),
          Math.cos((i * Math.PI) / 2) * 0.16,
          Math.sin((i * Math.PI) / 2) * 0.16,
          0,
          wheel,
        );
        blade.rotation.z = (i * Math.PI) / 2;
      }
      register(pinwheel, "pinwheel");
      animations.push((t) => {
        wheel.rotation.z =
          t * (time - items.get("pinwheel").tapped < 2 ? 2 : 0.5);
      });
      const mailbox = group(0.1, 0.25, -2);
      box(0.07, 0.75, 0.07, wood, 0, 0.375, 0, mailbox);
      box(0.55, 0.4, 0.4, mat("#b2a8cc"), 0, 0.9, 0, mailbox);
      register(mailbox, "mailbox");
    }
    if (spec.kind === "canyon") {
      for (let side of [-1, 1])
        for (let i = 0; i < 3; i++) {
          const peak = cylinder(
            0,
            0.8,
            1.3 + i * 0.35,
            mat(i % 2 ? "#bec5bb" : "#a3b4ae"),
            side * (2 + i * 0.8),
            0.9 + i * 0.16,
            -2.4,
            root,
            5,
          );
          peak.rotation.y = i;
        }
      const falls = group(0, -0.35, -3);
      box(
        1.4,
        1.8,
        0.09,
        mat("#9ac6ce", { transparent: true, opacity: 0.8 }),
        0,
        -0.65,
        0,
        falls,
      );
      const foam = ball(
        0.6,
        mat("#e5eee5"),
        0,
        -1.4,
        0.1,
        falls,
        [1, 0.18, 0.6],
      );
      register(
        falls,
        "waterfall",
        () => {
          foam.scale.x = 1.3;
        },
        0.8,
      );
      const flag = group(-2.5, 0.25, -1.5);
      cylinder(0.03, 0.03, 1.4, wood, 0, 0.7, 0, flag);
      const cloth = box(0.55, 0.3, 0.02, mat("#d7a17c"), 0.27, 1.3, 0, flag);
      register(flag, "flag");
      animations.push((t) => (cloth.rotation.y = Math.sin(t * 2) * 0.12));
      register(paintPot(-3.5, 1, "#7caeb5"), "paint-bucket");
    }
    if (spec.kind === "party") {
      const balloons = group(-2.7, 0.25, -1.8);
      for (let i = 0; i < 3; i++) {
        ball(
          0.24,
          mat(["#dbab84", "#c8b4cb", "#a3c4b1"][i]),
          (i - 1) * 0.42,
          1.4 + i * 0.18,
          0,
          balloons,
          [1, 1.2, 1],
        );
        lineBetween(
          [(i - 1) * 0.42, 0, 0],
          [(i - 1) * 0.42, 1.4 + i * 0.18, 0],
          wood,
          0.008,
          balloons,
        );
      }
      register(balloons, "balloons");
      animations.push((t) => (balloons.rotation.z = Math.sin(t * 1.4) * 0.03));
      const bunting = group();
      lineBetween([-3, 0.3, -2.6], [-3, 2.5, -2.6], wood, 0.04, bunting);
      lineBetween([3, 0.3, -2.6], [3, 2.5, -2.6], wood, 0.04, bunting);
      lineBetween([-3, 2.5, -2.6], [3, 2.5, -2.6], cream, 0.016, bunting);
      for (let i = 0; i < 10; i++) {
        let f = cylinder(
          0,
          0.16,
          0.28,
          mat(i % 2 ? "#d7b585" : "#a5c0b3"),
          -2.7 + i * 0.6,
          2.32,
          -2.6,
          bunting,
          3,
        );
        f.rotation.z = Math.PI;
        f.scale.z = 0.1;
      }
      register(bunting, "bunting", null, 0.8);
      box(2.8, 0.02, 1.8, mat("#e4c4aa"), 2.4, 0.27, -0.2);
      const basket = group(-1.1, 0.25, 1);
      box(0.6, 0.32, 0.45, wood, 0, 0.16, 0, basket);
      register(basket, "picnic-basket");
    }
    if (!spec.night && spec.kind !== "rain")
      for (let i = 0; i < 3; i++) {
        const c = cloud(-4 + i * 3.5, 4.3 + (i % 2) * 0.4, -5, 0.8);
        animations.push(
          (t) => (c.position.x = -4 + i * 3.5 + Math.sin(t * 0.15 + i) * 0.15),
        );
      }
    function update(dt, reducedMotion = false) {
      time += dt;
      if (!reducedMotion) animations.forEach((fn) => fn(time));
      for (const [id, item] of items) {
        const age = time - item.tapped;
        if (!reducedMotion && age >= 0 && age < 1.4) {
          item.g.rotation.z =
            item.baseZ + Math.sin(age * 8) * 0.08 * (1 - age / 1.4);
        } else if (!["boat", "balloons"].includes(id))
          item.g.rotation.z = item.baseZ;
      }
    }
    function interact(id) {
      const item = items.get(id);
      if (!item) return null;
      item.tapped = time;
      item.callback?.();
      return item.info;
    }
    function dispose() {
      const geometries = new Set();
      root.traverse((o) => {
        if (o.geometry) geometries.add(o.geometry);
      });
      root.removeFromParent();
      geometries.forEach((g) => g.dispose());
      materials.forEach((m) => m.dispose());
      items.clear();
      targets.length = 0;
      animations.length = 0;
    }
    return { root, spec, targets, update, interact, dispose };
  }
  window.MiloWorlds = { definitions, create };
})();
