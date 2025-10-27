<template>
  <Head title="Randevu Takvimi" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="p-6">
      <div>
        <FullCalendar
          :plugins="plugins"
          initial-view="timeGridWeek"
          :header-toolbar="{
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridDay,timeGridWeek,dayGridMonth'
          }"
          :events="events"
          :event-click="handleEventClick"
          :slot-min-time="'08:00:00'"
          :slot-max-time="'20:00:00'"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { defineProps } from 'vue'

// FullCalendar imports
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Takvim' },
]

const props = defineProps({
  events: { type: Array, default: () => [] },
})

const plugins = [dayGridPlugin, timeGridPlugin, interactionPlugin]

function handleEventClick(info) {
  // Simple alert for now; could open a modal with details
  const e = info.event
  alert(`${e.title}\n${e.start?.toLocaleString()} — ${e.end?.toLocaleString()}`)
}
</script>

<style>
@import '@fullcalendar/daygrid/main.css';
@import '@fullcalendar/timegrid/main.css';
</style>
