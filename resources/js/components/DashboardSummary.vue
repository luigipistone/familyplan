<script setup>
const props = defineProps({
  eventsCount: { type: Number, default: 0 },
  shoppingCount: { type: Number, default: 0 },
  remindersCount: { type: Number, default: 0 },
  notesCount: { type: Number, default: 0 },
});

const stats = [
  { label: 'Eventi', value: props.eventsCount },
  { label: 'Liste attive', value: props.shoppingCount },
  { label: 'Promemoria', value: props.remindersCount },
  { label: 'Note', value: props.notesCount },
];
</script>

<template>
  <section class="vue-summary">
    <article v-for="stat in stats" :key="stat.label" class="vue-summary-card">
      <strong>{{ stat.value }}</strong>
      <span>{{ stat.label }}</span>
    </article>
  </section>
</template>
