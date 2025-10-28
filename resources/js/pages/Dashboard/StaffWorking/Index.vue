<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

const props = defineProps({
  staff: { type: Array, default: () => [] },
  workingHours: { type: Array, default: () => [] },
})

// Group working hours by user and day
const grouped = computed(() => {
  try {
    const map = {}
    ;(props.workingHours || []).forEach(w => {
      const uid = w.user_id || 'unknown'
      if (!map[uid]) map[uid] = { user_name: w.user_name || '—', days: [] }
      map[uid].days.push(w)
    })
    // convert to array
    return Object.keys(map).map(k => map[k])
  } catch (e) {
    // defensive: don't break rendering if props shape unexpected
    return []
  }
})
</script>

<template>
  <Head title="Çalışma Saatleri" />

  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/' }, { title: 'Çalışma Saatleri' }]">
    <div class="space-y-4">
      <Card>
        <CardHeader>
          <CardTitle>Personel Çalışma Saatleri</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="grouped.length === 0" class="text-center py-8 text-gray-500">Henüz çalışma saati tanımlanmamış.</div>

          <div v-else class="space-y-4">
            <div v-for="g in grouped" :key="g.user_name">
              <div class="mb-2 font-semibold">{{ g.user_name }}</div>
              <div class="grid grid-cols-2 gap-2">
                <div v-for="d in g.days" :key="d.id" class="p-2 rounded border bg-white/50 dark:bg-slate-900/50">
                  <div class="text-sm text-gray-600 dark:text-gray-300">Gün: {{ d.day_of_week }}</div>
                  <div class="text-sm font-medium">
                    <template v-if="d.is_off">İzinli</template>
                    <template v-else-if="d.start_time && d.end_time">{{ d.start_time + ' - ' + d.end_time }}</template>
                    <template v-else>—</template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
