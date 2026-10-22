(() => {
  "use strict";
  const canvas = document.getElementById("world");
  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({
      canvas,
      antialias: true,
      alpha: false,
    });
  } catch (e) {
    document.getElementById("fallback").style.display = "flex";
    return;
  }
  renderer.setPixelRatio(Math.min(devicePixelRatio, 1.5));
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.15;
  const scene = new THREE.Scene();
  scene.background = new THREE.Color("#dfece7");
  scene.fog = new THREE.Fog("#dfece7", 23, 65);
  const camera = new THREE.OrthographicCamera(-8, 8, 6, -6, 0.1, 100);
  camera.position.set(10, 12, 15);
  let look = new THREE.Vector3(0, 0, 0);
  camera.lookAt(look);
  scene.add(new THREE.HemisphereLight(0xfff8de, 0x8daaa0, 2.1));
  const sun = new THREE.DirectionalLight(0xffefc9, 3.2);
  sun.position.set(-6, 12, 7);
  sun.castShadow = true;
  sun.shadow.mapSize.set(1024, 1024);
  sun.shadow.camera.left = -9;
  sun.shadow.camera.right = 9;
  sun.shadow.camera.top = 9;
  sun.shadow.camera.bottom = -9;
  sun.shadow.normalBias = 0.035;
  sun.shadow.bias = -0.00015;
  scene.add(sun);
  const mat = (c, roughness = 0.86) =>
    new THREE.MeshStandardMaterial({ color: c, roughness });
  const grass = mat("#a8c993"),
    soil = mat("#d8bd94"),
    leaf = mat("#81ad7b"),
    darkleaf = mat("#65976a"),
    trunk = mat("#b4906c"),
    cream = mat("#fff5dc"),
    mint = mat("#81b9a0"),
    dark = mat("#294d44"),
    yellow = mat("#efd08a"),
    wood = mat("#dcae7b"),
    water = mat("#8fcac7", 0.35);
  const mesh = (g, m, parent = scene) => {
    let o = new THREE.Mesh(g, m);
    o.castShadow = true;
    o.receiveShadow = true;
    parent.add(o);
    return o;
  };
  function sphere(r, m, x, y, z, parent = scene, scale = [1, 1, 1]) {
    let o = mesh(new THREE.SphereGeometry(r, 20, 12), m, parent);
    o.position.set(x, y, z);
    o.scale.set(...scale);
    return o;
  }
  function box(w, h, d, m, x, y, z, parent = scene) {
    let o = mesh(new THREE.BoxGeometry(w, h, d), m, parent);
    o.position.set(x, y, z);
    return o;
  }
  function roundBox(w, h, d, r, m, parent) {
    let s = new THREE.Shape();
    s.moveTo(-w / 2 + r, -h / 2);
    s.lineTo(w / 2 - r, -h / 2);
    s.quadraticCurveTo(w / 2, -h / 2, w / 2, -h / 2 + r);
    s.lineTo(w / 2, h / 2 - r);
    s.quadraticCurveTo(w / 2, h / 2, w / 2 - r, h / 2);
    s.lineTo(-w / 2 + r, h / 2);
    s.quadraticCurveTo(-w / 2, h / 2, -w / 2, h / 2 - r);
    s.lineTo(-w / 2, -h / 2 + r);
    s.quadraticCurveTo(-w / 2, -h / 2, -w / 2 + r, -h / 2);
    let g = new THREE.ExtrudeGeometry(s, {
      depth: Math.max(0.01, d - 2 * Math.min(r, d * 0.25)),
      bevelEnabled: true,
      bevelSegments: 3,
      steps: 1,
      bevelSize: r * 0.35,
      bevelThickness: Math.min(r, d * 0.25),
      curveSegments: 5,
    });
    g.translate(0, 0, -Math.max(0.01, d - 2 * Math.min(r, d * 0.25)) / 2);
    return mesh(g, m, parent);
  }
  let seed = 28;
  const rand = () => {
    seed = (seed * 1664525 + 1013904223) >>> 0;
    return seed / 4294967296;
  };
  let world = null;
  const reducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;
  function robot() {
    let g = new THREE.Group();
    scene.add(g);
    const torso = roundBox(0.53, 0.52, 0.4, 0.09, mint, g);
    torso.position.y = 0.68;
    const head = roundBox(0.76, 0.58, 0.48, 0.12, cream, g);
    head.position.y = 1.28;
    const face = roundBox(0.56, 0.29, 0.055, 0.08, dark, g);
    face.position.set(0, 1.27, 0.27);
    const eyes = [];
    for (let x of [-0.13, 0.13])
      eyes.push(sphere(0.045, mat("#e0f2c6"), x, 1.29, 0.31, g, [1, 1.3, 0.5]));
    sphere(0.06, yellow, 0, 0.73, 0.255, g, [1, 1, 0.5]);
    const arms = [];
    for (let s of [-1, 1]) {
      let pivot = new THREE.Group();
      pivot.position.set(s * 0.38, 0.82, 0);
      g.add(pivot);
      let arm = roundBox(0.13, 0.32, 0.15, 0.045, mint, pivot);
      arm.position.y = -0.12;
      sphere(0.09, cream, 0, -0.31, 0, pivot);
      arms.push(pivot);
    }
    const feet = [];
    for (let s of [-1, 1])
      feet.push(
        sphere(0.15, dark, s * 0.17, 0.21, 0.055, g, [0.85, 0.65, 1.25]),
      );
    const antenna = mesh(
      new THREE.CylinderGeometry(0.025, 0.025, 0.19, 8),
      mint,
      g,
    );
    antenna.position.y = 1.65;
    sphere(0.075, yellow, 0, 1.79, 0, g);
    g.position.set(-2.65, 0.26, 1.7);
    return { g, head, arms, feet, eyes };
  }
  function bunny() {
    let g = new THREE.Group();
    scene.add(g);
    sphere(0.32, cream, 0, 0.37, 0, g, [1, 1.12, 0.95]);
    sphere(0.26, cream, 0, 0.76, 0.09, g);
    const ears = [];
    for (let s of [-1, 1]) {
      const e = sphere(0.095, cream, s * 0.12, 1.08, 0.08, g, [0.8, 2.6, 0.7]);
      sphere(0.06, mat("#e8c5b2"), s * 0.12, 1.1, 0.133, g, [0.65, 2.6, 0.3]);
      ears.push(e);
      sphere(0.028, dark, s * 0.09, 0.8, 0.321, g, [1, 1, 0.5]);
      sphere(0.11, cream, s * 0.17, 0.14, 0.13, g, [1, 0.6, 1.5]);
    }
    sphere(0.04, mat("#c58d80"), 0, 0.72, 0.345, g, [1, 0.65, 0.5]);
    sphere(0.13, cream, 0, 0.38, -0.27, g);
    g.position.set(-2.1, 0.26, -0.12);
    return { g, ears };
  }
  const milo = robot(),
    pip = bunny();
  const bridge = new THREE.Group();
  bridge.position.set(0, 0.31, 0);
  scene.add(bridge);
  const planks = [];
  for (let i = 0; i < 12; i++) {
    let p = box(0.215, 0.13, 1, wood, -1.32 + i * 0.24, 0, 0, bridge);
    planks.push(p);
  }
  const posts = [],
    rails = [];
  for (let side of [-1, 1]) {
    for (let x of [-1.22, 1.22]) {
      let p = mesh(
        new THREE.CylinderGeometry(0.045, 0.06, 0.48, 8),
        wood,
        bridge,
      );
      p.position.set(x, 0.22, side);
      posts.push({ p, side });
    }
    let r = box(2.6, 0.065, 0.065, wood, 0, 0.43, side, bridge);
    rails.push({ r, side });
  }
  bridge.visible = false;
  const marker = mesh(
    new THREE.RingGeometry(0.19, 0.23, 32),
    new THREE.MeshBasicMaterial({
      color: "#fff5cb",
      transparent: true,
      opacity: 0.85,
      side: THREE.DoubleSide,
    }),
  );
  marker.rotation.x = -Math.PI / 2;
  marker.position.y = 0.28;
  marker.visible = false;
  let target = null;
  let route = [];
  let width = 0.43,
    wantedWidth = 0.43,
    build = 0,
    phase = "playing",
    phaseTime = 0,
    total = 0,
    last = 0,
    won = false,
    blink = 0;
  const sparkles = [];
  for (let i = 0; i < 22; i++) {
    let o = sphere(0.045, i % 2 ? yellow : cream, 0, -5, 0);
    o.visible = false;
    sparkles.push({
      o,
      angle: rand() * Math.PI * 2,
      speed: 1 + rand() * 2,
      y: rand() * 1.5,
    });
  }
  const $ = (id) => document.getElementById(id);
  const chapters = window.MiloChapters;
  const events = new AbortController();
  const listen = (node, type, callback) =>
    node.addEventListener(type, callback, { signal: events.signal });
  const progressKey = document.body.dataset.progressKey;
  const testMode = document.body.dataset.testMode === "true";
  let progress = { completed: [], lastChapter: chapters[0].id, voice: false };
  let storageAvailable = true;
  if (!testMode) {
    try {
      const saved = JSON.parse(localStorage.getItem(progressKey) || "{}");
      if (saved && typeof saved === "object") {
        progress.completed = Array.isArray(saved.completed)
          ? [
              ...new Set(
                saved.completed.filter((id) =>
                  chapters.some((c) => c.id === id),
                ),
              ),
            ]
          : [];
        if (chapters.some((c) => c.id === saved.lastChapter))
          progress.lastChapter = saved.lastChapter;
        progress.voice = saved.voice === true;
      }
    } catch {
      storageAvailable = false;
    }
  }
  let chapterIndex = Math.max(
    0,
    chapters.findIndex((c) => c.id === progress.lastChapter),
  );
  let stepIndex = 0;
  let frameId = null;
  let manualPaused = false;
  let destroyed = false;
  let pending = null;
  let visualMode = "idle";
  let visualTime = 0;
  let speechText = "";
  let epoch = 0;
  let toastTimer = null;
  let chapterObjects = {};
  let tweens = [];
  const propMaterials = new Set();
  const props = new THREE.Group();
  scene.add(props);
  const bridgeBlue = mat("#7caeb5");
  const chapter = () => chapters[chapterIndex];
  function saveProgress() {
    if (testMode) {
      $("chapterCount").textContent =
        `· prova ${progress.completed.length}/${chapters.length}`;
      $("saveStatus").textContent =
        "Prova adulto: tutto resta temporaneo. I progressi normali non vengono letti o modificati.";
      return;
    }
    try {
      localStorage.setItem(progressKey, JSON.stringify(progress));
      storageAvailable = true;
    } catch {
      storageAvailable = false;
    }
    $("chapterCount").textContent = storageAvailable
      ? `${progress.completed.length}/${chapters.length}`
      : "· non salvati";
    $("saveStatus").textContent = storageAvailable
      ? "Capitoli salvati su questo dispositivo. Nessuna fretta: puoi fermarti quando vuoi."
      : "Salvataggio non disponibile in questo browser. Puoi giocare, ma i progressi non resteranno dopo la chiusura.";
  }
  function toast(text) {
    $("toast").textContent = text;
    $("toast").classList.add("on");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => $("toast").classList.remove("on"), 3200);
  }
  function stopVoice() {
    if ("speechSynthesis" in window) window.speechSynthesis.cancel();
  }
  function narrate() {
    if (
      !progress.voice ||
      !("speechSynthesis" in window) ||
      document.hidden ||
      manualPaused
    )
      return;
    stopVoice();
    const speech = new SpeechSynthesisUtterance(speechText);
    speech.lang = "it-IT";
    speech.rate = 0.85;
    window.speechSynthesis.speak(speech);
  }
  function say(title, text, choices = [], n = 0) {
    $("heading").textContent = title;
    $("story").textContent = text;
    speechText = `${title} ${text} ${choices.length ? "Puoi scegliere: " + choices.map((c) => c.label).join(". ") : ""}`;
    $("actions").replaceChildren(
      ...choices.map((choice) => {
        const button = document.createElement("button");
        button.className = "action" + (choice.secondary ? " secondary" : "");
        button.textContent = `${choice.icon || ""}  ${choice.label}`;
        if (choice.effect) button.dataset.effect = choice.effect;
        button.onclick = () => {
          stopVoice();
          if (choice.run) choice.run();
          else choose(choice);
        };
        return button;
      }),
    );
    document
      .querySelectorAll(".steps i")
      .forEach((node, index) => node.classList.toggle("active", index <= n));
    document.body.dataset.miloState = phase;
    document.body.dataset.chapterId = chapter().id;
    narrate();
  }
  function renderStep(feedback) {
    const step = chapter().steps[stepIndex];
    phase = "playing";
    say(step.title, feedback || step.text, step.choices, stepIndex);
  }
  function completeChapter() {
    phase = "completed";
    phaseTime = 0;
    visualMode = "idle";
    won = true;
    target = null;
    route = [];
    marker.visible = false;
    if (!progress.completed.includes(chapter().id))
      progress.completed.push(chapter().id);
    saveProgress();
    say(
      "Una scoperta da portare con te.",
      chapter().activity,
      [
        { label: "Il sentiero dei capitoli", icon: "☷", run: openChapters },
        {
          label: "Rigioca",
          icon: "↻",
          secondary: true,
          run: () => startChapter(chapterIndex),
        },
      ],
      2,
    );
    $("speaker").textContent = testMode
      ? "Prova adulto · niente salvato"
      : "Per oggi può bastare · insieme a un adulto";
  }
  function choose(choice) {
    if (phase !== "playing" || manualPaused) return;
    const duration = choice.effect ? effects[choice.effect]() : 0;
    const next = () => {
      if (choice.to === "done") completeChapter();
      else {
        if (Number.isInteger(choice.to)) stepIndex = choice.to;
        renderStep(choice.feedback);
      }
    };
    if (duration > 0) {
      phase = "animating";
      document.body.dataset.miloState = phase;
      $("actions")
        .querySelectorAll("button")
        .forEach((button) => (button.disabled = true));
      pending = { remaining: duration, callback: next, epoch };
    } else next();
  }
  function material(color) {
    const m = mat(color);
    propMaterials.add(m);
    return m;
  }
  function group(parent = props) {
    const g = new THREE.Group();
    parent.add(g);
    return g;
  }
  function tween(node, property, value) {
    tweens = tweens.filter((t) => t.node !== node || t.property !== property);
    tweens.push({ node, property, value });
  }
  function label(text, parent, x, y, z, width = 1.1) {
    const drawing = document.createElement("canvas");
    drawing.width = 512;
    drawing.height = 160;
    const ctx = drawing.getContext("2d");
    ctx.fillStyle = "#fff8df";
    ctx.fillRect(0, 0, 512, 160);
    ctx.fillStyle = "#335b46";
    ctx.font = "bold 50px sans-serif";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(text, 256, 80, 480);
    const texture = new THREE.CanvasTexture(drawing);
    texture.colorSpace = THREE.SRGBColorSpace;
    const m = new THREE.MeshStandardMaterial({ map: texture, roughness: 0.9 });
    propMaterials.add(m);
    const plane = mesh(new THREE.PlaneGeometry(width, width * 0.32), m, parent);
    plane.position.set(x, y, z);
    return plane;
  }
  function apple(parent, x, y, z) {
    const g = group(parent);
    g.position.set(x, y, z);
    sphere(0.16, material("#d76c5b"), 0, 0.12, 0, g, [1, 0.9, 1]);
    box(0.025, 0.12, 0.025, trunk, 0, 0.29, 0, g);
    sphere(0.06, leaf, 0.055, 0.3, 0, g, [1.3, 0.35, 0.7]);
    return g;
  }
  function flower(parent, x, color) {
    const g = group(parent);
    g.position.set(x, 0, 0);
    const stem = mesh(
      new THREE.CylinderGeometry(0.035, 0.04, 0.6, 8),
      darkleaf,
      g,
    );
    stem.position.y = 0.3;
    for (let i = 0; i < 6; i++)
      sphere(
        0.14,
        color,
        Math.cos((i * Math.PI) / 3) * 0.18,
        0.7 + Math.sin((i * Math.PI) / 3) * 0.18,
        0,
        g,
        [1, 1, 0.45],
      );
    sphere(0.12, yellow, 0, 0.7, 0.05, g);
    return g;
  }
  function createProps(kind) {
    props.traverse((node) => {
      node.geometry?.dispose();
    });
    props.clear();
    propMaterials.forEach((m) => {
      m.map?.dispose();
      m.dispose();
    });
    propMaterials.clear();
    chapterObjects = {};
    tweens = [];
    const root = group();
    root.position.set(2.5, 0.3, -0.25);
    chapterObjects.root = root;
    const isBridge = kind === "bridge" || kind === "bridge-two";
    pip.g.position.set(-2.1, 0.26, -0.12);
    if (kind === "house" || kind === "privacy") {
      const house = group(root);
      house.scale.setScalar(0.65);
      chapterObjects.house = house;
      const wall = material("#d6a08a");
      chapterObjects.wall = wall;
      box(1.1, 0.9, 0.9, wall, 0, 0.45, 0, house);
      const roof = mesh(
        new THREE.ConeGeometry(0.92, 0.55, 4),
        material("#71937b"),
        house,
      );
      roof.position.y = 1.15;
      roof.rotation.y = Math.PI / 4;
      box(0.28, 0.5, 0.035, dark, 0, 0.25, 0.47, house);
      box(0.2, 0.2, 0.035, yellow, -0.31, 0.56, 0.47, house);
      if (kind === "privacy") {
        house.scale.setScalar(0.95);
        const sign = label("?", root, 0, 1.55, 0.15, 1.8);
        chapterObjects.sign = sign;
      }
    }
    if (kind === "flowers") {
      chapterObjects.flower = flower(root, -0.45, material("#e8bf55"));
      chapterObjects.pink = flower(root, 0.45, material("#d590ae"));
      chapterObjects.drops = [];
      for (let i = 0; i < 8; i++) {
        const d = sphere(
          0.035,
          material("#82bfcb"),
          -0.45 + (i % 3) * 0.08,
          0.6 + i * 0.12,
          0.1,
          root,
          [0.7, 1.6, 0.7],
        );
        d.visible = false;
        chapterObjects.drops.push(d);
      }
    }
    if (kind === "apples" || kind === "wash") {
      box(1.15, 0.24, 0.8, material("#c59c6d"), 0, 0.12, 0, root);
      chapterObjects.apples = [];
      for (let i = 0; i < 3; i++) {
        const a = apple(root, -0.33 + i * 0.33, 0.25, 0);
        a.visible = i === 0;
        chapterObjects.apples.push(a);
      }
      if (kind === "wash") {
        const plate = mesh(
          new THREE.CylinderGeometry(0.35, 0.35, 0.035, 30),
          cream,
          root,
        );
        plate.position.set(0.85, 0.07, 0.2);
        chapterObjects.drops = [];
        for (let i = 0; i < 8; i++) {
          let d = sphere(
            0.035,
            material("#82bfcb"),
            -0.33 + (i % 3) * 0.06,
            0.5 + i * 0.12,
            0,
            root,
          );
          d.visible = false;
          chapterObjects.drops.push(d);
        }
      }
    }
    if (kind === "rain") {
      pip.g.position.set(2.5, 0.26, 0);
      chapterObjects.rain = group(root);
      chapterObjects.rainDrops = [];
      for (let i = 0; i < 24; i++) {
        let d = sphere(
          0.025,
          material("#80b1be"),
          (rand() - 0.5) * 2,
          0.5 + rand() * 2.5,
          (rand() - 0.5) * 1.5,
          chapterObjects.rain,
          [0.7, 2, 0.7],
        );
        chapterObjects.rainDrops.push(d);
      }
      const umbrella = group(root);
      umbrella.scale.setScalar(0.001);
      chapterObjects.umbrella = umbrella;
      const pole = mesh(
        new THREE.CylinderGeometry(0.025, 0.025, 1.7, 8),
        wood,
        umbrella,
      );
      pole.position.y = 0.85;
      const cap = mesh(
        new THREE.SphereGeometry(0.82, 24, 12, 0, Math.PI * 2, 0, Math.PI / 2),
        material("#dfbc6d"),
        umbrella,
      );
      cap.position.y = 1.65;
      cap.scale.y = 0.48;
    }
    if (kind === "box") {
      chapterObjects.box = box(1, 0.65, 0.8, wood, 0, 0.325, 0, root);
      const lid = group(root);
      lid.position.set(0, 0.68, -0.4);
      box(1.08, 0.09, 0.88, material("#c89561"), 0, 0, 0.4, lid);
      chapterObjects.lid = lid;
      const starShape = new THREE.Shape();
      for (let i = 0; i < 10; i++) {
        const a = (i * Math.PI) / 5 - Math.PI / 2,
          r = i % 2 ? 0.16 : 0.36;
        i
          ? starShape.lineTo(Math.cos(a) * r, Math.sin(a) * r)
          : starShape.moveTo(Math.cos(a) * r, Math.sin(a) * r);
      }
      starShape.closePath();
      const star = mesh(
        new THREE.ExtrudeGeometry(starShape, {
          depth: 0.08,
          bevelEnabled: false,
        }),
        yellow,
        root,
      );
      star.position.set(0, 0.91, 0.28);
      star.visible = false;
      chapterObjects.star = star;
    }
    if (kind === "ball") {
      chapterObjects.ball = sphere(0.43, material("#d45f58"), 0, 0.45, 0, root);
      chapterObjects.comparison = sphere(
        0.2,
        material("#6b9fc5"),
        0.72,
        0.2,
        0.1,
        root,
      );
      chapterObjects.comparison.visible = false;
    }
    if (kind === "sign") {
      const post = mesh(
        new THREE.CylinderGeometry(0.05, 0.07, 1.2, 8),
        wood,
        root,
      );
      post.position.y = 0.6;
      chapterObjects.sign = label("?", root, 0, 1.2, 0.06, 1.1);
      chapterObjects.destination = group(root);
      chapterObjects.destination.position.set(0.9, 0, -0.3);
      chapterObjects.destination.visible = false;
      box(0.55, 0.5, 0.5, cream, 0, 0.25, 0, chapterObjects.destination);
      let roof = mesh(
        new THREE.ConeGeometry(0.48, 0.35, 4),
        mint,
        chapterObjects.destination,
      );
      roof.position.y = 0.67;
      roof.rotation.y = Math.PI / 4;
    }
    if (kind === "party") {
      const table = group(root);
      table.scale.setScalar(0.001);
      chapterObjects.table = table;
      box(1.6, 0.1, 1, wood, 0, 0.65, 0, table);
      for (let x of [-0.65, 0.65])
        for (let z of [-0.35, 0.35])
          box(0.08, 0.65, 0.08, wood, x, 0.32, z, table);
      for (let x of [-0.4, 0.4]) {
        let plate = mesh(
          new THREE.CylinderGeometry(0.25, 0.25, 0.035, 25),
          cream,
          table,
        );
        plate.position.set(x, 0.73, 0);
      }
      chapterObjects.apples = [
        apple(table, -0.4, 0.76, 0),
        apple(table, 0.4, 0.76, 0),
      ];
      chapterObjects.apples.forEach((a) => (a.visible = false));
      const balloons = group(root);
      balloons.position.set(0, 1, -0.7);
      for (let i = 0; i < 3; i++) {
        sphere(
          0.18,
          material(["#dba078", "#8dbda2", "#e3c778"][i]),
          (i - 1) * 0.4,
          1 + i * 0.15,
          0,
          balloons,
          [1, 1.25, 1],
        );
        const line = mesh(
          new THREE.CylinderGeometry(0.006, 0.006, 1, 6),
          cream,
          balloons,
        );
        line.position.set((i - 1) * 0.4, 0.5 + i * 0.15, 0);
      }
    }
    const interactions = {
      house: ["house-large"],
      pink: ["point-pink"],
      flower: ["water-flower"],
      box: ["box-open", "show-star"],
      ball: ["ball-show", "ball-correct"],
      comparison: ["ball-compare"],
      table: ["party-setup", "party-two"],
      sign: ["sign-question", "sign-reveal", "sign-private", "sign-fantasy"],
    };
    for (const [key, candidates] of Object.entries(interactions)) {
      if (chapterObjects[key]?.isObject3D)
        chapterObjects[key].userData.miloEffects = candidates;
    }
  }
  function replaceSign(text, width = 1.4) {
    const old = chapterObjects.sign;
    old.visible = false;
    chapterObjects.sign = label(text, chapterObjects.root, 0, 1.5, 0.1, width);
  }
  const effects = {
    "bridge-build": () => {
      bridge.visible = true;
      build = 0;
      visualMode = "build-try";
      visualTime = 0;
      target = new THREE.Vector3(-1.55, 0.26, 1.05);
      return 5.1;
    },
    "bridge-blue": () => {
      planks.forEach((p) => (p.material = bridgeBlue));
      return 0.4;
    },
    "bridge-widen": () => {
      wantedWidth = 1.55;
      return 1.2;
    },
    cross: () => {
      visualMode = "cross";
      visualTime = 0;
      return 3.7;
    },
    "house-blue": () => {
      chapterObjects.wall.color.set("#6e9fc4");
      return 0.5;
    },
    "house-red": () => {
      chapterObjects.wall.color.set("#ce7d70");
      return 0.5;
    },
    "house-large": () => {
      tween(chapterObjects.house, "scale", 1.18);
      return 1;
    },
    "point-flowers": () => {
      chapterObjects.root.rotation.y = 0.1;
      return 0.3;
    },
    "water-flower": () => {
      visualMode = "watering";
      visualTime = 0;
      chapterObjects.drops.forEach((d) => (d.visible = true));
      tween(chapterObjects.flower, "scale", 1.35);
      return 1.7;
    },
    "point-pink": () => {
      tween(chapterObjects.pink, "scale", 1.1);
      return 0.4;
    },
    "apples-one": () => 0.2,
    "apples-three": () => {
      chapterObjects.apples.forEach((a, i) => {
        a.visible = true;
        a.scale.setScalar(i ? 0.01 : 1);
        tween(a, "scale", 1);
      });
      return 1;
    },
    "wash-apple": () => {
      visualMode = "watering";
      visualTime = 0;
      chapterObjects.drops.forEach((d) => (d.visible = true));
      return 1.6;
    },
    "serve-apple": () => {
      tween(chapterObjects.apples[0], "x", 0.85);
      tween(chapterObjects.apples[0], "y", 0.08);
      tween(chapterObjects.apples[0], "z", 0.2);
      return 1;
    },
    "rain-start": () => 0.3,
    "umbrella-open": () => {
      tween(chapterObjects.umbrella, "scale", 1);
      return 1.3;
    },
    "box-open": () => {
      tween(chapterObjects.lid, "rotationX", -1.5);
      chapterObjects.star.visible = true;
      tween(chapterObjects.star, "y", 1.25);
      return 1.4;
    },
    "show-star": () => {
      tween(chapterObjects.star, "scale", 1.2);
      return 0.5;
    },
    "ball-show": () => {
      tween(chapterObjects.ball, "y", 0.6);
      return 0.5;
    },
    "ball-correct": () => {
      tween(chapterObjects.ball, "scale", 1.1);
      return 0.5;
    },
    "ball-compare": () => {
      chapterObjects.comparison.visible = true;
      return 0.5;
    },
    "sign-question": () => 0.3,
    "sign-reveal": () => {
      replaceSign("→", 1);
      chapterObjects.destination.visible = true;
      return 0.7;
    },
    "sign-private": () => 0.3,
    "sign-fantasy": () => {
      replaceSign("Casa delle Nuvole", 2.1);
      return 0.7;
    },
    "party-setup": () => {
      tween(chapterObjects.table, "scale", 1);
      return 1;
    },
    "party-two": () => {
      chapterObjects.apples.forEach((a) => {
        a.visible = true;
        a.scale.setScalar(0.01);
        tween(a, "scale", 1);
      });
      return 0.9;
    },
    celebrate: () => 0.3,
  };
  function startChapter(index) {
    if (!Number.isInteger(index) || index < 0 || index >= chapters.length)
      return;
    epoch++;
    clearTimeout(toastTimer);
    $("toast").classList.remove("on");
    pending = null;
    stopVoice();
    chapterIndex = index;
    stepIndex = 0;
    phase = "playing";
    phaseTime = 0;
    visualMode = "idle";
    visualTime = 0;
    won = false;
    target = null;
    route = [];
    marker.visible = false;
    milo.g.position.set(-2.65, 0.26, 1.7);
    milo.g.rotation.y = 0;
    milo.arms.forEach((a) => (a.rotation.z = 0));
    pip.g.rotation.y = 0;
    sparkles.forEach((s) => (s.o.visible = false));
    const isBridge =
      chapter().scene === "bridge" || chapter().scene === "bridge-two";
    width = wantedWidth = isBridge ? 0.43 : 1.55;
    build = chapter().scene === "bridge" ? 0 : 1;
    world?.dispose();
    world = window.MiloWorlds.create(THREE, chapter().id);
    scene.add(world.root);
    scene.background.set(world.spec.sky);
    scene.fog.color.set(world.spec.sky);
    sun.intensity = world.spec.night ? 1.3 : 3.2;
    document.body.dataset.environment = chapter().id;
    document.body.dataset.lighting = world.spec.night ? "night" : "day";
    $("locationName").textContent = world.spec.name;
    bridge.visible = !!world.spec.river && chapter().scene !== "bridge";
    planks.forEach((p) => (p.material = wood));
    createProps(chapter().scene);
    progress.lastChapter = chapter().id;
    saveProgress();
    document.querySelector(".chapter").textContent =
      `Capitolo ${String(index + 1).padStart(2, "0")} · ${chapter().title}`;
    $("speaker").textContent = "Milo, il tuo aiutante";
    $("chapterSkill").textContent = chapter().skill;
    $("parentNote").textContent = chapter().parent;
    renderStep();
    resize();
  }
  function isPaused() {
    return (
      manualPaused ||
      document.hidden ||
      $("parent").open ||
      $("chaptersDialog").open ||
      $("exploreDialog").open
    );
  }
  function syncPause() {
    if (isPaused()) {
      if (frameId !== null) cancelAnimationFrame(frameId);
      frameId = null;
      stopVoice();
    } else if (frameId === null && !destroyed) {
      last = 0;
      frameId = requestAnimationFrame(frame);
    }
    $("pause").setAttribute("aria-pressed", String(manualPaused));
    $("pause").setAttribute(
      "aria-label",
      manualPaused ? "Riprendi il gioco" : "Metti in pausa",
    );
    $("pause").innerHTML = manualPaused
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5 12 7-12 7Z"/></svg>'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>';
    $("actions").inert = manualPaused;
  }
  function openChapters() {
    $("chapterGrid").replaceChildren(
      ...chapters.map((c, index) => {
        const button = document.createElement("button");
        button.className =
          "chapter-tile" +
          (progress.completed.includes(c.id) ? " is-complete" : "");
        button.dataset.chapterId = c.id;
        button.setAttribute("aria-current", String(index === chapterIndex));
        const icon = document.createElement("span");
        icon.className = "chapter-icon";
        icon.textContent = c.icon;
        icon.setAttribute("aria-hidden", "true");
        const title = document.createElement("strong");
        title.textContent = `${index + 1}. ${c.title} · ${window.MiloWorlds.definitions[c.id].name}`;
        const status = document.createElement("small");
        status.textContent = progress.completed.includes(c.id)
          ? testMode
            ? "✓ Provato · non salvato"
            : "✓ Già esplorato"
          : c.skill;
        button.append(icon, title, status);
        button.onclick = () => {
          $("chaptersDialog").close();
          startChapter(index);
          syncPause();
        };
        return button;
      }),
    );
    $("chaptersDialog").showModal();
    syncPause();
  }
  function discover(id) {
    const info = world.interact(id);
    if (!info) return;
    document.body.dataset.lastDiscovery = id;
    const option =
      phase === "playing" && !pending
        ? chapter().steps[stepIndex].choices.find((c) =>
            info[4]?.includes(c.effect),
          )
        : null;
    if (option) choose(option);
    else toast(info[3]);
  }
  listen($("exploreButton"), "click", () => {
    $("exploreTitle").textContent = world.spec.name;
    $("discoveryList").replaceChildren(
      ...world.spec.discoveries.map((info) => {
        const button = document.createElement("button");
        button.className = "action";
        button.dataset.discovery = info[0];
        button.textContent = info[1] + " " + info[2];
        button.onclick = () => {
          $("exploreDialog").close();
          discover(info[0]);
        };
        return button;
      }),
    );
    $("exploreDialog").showModal();
    syncPause();
  });
  listen($("closeExplore"), "click", () => $("exploreDialog").close());
  listen($("chaptersButton"), "click", openChapters);
  listen($("closeChapters"), "click", () => $("chaptersDialog").close());
  listen($("info"), "click", () => {
    $("parent").showModal();
    syncPause();
  });
  listen($("closeInfo"), "click", () => $("parent").close());
  listen($("closeInfoBottom"), "click", () => $("parent").close());
  for (const dialog of [$("parent"), $("chaptersDialog"), $("exploreDialog")])
    listen(dialog, "close", syncPause);
  listen($("pause"), "click", () => {
    manualPaused = !manualPaused;
    syncPause();
  });
  listen(document, "visibilitychange", syncPause);
  listen($("listen"), "click", () => {
    if (!("speechSynthesis" in window)) {
      toast("La voce non è disponibile: leggete insieme.");
      return;
    }
    progress.voice = !progress.voice;
    saveProgress();
    $("listen").setAttribute("aria-pressed", String(progress.voice));
    $("listen").setAttribute(
      "aria-label",
      progress.voice ? "Disattiva voce" : "Attiva voce",
    );
    if (progress.voice) narrate();
    else stopVoice();
  });
  $("listen").setAttribute("aria-pressed", String(progress.voice));
  $("listen").setAttribute(
    "aria-label",
    progress.voice ? "Disattiva voce" : "Attiva voce",
  );
  listen($("fullscreen"), "click", async () => {
    try {
      if (document.fullscreenElement) await document.exitFullscreen();
      else if (document.documentElement.requestFullscreen)
        await document.documentElement.requestFullscreen();
      else toast("Puoi ruotare il dispositivo per avere più spazio.");
    } catch {
      toast("Lo schermo intero non è disponibile in questo browser.");
    }
  });
  const ray = new THREE.Raycaster(),
    mouse = new THREE.Vector2(),
    plane = new THREE.Plane(new THREE.Vector3(0, 1, 0), -0.26);
  function moveTo(p) {
    const radius = Math.hypot(p.x, p.z);
    if (radius > 4.9) {
      p.x *= 4.9 / radius;
      p.z *= 4.9 / radius;
    }
    route = [];
    if (world.spec.river) {
      // Cross the stream only on the bridge, once it is wide enough.
      const otherBank = Math.sign(p.x) !== Math.sign(milo.g.position.x);
      if (otherBank && (width < 1.2 || build < 0.95)) p.x = -1.25;
      else if (otherBank) {
        const side = Math.sign(milo.g.position.x) || -1;
        route.push(
          new THREE.Vector3(side * 1.3, 0.26, 0),
          new THREE.Vector3(-side * 1.3, 0.26, 0),
        );
      } else if (Math.abs(p.x) < 1.1 && Math.abs(p.z) > width / 2)
        p.x = Math.sign(milo.g.position.x) * 1.25;
    }
    p.y = 0.26;
    if (route.length) {
      route.push(p.clone());
      target = route.shift();
    } else target = p;
    marker.position.set(p.x, 0.279, p.z);
    marker.visible = true;
  }
  listen(canvas, "pointerdown", (e) => {
    if (isPaused()) return;
    canvas.focus({ preventScroll: true });
    mouse.set(
      (e.clientX / innerWidth) * 2 - 1,
      (-e.clientY / innerHeight) * 2 + 1,
    );
    ray.setFromCamera(mouse, camera);
    const hit = ray
      .intersectObjects([...world.targets, props], true)
      .find((hit) => {
        let node = hit.object;
        while (node) {
          if (!node.visible) return false;
          node = node.parent;
        }
        return true;
      });
    if (hit) {
      let node = hit.object;
      while (node && !node.userData.worldAction && !node.userData.miloEffects)
        node = node.parent;
      if (node?.userData.miloEffects) {
        const option = chapter().steps[stepIndex].choices.find((c) =>
          node.userData.miloEffects.includes(c.effect),
        );
        if (option && phase === "playing" && !pending) choose(option);
        else toast("Guarda la missione: scegliamo insieme il prossimo passo.");
        return;
      }
      if (node) {
        discover(node.userData.worldAction);
        return;
      }
    }
    const p = new THREE.Vector3();
    if (ray.ray.intersectPlane(plane, p)) moveTo(p);
  });
  listen(canvas, "keydown", (e) => {
    if (isPaused()) return;
    const keys = {
      ArrowUp: [0, -1],
      w: [0, -1],
      ArrowDown: [0, 1],
      s: [0, 1],
      ArrowLeft: [-1, 0],
      a: [-1, 0],
      ArrowRight: [1, 0],
      d: [1, 0],
    };
    const direction = keys[e.key];
    if (!direction) return;
    e.preventDefault();
    const p = (target || milo.g.position).clone();
    p.x += direction[0] * 0.45;
    p.z += direction[1] * 0.45;
    moveTo(p);
  });
  function resize() {
    const a = innerWidth / innerHeight;
    renderer.setSize(innerWidth, innerHeight, false);
    const span = a < 0.85 ? 12 / a : 12.8;
    const short = innerHeight < 570 && a > 1.4;
    const shift = short ? span * a * 0.18 : 0;
    camera.left = (-span * a) / 2 + shift;
    camera.right = (span * a) / 2 + shift;
    camera.top = span / 2;
    camera.bottom = -span / 2;
    look.set(0, 0, a < 0.85 ? 1.4 : 1.5);
    camera.position
      .copy(look)
      .add(new THREE.Vector3(...(world?.spec.camera || [10, 12, 15])));
    camera.lookAt(look);
    camera.updateProjectionMatrix();
    renderer.render(scene, camera);
  }
  listen(window, "resize", resize);
  listen(canvas, "webglcontextlost", (e) => {
    e.preventDefault();
    manualPaused = true;
    syncPause();
    $("fallback").style.display = "flex";
  });

  function frame(ms) {
    frameId = null;
    if (destroyed || isPaused()) return;
    let dt = last ? Math.min((ms - last) / 1000, 0.15) : 0.016;
    last = ms;
    total += dt;
    phaseTime += dt;
    let moving = false;
    if (target) {
      let delta = target.clone().sub(milo.g.position);
      delta.y = 0;
      let d = delta.length();
      if (d > 0.065) {
        moving = true;
        delta.normalize();
        milo.g.position.addScaledVector(delta, Math.min(d, dt * 1.5));
        let desired = Math.atan2(delta.x, delta.z);
        let diff = Math.atan2(
          Math.sin(desired - milo.g.rotation.y),
          Math.cos(desired - milo.g.rotation.y),
        );
        milo.g.rotation.y += diff * Math.min(1, dt * 9);
      } else {
        target = route.shift() || null;
        marker.visible = !!target;
      }
    }
    milo.head.position.y = 1.28 + Math.sin(total * 2.6) * 0.025;
    for (let i = 0; i < 2; i++) {
      milo.feet[i].position.y =
        0.21 +
        (moving ? Math.max(0, Math.sin(total * 12 + i * Math.PI)) * 0.1 : 0);
      milo.arms[i].rotation.x = moving
        ? Math.sin(total * 12 + i * Math.PI) * 0.4
        : Math.sin(total * 2 + i) * 0.07;
      if (won)
        milo.arms[i].rotation.z =
          (i ? 1 : -1) * (0.5 + Math.sin(total * 8) * 0.2);
    }
    blink += dt;
    for (let e of milo.eyes) e.scale.y = blink > 4 && blink < 4.14 ? 0.14 : 1.3;
    if (blink > 4.2) blink = 0;
    width = THREE.MathUtils.damp(width, wantedWidth, 5, dt);
    for (let i = 0; i < planks.length; i++) {
      planks[i].scale.z = width;
      let appear = Math.min(1, Math.max(0, build * 1.6 - i * 0.055));
      planks[i].scale.y = Math.max(0.001, appear);
      planks[i].position.y = (1 - appear) * 0.7;
    }
    for (let { p, side } of posts) p.position.z = side * width * 0.47;
    for (let { r, side } of rails) r.position.z = side * width * 0.47;
    for (let { p } of posts) p.scale.y = Math.min(1, build);
    for (let { r } of rails) r.scale.y = Math.min(1, build);
    visualTime += dt;
    if (visualMode === "build-try") {
      build = Math.min(1, visualTime / 1.8);
      if (visualTime > 2.3) {
        pip.g.rotation.y = Math.PI / 2;
        pip.g.position.x = THREE.MathUtils.lerp(
          -2.1,
          -1.18,
          Math.min(1, (visualTime - 2.3) / 1.3),
        );
        pip.g.position.y =
          0.26 +
          (visualTime < 3.6 ? Math.abs(Math.sin(visualTime * 9)) * 0.1 : 0);
      }
    }
    if (visualMode === "cross") {
      const t = Math.min(1, visualTime / 3.5);
      pip.g.position.x = THREE.MathUtils.lerp(-1.18, 2.7, t);
      pip.g.rotation.y = Math.PI / 2;
      pip.g.position.y =
        t < 1 ? 0.29 + Math.abs(Math.sin(visualTime * 10)) * 0.13 : 0.26;
    }
    if (visualMode === "watering" && chapterObjects.drops)
      chapterObjects.drops.forEach((d, i) => {
        d.position.y =
          0.6 + ((((1.5 - visualTime * 1.5 + i * 0.12) % 1.4) + 1.4) % 1.4);
      });
    if (chapterObjects.rainDrops)
      chapterObjects.rainDrops.forEach((d, i) => {
        d.position.y =
          0.2 + ((((3 - total * 1.2 + i * 0.18) % 2.8) + 2.8) % 2.8);
        if (chapterObjects.umbrella?.scale.x > 0.8 && d.position.y < 1.7)
          d.visible = false;
        else d.visible = true;
      });
    for (const t of tweens) {
      if (t.property === "scale")
        t.node.scale.setScalar(
          THREE.MathUtils.damp(t.node.scale.x, t.value, 7, dt),
        );
      else if (t.property === "rotationX")
        t.node.rotation.x = THREE.MathUtils.damp(
          t.node.rotation.x,
          t.value,
          7,
          dt,
        );
      else
        t.node.position[t.property] = THREE.MathUtils.damp(
          t.node.position[t.property],
          t.value,
          7,
          dt,
        );
    }
    pip.ears[0].rotation.z = Math.sin(total * 1.4) * 0.07;
    pip.ears[1].rotation.z = -Math.sin(total * 1.4 + 0.4) * 0.06;
    if (won) pip.g.position.y = 0.26 + Math.max(0, Math.sin(total * 4)) * 0.07;
    if (pending) {
      pending.remaining -= dt;
      if (pending.remaining <= 0) {
        const next = pending;
        pending = null;
        visualMode = "idle";
        pip.g.rotation.y = 0;
        chapterObjects.drops?.forEach((d) => (d.visible = false));
        if (next.epoch === epoch) next.callback();
      }
    }

    world.update(dt, reducedMotion);
    marker.scale.setScalar(1 + Math.sin(total * 4) * 0.1);
    if (won) {
      for (let i = 0; i < sparkles.length; i++) {
        let s = sparkles[i],
          r = 0.3 + ((phaseTime * s.speed + i * 0.1) % 2.4);
        s.o.position.set(
          2.6 + Math.cos(s.angle) * r,
          0.7 + ((phaseTime * s.speed + s.y) % 2.8),
          Math.sin(s.angle) * r,
        );
        s.o.scale.setScalar(Math.max(0.05, 1 - phaseTime / 7));
        s.o.visible = phaseTime < 7;
      }
    }
    renderer.render(scene, camera);
    frameId = requestAnimationFrame(frame);
  }
  startChapter(chapterIndex);
  resize();
  syncPause();
  listen(window, "pageshow", (event) => {
    if (event.persisted) syncPause();
  });
  listen(
    window,
    "pagehide",
    (event) => {
      if (event.persisted) {
        if (frameId !== null) cancelAnimationFrame(frameId);
        frameId = null;
        stopVoice();
        return;
      }
      destroyed = true;
      if (frameId !== null) cancelAnimationFrame(frameId);
      frameId = null;
      pending = null;
      clearTimeout(toastTimer);
      stopVoice();
      events.abort();
      const geometries = new Set(),
        materials = new Set(),
        textures = new Set();
      scene.traverse((o) => {
        if (o.geometry) geometries.add(o.geometry);
        if (o.material) {
          for (const m of [].concat(o.material)) {
            materials.add(m);
            if (m.map) textures.add(m.map);
          }
        }
      });
      geometries.forEach((g) => g.dispose());
      textures.forEach((t) => t.dispose());
      materials.forEach((m) => m.dispose());
      renderer.dispose();
      renderer.forceContextLoss();
    },
    { once: true },
  );
})();
