<script setup>
import { computed, onMounted, ref } from 'vue';
import { localDateKey, readWidgetCache, writeWidgetCache } from '../widgetCache';

const props = defineProps({
  endpoint: { type: String, required: true },
  channels: { type: Array, default: () => [] },
});

const storageKey = 'familyplan:tv-channel';
const selectedChannel = ref('rai1');
const loading = ref(false);
const error = ref('');
const payload = ref(null);

const channelOptions = computed(() => props.channels.length ? props.channels : [
  { key: 'rai1', label: 'Rai 1' },
  { key: 'rai2', label: 'Rai 2' },
  { key: 'rai3', label: 'Rai 3' },
  { key: 'rete4', label: 'Rete 4' },
  { key: 'canale5', label: 'Canale 5' },
  { key: 'italia1', label: 'Italia 1' },
  { key: 'la7', label: 'LA7' },
  { key: 'tv8', label: 'TV8' },
  { key: 'nove', label: 'Nove' },
  { key: 'realtime', label: 'Real Time' },
]);

async function loadPrograms() {
  const cacheKey = `familyplan:tv:${localDateKey()}:${selectedChannel.value}`;
  payload.value ||= readWidgetCache(cacheKey, 12 * 60 * 60 * 1000);
  loading.value = !payload.value;
  error.value = '';

  try {
    const response = await fetch(`${props.endpoint}?channel=${encodeURIComponent(selectedChannel.value)}`, {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.ok === false) {
      error.value = data.message || 'Programmi TV non disponibili.';
      payload.value = null;
      return;
    }

    payload.value = data;
    writeWidgetCache(cacheKey, data);
  } catch {
    error.value = 'Programmi TV non disponibili.';
    payload.value = null;
  } finally {
    loading.value = false;
  }
}

function onChannelChange() {
  window.localStorage.setItem(storageKey, selectedChannel.value);
  payload.value = null;
  loadPrograms();
}

onMounted(() => {
  const saved = window.localStorage.getItem(storageKey);
  if (saved && channelOptions.value.some((channel) => channel.key === saved)) {
    selectedChannel.value = saved;
  }

  loadPrograms();
});
</script>

<template>
  <article class="card weather-widget tv-widget">
    <div class="weather-widget-head">
      <div>
        <h2>TV stasera</h2>
        <p>{{ payload?.date_label || 'Programmi della serata' }}</p>
      </div>
      <label class="weather-select">
        <span class="sr-only">Seleziona canale</span>
        <select v-model="selectedChannel" @change="onChannelChange">
          <option v-for="channel in channelOptions" :key="channel.key" :value="channel.key">{{ channel.label }}</option>
        </select>
      </label>
    </div>

    <div v-if="loading" class="weather-widget-body">
      <p class="muted">Aggiornamento palinsesto...</p>
    </div>

    <div v-else-if="error" class="weather-widget-body">
      <p class="muted">{{ error }}</p>
    </div>

    <div v-else-if="payload" class="weather-widget-body football-widget-body">
      <div v-if="payload.programs?.length" class="football-match-list">
        <article v-for="program in payload.programs" :key="program.time + program.title" class="football-match">
          <div class="football-match-meta">
            <span>{{ payload.channel_label }}</span>
            <strong>{{ program.time }}</strong>
          </div>
          <p>{{ program.title }}</p>
          <small v-if="program.category" class="muted">{{ program.category }}</small>
        </article>
      </div>
      <p v-else class="muted">Nessun programma serale trovato per questo canale.</p>
    </div>
  </article>
</template>
