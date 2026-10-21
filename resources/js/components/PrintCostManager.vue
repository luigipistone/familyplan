<script setup>
import { computed, ref } from 'vue';

const grams = ref(0);
const hours = ref(0);
const minutes = ref(0);
const filamentPerKg = ref(20);
const energyPerKwh = ref(0.24);
const averageWatts = ref(120);
const machinePerHour = ref(0);
const quantity = ref(1);
const failurePercentage = ref(0);

const numericValue = (value) => Number(String(value ?? '').replace(',', '.')) || 0;
const printHours = computed(() => Math.max(0, numericValue(hours.value)) + Math.max(0, numericValue(minutes.value)) / 60);
const materialCost = computed(() => Math.max(0, Number(grams.value) || 0) / 1000 * Math.max(0, Number(filamentPerKg.value) || 0));
const energyKwh = computed(() => Math.max(0, Number(averageWatts.value) || 0) / 1000 * printHours.value);
const energyCost = computed(() => energyKwh.value * Math.max(0, Number(energyPerKwh.value) || 0));
const machineCost = computed(() => printHours.value * Math.max(0, Number(machinePerHour.value) || 0));
const singleBase = computed(() => materialCost.value + energyCost.value + machineCost.value);
const singleCost = computed(() => {
  const risk = Math.min(90, Math.max(0, Number(failurePercentage.value) || 0));
  return risk ? singleBase.value / (1 - risk / 100) : singleBase.value;
});
const totalCost = computed(() => singleCost.value * Math.max(1, Math.trunc(Number(quantity.value) || 1)));
const euro = value => new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(value || 0);

</script>

<template>
  <section class="page-head cost3d-head">
    <div><h1>Costi 3D</h1><p>Inserisci i dati dello slicer e scopri quanto costa la stampa.</p></div>
  </section>

  <section class="simple-cost-layout">
    <div class="panel simple-cost-form">
      <div class="simple-cost-fields">
        <label>Peso totale filamento
          <span class="input-suffix"><input v-model="grams" type="number" min="0" step="0.1"><small>g</small></span>
        </label>
        <label class="cost-range-field">Costo filamento
          <span><input v-model="filamentPerKg" type="range" min="5" max="100" step="0.5"><strong>{{ Number(filamentPerKg).toLocaleString('it-IT') }} €/kg</strong></span>
        </label>
      </div>

      <div class="simple-cost-fields">
        <label>Ore di stampa<input v-model="hours" type="text" inputmode="decimal" placeholder="Es. 1,7"><small>Puoi inserire direttamente valori come 1,7 h.</small></label>
        <label>Minuti<input v-model="minutes" type="number" min="0" max="59" step="1"></label>
      </div>

      <details class="cost-advanced">
        <summary>Opzioni avanzate</summary>
        <div class="simple-cost-fields advanced-fields">
          <label>Potenza media<input v-model="averageWatts" type="number" min="0" step="1"><small>Watt</small></label>
          <label>Costo energia<input v-model="energyPerKwh" type="number" min="0" step="0.01"><small>€/kWh</small></label>
          <label>Usura macchina<input v-model="machinePerHour" type="number" min="0" step="0.01"><small>€/ora</small></label>
          <label>Rischio fallimento<input v-model="failurePercentage" type="number" min="0" max="90" step="1"><small>%</small></label>
          <label>Quantità<input v-model="quantity" type="number" min="1" step="1"></label>
        </div>
      </details>
    </div>

    <aside class="panel simple-cost-result" aria-live="polite">
      <span>Costo stimato</span>
      <strong>{{ euro(totalCost) }}</strong>
      <p v-if="Number(quantity) > 1">{{ euro(singleCost) }} per pezzo</p>
      <dl>
        <div><dt>Filamento</dt><dd>{{ euro(materialCost * Math.max(1, Number(quantity) || 1)) }}</dd></div>
        <div><dt>Energia</dt><dd>{{ euro(energyCost * Math.max(1, Number(quantity) || 1)) }}</dd></div>
        <div v-if="machineCost"><dt>Usura macchina</dt><dd>{{ euro(machineCost * Math.max(1, Number(quantity) || 1)) }}</dd></div>
        <div v-if="failurePercentage"><dt>Rischio incluso</dt><dd>{{ Number(failurePercentage) }}%</dd></div>
      </dl>
      <small>Stima basata sui dati indicati. Supporti e spurgo devono essere inclusi nel peso totale.</small>
    </aside>
  </section>
</template>
