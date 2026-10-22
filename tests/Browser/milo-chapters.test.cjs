const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const context = { window: {} };
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/milo/chapters.js'), 'utf8'), context);
const chapters = context.window.MiloChapters;

test('the curriculum has twelve independently selectable chapters with unique stable progress IDs', () => {
  assert.equal(chapters.length, 12);
  assert.equal(new Set(chapters.map(chapter => chapter.id)).size, chapters.length);
  for (const chapter of chapters) {
    assert.match(chapter.id, /^[a-z]+$/);
    assert.ok(chapter.skill && chapter.parent && chapter.activity);
  }
});

test('every chapter has a reachable ending and all choices lead to a valid step or helpful retry', () => {
  for (const chapter of chapters) {
    const reached = new Set();
    const visit = index => {
      if (index === 'done') return true;
      assert.ok(Number.isInteger(index) && index >= 0 && index < chapter.steps.length, chapter.id);
      if (reached.has(index)) return false;
      reached.add(index);
      const step = chapter.steps[index];
      assert.ok(step.title && step.text);
      assert.equal(step.choices.length, 2, chapter.id + ': offer two manageable choices');
      assert.equal(step.choices.filter(c => c.to !== undefined).length, 1, chapter.id + ': choices must have different consequences');
      assert.equal(new Set(step.choices.map(c => c.label)).size, 2);
      let ends = false;
      for (const choice of step.choices) {
        assert.ok(choice.label && choice.icon);
        if (choice.to !== undefined) ends = visit(choice.to) || ends;
        else assert.ok(choice.feedback, 'A retry must explain how to continue');
      }
      return ends;
    };
    assert.ok(visit(0), chapter.id + ' cannot finish');
    assert.equal(reached.size, chapter.steps.length, chapter.id + ' has an unreachable step');
  }
});

vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/milo/worlds.js'), 'utf8'), context);
test('each chapter has a distinct location and three valid optional discoveries', () => {
  const worlds = context.window.MiloWorlds.definitions;
  assert.equal(Object.keys(worlds).length, chapters.length);
  assert.equal(new Set(Object.values(worlds).map(w => w.kind)).size, chapters.length);
  assert.ok(new Set(Object.values(worlds).map(w => w.shape)).size >= 5);
  for (const chapter of chapters) {
    const world = worlds[chapter.id];
    assert.ok(world.name && world.camera.length === 3, chapter.id);
    assert.equal(world.discoveries.length, 3);
    assert.equal(new Set(world.discoveries.map(d => d[0])).size, 3);
    const effects = chapter.steps.flatMap(step => step.choices.map(c => c.effect));
    for (const discovery of world.discoveries) {
      assert.ok(discovery[1] && discovery[2] && discovery[3]);
      for (const effect of discovery[4] || []) assert.ok(effects.includes(effect), chapter.id + ': ' + effect);
    }
    assert.equal(!!world.river, ['bridge', 'bridge-two'].includes(chapter.scene));
  }
});

test('every location builds its three interactive objects and releases its resources', () => {
  const engine = { console: { warn() {} } };
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/vendor/three/three-0.160.0.min.js'), 'utf8'), engine);
  for (const chapter of chapters) {
    const world = context.window.MiloWorlds.create(engine.THREE, chapter.id);
    const expected = world.spec.discoveries.map(d => d[0]).sort();
    assert.equal(JSON.stringify(world.targets.map(g => g.userData.worldAction).sort()), JSON.stringify(expected));
    let geometries = 0, disposed = 0;
    const seen = new Set();
    world.root.traverse(node => {
      if (node.geometry && !seen.has(node.geometry)) {
        seen.add(node.geometry); geometries++;
        node.geometry.addEventListener('dispose', () => disposed++);
      }
    });
    for (const id of expected) {
      assert.equal(world.interact(id)[0], id);
      world.update(.2); world.update(.2, true);
    }
    assert.ok(geometries > 20, chapter.id);
    world.dispose();
    assert.equal(disposed, geometries);
    assert.equal(world.targets.length, 0);
  }
});
