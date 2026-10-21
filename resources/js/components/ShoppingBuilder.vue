<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  formKey: { type: String, default: '' },
  mode: { type: String, default: 'create' },
  categories: { type: Object, default: () => ({}) },
  initialSelected: { type: Array, default: () => [] },
  initialProducts: { type: Array, default: () => [] },
});

function defaultProducts() {
  return props.initialProducts.map((product) => ({
    name: product.name || '',
    quantity: product.quantity || '',
    checked: Boolean(product.checked),
  }));
}

const selectedItems = ref([...props.initialSelected]);
const products = ref(defaultProducts());
const feedback = ref('');
const manualName = ref('');
const manualQuantity = ref('');
const flashKey = ref('');

const categoriesList = computed(() => Object.entries(props.categories));

function flashItem(name, message) {
  flashKey.value = name;
  feedback.value = message;
  window.setTimeout(() => {
    if (flashKey.value === name) flashKey.value = '';
  }, 650);
}

function toggleSelected(item) {
  if (selectedItems.value.includes(item)) {
    selectedItems.value = selectedItems.value.filter((value) => value !== item);
    feedback.value = `${item} rimosso.`;
    return;
  }

  selectedItems.value = [...selectedItems.value, item];
  flashItem(item, `${item} selezionato.`);
}

function addProduct(name, quantity = '', checked = false) {
  const trimmedName = name.trim();
  const trimmedQuantity = quantity.trim();
  if (!trimmedName) return;

  products.value = [
    ...products.value,
    { name: trimmedName, quantity: trimmedQuantity, checked: Boolean(checked) },
  ];
}

function addProductFromCategory(item) {
  addProduct(item);
  flashItem(item, `${item} aggiunto alla bozza.`);
}

function addManualProduct() {
  if (!manualName.value.trim()) return;
  addProduct(manualName.value, manualQuantity.value);
  feedback.value = `${manualName.value.trim()} aggiunto alla bozza.`;
  manualName.value = '';
  manualQuantity.value = '';
}

function removeProduct(index) {
  products.value = products.value.filter((_, currentIndex) => currentIndex !== index);
}

function resetBuilder() {
  selectedItems.value = [...props.initialSelected];
  products.value = defaultProducts();
  feedback.value = '';
  manualName.value = '';
  manualQuantity.value = '';
  flashKey.value = '';
}

onMounted(() => {
  if (!props.formKey) return;
  window.addEventListener(`shopping-builder:reset:${props.formKey}`, resetBuilder);
});

onBeforeUnmount(() => {
  if (!props.formKey) return;
  window.removeEventListener(`shopping-builder:reset:${props.formKey}`, resetBuilder);
});
</script>

<template>
  <section class="grocery-picker vue-shopping-builder">
    <section v-if="mode === 'edit'" class="shopping-products vue-shopping-products">
      <h2>Prodotti</h2>
      <div v-if="products.length" class="shopping-products-list">
        <div v-for="(product, index) in products" :key="`${product.name}-${index}`" class="product-row">
          <input :name="`products[${index}][name]`" :value="product.name" type="hidden">
          <input :name="`products[${index}][quantity]`" :value="product.quantity" type="hidden">
          <input :name="`products[${index}][checked]`" :value="product.checked ? 1 : 0" type="hidden">
          <span :class="{ done: product.checked }">{{ product.name }} {{ product.quantity }}</span>
          <button class="icon-btn danger-icon" type="button" aria-label="Rimuovi prodotto" title="Rimuovi prodotto" @click="removeProduct(index)">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg>
          </button>
        </div>
      </div>
      <p v-else class="muted">Nessun prodotto inserito.</p>
      <div class="inline">
        <input v-model="manualName" placeholder="Articolo" type="text">
        <input v-model="manualQuantity" placeholder="Qtà" type="text">
        <button type="button" @click="addManualProduct">+</button>
      </div>
    </section>

    <details v-for="([category, items], index) in categoriesList" :key="category" class="grocery-accordion" :open="index === 0">
      <summary>{{ category }}</summary>
      <div class="grocery-items is-active">
        <button
          v-for="item in items"
          :key="item"
          :class="{ 'was-added': mode === 'create' ? selectedItems.includes(item) : flashKey === item }"
          type="button"
          @click="mode === 'create' ? toggleSelected(item) : addProductFromCategory(item)"
        >
          {{ item }}
        </button>
      </div>
    </details>

    <div v-if="mode === 'create'" class="selected-groceries">
      <button
        v-for="item in selectedItems"
        :key="item"
        class="selected-grocery-chip"
        type="button"
        @click="toggleSelected(item)"
      >
        <span>{{ item }}</span>
        <strong>x</strong>
      </button>
      <p v-if="!selectedItems.length" class="muted">Nessun prodotto selezionato.</p>
      <input v-for="item in selectedItems" :key="`input-${item}`" name="selected_items[]" :value="item" type="hidden">
    </div>

    <p class="grocery-feedback" aria-live="polite">{{ feedback }}</p>
  </section>
</template>
