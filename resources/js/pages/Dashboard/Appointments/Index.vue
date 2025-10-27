<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { dashboard } from '@/routes'

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Randevular' },
]

const props = defineProps({
  appointments: { type: Array, default: () => [] },
})

function formatDate(value) {
  if (!value) return '—'
  const d = new Date(value)
  return d.toLocaleString()
}

function formatPrice(cents) {
  if (cents == null) return '—'
  // assuming cents (kuruş)
  return (cents / 100).toFixed(2) + ' ₺'
}
</script>

<template>
  <Head title="Randevular" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Randevular</h1>

      <div v-if="appointments && appointments.length">
      <table class="w-full table-auto border-collapse">
        <thead>
          <tr class="text-left border-b">
            <th class="py-2">Tarih / Saat</th>
            <th class="py-2">Müşteri</th>
            <th class="py-2">Personel</th>
            <th class="py-2">Fiyat</th>
            <th class="py-2">Durum</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in appointments" :key="a.id" class="border-b">
            <td class="py-2">{{ formatDate(a.start_time) }}</td>
            <td class="py-2">{{ a.customer ? a.customer.name : '—' }}</td>
            <td class="py-2">{{ a.staff ? a.staff.name : '—' }}</td>
            <td class="py-2">{{ formatPrice(a.total_price) }}</td>
            <td class="py-2">{{ a.status }}</td>
          </tr>
        </tbody>
      </table>
    </div>

      <div v-else class="text-muted">Henüz randevu bulunmuyor.</div>
    </div>
  </AppLayout>
</template>

<!-- script content merged above -->

<style scoped>
.text-muted { color: #6b7280; }
</style>
