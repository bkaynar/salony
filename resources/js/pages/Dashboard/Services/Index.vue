<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'
import { Plus, Edit, Trash2, Scissors, Clock, DollarSign, TrendingUp, Activity } from 'lucide-vue-next'

const props = defineProps({
  services: { type: Array, default: () => [] },
})

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Hizmetler' },
]

// Dialog / selection state
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const selectedService = ref(null)

// Forms
const createForm = useForm({
  name: '',
  description: '',
  price: 0,
  duration_minutes: 30,
  is_active: true,
})

const editForm = useForm({
  name: '',
  description: '',
  price: 0,
  duration_minutes: 30,
  is_active: true,
})

// Search & sort
const search = ref('')
const sortBy = ref('name')

const filteredServices = computed(() => {
  let list = Array.isArray(props.services) ? props.services.slice() : []
  const q = (search.value || '').toString().trim().toLowerCase()
  if (q) {
    list = list.filter(s => {
      const name = (s.name || '').toString().toLowerCase()
      const desc = (s.description || '').toString().toLowerCase()
      const price = (s.price || '').toString().toLowerCase()
      return name.includes(q) || desc.includes(q) || price.includes(q)
    })
  }

  if (sortBy.value === 'price') {
    list.sort((a, b) => Number(a.price || 0) - Number(b.price || 0))
  } else if (sortBy.value === 'duration') {
    list.sort((a, b) => Number(a.duration_minutes || 0) - Number(b.duration_minutes || 0))
  } else {
    list.sort((a, b) => (a.name || '').toString().localeCompare((b.name || '').toString()))
  }

  return list
})

const stats = computed(() => {
  const total = Array.isArray(props.services) ? props.services.length : 0
  const active = Array.isArray(props.services) ? props.services.filter(s => s.is_active).length : 0
  const totalRevenue = Array.isArray(props.services) ? props.services.reduce((sum, s) => sum + Number(s.price || 0), 0) : 0
  const avgDuration = total > 0 ? props.services.reduce((sum, s) => sum + Number(s.duration_minutes || 0), 0) / total : 0

  return {
    total,
    active,
    totalRevenue,
    avgDuration: Math.round(avgDuration),
  }
})

// Dialog helpers
function openCreateDialog() {
  createForm.reset()
  isCreateDialogOpen.value = true
}

function submitCreate() {
  createForm.post('/dashboard/services', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

function openEditDialog(service) {
  selectedService.value = service
  editForm.reset()
  editForm.fill({
    name: service.name || '',
    description: service.description || '',
    price: service.price || 0,
    duration_minutes: service.duration_minutes || 0,
    is_active: !!service.is_active,
  })
  isEditDialogOpen.value = true
}

function submitEdit() {
  if (!selectedService.value) return
  editForm.put(`/dashboard/services/${selectedService.value.id}`, {
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
    },
  })
}

function openDeleteDialog(service) {
  selectedService.value = service
  isDeleteDialogOpen.value = true
}

function submitDelete() {
  if (!selectedService.value) return
  router.delete(`/dashboard/services/${selectedService.value.id}`, {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
    },
  })
}

function toggleActive(service) {
  router.put(`/dashboard/services/${service.id}`, { is_active: !service.is_active })
}
</script>

