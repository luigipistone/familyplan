<script setup>
import { computed } from 'vue';

const props = defineProps({
  events: { type: Array, default: () => [] },
});

const groupedEvents = computed(() => ({
  Oggi: props.events.filter((event) => event.day_group === 'Oggi'),
  Domani: props.events.filter((event) => event.day_group === 'Domani'),
}));
</script>

<template>
  <div class="stack">
    <section class="stack">
      <h2>Oggi</h2>
      <div class="timeline">
        <article v-for="event in groupedEvents.Oggi" :key="`today-${event.id}`">
          <time>{{ event.starts_at }}</time>
          <div class="timeline-body">
            <img v-if="event.child_avatar_url" class="avatar avatar-md" :src="event.child_avatar_url" :alt="event.child_name || 'Figlio'">
            <span v-else class="avatar avatar-md avatar-fallback">{{ event.child_initials || 'F' }}</span>
            <div class="timeline-copy">
              <strong>{{ event.child_name || 'Figlio' }}</strong>
              <p>{{ event.title }} · {{ event.assigned_name || 'Da assegnare' }}</p>
            </div>
          </div>
        </article>
        <p v-if="!groupedEvents.Oggi.length" class="muted">Nessun impegno per oggi.</p>
      </div>
    </section>
    <section class="stack">
      <h2>Domani</h2>
      <div class="timeline">
        <article v-for="event in groupedEvents.Domani" :key="`tomorrow-${event.id}`">
          <time>{{ event.starts_at }}</time>
          <div class="timeline-body">
            <img v-if="event.child_avatar_url" class="avatar avatar-md" :src="event.child_avatar_url" :alt="event.child_name || 'Figlio'">
            <span v-else class="avatar avatar-md avatar-fallback">{{ event.child_initials || 'F' }}</span>
            <div class="timeline-copy">
              <strong>{{ event.child_name || 'Figlio' }}</strong>
              <p>{{ event.title }} · {{ event.assigned_name || 'Da assegnare' }}</p>
            </div>
          </div>
        </article>
        <p v-if="!groupedEvents.Domani.length" class="muted">Nessun impegno per domani.</p>
      </div>
    </section>
    <p v-if="!events.length" class="muted">Nessun impegno figli per oggi o domani.</p>
  </div>
</template>
