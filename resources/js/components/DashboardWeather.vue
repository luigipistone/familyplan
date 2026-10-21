<script setup>
import { computed, onMounted, ref } from 'vue';
import { readWidgetCache, writeWidgetCache } from '../widgetCache';

const props = defineProps({
  endpoint: { type: String, required: true },
  locations: { type: Array, default: () => [] },
  defaultLocation: { type: String, default: 'milano' },
});

const storageKey = 'familyplan:weather-location';
const selectedLocation = ref(props.defaultLocation);
const loading = ref(false);
const error = ref('');
const configured = ref(true);
const weather = ref(null);

const locationOptions = computed(() => props.locations.length ? props.locations : [
  { key: 'milano', label: 'Milano' },
  { key: 'riesi', label: 'Riesi' },
]);

async function loadWeather() {
  const cacheKey = `familyplan:weather:${selectedLocation.value}`;
  weather.value ||= readWidgetCache(cacheKey, 2 * 60 * 60 * 1000);
  loading.value = !weather.value;
  error.value = '';

  try {
    const response = await fetch(`${props.endpoint}?place=${encodeURIComponent(selectedLocation.value)}`, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const payload = await response.json().catch(() => ({}));

    configured.value = payload.configured !== false;

    if (!response.ok || payload.ok === false) {
      error.value = payload.message || 'Meteo non disponibile in questo momento.';
      weather.value = null;
      return;
    }

    weather.value = payload.weather || null;
    if (weather.value) writeWidgetCache(cacheKey, weather.value);
    if (!weather.value) {
      error.value = 'Meteo non disponibile in questo momento.';
    }
  } catch {
    error.value = 'Meteo non disponibile in questo momento.';
    weather.value = null;
  } finally {
    loading.value = false;
  }
}

function onLocationChange() {
  window.localStorage.setItem(storageKey, selectedLocation.value);
  weather.value = null;
  loadWeather();
}

onMounted(() => {
  const saved = window.localStorage.getItem(storageKey);
  if (saved && locationOptions.value.some((location) => location.key === saved)) {
    selectedLocation.value = saved;
  }

  loadWeather();
});
</script>

<template>
  <article class="card weather-widget">
    <div class="weather-widget-head">
      <div>
        <h2>Meteo</h2>
        <p>{{ weather?.location_label || 'Condizioni attuali' }}</p>
      </div>
      <label class="weather-select">
        <span class="sr-only">Seleziona luogo</span>
        <select v-model="selectedLocation" @change="onLocationChange">
          <option v-for="location in locationOptions" :key="location.key" :value="location.key">{{ location.label }}</option>
        </select>
      </label>
    </div>

    <div v-if="loading" class="weather-widget-body">
      <p class="muted">Aggiornamento meteo...</p>
    </div>

    <div v-else-if="error" class="weather-widget-body">
      <p class="muted">{{ error }}</p>
      <p v-if="!configured" class="muted">Aggiungi la chiave Google Weather API in `config.local.php`.</p>
    </div>

    <div v-else-if="weather" class="weather-widget-body">
      <div class="weather-primary">
        <img v-if="weather.icon_url" :src="weather.icon_url" :alt="weather.description" class="weather-icon">
        <div>
          <strong>{{ weather.temperature }}</strong>
          <p>{{ weather.description }}</p>
        </div>
      </div>
      <div class="weather-stats">
        <span>Sensazione {{ weather.feels_like }}</span>
        <span>Umidità {{ weather.humidity }}</span>
        <span>Pioggia {{ weather.precipitation_probability }}</span>
        <span>Vento {{ weather.wind_speed }}</span>
      </div>
      <small class="weather-updated">Aggiornato {{ weather.updated_at }}</small>
    </div>
  </article>
</template>