<template>
  <Head title="Hizmetler" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="space-y-6 p-6 md:p-8">
      <!-- Header (subtle, dark/light friendly) -->
      <div class="rounded-lg border bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="relative p-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
              <div class="relative">
                <div class="absolute inset-0 bg-slate-50 dark:bg-slate-800 rounded-2xl opacity-20"></div>
                <div class="relative p-4 rounded-2xl bg-slate-100 dark:bg-slate-800 shadow-sm">
                  <Scissors class="w-8 h-8 text-slate-700 dark:text-slate-100" />
                </div>
              </div>
              <div>
                <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 dark:text-white">
                  Hizmetler
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 font-medium">
                  Salonunuzda sunduğunuz hizmetleri yönetin
                </p>
              </div>
            </div>
            <Button
              @click="openCreateDialog()"
              size="lg"
              class="bg-slate-800 text-white dark:bg-slate-50 dark:text-slate-900 hover:opacity-95 shadow-sm transition-all duration-150 font-semibold"
            >
              <Plus class="w-5 h-5 mr-2" />
              Yeni Hizmet Ekle
            </Button>
          </div>

          <!-- Stats Overview -->
          <div v-if="services.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-8">
            <div class="group relative overflow-hidden rounded-xl border border-white/20 bg-white/50 backdrop-blur-sm dark:bg-slate-900/50 p-5 transition-shadow duration-150">
                <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Hizmet</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.total }}</p>
                </div>
                  <div class="p-3 rounded-xl bg-blue-500 shadow-lg">
                    <Scissors class="w-6 h-6 text-white" />
                  </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
            </div>
              <div class="group relative overflow-hidden rounded-xl border border-white/20 bg-white/50 backdrop-blur-sm dark:bg-slate-900/50 p-5 transition-shadow duration-150">
                <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Aktif Hizmet</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.active }}</p>
                </div>
                  <div class="p-3 rounded-xl bg-green-500 shadow-lg">
                    <Activity class="w-6 h-6 text-white" />
                  </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-600"></div>
            </div>
              <div class="group relative overflow-hidden rounded-xl border border-white/20 bg-white/50 backdrop-blur-sm dark:bg-slate-900/50 p-5 transition-shadow duration-150">
                <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Gelir</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">₺{{ stats.totalRevenue.toFixed(0) }}</p>
                </div>
                  <div class="p-3 rounded-xl bg-amber-500 shadow-lg">
                    <TrendingUp class="w-6 h-6 text-white" />
                  </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-600"></div>
            </div>
              <div class="group relative overflow-hidden rounded-xl border border-white/20 bg-white/50 backdrop-blur-sm dark:bg-slate-900/50 p-5 transition-shadow duration-150">
                <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ort. Süre</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.avgDuration }}<span class="text-lg">dk</span></p>
                </div>
                  <div class="p-3 rounded-xl bg-purple-500 shadow-lg">
                    <Clock class="w-6 h-6 text-white" />
                  </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-600"></div>
            </div>

          </div>

          <!-- Modern Search & Filter -->
          <div class="mt-6 flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl blur opacity-10 transition-opacity duration-300"></div>
                <Input
                  v-model="search"
                  placeholder="🔍 Hizmet ara (ad, açıklama, fiyat)"
                  class="relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm border-white/40 dark:border-slate-700/40 focus:border-purple-500 transition-all duration-300"
                />
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm border border-white/40 dark:border-slate-700/40">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sırala:</label>
                <select
                  v-model="sortBy"
                  class="bg-transparent border-0 text-sm font-medium text-gray-900 dark:text-white focus:ring-0 cursor-pointer"
                >
                  <option value="name">Ada göre</option>
                  <option value="price">Fiyata göre</option>
                  <option value="duration">Süreye göre</option>
                </select>
                </div>
                <Button
                  variant="outline"
                  @click="search = ''"
                  class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm border-white/40 dark:border-slate-700/40 hover:bg-white dark:hover:bg-slate-800 transition-all duration-300"
                >
                  Temizle
                </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Services Grid -->
      <div v-if="services.length === 0">
        <div class="relative overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800">
          <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgwLDAsMCwwLjAzKSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-50"></div>

          <div class="relative flex flex-col items-center justify-center py-20">
            <div class="relative mb-6">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl blur-2xl opacity-20"></div>
              <div class="relative p-8 rounded-3xl bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-950 dark:to-purple-950">
                <Scissors class="w-20 h-20 text-blue-600 dark:text-blue-400" />
              </div>
            </div>
            <h3 class="text-3xl font-black bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
              Henüz Hizmet Eklenmemiş
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-8 text-center max-w-md">
              Salonunuzda sunduğunuz hizmetleri ekleyerek randevu sistemini kullanmaya başlayın
            </p>
            <Button
              @click="openCreateDialog()"
              class="bg-blue-600 text-white hover:opacity-95 shadow transition-colors duration-150"
              size="lg"
            >
              <Plus class="w-5 h-5 mr-2" />
              İlk Hizmeti Ekle
            </Button>
          </div>
        </div>
      </div>

      <!-- selection removed: bulk actions hidden per user request -->

      <div v-else-if="filteredServices.length === 0" class="grid grid-cols-1">
        <div class="relative overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 p-12">
          <div class="flex flex-col items-center justify-center">
            <div class="p-6 rounded-2xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-900 mb-4">
              <Scissors class="w-12 h-12 text-gray-500 dark:text-gray-400" />
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">Aramanıza uygun hizmet bulunamadı.</p>
          </div>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="service in filteredServices"
          :key="service.id"
          class="group relative perspective-1000"
        >
          <!-- card (no hover growth) -->
          <Card
            class="relative overflow-hidden border-2 shadow"
            :class="service.is_active
              ? 'border-blue-200/50 dark:border-blue-900/50 bg-gradient-to-br from-white via-blue-50/20 to-purple-50/20 dark:from-slate-900 dark:via-blue-950/20 dark:to-purple-950/20 backdrop-blur-sm'
              : 'border-gray-200 dark:border-gray-800 opacity-60 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm'"
          >
            <!-- Animated gradient overlay -->
              <!-- simplified header -->
              <CardHeader class="relative pb-4 pt-4 space-y-2 px-6">
                <div class="flex items-start justify-between">
                  <div class="flex items-center gap-3">
                    <div class="p-2 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-white">
                      <Scissors class="w-6 h-6" />
                    </div>
                    <div>
                      <CardTitle class="text-lg font-semibold text-slate-900 dark:text-white leading-tight">
                        {{ service.name }}
                      </CardTitle>
                      <Badge
                        :class="service.is_active
                          ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                          : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                        class="mt-2 text-sm font-medium px-2 py-0.5 rounded"
                      >
                        {{ service.is_active ? '✓ Aktif' : '○ Pasif' }}
                      </Badge>
                    </div>
                  </div>
                </div>

                <p v-if="service.description" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 leading-relaxed">
                  {{ service.description }}
                </p>
              </CardHeader>

            <CardContent class="relative space-y-5 pb-6 px-6">
              <!-- Price & Duration with enhanced design -->
              <div class="grid grid-cols-2 gap-4">
                <div class="relative rounded-md p-3 bg-transparent border border-gray-100 dark:border-slate-700">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Fiyat</span>
                    <DollarSign class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                  </div>
                  <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ service.price.toFixed(2) }}<span class="text-base">₺</span></div>
                </div>

                <div class="relative rounded-md p-3 bg-transparent border border-gray-100 dark:border-slate-700">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Süre</span>
                    <Clock class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                  </div>
                  <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ service.duration_minutes }}<span class="text-base">dk</span></div>
                </div>
              </div>

              <!-- Enhanced Action Buttons -->
              <div class="flex gap-2 pt-2">
                <Button @click="openEditDialog(service)" variant="outline" size="sm" class="flex-1 font-medium">
                  <Edit class="w-4 h-4 mr-1.5" />
                  Düzenle
                </Button>
                <Button @click.prevent="toggleActive(service)" variant="outline" size="sm" class="flex-1 font-medium">
                  {{ service.is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                </Button>
                <Button @click="openDeleteDialog(service)" variant="outline" size="sm" class="font-medium text-red-600">
                  <Trash2 class="w-4 h-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Create Dialog -->
    <Dialog v-model:open="isCreateDialogOpen">
      <DialogContent class="max-w-2xl overflow-hidden border-2 border-blue-200/50 dark:border-blue-900/50">
        <!-- Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5 -z-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-purple-500/10 to-transparent rounded-full blur-3xl -z-10"></div>

        <DialogHeader class="relative pb-6 border-b border-blue-200/50 dark:border-blue-900/50">
          <div class="flex items-center gap-4">
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur-lg opacity-30"></div>
              <div class="relative p-3 rounded-2xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 text-white shadow-xl">
                <Plus class="w-6 h-6" />
              </div>
            </div>
            <DialogTitle class="text-3xl font-black bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 dark:from-blue-400 dark:via-purple-400 dark:to-pink-400 bg-clip-text text-transparent">
              Yeni Hizmet Ekle
            </DialogTitle>
          </div>
        </DialogHeader>
        <form @submit.prevent="submitCreate" class="space-y-6 pt-4">
          <div class="space-y-2">
            <Label for="create-name" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Hizmet Adı *</Label>
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
              <Input
                id="create-name"
                v-model="createForm.name"
                placeholder="Örn: Saç Kesimi, Manikür..."
                required
                class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-purple-500 transition-all duration-300"
              />
            </div>
            <p v-if="createForm.errors.name" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
              <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
              {{ createForm.errors.name }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="create-description" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Açıklama</Label>
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
              <Textarea
                id="create-description"
                v-model="createForm.description"
                placeholder="Hizmet hakkında detaylar..."
                rows="3"
                class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-purple-500 transition-all duration-300"
              />
            </div>
            <p v-if="createForm.errors.description" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
              <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
              {{ createForm.errors.description }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2">
              <Label for="create-price" class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <DollarSign class="w-4 h-4 text-green-600" />
                Fiyat (₺) *
              </Label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
                <Input
                  id="create-price"
                  type="number"
                  step="0.01"
                  v-model="createForm.price"
                  placeholder="0.00"
                  required
                  class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-green-500 transition-all duration-300"
                />
              </div>
              <p v-if="createForm.errors.price" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
                <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
                {{ createForm.errors.price }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="create-duration" class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <Clock class="w-4 h-4 text-blue-600" />
                Süre (Dakika) *
              </Label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
                <Input
                  id="create-duration"
                  type="number"
                  v-model="createForm.duration_minutes"
                  placeholder="30"
                  required
                  class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-blue-500 transition-all duration-300"
                />
              </div>
              <p v-if="createForm.errors.duration_minutes" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
                <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
                {{ createForm.errors.duration_minutes }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-950/20 dark:to-purple-950/20 border border-blue-200/50 dark:border-blue-900/50">
            <Switch id="create-active" v-model:checked="createForm.is_active" class="data-[state=checked]:bg-gradient-to-r data-[state=checked]:from-blue-600 data-[state=checked]:to-purple-600" />
            <Label for="create-active" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-200">
              Hizmet Aktif
            </Label>
          </div>

          <DialogFooter class="gap-3 pt-4">
            <Button
              type="button"
              variant="outline"
              @click="isCreateDialogOpen = false"
              class="border-2 hover:bg-gray-50 dark:hover:bg-slate-800 transition-all duration-200"
            >
              İptal
            </Button>
            <Button
              type="submit"
              :disabled="createForm.processing"
              class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 hover:from-blue-700 hover:via-purple-700 hover:to-pink-700 text-white font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ createForm.processing ? 'Kaydediliyor...' : 'Kaydet' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Dialog -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="max-w-2xl overflow-hidden border-2 border-purple-200/50 dark:border-purple-900/50">
        <!-- Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-pink-500/5 to-purple-500/5 -z-10"></div>
        <div class="absolute top-0 left-0 w-64 h-64 bg-gradient-to-br from-pink-500/10 to-transparent rounded-full blur-3xl -z-10"></div>

        <DialogHeader class="relative pb-6 border-b border-purple-200/50 dark:border-purple-900/50">
          <div class="flex items-center gap-4">
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl blur-lg opacity-50 animate-pulse"></div>
              <div class="relative p-3 rounded-2xl bg-gradient-to-br from-purple-500 via-pink-500 to-purple-600 text-white shadow-xl">
                <Edit class="w-6 h-6" />
              </div>
            </div>
            <DialogTitle class="text-3xl font-black bg-gradient-to-r from-purple-600 via-pink-600 to-purple-600 dark:from-purple-400 dark:via-pink-400 dark:to-purple-400 bg-clip-text text-transparent">
              Hizmet Düzenle
            </DialogTitle>
          </div>
        </DialogHeader>
        <form @submit.prevent="submitEdit" class="space-y-6 pt-4">
          <div class="space-y-2">
            <Label for="edit-name" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Hizmet Adı *</Label>
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
              <Input
                id="edit-name"
                v-model="editForm.name"
                placeholder="Örn: Saç Kesimi, Manikür..."
                required
                class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-pink-500 transition-all duration-300"
              />
            </div>
            <p v-if="editForm.errors.name" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
              <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
              {{ editForm.errors.name }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="edit-description" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Açıklama</Label>
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
              <Textarea
                id="edit-description"
                v-model="editForm.description"
                placeholder="Hizmet hakkında detaylar..."
                rows="3"
                class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-pink-500 transition-all duration-300"
              />
            </div>
            <p v-if="editForm.errors.description" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
              <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
              {{ editForm.errors.description }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2">
              <Label for="edit-price" class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <DollarSign class="w-4 h-4 text-green-600" />
                Fiyat (₺) *
              </Label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
                <Input
                  id="edit-price"
                  type="number"
                  step="0.01"
                  v-model="editForm.price"
                  placeholder="0.00"
                  required
                  class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-green-500 transition-all duration-300"
                />
              </div>
              <p v-if="editForm.errors.price" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
                <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
                {{ editForm.errors.price }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="edit-duration" class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <Clock class="w-4 h-4 text-blue-600" />
                Süre (Dakika) *
              </Label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-lg opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
                <Input
                  id="edit-duration"
                  type="number"
                  v-model="editForm.duration_minutes"
                  placeholder="30"
                  required
                  class="relative bg-white/80 dark:bg-slate-900/80 border-gray-200 dark:border-slate-700 focus:border-blue-500 transition-all duration-300"
                />
              </div>
              <p v-if="editForm.errors.duration_minutes" class="text-sm text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
                <span class="inline-block w-1 h-1 rounded-full bg-red-600"></span>
                {{ editForm.errors.duration_minutes }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 p-4 rounded-xl bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-950/20 dark:to-pink-950/20 border border-purple-200/50 dark:border-purple-900/50">
            <Switch id="edit-active" v-model:checked="editForm.is_active" class="data-[state=checked]:bg-gradient-to-r data-[state=checked]:from-purple-600 data-[state=checked]:to-pink-600" />
            <Label for="edit-active" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-200">
              Hizmet Aktif
            </Label>
          </div>

          <DialogFooter class="gap-3 pt-4">
            <Button
              type="button"
              variant="outline"
              @click="isEditDialogOpen = false"
              class="border-2 hover:bg-gray-50 dark:hover:bg-slate-800 transition-all duration-200"
            >
              İptal
            </Button>
            <Button
              type="submit"
              :disabled="editForm.processing"
              class="bg-gradient-to-r from-purple-600 via-pink-600 to-purple-600 hover:from-purple-700 hover:via-pink-700 hover:to-purple-700 text-white font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ editForm.processing ? 'Güncelleniyor...' : 'Güncelle' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
    <Dialog v-model:open="isDeleteDialogOpen">
      <DialogContent class="max-w-md overflow-hidden border-2 border-red-200/50 dark:border-red-900/50">
        <!-- Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-rose-500/5 to-red-500/5 -z-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-red-500/10 to-transparent rounded-full blur-3xl -z-10"></div>

        <DialogHeader class="relative pb-6 border-b border-red-200/50 dark:border-red-900/50">
          <div class="flex items-center gap-4">
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-rose-600 rounded-2xl blur-lg opacity-50 animate-pulse"></div>
              <div class="relative p-3 rounded-2xl bg-gradient-to-br from-red-500 via-rose-500 to-red-600 text-white shadow-xl">
                <Trash2 class="w-6 h-6" />
              </div>
            </div>
            <DialogTitle class="text-3xl font-black bg-gradient-to-r from-red-600 via-rose-600 to-red-600 dark:from-red-400 dark:via-rose-400 dark:to-red-400 bg-clip-text text-transparent">
              Hizmeti Sil
            </DialogTitle>
          </div>
        </DialogHeader>

        <div class="py-6">
          <div class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-red-50 via-rose-50 to-red-50 dark:from-red-950/30 dark:via-rose-950/30 dark:to-red-950/30 border-2 border-red-200/50 dark:border-red-900/50">
            <!-- Decorative pattern -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTEsNjMsNjMsMC4wNSkiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-50"></div>

            <div class="relative space-y-4">
              <p class="text-gray-700 dark:text-gray-300 font-medium leading-relaxed">
                Aşağıdaki hizmeti kalıcı olarak silmek istediğinize emin misiniz?
              </p>

              <div class="relative overflow-hidden flex items-center gap-3 p-4 rounded-xl bg-white dark:bg-slate-900 border-2 border-red-300 dark:border-red-800 shadow-lg">
                <div class="p-2 rounded-lg bg-gradient-to-br from-red-500 to-rose-500 text-white">
                  <Scissors class="w-5 h-5" />
                </div>
                <strong class="text-lg font-black bg-gradient-to-r from-red-600 to-rose-600 dark:from-red-400 dark:to-rose-400 bg-clip-text text-transparent">
                  {{ selectedService?.name }}
                </strong>
              </div>

              <div class="flex items-start gap-3 p-4 rounded-xl bg-red-100 dark:bg-red-950/50 border border-red-300 dark:border-red-800">
                <div class="flex-shrink-0 text-2xl">⚠️</div>
                <div>
                  <p class="text-sm font-bold text-red-700 dark:text-red-300">
                    Dikkat!
                  </p>
                  <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                    Bu işlem geri alınamaz ve tüm veriler kalıcı olarak silinecektir.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="gap-3">
          <Button
            variant="outline"
            @click="isDeleteDialogOpen = false"
            class="border-2 hover:bg-gray-50 dark:hover:bg-slate-800 transition-all duration-200"
          >
            İptal
          </Button>
          <Button
            variant="destructive"
            @click="submitDelete()"
            class="bg-gradient-to-r from-red-600 via-rose-600 to-red-600 hover:from-red-700 hover:via-rose-700 hover:to-red-700 text-white font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300"
          >
            <Trash2 class="w-4 h-4 mr-2" />
            Evet, Sil
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
