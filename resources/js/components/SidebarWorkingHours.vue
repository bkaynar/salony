<template>
  <div class="px-4 py-3">
    <div class="flex items-center justify-between mb-2">
      <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Çalışma Saatleri</div>
      <div v-if="hasAnyToday" class="text-xs font-semibold text-green-700 dark:text-green-300">Açık</div>
      <div v-else class="text-xs font-semibold text-gray-500 dark:text-gray-400">Kapalı</div>
    </div>

    <div v-if="!hasWorkingHours" class="text-xs text-gray-500 dark:text-gray-400">Henüz çalışma saati tanımlanmamış</div>

    <div v-else class="space-y-2">
      <div v-for="entry in todayList" :key="entry.user_id" class="flex items-center gap-3">
        <div class="flex-1">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span :class="['w-2 h-2 rounded-full', entry.is_open ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600']"></span>
              <div class="text-sm font-medium text-slate-900 dark:text-white">{{ entry.user_name }}</div>
            </div>
            <div class="text-xs text-gray-600 dark:text-gray-300">{{ entry.display }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-3">
      <Link href="/dashboard/staff-working" class="text-xs text-blue-600 dark:text-blue-400">Düzenle</Link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

const page = usePage()

// workingHours prop expected from controller: array of { id, user_id, user_name, day_of_week, start_time, end_time, is_off }
// Some app shells expose page.props as a plain object (not a ref). Use defensive access so we don't read .value when it doesn't exist.
const workingHours = computed(() => {
  try {
    // prefer plain object access (common in this codebase)
    if (page && page.props && page.props.workingHours) return page.props.workingHours
    // fallback for setups where props is a ref: page.props.value
    if (page && page.props && page.props.value && page.props.value.workingHours) return page.props.value.workingHours
  } catch (e) {
    // ignore and return empty
  }
  return []
})

const hasWorkingHours = computed(() => workingHours.value && workingHours.value.length > 0)

const todayIndex = new Date().getDay() // 0 (Sun) - 6 (Sat); matches server day_of_week

function timeToMinutes(t) {
  if (!t) return null
  const parts = t.split(':')
  if (parts.length < 2) return null
  return Number(parts[0]) * 60 + Number(parts[1])
}

const nowMinutes = computed(() => {
  const d = new Date()
  return d.getHours() * 60 + d.getMinutes()
})

// Build today's list per user
const todayList = computed(() => {
  const map = {}
  ;(workingHours.value || []).forEach(w => {
    if (Number(w.day_of_week) !== todayIndex) return
    const uid = w.user_id || 'unknown'
    const name = w.user_name || (w.user && w.user.name) || '—'
    let display = ''
    let is_open = false
    if (w.is_off) {
      display = 'İzinli'
      is_open = false
    } else if (w.start_time && w.end_time) {
      display = `${w.start_time} - ${w.end_time}`
      const start = timeToMinutes(w.start_time)
      const end = timeToMinutes(w.end_time)
      if (start !== null && end !== null && nowMinutes.value >= start && nowMinutes.value <= end) {
        is_open = true
      }
    } else {
      display = '—'
    }

    map[uid] = { user_id: uid, user_name: name, display, is_open }
  })

  // return sorted by name
  return Object.values(map).sort((a, b) => (a.user_name || '').localeCompare(b.user_name || ''))
})

const hasAnyToday = computed(() => todayList.value.some(e => e.is_open))
</script>
