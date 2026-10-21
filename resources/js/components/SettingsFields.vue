<script setup>
import { ref } from 'vue';

const props = defineProps({
  theme: { type: String, default: 'light' },
  weekStartsOn: { type: String, default: 'monday' },
  compactDashboard: { type: Boolean, default: false },
  emailNotifications: { type: Boolean, default: true },
  pushNotifications: { type: Boolean, default: true },
  calendarReminderMinutes: { type: Number, default: 30 },
  shoppingArchiveDays: { type: Number, default: 7 },
});

const theme = ref(props.theme);
const weekStartsOn = ref(props.weekStartsOn);
const compactDashboard = ref(props.compactDashboard);
const emailNotifications = ref(props.emailNotifications);
const pushNotifications = ref(props.pushNotifications);
const calendarReminderMinutes = ref(String(props.calendarReminderMinutes));
const shoppingArchiveDays = ref(props.shoppingArchiveDays);
</script>

<template>
  <div class="settings-grid">
    <section class="setting-card">
      <h2>Aspetto</h2>
      <div class="two">
        <label>Tema<select v-model="theme" name="theme"><option value="light">Chiaro</option><option value="dark">Scuro</option></select></label>
        <label>Settimana<select v-model="weekStartsOn" name="week_starts_on"><option value="monday">Inizia di lunedi</option><option value="sunday">Inizia di domenica</option></select></label>
      </div>
      <label class="check"><input v-model="compactDashboard" type="checkbox" name="compact_dashboard" value="1"> Dashboard piu compatta</label>
    </section>
    <section class="setting-card">
      <h2>Notifiche</h2>
      <p>Scegli quali canali usare per promemoria, eventi condivisi e aggiornamenti importanti.</p>
      <div class="two">
        <label class="check"><input v-model="emailNotifications" type="checkbox" name="email_notifications" value="1"> Email attive</label>
        <label class="check"><input v-model="pushNotifications" type="checkbox" name="push_notifications" value="1"> Notifiche dispositivo</label>
      </div>
      <label>Preavviso calendario<select v-model="calendarReminderMinutes" name="calendar_reminder_minutes">
        <option value="0">Nessun preavviso</option>
        <option value="10">10 minuti prima</option>
        <option value="30">30 minuti prima</option>
        <option value="60">1 ora prima</option>
        <option value="1440">1 giorno prima</option>
      </select></label>
    </section>
    <section class="setting-card">
      <h2>Liste spesa</h2>
      <p>Imposta dopo quanti giorni archiviare automaticamente una lista completata.</p>
      <label>Archivia dopo<input v-model="shoppingArchiveDays" type="number" name="shopping_archive_days" min="1" max="30"></label>
    </section>
  </div>
</template>
