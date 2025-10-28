<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Building2, Edit, Trash2, Users, Package, Calendar, Search } from 'lucide-vue-next'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const props = defineProps({
  salons: Array,
  plans: Array,
})

const breadcrumbItems = [
  { title: 'Admin Panel', href: '/admin' },
  { title: 'Salonlar' },
]

const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const selectedSalon = ref(null)
const search = ref('')

const editForm = useForm({
  plan_id: null,
  subscription_ends_at: '',
})

const filteredSalons = computed(() => {
  if (!search.value) return props.salons
  const q = search.value.toLowerCase()
  return props.salons.filter(salon =>
    salon.name.toLowerCase().includes(q) ||
    salon.subdomain.toLowerCase().includes(q) ||
    salon.phone?.toLowerCase().includes(q)
  )
})

function openEditDialog(salon) {
  selectedSalon.value = salon
  editForm.plan_id = salon.plan_id
  editForm.subscription_ends_at = salon.subscription_ends_at || ''
  isEditDialogOpen.value = true
}

function submitEdit() {
  if (!selectedSalon.value) return
  editForm.put(`/admin/salons/${selectedSalon.value.id}`, {
    onSuccess: () => {
      isEditDialogOpen.value = false
    },
  })
}

function openDeleteDialog(salon) {
  selectedSalon.value = salon
  isDeleteDialogOpen.value = true
}

function submitDelete() {
  if (!selectedSalon.value) return
  router.delete(`/admin/salons/${selectedSalon.value.id}`, {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
    },
  })
}
</script>

<template>
  <Head title="Salonlar - Admin" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="space-y-6 p-6 md:p-8">
      <!-- Header -->
      <div class="rounded-lg border bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-800 shadow-sm p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div class="flex items-center gap-4">
            <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-800 shadow-sm">
              <Building2 class="w-8 h-8 text-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 dark:text-white">
                Salonlar
              </h1>
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 font-medium">
                Tüm salonları görüntüle ve yönet
              </p>
            </div>
          </div>
        </div>

        <!-- Search -->
        <div class="mt-6">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <Input
              v-model="search"
              placeholder="Salon ara (ad, subdomain, telefon)"
              class="pl-10"
            />
          </div>
        </div>
      </div>

      <!-- Salons List -->
      <div v-if="filteredSalons.length === 0" class="rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-slate-900 p-20 text-center">
        <div class="p-8 rounded-2xl bg-slate-100 dark:bg-slate-800 inline-block mb-4">
          <Building2 class="w-16 h-16 text-slate-600 dark:text-slate-400" />
        </div>
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          Salon Bulunamadı
        </h3>
        <p class="text-gray-600 dark:text-gray-400">
          Arama kriterlerinize uygun salon bulunamadı
        </p>
      </div>

      <div v-else class="grid grid-cols-1 gap-4">
        <Card
          v-for="salon in filteredSalons"
          :key="salon.id"
          class="border-2 border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900"
        >
          <CardContent class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <!-- Salon Info -->
              <div class="flex items-start gap-4 flex-1">
                <div class="p-3 rounded-lg bg-slate-100 dark:bg-slate-700">
                  <Building2 class="w-6 h-6 text-slate-700 dark:text-white" />
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ salon.name }}</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ salon.subdomain }}.salony.com</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ salon.phone }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ salon.created_at }}</p>
                </div>
              </div>

              <!-- Stats & Info -->
              <div class="flex items-center gap-4">
                <div class="text-center">
                  <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <Users class="w-4 h-4" />
                    <span class="text-sm font-semibold">{{ salon.users_count }}</span>
                  </div>
                  <p class="text-xs text-gray-500">Kullanıcı</p>
                </div>

                <div class="text-center">
                  <Badge :class="salon.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'">
                    {{ salon.is_active ? 'Aktif' : 'Pasif' }}
                  </Badge>
                  <p v-if="salon.subscription_ends_at" class="text-xs text-gray-500 mt-1">{{ salon.subscription_ends_at }}</p>
                </div>

                <div class="text-center">
                  <div class="flex items-center gap-2">
                    <Package class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                      {{ salon.plan_name || 'Yok' }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-500">Plan</p>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex gap-2">
                <Button
                  @click="openEditDialog(salon)"
                  variant="outline"
                  size="sm"
                >
                  <Edit class="w-4 h-4 mr-1.5" />
                  Düzenle
                </Button>
                <Button
                  @click="openDeleteDialog(salon)"
                  variant="outline"
                  size="sm"
                  class="text-red-600"
                >
                  <Trash2 class="w-4 h-4" />
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Edit Dialog -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="max-w-xl">
        <DialogHeader class="pb-4 border-b border-gray-200 dark:border-slate-800">
          <DialogTitle class="text-2xl font-bold text-slate-900 dark:text-white">
            Salon Düzenle
          </DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitEdit" class="space-y-5 pt-4">
          <div>
            <Label for="edit-plan">Plan</Label>
            <Select v-model="editForm.plan_id">
              <SelectTrigger>
                <SelectValue placeholder="Plan seçin" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="null">Plan Yok</SelectItem>
                <SelectItem v-for="plan in plans" :key="plan.id" :value="plan.id">
                  {{ plan.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="editForm.errors.plan_id" class="text-sm text-red-600 mt-1">{{ editForm.errors.plan_id }}</p>
          </div>

          <div>
            <Label for="edit-subscription">Abonelik Bitiş Tarihi</Label>
            <Input
              id="edit-subscription"
              type="date"
              v-model="editForm.subscription_ends_at"
            />
            <p v-if="editForm.errors.subscription_ends_at" class="text-sm text-red-600 mt-1">{{ editForm.errors.subscription_ends_at }}</p>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="isEditDialogOpen = false">İptal</Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Güncelleniyor...' : 'Güncelle' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
    <Dialog v-model:open="isDeleteDialogOpen">
      <DialogContent class="max-w-md">
        <DialogHeader class="pb-4 border-b border-gray-200 dark:border-slate-800">
          <DialogTitle class="text-2xl font-bold text-red-600 dark:text-red-400">
            Salonu Sil
          </DialogTitle>
        </DialogHeader>

        <div class="py-4">
          <div class="p-4 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900">
            <p class="text-gray-700 dark:text-gray-300 mb-2">
              Bu salonu kalıcı olarak silmek istediğinize emin misiniz?
            </p>
            <div class="flex items-center gap-2 mt-3 p-3 rounded-lg bg-white dark:bg-slate-900 border border-red-300 dark:border-red-800">
              <Building2 class="w-5 h-5 text-red-600 dark:text-red-400" />
              <strong class="text-red-700 dark:text-red-300">{{ selectedSalon?.name }}</strong>
            </div>
            <p class="text-sm text-red-600 dark:text-red-400 mt-3">
              ⚠️ Bu işlem geri alınamaz! Tüm salon verileri silinecektir!
            </p>
          </div>
        </div>

        <DialogFooter class="gap-2">
          <Button variant="outline" @click="isDeleteDialogOpen = false">İptal</Button>
          <Button variant="destructive" @click="submitDelete()">
            <Trash2 class="w-4 h-4 mr-2" />
            Evet, Sil
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
