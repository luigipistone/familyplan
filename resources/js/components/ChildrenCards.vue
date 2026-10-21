<script setup>
defineProps({
  children: { type: Array, default: () => [] },
});
</script>

<template>
  <div class="stack">
    <article v-for="child in children" :key="child.id" class="card">
      <div class="child-card-head">
        <img v-if="child.avatar_url" class="avatar avatar-lg" :src="child.avatar_url" :alt="child.name">
        <span v-else class="avatar avatar-lg avatar-fallback">{{ child.initials || 'F' }}</span>
        <div>
          <h2>{{ child.name }}</h2>
          <p>{{ child.age_label }} · {{ child.management_label }}</p>
        </div>
      </div>
      <small>{{ child.personal_info }}</small>
    </article>
    <p v-if="!children.length" class="muted">Nessun profilo figlio collegato.</p>
  </div>
</template>
