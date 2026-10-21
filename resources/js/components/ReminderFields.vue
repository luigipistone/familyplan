<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  formKey: { type: String, default: '' },
  title: { type: String, default: '' },
  dueAt: { type: String, default: '' },
  recurrence: { type: String, default: 'none' },
  body: { type: String, default: '' },
  sharedWith: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
});

const title = ref(props.title);
const dueAt = ref(props.dueAt);
const recurrence = ref(props.recurrence || 'none');
const body = ref(props.body || '');
const sharedWith = ref(props.sharedWith.map(String));
const editorRef = ref(null);

function runCommand(command, value = null) {
  if (command === 'createLink') {
    const selection = window.getSelection?.();
    if (!selection || selection.toString().trim() === '') return;
    value = window.prompt('Inserisci il link completo', 'https://') || '';
    if (!value.trim()) return;
  }
  document.execCommand(command, false, value);
}

function onEditorInput(event) {
  body.value = event.target.innerHTML.trim();
}

function resetFields() {
  title.value = props.title;
  dueAt.value = props.dueAt;
  recurrence.value = props.recurrence || 'none';
  body.value = props.body || '';
  sharedWith.value = props.sharedWith.map(String);
  nextTick(() => {
    if (editorRef.value) editorRef.value.innerHTML = body.value || '';
  });
}

onMounted(() => {
  if (!props.formKey) return;
  window.addEventListener(`reminder-form:reset:${props.formKey}`, resetFields);
  if (editorRef.value) {
    editorRef.value.innerHTML = body.value || '';
  }
});

onBeforeUnmount(() => {
  if (!props.formKey) return;
  window.removeEventListener(`reminder-form:reset:${props.formKey}`, resetFields);
});
</script>

<template>
  <div class="form-stack">
    <div class="two">
      <label>Titolo<input v-model="title" name="title" required></label>
      <label>Data e ora<input v-model="dueAt" type="datetime-local" name="due_at"></label>
    </div>
    <label>Ricorrenza<select v-model="recurrence" name="recurrence"><option value="none">Nessuna</option><option value="daily">Giornaliera</option><option value="weekly">Settimanale</option><option value="monthly">Mensile</option></select></label>
    <label>
      Testo
      <textarea v-model="body" name="body" data-wysiwyg-input hidden></textarea>
      <div class="wysiwyg" data-wysiwyg>
        <div class="wysiwyg-toolbar">
          <button type="button" @click="runCommand('bold')"><strong>B</strong></button>
          <button type="button" @click="runCommand('italic')"><em>I</em></button>
          <button type="button" @click="runCommand('underline')"><span style="text-decoration: underline;">U</span></button>
          <button type="button" @click="runCommand('strikeThrough')"><span style="text-decoration: line-through;">S</span></button>
          <button type="button" @click="runCommand('insertUnorderedList')">•</button>
          <button type="button" @click="runCommand('insertOrderedList')">1.</button>
          <button type="button" @click="runCommand('formatBlock', 'blockquote')">"</button>
          <button type="button" @click="runCommand('createLink')">Link</button>
          <button type="button" @click="runCommand('removeFormat')">Tx</button>
        </div>
        <div ref="editorRef" class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Scrivi il promemoria." @input="onEditorInput"></div>
      </div>
    </label>
    <fieldset class="check-list">
      <legend>Condividi</legend>
      <label v-for="user in users" :key="user.id" class="check"><input v-model="sharedWith" type="checkbox" name="shared_with[]" :value="String(user.id)"> {{ user.name }}</label>
    </fieldset>
  </div>
</template>
