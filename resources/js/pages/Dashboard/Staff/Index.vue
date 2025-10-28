<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Plus, Edit, Trash2, UserCog, Mail, Shield, Eye, EyeOff } from 'lucide-vue-next'

const route = (name, params) => {
  const routes = {
    'dashboard.staff.store': '/dashboard/staff',
    'dashboard.staff.update': (id) => `/dashboard/staff/${id}`,
    'dashboard.staff.destroy': (id) => `/dashboard/staff/${id}`,
  }
  if (typeof routes[name] === 'function') {
    return routes[name](params)
  }
  return routes[name]
}

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Personel Yönetimi' },
]

const props = defineProps({
  staff: { type: Array, default: () => [] },
})

const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const selectedStaff = ref(null)
const showCreatePassword = ref(false)
const showEditPassword = ref(false)

const createForm = useForm({
  name: '',
  email: '',
  password: '',
  is_bookable: true,
  role: 'staff',
})

const editForm = useForm({
  name: '',
  email: '',
  password: '',
  is_bookable: true,
  role: 'staff',
})

function openCreateDialog() {
  createForm.reset()
  createForm.is_bookable = true
  createForm.role = 'staff'
  showCreatePassword.value = false
  isCreateDialogOpen.value = true
}

function submitCreate() {
  createForm.post(route('dashboard.staff.store'), {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

function openEditDialog(staff) {
  selectedStaff.value = staff
  editForm.name = staff.name
  editForm.email = staff.email
  editForm.password = ''
  editForm.is_bookable = staff.is_bookable
  editForm.role = staff.roles[0] || 'staff'
  showEditPassword.value = false
  isEditDialogOpen.value = true
}

function submitEdit() {
  if (!selectedStaff.value) return
  editForm.put(route('dashboard.staff.update', selectedStaff.value.id), {
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
      selectedStaff.value = null
    },
  })
}

function openDeleteDialog(staff) {
  selectedStaff.value = staff
  isDeleteDialogOpen.value = true
}

function confirmDelete() {
  if (!selectedStaff.value) return
  router.delete(route('dashboard.staff.destroy', selectedStaff.value.id), {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
      selectedStaff.value = null
    },
  })
}

function getRoleBadgeColor(role) {
  return role === 'salon_admin'
    ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
    : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
}

function getRoleLabel(role) {
  return role === 'salon_admin' ? 'Admin' : 'Personel'
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
</script>

<template>
  <Head title="Personel Yönetimi" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            Personel Yönetimi
          </h1>
          <p class="text-muted-foreground mt-1">Salon personellerini yönetin</p>
        </div>
        <Button @click="openCreateDialog" class="bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800">
          <Plus class="w-4 h-4 mr-2" />
          Yeni Personel
        </Button>
      </div>

      <!-- Staff Grid -->
      <div v-if="staff && staff.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card
          v-for="member in staff"
          :key="member.id"
          class="staff-card hover:shadow-lg transition-all duration-300"
        >
          <CardHeader class="pb-3">
            <div class="flex justify-between items-start">
              <div class="flex items-center gap-3">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl text-white">
                  <UserCog class="w-6 h-6" />
                </div>
                <div>
                  <CardTitle class="text-lg">{{ member.name }}</CardTitle>
                  <Badge :class="getRoleBadgeColor(member.roles[0])" class="mt-1">
                    {{ getRoleLabel(member.roles[0]) }}
                  </Badge>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
              <Mail class="w-4 h-4" />
              <span>{{ member.email }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <Shield class="w-4 h-4" :class="member.is_bookable ? 'text-green-600' : 'text-gray-400'" />
              <span :class="member.is_bookable ? 'text-green-600 font-medium' : 'text-gray-400'">
                {{ member.is_bookable ? 'Randevu alınabilir' : 'Randevu alınamaz' }}
              </span>
            </div>
            <div class="pt-2 text-xs text-muted-foreground border-t">
              Kayıt: {{ formatDate(member.created_at) }}
            </div>

            <!-- Actions -->
            <div class="flex gap-2 pt-2">
              <Button @click="openEditDialog(member)" variant="outline" size="sm" class="flex-1">
                <Edit class="w-4 h-4 mr-2" />
                Düzenle
              </Button>
              <Button @click="openDeleteDialog(member)" variant="destructive" size="sm">
                <Trash2 class="w-4 h-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <div v-else class="text-center py-12">
        <UserCog class="w-16 h-16 mx-auto text-muted-foreground mb-4" />
        <p class="text-xl font-semibold text-muted-foreground">Henüz personel bulunmuyor</p>
        <p class="text-sm text-muted-foreground mt-2">Yeni personel eklemek için yukarıdaki butona tıklayın</p>
      </div>

      <!-- Create Staff Dialog -->
      <Dialog v-model:open="isCreateDialogOpen">
        <DialogContent class="max-w-2xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Plus class="w-6 h-6" />
                </div>
                Yeni Personel Ekle
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
                placeholder="Personel adı soyadı"
                required
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
                placeholder="personel@email.com"
                required
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="create-password" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                Şifre
              </Label>
              <div class="relative">
                <Input
                  v-model="createForm.password"
                  id="create-password"
                  :type="showCreatePassword ? 'text' : 'password'"
                  placeholder="En az 8 karakter"
                  required
                  class="modern-input pr-10"
                />
                <button
                  type="button"
                  @click="showCreatePassword = !showCreatePassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <Eye v-if="!showCreatePassword" class="w-5 h-5" />
                  <EyeOff v-else class="w-5 h-5" />
                </button>
              </div>
            </div>

            <div class="form-group-modern">
              <Label for="create-role" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-indigo-500 rounded-full"></span>
                Rol
              </Label>
              <select
                v-model="createForm.role"
                id="create-role"
                class="modern-select"
                required
              >
                <option value="staff">Personel</option>
                <option value="salon_admin">Admin</option>
              </select>
            </div>

            <div class="form-group-modern">
              <div class="flex items-center gap-3">
                <input
                  v-model="createForm.is_bookable"
                  id="create-bookable"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <Label for="create-bookable" class="text-base font-semibold cursor-pointer">
                  Randevu alınabilir
                </Label>
              </div>
              <p class="text-sm text-muted-foreground mt-1 ml-8">
                Bu personel için randevu oluşturulabilsin mi?
              </p>
            </div>
          </form>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t flex-shrink-0">
            <Button type="button" variant="outline" @click="isCreateDialogOpen = false" class="min-w-[100px]">
              İptal
            </Button>
            <Button type="submit" @click="submitCreate" :disabled="createForm.processing" class="min-w-[140px] bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800">
              <Plus class="w-4 h-4 mr-2" />
              Personel Ekle
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Edit Staff Dialog -->
      <Dialog v-model:open="isEditDialogOpen">
        <DialogContent class="max-w-2xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient-edit flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Edit class="w-6 h-6" />
                </div>
                Personel Düzenle
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
                placeholder="Personel adı soyadı"
                required
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
                placeholder="personel@email.com"
                required
                class="modern-input"
              />
            </div>

            <div class="form-group-modern">
              <Label for="edit-password" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                Yeni Şifre (Opsiyonel)
              </Label>
              <div class="relative">
                <Input
                  v-model="editForm.password"
                  id="edit-password"
                  :type="showEditPassword ? 'text' : 'password'"
                  placeholder="Değiştirmek için yeni şifre girin"
                  class="modern-input pr-10"
                />
                <button
                  type="button"
                  @click="showEditPassword = !showEditPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <Eye v-if="!showEditPassword" class="w-5 h-5" />
                  <EyeOff v-else class="w-5 h-5" />
                </button>
              </div>
            </div>

            <div class="form-group-modern">
              <Label for="edit-role" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-indigo-500 rounded-full"></span>
                Rol
              </Label>
              <select
                v-model="editForm.role"
                id="edit-role"
                class="modern-select"
                required
              >
                <option value="staff">Personel</option>
                <option value="salon_admin">Admin</option>
              </select>
            </div>

            <div class="form-group-modern">
              <div class="flex items-center gap-3">
                <input
                  v-model="editForm.is_bookable"
                  id="edit-bookable"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <Label for="edit-bookable" class="text-base font-semibold cursor-pointer">
                  Randevu alınabilir
                </Label>
              </div>
              <p class="text-sm text-muted-foreground mt-1 ml-8">
                Bu personel için randevu oluşturulabilsin mi?
              </p>
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
                Personeli Sil
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
                  Bu personeli silmek istediğinizden emin misiniz? Personele ait tüm randevu bilgileri kalıcı olarak silinecektir.
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
.staff-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.staff-card:hover {
  transform: translateY(-4px);
}

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
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%);
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
.form-group-modern:nth-child(5) { animation-delay: 0.25s; }

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
  border-color: rgb(79 70 229);
  box-shadow: 0 0 0 4px rgb(79 70 229 / 0.2);
}

:root.dark .modern-input {
  border-color: rgb(55 65 81);
}

:root.dark .modern-input:hover {
  border-color: rgb(75 85 99);
}

.modern-select {
  width: 100%;
  height: 3rem;
  padding: 0 1rem;
  border-radius: 0.75rem;
  border: 2px solid rgb(229 231 235);
  background-color: white;
  font-size: 1rem;
  font-weight: 500;
  transition: all 0.3s;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

.modern-select:hover {
  border-color: rgb(209 213 219);
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.modern-select:focus {
  outline: none;
  border-color: rgb(79 70 229);
  box-shadow: 0 0 0 4px rgb(79 70 229 / 0.2);
}

:root.dark .modern-select {
  border-color: rgb(55 65 81);
  background-color: rgb(31 41 55);
}

:root.dark .modern-select:hover {
  border-color: rgb(75 85 99);
}
</style>
