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
      assert.ok(step.title && step.text && step.choices.length);
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
