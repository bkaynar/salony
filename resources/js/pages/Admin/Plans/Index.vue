<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Switch } from '@/components/ui/switch'
import { Package, Plus, Edit, Trash2, Check, X, Users } from 'lucide-vue-next'

const props = defineProps({
  plans: Array,
})

const breadcrumbItems = [
  { title: 'Admin Panel', href: '/admin' },
  { title: 'Paketler' },
]

const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const selectedPlan = ref(null)

const createForm = useForm({
  name: '',
  price_monthly: 0,
  price_yearly: 0,
  max_staff_count: 5,
  allow_online_booking: true,
  allow_sms_notifications: false,
})

const editForm = useForm({
  name: '',
  price_monthly: 0,
  price_yearly: 0,
  max_staff_count: 5,
  allow_online_booking: true,
  allow_sms_notifications: false,
})

function openCreateDialog() {
  createForm.reset()
  isCreateDialogOpen.value = true
}

function submitCreate() {
  createForm.post('/admin/plans', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

function openEditDialog(plan) {
  selectedPlan.value = plan
  editForm.fill(plan)
  isEditDialogOpen.value = true
}

function submitEdit() {
  if (!selectedPlan.value) return
  editForm.put(`/admin/plans/${selectedPlan.value.id}`, {
    onSuccess: () => {
      isEditDialogOpen.value = false
    },
  })
}

function openDeleteDialog(plan) {
  selectedPlan.value = plan
  isDeleteDialogOpen.value = true
}

function submitDelete() {
  if (!selectedPlan.value) return
  router.delete(`/admin/plans/${selectedPlan.value.id}`, {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
    },
  })
}
</script>

<template>
  <Head title="Paketler - Admin" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="space-y-6 p-6 md:p-8">
      <!-- Header -->
      <div class="rounded-lg border bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-800 shadow-sm p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div class="flex items-center gap-4">
            <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-800 shadow-sm">
              <Package class="w-8 h-8 text-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 dark:text-white">
                Abonelik Paketleri
              </h1>
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 font-medium">
                Salonlar için fiyatlandırma planları
              </p>
            </div>
          </div>
          <Button
            @click="openCreateDialog()"
            size="lg"
            class="bg-slate-800 text-white dark:bg-slate-50 dark:text-slate-900 hover:opacity-95"
          >
            <Plus class="w-5 h-5 mr-2" />
            Yeni Plan Ekle
          </Button>
        </div>
      </div>

      <!-- Plans Grid -->
      <div v-if="plans.length === 0" class="rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-slate-900 p-20 text-center">
        <div class="p-8 rounded-2xl bg-slate-100 dark:bg-slate-800 inline-block mb-4">
          <Package class="w-16 h-16 text-slate-600 dark:text-slate-400" />
        </div>
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          Henüz Plan Eklenmemiş
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Salonlar için abonelik planları oluşturun
        </p>
        <Button @click="openCreateDialog()" class="bg-slate-800 text-white dark:bg-slate-50 dark:text-slate-900">
          <Plus class="w-5 h-5 mr-2" />
          İlk Planı Ekle
        </Button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
          v-for="plan in plans"
          :key="plan.id"
          class="relative overflow-hidden border-2 shadow border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900"
        >
          <CardHeader class="pb-4 pt-6 px-6">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-md bg-slate-100 dark:bg-slate-700">
                  <Package class="w-6 h-6 text-slate-700 dark:text-white" />
                </div>
                <div>
                  <CardTitle class="text-xl font-bold text-slate-900 dark:text-white">
                    {{ plan.name }}
                  </CardTitle>
                  <Badge class="mt-2 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                    <Users class="w-3 h-3 mr-1" />
                    {{ plan.salons_count }} Salon
                  </Badge>
                </div>
              </div>
            </div>
          </CardHeader>

          <CardContent class="space-y-4 pb-6 px-6">
            <!-- Pricing -->
            <div class="grid grid-cols-2 gap-4">
              <div class="p-3 rounded-lg border border-gray-200 dark:border-slate-700">
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Aylık</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">
                  ₺{{ plan.price_monthly }}
                </p>
              </div>
              <div class="p-3 rounded-lg border border-gray-200 dark:border-slate-700">
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Yıllık</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">
                  ₺{{ plan.price_yearly }}
                </p>
              </div>
            </div>

            <!-- Features -->
            <div class="space-y-2 pt-2">
              <div class="flex items-center gap-2 text-sm">
                <Users class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                <span class="text-gray-700 dark:text-gray-300">Maks. {{ plan.max_staff_count }} Personel</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <Check v-if="plan.allow_online_booking" class="w-4 h-4 text-green-600" />
                <X v-else class="w-4 h-4 text-red-600" />
                <span class="text-gray-700 dark:text-gray-300">Online Randevu</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <Check v-if="plan.allow_sms_notifications" class="w-4 h-4 text-green-600" />
                <X v-else class="w-4 h-4 text-red-600" />
                <span class="text-gray-700 dark:text-gray-300">SMS Bildirimleri</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 pt-2">
              <Button
                @click="openEditDialog(plan)"
                variant="outline"
                size="sm"
                class="flex-1"
              >
                <Edit class="w-4 h-4 mr-1.5" />
                Düzenle
              </Button>
              <Button
                @click="openDeleteDialog(plan)"
                variant="outline"
                size="sm"
                class="text-red-600"
              >
                <Trash2 class="w-4 h-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Create Dialog -->
    <Dialog v-model:open="isCreateDialogOpen">
      <DialogContent class="max-w-2xl">
        <DialogHeader class="pb-4 border-b border-gray-200 dark:border-slate-800">
          <DialogTitle class="text-2xl font-bold text-slate-900 dark:text-white">
            Yeni Plan Ekle
          </DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitCreate" class="space-y-5 pt-4">
          <div>
            <Label for="create-name">Plan Adı *</Label>
            <Input id="create-name" v-model="createForm.name" placeholder="Örn: Basic, Pro, Premium" required />
            <p v-if="createForm.errors.name" class="text-sm text-red-600 mt-1">{{ createForm.errors.name }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-price-monthly">Aylık Fiyat (₺) *</Label>
              <Input id="create-price-monthly" type="number" step="0.01" v-model="createForm.price_monthly" required />
              <p v-if="createForm.errors.price_monthly" class="text-sm text-red-600 mt-1">{{ createForm.errors.price_monthly }}</p>
            </div>
            <div>
              <Label for="create-price-yearly">Yıllık Fiyat (₺) *</Label>
              <Input id="create-price-yearly" type="number" step="0.01" v-model="createForm.price_yearly" required />
              <p v-if="createForm.errors.price_yearly" class="text-sm text-red-600 mt-1">{{ createForm.errors.price_yearly }}</p>
            </div>
          </div>

          <div>
            <Label for="create-max-staff">Maksimum Personel Sayısı *</Label>
            <Input id="create-max-staff" type="number" v-model="createForm.max_staff_count" required />
            <p v-if="createForm.errors.max_staff_count" class="text-sm text-red-600 mt-1">{{ createForm.errors.max_staff_count }}</p>
          </div>

          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <Switch id="create-booking" v-model:checked="createForm.allow_online_booking" />
              <Label for="create-booking" class="cursor-pointer">Online Randevu İzni</Label>
            </div>
            <div class="flex items-center gap-3">
              <Switch id="create-sms" v-model:checked="createForm.allow_sms_notifications" />
              <Label for="create-sms" class="cursor-pointer">SMS Bildirimleri</Label>
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="isCreateDialogOpen = false">İptal</Button>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Kaydediliyor...' : 'Kaydet' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Dialog -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="max-w-2xl">
        <DialogHeader class="pb-4 border-b border-gray-200 dark:border-slate-800">
          <DialogTitle class="text-2xl font-bold text-slate-900 dark:text-white">
            Plan Düzenle
          </DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitEdit" class="space-y-5 pt-4">
          <div>
            <Label for="edit-name">Plan Adı *</Label>
            <Input id="edit-name" v-model="editForm.name" required />
            <p v-if="editForm.errors.name" class="text-sm text-red-600 mt-1">{{ editForm.errors.name }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-price-monthly">Aylık Fiyat (₺) *</Label>
              <Input id="edit-price-monthly" type="number" step="0.01" v-model="editForm.price_monthly" required />
              <p v-if="editForm.errors.price_monthly" class="text-sm text-red-600 mt-1">{{ editForm.errors.price_monthly }}</p>
            </div>
            <div>
              <Label for="edit-price-yearly">Yıllık Fiyat (₺) *</Label>
              <Input id="edit-price-yearly" type="number" step="0.01" v-model="editForm.price_yearly" required />
              <p v-if="editForm.errors.price_yearly" class="text-sm text-red-600 mt-1">{{ editForm.errors.price_yearly }}</p>
            </div>
          </div>

          <div>
            <Label for="edit-max-staff">Maksimum Personel Sayısı *</Label>
            <Input id="edit-max-staff" type="number" v-model="editForm.max_staff_count" required />
            <p v-if="editForm.errors.max_staff_count" class="text-sm text-red-600 mt-1">{{ editForm.errors.max_staff_count }}</p>
          </div>

          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <Switch id="edit-booking" v-model:checked="editForm.allow_online_booking" />
              <Label for="edit-booking" class="cursor-pointer">Online Randevu İzni</Label>
            </div>
            <div class="flex items-center gap-3">
              <Switch id="edit-sms" v-model:checked="editForm.allow_sms_notifications" />
              <Label for="edit-sms" class="cursor-pointer">SMS Bildirimleri</Label>
            </div>
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
            Planı Sil
          </DialogTitle>
        </DialogHeader>

        <div class="py-4">
          <div class="p-4 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900">
            <p class="text-gray-700 dark:text-gray-300 mb-2">
              Bu planı silmek istediğinize emin misiniz?
            </p>
            <div class="flex items-center gap-2 mt-3 p-3 rounded-lg bg-white dark:bg-slate-900 border border-red-300 dark:border-red-800">
              <Package class="w-5 h-5 text-red-600 dark:text-red-400" />
              <strong class="text-red-700 dark:text-red-300">{{ selectedPlan?.name }}</strong>
            </div>
            <p class="text-sm text-red-600 dark:text-red-400 mt-3">
              ⚠️ Eğer bu plana bağlı salonlar varsa, önce onları değiştirmeniz gerekir!
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
