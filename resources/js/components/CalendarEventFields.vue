<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  formKey: { type: String, required: true },
  users: { type: Array, default: () => [] },
  children: { type: Array, default: () => [] },
});

const defaults = () => ({
  title: '',
  starts_at: '',
  ends_at: '',
  visibility: 'family',
  assigned_user_id: '',
  child_id: '',
  description: '',
});

const form = ref(defaults());
const autoEnd = ref(true);

function pad(value) {
  return String(value).padStart(2, '0');
}

function plusOneHour(value) {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  date.setHours(date.getHours() + 1);
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function setValues(values = {}) {
  form.value = {
    ...defaults(),
    ...values,
  };
  autoEnd.value = !form.value.ends_at;
  if (autoEnd.value && form.value.starts_at) {
    form.value.ends_at = plusOneHour(form.value.starts_at);
  }
}

function resetValues() {
  form.value = defaults();
  autoEnd.value = true;
}

function onStartChange(event) {
  form.value.starts_at = event.target.value;
  if (autoEnd.value) {
    form.value.ends_at = plusOneHour(form.value.starts_at);
  }
}

function onEndChange(event) {
  form.value.ends_at = event.target.value;
  autoEnd.value = false;
}

function handleSetValues(event) {
  setValues(event.detail || {});
}

function handleReset() {
  resetValues();
}

onMounted(() => {
  window.addEventListener(`calendar-form:set-values:${props.formKey}`, handleSetValues);
  window.addEventListener(`calendar-form:reset:${props.formKey}`, handleReset);
});

onBeforeUnmount(() => {
  window.removeEventListener(`calendar-form:set-values:${props.formKey}`, handleSetValues);
  window.removeEventListener(`calendar-form:reset:${props.formKey}`, handleReset);
});
</script>

<template>
  <div class="form-stack">
    <div class="two">
      <label>Titolo<input v-model="form.title" name="title" data-event-field="title" required></label>
      <label>Inizio<input :value="form.starts_at" type="datetime-local" name="starts_at" data-event-field="starts_at" required @input="onStartChange"></label>
    </div>
    <div class="two">
      <label>Fine<input :value="form.ends_at" type="datetime-local" name="ends_at" data-event-field="ends_at" @input="onEndChange"></label>
      <label>Visibilita<select v-model="form.visibility" name="visibility" data-event-field="visibility"><option value="family">Famiglia</option><option value="private">Privato</option></select></label>
    </div>
    <div class="two">
      <label>Assegnato a<select v-model="form.assigned_user_id" name="assigned_user_id" data-event-field="assigned_user_id"><option value="">Nessuno</option><option v-for="user in users" :key="user.id" :value="String(user.id)">{{ user.name }}</option></select></label>
      <label>Figlio<select v-model="form.child_id" name="child_id" data-event-field="child_id"><option value="">Nessuno</option><option v-for="child in children" :key="child.id" :value="String(child.id)">{{ child.name }}</option></select></label>
    </div>
    <label>Dettagli<textarea v-model="form.description" name="description" rows="4" data-event-field="description"></textarea></label>
  </div>
</template>
