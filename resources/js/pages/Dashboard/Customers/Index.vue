<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm, Link } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Plus, Edit, Trash2, User, Phone, Mail, Calendar, Search, Eye } from 'lucide-vue-next'

const route = (name, params) => {
  const routes = {
    'dashboard.customers.store': '/dashboard/customers',
    'dashboard.customers.update': (id) => `/dashboard/customers/${id}`,
    'dashboard.customers.destroy': (id) => `/dashboard/customers/${id}`,
    'dashboard.customers.show': (id) => `/dashboard/customers/${id}`,
  }
  if (typeof routes[name] === 'function') {
    return routes[name](params)
  }
  return routes[name]
}

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Müşteriler' },
]

const props = defineProps({
  customers: { type: Array, default: () => [] },
})

const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const selectedCustomer = ref(null)
const searchQuery = ref('')

const createForm = useForm({
  name: '',
  phone: '',
  email: '',
  notes: '',
})

const editForm = useForm({
  name: '',
  phone: '',
  email: '',
  notes: '',
})

function openCreateDialog() {
  createForm.reset()
  isCreateDialogOpen.value = true
}

function submitCreate() {
  createForm.post(route('dashboard.customers.store'), {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

function openEditDialog(customer) {
  selectedCustomer.value = customer
  editForm.name = customer.name
  editForm.phone = customer.phone || ''
  editForm.email = customer.email || ''
  editForm.notes = customer.notes || ''
  isEditDialogOpen.value = true
}

function submitEdit() {
  if (!selectedCustomer.value) return
  editForm.put(route('dashboard.customers.update', selectedCustomer.value.id), {
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
      selectedCustomer.value = null
    },
  })
}

function openDeleteDialog(customer) {
  selectedCustomer.value = customer
  isDeleteDialogOpen.value = true
}

function confirmDelete() {
  if (!selectedCustomer.value) return
  router.delete(route('dashboard.customers.destroy', selectedCustomer.value.id), {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
      selectedCustomer.value = null
    },
  })
}

function formatDate(dateString) {
  if (!dateString) return '—'
  const d = new Date(dateString)
  return d.toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const filteredCustomers = computed(() => {
  if (!searchQuery.value) return props.customers

  const query = searchQuery.value.toLowerCase()
  return props.customers.filter(customer =>
    customer.name.toLowerCase().includes(query) ||
    customer.phone?.toLowerCase().includes(query) ||
    customer.email?.toLowerCase().includes(query)
  )
})
</script>

<template>
  <Head title="Müşteriler" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            Müşteriler
          </h1>
          <p class="text-muted-foreground mt-1">Tüm müşterilerinizi buradan yönetebilirsiniz</p>
        </div>
        <Button @click="openCreateDialog" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800">
          <Plus class="w-4 h-4 mr-2" />
          Yeni Müşteri
        </Button>
      </div>

      <!-- Search and Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <Card class="md:col-span-2">
          <CardContent class="p-4">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <Input
                v-model="searchQuery"
                placeholder="Müşteri ara (isim, telefon, email)..."
                class="pl-10"
              />
            </div>
          </CardContent>
        </Card>

        <!-- Stats -->
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <User class="w-6 h-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Toplam Müşteri</p>
                <p class="text-2xl font-bold">{{ customers.length }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <Calendar class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Aktif Randevu</p>
                <p class="text-2xl font-bold">{{ customers.reduce((sum, c) => sum + c.appointments_count, 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Customers Grid -->
      <div v-if="filteredCustomers && filteredCustomers.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card
          v-for="customer in filteredCustomers"
          :key="customer.id"
          class="customer-card hover:shadow-lg transition-all duration-300"
        >
          <CardHeader class="pb-3">
            <div class="flex justify-between items-start">
              <div class="flex items-center gap-3">
                <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl text-white">
                  <User class="w-6 h-6" />
                </div>
                <div>
                  <CardTitle class="text-lg">{{ customer.name }}</CardTitle>
                  <Badge v-if="customer.appointments_count > 0" variant="secondary" class="mt-1">
                    {{ customer.appointments_count }} randevu
                  </Badge>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent class="space-y-3">
            <div v-if="customer.phone" class="flex items-center gap-2 text-sm text-muted-foreground">
              <Phone class="w-4 h-4" />
              <span>{{ customer.phone }}</span>
            </div>
            <div v-if="customer.email" class="flex items-center gap-2 text-sm text-muted-foreground">
              <Mail class="w-4 h-4" />
              <span>{{ customer.email }}</span>
            </div>
            <div v-if="customer.notes" class="pt-2 border-t">
              <p class="text-sm text-muted-foreground line-clamp-2">{{ customer.notes }}</p>
            </div>
            <div class="pt-2 text-xs text-muted-foreground">
              Kayıt: {{ formatDate(customer.created_at) }}
            </div>

            <!-- Actions -->
            <div class="flex gap-2 pt-2">
              <Link :href="route('dashboard.customers.show', customer.id)" class="flex-1">
                <Button variant="outline" size="sm" class="w-full">
                  <Eye class="w-4 h-4 mr-2" />
                  Detay
                </Button>
              </Link>
              <Button @click="openEditDialog(customer)" variant="outline" size="sm">
                <Edit class="w-4 h-4" />
              </Button>
              <Button @click="openDeleteDialog(customer)" variant="destructive" size="sm">
                <Trash2 class="w-4 h-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <div v-else class="text-center py-12">
        <User class="w-16 h-16 mx-auto text-muted-foreground mb-4" />
        <p class="text-xl font-semibold text-muted-foreground">
          {{ searchQuery ? 'Müşteri bulunamadı' : 'Henüz müşteri bulunmuyor' }}
        </p>
        <p class="text-sm text-muted-foreground mt-2">
          {{ searchQuery ? 'Farklı bir arama terimi deneyin' : 'Yeni müşteri eklemek için yukarıdaki butona tıklayın' }}
        </p>
      </div>

      <!-- Create Customer Dialog -->
      <Dialog v-model:open="isCreateDialogOpen">
        <DialogContent class="max-w-2xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Plus class="w-6 h-6" />
                </div>
                Yeni Müşteri Ekle
              </DialogTitle>
            </DialogHeader>
          </div>

          <form @submit.prevent="submitCreate" class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
            <div class="form-group-modern">
              <Label for="create-name" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                Ad Soyad
              </Label>
              <Input
                v-model="createForm.name"
                id="create-name"
                placeholder="Müşteri adı soyadı"
                required
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="create-phone" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded-full"></span>
                Telefon
              </Label>
              <Input
                v-model="createForm.phone"
                id="create-phone"
                type="tel"
                placeholder="0555 123 45 67"
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="create-email" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                E-posta
              </Label>
              <Input
                v-model="createForm.email"
                id="create-email"
                type="email"
                placeholder="ornek@email.com"
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="create-notes" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                Notlar (Opsiyonel)
              </Label>
              <textarea
                v-model="createForm.notes"
                id="create-notes"
                rows="3"
                class="modern-textarea"
                placeholder="Müşteri hakkında notlar..."
              />
            </div>
          </form>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t flex-shrink-0">
            <Button type="button" variant="outline" @click="isCreateDialogOpen = false" class="min-w-[100px]">
              İptal
            </Button>
            <Button type="submit" @click="submitCreate" :disabled="createForm.processing" class="min-w-[140px] bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800">
              <Plus class="w-4 h-4 mr-2" />
              Müşteri Ekle
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Edit Customer Dialog -->
      <Dialog v-model:open="isEditDialogOpen">
        <DialogContent class="max-w-2xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient-edit flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Edit class="w-6 h-6" />
                </div>
                Müşteri Düzenle
              </DialogTitle>
            </DialogHeader>
          </div>

          <form @submit.prevent="submitEdit" class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
            <div class="form-group-modern">
              <Label for="edit-name" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                Ad Soyad
              </Label>
              <Input
                v-model="editForm.name"
                id="edit-name"
                placeholder="Müşteri adı soyadı"
                required
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="edit-phone" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded-full"></span>
                Telefon
              </Label>
              <Input
                v-model="editForm.phone"
                id="edit-phone"
                type="tel"
                placeholder="0555 123 45 67"
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="edit-email" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                E-posta
              </Label>
              <Input
                v-model="editForm.email"
                id="edit-email"
                type="email"
                placeholder="ornek@email.com"
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="edit-notes" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                Notlar (Opsiyonel)
              </Label>
              <textarea
                v-model="editForm.notes"
                id="edit-notes"
                rows="3"
                class="modern-textarea"
                placeholder="Müşteri hakkında notlar..."
              />
            </div>
          </form>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t flex-shrink-0">
            <Button type="button" variant="outline" @click="isEditDialogOpen = false" class="min-w-[100px]">
              İptal
            </Button>
            <Button type="submit" @click="submitEdit" :disabled="editForm.processing" class="min-w-[120px] bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800">
              <Edit class="w-4 h-4 mr-2" />
              Güncelle
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Delete Confirmation Dialog -->
      <Dialog v-model:open="isDeleteDialogOpen">
        <DialogContent class="max-w-md modern-modal flex flex-col max-h-[90vh]">
          <div class="modal-header-gradient-delete flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Trash2 class="w-6 h-6" />
                </div>
                Müşteriyi Sil
              </DialogTitle>
            </DialogHeader>
          </div>

          <div class="px-6 py-6 flex-1">
            <div class="flex items-start gap-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
              <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-base font-medium text-gray-900 dark:text-gray-100 mb-1">
                  Bu işlem geri alınamaz
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Bu müşteriyi silmek istediğinizden emin misiniz? Tüm müşteri bilgileri kalıcı olarak silinecektir.
                </p>
              </div>
            </div>
          </div>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t flex-shrink-0">
            <Button variant="outline" @click="isDeleteDialogOpen = false" class="min-w-[100px]">
              İptal
            </Button>
            <Button variant="destructive" @click="confirmDelete" class="min-w-[100px] bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800">
              <Trash2 class="w-4 h-4 mr-2" />
              Sil
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

<style scoped>
.customer-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.customer-card:hover {
  transform: translateY(-4px);
}

/* Reuse modal styles from Appointments */
.modern-modal {
  animation: modalSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header-gradient {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
  position: relative;
  overflow: hidden;
}

.modal-header-gradient::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  animation: shimmer 3s infinite;
}

.modal-header-gradient-edit {
  background: linear-gradient(135deg, #9333ea 0%, #7c3aed 50%, #6d28d9 100%);
  position: relative;
  overflow: hidden;
}

.modal-header-gradient-edit::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  animation: shimmer 3s infinite;
}

.modal-header-gradient-delete {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
  position: relative;
  overflow: hidden;
}

.modal-header-gradient-delete::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0%, 100% {
    left: -100%;
  }
  50% {
    left: 100%;
  }
}

.form-group-modern {
  animation: fadeInUp 0.4s ease-out backwards;
}

.form-group-modern:nth-child(1) { animation-delay: 0.05s; }
.form-group-modern:nth-child(2) { animation-delay: 0.1s; }
.form-group-modern:nth-child(3) { animation-delay: 0.15s; }
.form-group-modern:nth-child(4) { animation-delay: 0.2s; }

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modern-input {
  height: 3rem;
  border-radius: 0.75rem;
  border: 2px solid rgb(229 231 235);
  font-size: 1rem;
  font-weight: 500;
  transition: all 0.3s;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

.modern-input:hover {
  border-color: rgb(209 213 219);
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.modern-input:focus {
  outline: none;
  border-color: rgb(59 130 246);
  box-shadow: 0 0 0 4px rgb(59 130 246 / 0.2);
}

:root.dark .modern-input {
  border-color: rgb(55 65 81);
}

:root.dark .modern-input:hover {
  border-color: rgb(75 85 99);
}

.modern-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 2px solid rgb(229 231 235);
  background-color: white;
  font-size: 1rem;
  transition: all 0.3s;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  resize: none;
}

.modern-textarea:hover {
  border-color: rgb(209 213 219);
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.modern-textarea:focus {
  outline: none;
  border-color: rgb(59 130 246);
  box-shadow: 0 0 0 4px rgb(59 130 246 / 0.2);
}

:root.dark .modern-textarea {
  border-color: rgb(55 65 81);
  background-color: rgb(31 41 55);
}

:root.dark .modern-textarea:hover {
  border-color: rgb(75 85 99);
}
</style>
