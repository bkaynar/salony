<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Scissors, Clock, MapPin, Phone, Calendar, CheckCircle2 } from 'lucide-vue-next'

const props = defineProps({
  salon: Object,
  services: Array,
  staff: Array,
})

const isBookingDialogOpen = ref(false)

function formatPrice(price) {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
  }).format(price / 100)
}

function openBookingDialog() {
  if (!props.salon.allow_online_booking) {
    alert('Online randevu özelliği bu salon için aktif değil. Lütfen telefonla iletişime geçin.')
    return
  }
  isBookingDialogOpen.value = true
}
</script>

<template>
  <Head :title="salon.name" />

  <!-- Modern Public Landing Page -->
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
    <!-- Header/Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 to-slate-800 dark:from-slate-950 dark:to-slate-900 text-white">
      <div class="absolute inset-0 bg-grid-white/5"></div>
      <div class="relative max-w-7xl mx-auto px-6 py-20 md:py-32">
        <div class="text-center">
          <div class="inline-block p-4 rounded-2xl bg-white/10 backdrop-blur-sm mb-6">
            <Scissors class="w-12 h-12" />
          </div>
          <h1 class="text-4xl md:text-6xl font-bold mb-4">
            {{ salon.name }}
          </h1>
          <p class="text-xl text-slate-300 max-w-2xl mx-auto mb-8">
            Profesyonel güzellik ve bakım hizmetleri
          </p>

          <!-- Contact Info -->
          <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-slate-300 mb-8">
            <div class="flex items-center gap-2">
              <MapPin class="w-4 h-4" />
              <span>{{ salon.address }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Phone class="w-4 h-4" />
              <a :href="`tel:${salon.phone}`" class="hover:text-white transition">{{ salon.phone }}</a>
            </div>
          </div>

          <!-- CTA Button -->
          <Button
            v-if="salon.allow_online_booking"
            @click="openBookingDialog"
            size="lg"
            class="bg-white text-slate-900 hover:bg-slate-100 text-lg px-8 py-6"
          >
            <Calendar class="w-5 h-5 mr-2" />
            Online Randevu Al
          </Button>
          <Button
            v-else
            size="lg"
            variant="outline"
            class="border-white text-white hover:bg-white/10"
            as-child
          >
            <a :href="`tel:${salon.phone}`">
              <Phone class="w-5 h-5 mr-2" />
              Hemen Ara
            </a>
          </Button>
        </div>
      </div>
    </div>

    <!-- Services Section -->
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-24">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">
          Hizmetlerimiz & Fiyatlar
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          Size en uygun hizmeti seçin
        </p>
      </div>

      <div v-if="services.length === 0" class="text-center py-12">
        <p class="text-slate-500 dark:text-slate-400">Henüz hizmet eklenmemiş</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
          v-for="service in services"
          :key="service.id"
          class="border-2 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all hover:shadow-lg"
        >
          <CardHeader class="pb-4">
            <CardTitle class="text-xl font-bold text-slate-900 dark:text-white">
              {{ service.name }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                  <Clock class="w-4 h-4" />
                  <span class="text-sm">{{ service.duration_minutes }} dakika</span>
                </div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                  {{ formatPrice(service.price) }}
                </div>
              </div>
              <p v-if="service.description" class="text-sm text-slate-600 dark:text-slate-400">
                {{ service.description }}
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Staff Section -->
    <div v-if="staff.length > 0" class="bg-slate-50 dark:bg-slate-900/50 py-16 md:py-24">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">
            Uzman Ekibimiz
          </h2>
          <p class="text-slate-600 dark:text-slate-400">
            Profesyonel personelimiz sizin için burada
          </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
          <div
            v-for="member in staff"
            :key="member.id"
            class="text-center"
          >
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 mb-3">
              <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center text-2xl font-bold text-slate-700 dark:text-slate-200">
                {{ member.name.charAt(0) }}
              </div>
            </div>
            <p class="font-semibold text-slate-900 dark:text-white">{{ member.name }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer CTA -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 dark:from-slate-950 dark:to-slate-900 text-white py-16">
      <div class="max-w-4xl mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          Randevunuzu Hemen Alın
        </h2>
        <p class="text-slate-300 mb-8">
          Size en uygun tarih ve saatte hizmet vermekten mutluluk duyarız
        </p>
        <Button
          v-if="salon.allow_online_booking"
          @click="openBookingDialog"
          size="lg"
          class="bg-white text-slate-900 hover:bg-slate-100 text-lg px-8 py-6"
        >
          <Calendar class="w-5 h-5 mr-2" />
          Online Randevu Al
        </Button>
        <Button
          v-else
          size="lg"
          class="bg-white text-slate-900 hover:bg-slate-100 text-lg px-8"
          as-child
        >
          <a :href="`tel:${salon.phone}`">
            <Phone class="w-5 h-5 mr-2" />
            {{ salon.phone }}
          </a>
        </Button>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-slate-900 dark:bg-slate-950 text-slate-400 py-8">
      <div class="max-w-7xl mx-auto px-6 text-center text-sm">
        <p>&copy; 2025 {{ salon.name }}. Tüm hakları saklıdır.</p>
        <p class="mt-2">Powered by <span class="text-white font-semibold">Salony</span></p>
      </div>
    </div>
  </div>

  <!-- Booking Dialog - To be implemented -->
  <Dialog v-model:open="isBookingDialogOpen">
    <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="text-2xl font-bold">Online Randevu</DialogTitle>
      </DialogHeader>
      <div class="py-8 text-center text-slate-600 dark:text-slate-400">
        <Calendar class="w-16 h-16 mx-auto mb-4 text-slate-400" />
        <p class="text-lg mb-2">Randevu formu yakında eklenecek!</p>
        <p class="text-sm">Şimdilik lütfen telefon ile iletişime geçin:</p>
        <a :href="`tel:${salon.phone}`" class="text-2xl font-bold text-slate-900 dark:text-white block mt-4">
          {{ salon.phone }}
        </a>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
.bg-grid-white\/5 {
  background-image: linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
  background-size: 50px 50px;
}
</style>
