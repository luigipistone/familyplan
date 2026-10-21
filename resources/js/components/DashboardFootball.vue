<script setup>
import { onMounted, ref } from 'vue';
import { localDateKey, readWidgetCache, writeWidgetCache } from '../widgetCache';

const props = defineProps({
  endpoint: { type: String, required: true },
});

const loading = ref(false);
const error = ref('');
const payload = ref(null);
const cacheKey = `familyplan:football:${localDateKey()}`;

async function loadMatches() {
  loading.value = !payload.value;
  error.value = '';

  try {
    const response = await fetch(props.endpoint, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok || data.ok === false) {
      error.value = data.message || 'Risultati calcio non disponibili.';
      payload.value = null;
      return;
    }

    payload.value = data;
    writeWidgetCache(cacheKey, data);
  } catch {
    error.value = 'Risultati calcio non disponibili.';
    payload.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  payload.value = readWidgetCache(cacheKey, 12 * 60 * 60 * 1000);
  loadMatches();
});
</script>

<template>
  <article class="card weather-widget football-widget">
    <div class="weather-widget-head">
      <div>
        <h2>Calcio</h2>
        <p>{{ payload?.date_label || 'Partite importanti di oggi' }}</p>
      </div>
    </div>

    <div v-if="loading" class="weather-widget-body">
      <p class="muted">Aggiornamento risultati...</p>
    </div>

    <div v-else-if="error" class="weather-widget-body">
      <p class="muted">{{ error }}</p>
    </div>

    <div v-else-if="payload" class="weather-widget-body football-widget-body">
      <div v-if="payload.favorite_team" class="football-favorite">
        <strong>{{ payload.favorite_team.title }}</strong>
        <p>{{ payload.favorite_team.subtitle }}</p>
      </div>

      <div v-if="payload.matches?.length" class="football-match-list">
        <article v-for="match in payload.matches" :key="match.id" class="football-match" :class="{ 'is-favorite': match.is_favorite }">
          <div class="football-match-meta">
            <span>{{ match.league }}</span>
            <strong>{{ match.time_label }}</strong>
          </div>
          <p>{{ match.label }}</p>
        </article>
      </div>
      <p v-else class="muted">Nessuna partita importante trovata oggi.</p>
    </div>
  </article>
</template>
