<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
  name: { type: String, default: '' },
  phone: { type: String, default: '' },
  email: { type: String, default: '' },
  birthDate: { type: String, default: '' },
  favoriteTeam: { type: String, default: '' },
  personalInfo: { type: String, default: '' },
  profilePhotoUrl: { type: String, default: '' },
  avatarInitials: { type: String, default: '' },
});

const name = ref(props.name);
const phone = ref(props.phone);
const email = ref(props.email);
const birthDate = ref(props.birthDate);
const favoriteTeam = ref(props.favoriteTeam);
const personalInfo = ref(props.personalInfo || '');
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
  personalInfo.value = event.target.innerHTML.trim();
}

onMounted(() => {
  if (editorRef.value) {
    editorRef.value.innerHTML = personalInfo.value || '';
  }
});
</script>

<template>
  <div class="form-stack">
    <div class="avatar-upload-panel">
      <img v-if="profilePhotoUrl" class="avatar avatar-xl" :src="profilePhotoUrl" :alt="name">
      <span v-else class="avatar avatar-xl avatar-fallback">{{ avatarInitials || 'FP' }}</span>
      <label>Immagine profilo<input type="file" name="profile_photo" accept="image/*"></label>
    </div>
    <div class="two">
      <label>Nome<input v-model="name" name="name" required></label>
      <label>Telefono<input v-model="phone" name="phone"></label>
    </div>
    <div class="two">
      <label>Email<input v-model="email" type="email" name="email"></label>
      <label>Data di nascita<input v-model="birthDate" type="date" name="birth_date"></label>
    </div>
    <label>Squadra del cuore<input v-model="favoriteTeam" name="favorite_team" placeholder="Es. Milan, Juventus, Inter, Arsenal"></label>
    <label>
      Informazioni personali
      <textarea v-model="personalInfo" name="personal_info" rows="5" data-wysiwyg-input hidden></textarea>
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
        <div ref="editorRef" class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Aggiungi note personali o informazioni utili." @input="onEditorInput"></div>
      </div>
    </label>
  </div>
</template>
