<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  formKey: { type: String, required: true },
  mothers: { type: Array, default: () => [] },
  fathers: { type: Array, default: () => [] },
  showIsActive: { type: Boolean, default: false },
});

const defaults = () => ({
  name: '',
  phone: '',
  email: '',
  password: '',
  role: 'familiare',
  category: 'mamma',
  mother_id: '',
  father_id: '',
  birth_date: '',
  personal_info: '',
  profile_photo_url: '',
  avatar_initials: 'FP',
  is_active: true,
});

const form = ref(defaults());
const passwordVisible = ref(false);
const editorRef = ref(null);

function ageFromDate(value) {
  if (!value) return null;
  const birth = new Date(`${value}T00:00:00`);
  if (Number.isNaN(birth.getTime())) return null;
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const beforeBirthday = today.getMonth() < birth.getMonth() ||
    (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate());
  if (beforeBirthday) age -= 1;
  return age;
}

const isChild = computed(() => form.value.category === 'figlio');
const age = computed(() => ageFromDate(form.value.birth_date));
const childHasAccess = computed(() => isChild.value && age.value !== null && age.value >= 14);
const showParents = computed(() => isChild.value);
const showAccessFields = computed(() => !isChild.value || childHasAccess.value);

watch(showAccessFields, (visible) => {
  if (!visible) {
    form.value.phone = '';
    form.value.email = '';
    form.value.password = '';
  }
});

function setValues(values = {}) {
  form.value = {
    ...defaults(),
    ...values,
    is_active: values.is_active ?? true,
  };
  passwordVisible.value = false;
  nextTick(() => {
    if (editorRef.value) editorRef.value.innerHTML = form.value.personal_info || '';
  });
}

function resetValues() {
  form.value = defaults();
  passwordVisible.value = false;
  nextTick(() => {
    if (editorRef.value) editorRef.value.innerHTML = '';
  });
}

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
  form.value.personal_info = event.target.innerHTML.trim();
}

function handleSetValues(event) {
  setValues(event.detail || {});
}

function handleReset() {
  resetValues();
}

onMounted(() => {
  window.addEventListener(`user-form:set-values:${props.formKey}`, handleSetValues);
  window.addEventListener(`user-form:reset:${props.formKey}`, handleReset);
  if (editorRef.value) {
    editorRef.value.innerHTML = form.value.personal_info || '';
  }
});

onBeforeUnmount(() => {
  window.removeEventListener(`user-form:set-values:${props.formKey}`, handleSetValues);
  window.removeEventListener(`user-form:reset:${props.formKey}`, handleReset);
});
</script>

<template>
  <div class="form-stack">
    <div class="avatar-upload-panel">
      <img v-if="form.profile_photo_url" class="avatar avatar-xl" :src="form.profile_photo_url" :alt="form.name || 'Profilo'">
      <span v-else class="avatar avatar-xl avatar-fallback">{{ form.avatar_initials || 'FP' }}</span>
      <label>Immagine profilo<input type="file" name="profile_photo" accept="image/*"></label>
    </div>
    <div class="two">
      <label>Nome<input v-model="form.name" name="name" required></label>
      <label v-if="showAccessFields">Telefono<input v-model="form.phone" name="phone"></label>
    </div>
    <div class="two">
      <label v-if="showAccessFields">Email<input v-model="form.email" type="email" name="email"></label>
      <label v-if="showAccessFields">
        {{ props.formKey === 'edit' ? 'Nuova password' : 'Password' }}
        <span class="input-with-icon password-field" :class="{ 'is-visible': passwordVisible }">
          <input v-model="form.password" :type="passwordVisible ? 'text' : 'password'" name="password" minlength="12" autocomplete="new-password" :placeholder="props.formKey === 'edit' ? 'Lascia vuoto per non cambiarla' : ''">
          <span class="password-toggle" role="button" tabindex="0" aria-label="Mostra password" title="Mostra password" @click="passwordVisible = !passwordVisible">
            <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A2.9 2.9 0 0 0 12 15a3 3 0 0 0 2.7-1.7"/><path d="M7.4 7.4C4.4 9 2.5 12 2.5 12s3.5 6 9.5 6c1.7 0 3.1-.4 4.4-1"/><path d="M12.6 6.1C18.2 6.5 21.5 12 21.5 12a14.7 14.7 0 0 1-2.1 2.7"/></svg>
          </span>
        </span>
      </label>
    </div>
    <div class="two">
      <label>Ruolo<select v-model="form.role" name="role"><option value="familiare">Familiare</option><option value="admin">Admin</option></select></label>
      <label>Tipologia<select v-model="form.category" name="category"><option value="mamma">Mamma</option><option value="papa">Papa</option><option value="figlio">Figlio</option><option value="nonno">Nonno</option><option value="zia">Zia</option><option value="altro">Altro</option></select></label>
    </div>
    <div v-if="showParents" class="two">
      <label>Mamma<select v-model="form.mother_id" name="mother_id"><option value="">Nessuna</option><option v-for="mother in mothers" :key="mother.id" :value="String(mother.id)">{{ mother.name }}</option></select></label>
      <label>Papa<select v-model="form.father_id" name="father_id"><option value="">Nessuno</option><option v-for="father in fathers" :key="father.id" :value="String(father.id)">{{ father.name }}</option></select></label>
    </div>
    <label>Data di nascita<input v-model="form.birth_date" type="date" name="birth_date"></label>
    <label>
      Info
      <textarea v-model="form.personal_info" name="personal_info" rows="4" data-wysiwyg-input hidden></textarea>
      <div class="wysiwyg" data-wysiwyg>
        <div class="wysiwyg-toolbar" aria-label="Formattazione testo">
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
        <div ref="editorRef" class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Aggiungi note, dettagli sanitari, abitudini o informazioni utili." @input="onEditorInput"></div>
      </div>
    </label>
    <label v-if="showIsActive" class="check"><input v-model="form.is_active" type="checkbox" name="is_active" value="1"> Account attivo</label>
  </div>
</template>
