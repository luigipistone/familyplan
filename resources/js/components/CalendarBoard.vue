<script setup>
import { computed } from 'vue';

const props = defineProps({
  days: { type: Array, default: () => [] },
  events: { type: Array, default: () => [] },
  todayEvents: { type: Array, default: () => [] },
  monthLabel: { type: String, default: '' },
  todayLabel: { type: String, default: '' },
  previousMonthUrl: { type: String, default: '#' },
  nextMonthUrl: { type: String, default: '#' },
});

const weekdays = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];

const eventsByDay = computed(() => {
  const map = new Map();
  props.days.forEach((day) => {
    map.set(day.date, props.events.filter((event) => event.start_day <= day.date && event.end_day >= day.date));
  });
  return map;
});

function eventClasses(event, day) {
  return {
    'calendar-event': true,
    'is-span': event.start_day !== event.end_day,
    'continues-before': event.start_day < day.date,
    'continues-after': event.end_day > day.date,
  };
}

function eventStyle(event) {
  const style = event.creator_style || {};
  return {
    '--event-accent': style.accent || '#2f6f9d',
    '--event-bg': style.background || 'rgba(47, 111, 157, .14)',
    '--event-bg-hover': style.background_hover || 'rgba(47, 111, 157, .22)',
    '--event-bg-span': style.background_span || 'rgba(47, 111, 157, .20)',
    '--event-border': style.border || 'rgba(47, 111, 157, .34)',
    '--event-border-span': style.border_span || 'rgba(47, 111, 157, .50)',
    '--event-text': style.text || '#18303c',
    '--event-muted': style.muted || '#56707d',
  };
}

function startLabel(event, day) {
  return event.start_day === day.date ? event.start_time : '↳';
}

function openEvent(event) {
  window.openCalendarEventEditor?.(event, event.delete_action);
}

function openCreate(day) {
  window.openCalendarCreateModal?.(day);
}
</script>

<template>
  <div class="vue-calendar-board">
    <section class="mobile-today">
      <div class="row"><h2>Oggi</h2><time>{{ todayLabel }}</time></div>
      <div class="calendar-events">
        <button
          v-for="event in todayEvents"
          :key="`today-${event.id}`"
          class="calendar-event"
          type="button"
          :style="eventStyle(event)"
          data-edit-event
          :data-event-id="event.id"
          :data-event-action="event.action"
          :data-event-duplicate-action="event.duplicate_action"
          :data-event-delete-action="event.delete_action"
          :data-event-title="event.title"
          :data-event-description="event.description || ''"
          :data-event-starts-at="event.starts_at"
          :data-event-ends-at="event.ends_at || ''"
          :data-event-visibility="event.visibility"
          :data-event-assigned-user-id="event.assigned_user_id || ''"
          :data-event-child-id="event.child_id || ''"
          @click.stop="openEvent(event)"
        >
          <span>{{ event.start_time }}</span>
          <strong>{{ event.title }}</strong>
          <small>{{ event.creator_name }}</small>
        </button>
        <p v-if="!todayEvents.length" class="muted">Nessun evento per oggi.</p>
      </div>
    </section>

    <section class="calendar-shell">
      <header class="calendar-toolbar">
        <a class="icon-btn" :href="previousMonthUrl" aria-label="Mese precedente" title="Mese precedente"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></a>
        <h2>{{ monthLabel }}</h2>
        <a class="icon-btn" :href="nextMonthUrl" aria-label="Mese successivo" title="Mese successivo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></a>
      </header>
      <div class="calendar-weekdays">
        <span v-for="weekday in weekdays" :key="weekday">{{ weekday }}</span>
      </div>
      <div class="calendar-month">
        <article
          v-for="day in days"
          :key="day.date"
          class="calendar-day"
          :class="{ 'is-muted': !day.isCurrentMonth, 'is-today': day.isToday }"
          :data-calendar-day="day.date"
        >
          <button class="calendar-day-trigger" type="button" @click.stop="openCreate(day.date)">
            <time :datetime="day.date">{{ day.day }}</time>
          </button>
          <div class="calendar-events">
            <button
              v-for="event in (eventsByDay.get(day.date) || [])"
              :key="`${day.date}-${event.id}`"
              :class="eventClasses(event, day)"
              type="button"
              draggable="true"
              :style="eventStyle(event)"
              data-edit-event
              :data-event-id="event.id"
              :data-event-action="event.action"
              :data-event-duplicate-action="event.duplicate_action"
              :data-event-delete-action="event.delete_action"
              :data-event-title="event.title"
              :data-event-description="event.description || ''"
              :data-event-starts-at="event.starts_at"
              :data-event-ends-at="event.ends_at || ''"
              :data-event-visibility="event.visibility"
              :data-event-assigned-user-id="event.assigned_user_id || ''"
              :data-event-child-id="event.child_id || ''"
              @click.stop="openEvent(event)"
            >
              <span>{{ startLabel(event, day) }}</span>
              <strong>{{ event.title }}</strong>
              <small>{{ event.creator_name }}</small>
            </button>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>
