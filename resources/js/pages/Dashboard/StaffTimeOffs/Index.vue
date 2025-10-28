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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Plus, Edit, Trash2, Calendar, Clock, User, CalendarDays, List } from 'lucide-vue-next'

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Personel İzinleri' },
]

const props = defineProps({
  timeOffs: { type: Array, default: () => [] },
  staff: { type: Array, default: () => [] },
})

const viewMode = ref('list') // 'list' or 'calendar'
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const selectedTimeOff = ref(null)

const createForm = useForm({
  user_id: null,
  start_time: '',
  end_time: '',
  reason: '',
})

const editForm = useForm({
  user_id: null,
  start_time: '',
  end_time: '',
  reason: '',
})

function openCreateDialog() {
  createForm.reset()
  createForm.clearErrors()
  isCreateDialogOpen.value = true
}

function openEditDialog(timeOff) {
  selectedTimeOff.value = timeOff
  editForm.user_id = timeOff.user_id
  editForm.start_time = timeOff.start_time.substring(0, 16) // Format for datetime-local
  editForm.end_time = timeOff.end_time.substring(0, 16)
  editForm.reason = timeOff.reason || ''
  editForm.clearErrors()
  isEditDialogOpen.value = true
}

function openDeleteDialog(timeOff) {
  selectedTimeOff.value = timeOff
  isDeleteDialogOpen.value = true
}

function submitCreate() {
  createForm.post('/dashboard/time-offs', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

function submitEdit() {
  editForm.put(`/dashboard/time-offs/${selectedTimeOff.value.id}`, {
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
    },
  })
}

function submitDelete() {
  router.delete(`/dashboard/time-offs/${selectedTimeOff.value.id}`, {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
    },
  })
}

// Group time offs by staff
const groupedTimeOffs = computed(() => {
  const grouped = {}
  props.timeOffs.forEach(timeOff => {
    if (!grouped[timeOff.user_name]) {
      grouped[timeOff.user_name] = []
    }
    grouped[timeOff.user_name].push(timeOff)
  })
  return grouped
})

// Calculate duration in hours
function getDuration(start, end) {
  const startDate = new Date(start)
  const endDate = new Date(end)
  const diff = endDate - startDate
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(hours / 24)
  const remainingHours = hours % 24

  if (days > 0) {
    return `${days} gün ${remainingHours} saat`
  }
  return `${hours} saat`
}
</script>

<template>
  <Head title="Personel İzinleri" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="space-y-4">
      <!-- Header -->
      <Card>
        <CardHeader class="border-b bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
          <div class="flex justify-between items-center">
            <CardTitle class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Personel İzinleri
            </CardTitle>
            <div class="flex gap-2">
              <Button
                @click="viewMode = 'list'"
                :variant="viewMode === 'list' ? 'default' : 'outline'"
                size="sm"
              >
                <List class="w-4 h-4 mr-2" />
                Liste
              </Button>
              <Button
                @click="viewMode = 'calendar'"
                :variant="viewMode === 'calendar' ? 'default' : 'outline'"
                size="sm"
              >
                <CalendarDays class="w-4 h-4 mr-2" />
                Takvim
              </Button>
              <Button @click="openCreateDialog()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800">
                <Plus class="w-4 h-4 mr-2" />
                Yeni İzin Ekle
              </Button>
            </div>
          </div>
        </CardHeader>
      </Card>

      <!-- List View -->
      <div v-if="viewMode === 'list'">
        <div v-if="Object.keys(groupedTimeOffs).length === 0" class="text-center py-12">
          <Calendar class="w-16 h-16 mx-auto text-gray-300 mb-4" />
          <p class="text-gray-500 text-lg">Henüz izin kaydı bulunmuyor</p>
          <Button @click="openCreateDialog()" class="mt-4">
            <Plus class="w-4 h-4 mr-2" />
            İlk İzni Ekle
          </Button>
        </div>

        <div v-else class="space-y-4">
          <Card v-for="(offs, staffName) in groupedTimeOffs" :key="staffName">
            <CardHeader class="bg-slate-50 dark:bg-slate-900">
              <div class="flex items-center gap-2">
                <User class="w-5 h-5 text-blue-600" />
                <CardTitle class="text-lg">{{ staffName }}</CardTitle>
                <Badge variant="outline">{{ offs.length }} izin</Badge>
              </div>
            </CardHeader>
            <CardContent class="p-4">
              <div class="space-y-3">
                <div
                  v-for="timeOff in offs"
                  :key="timeOff.id"
                  class="flex items-center justify-between p-4 rounded-lg border hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-950 transition-colors"
                >
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                      <div class="flex items-center gap-2 text-sm">
                        <Calendar class="w-4 h-4 text-blue-600" />
                        <span class="font-semibold">{{ timeOff.start_date_formatted }}</span>
                        <span class="text-gray-400">→</span>
                        <span class="font-semibold">{{ timeOff.end_date_formatted }}</span>
                      </div>
                      <Badge variant="secondary">
                        <Clock class="w-3 h-3 mr-1" />
                        {{ getDuration(timeOff.start_time, timeOff.end_time) }}
                      </Badge>
                    </div>
                    <p v-if="timeOff.reason" class="text-sm text-gray-600 dark:text-gray-400">
                      {{ timeOff.reason }}
                    </p>
                  </div>
                  <div class="flex gap-2">
                    <Button @click="openEditDialog(timeOff)" variant="outline" size="sm">
                      <Edit class="w-4 h-4" />
                    </Button>
                    <Button @click="openDeleteDialog(timeOff)" variant="outline" size="sm" class="text-red-600 hover:bg-red-50">
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Calendar View -->
      <div v-else>
        <Card>
          <CardContent class="p-4">
            <p class="text-center text-gray-500 py-8">Takvim görünümü yakında eklenecek...</p>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Create Dialog -->
    <Dialog v-model:open="isCreateDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Yeni İzin Ekle</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div>
            <Label for="create-staff">Personel</Label>
            <Select v-model="createForm.user_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Personel seçin" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="s in staff" :key="s.id" :value="s.id">
                  {{ s.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="createForm.errors.user_id" class="text-sm text-red-600 mt-1">{{ createForm.errors.user_id }}</p>
          </div>

          <div>
            <Label for="create-start">Başlangıç</Label>
            <Input
              id="create-start"
              type="datetime-local"
              v-model="createForm.start_time"
              required
            />
            <p v-if="createForm.errors.start_time" class="text-sm text-red-600 mt-1">{{ createForm.errors.start_time }}</p>
          </div>

          <div>
            <Label for="create-end">Bitiş</Label>
            <Input
              id="create-end"
              type="datetime-local"
              v-model="createForm.end_time"
              required
            />
            <p v-if="createForm.errors.end_time" class="text-sm text-red-600 mt-1">{{ createForm.errors.end_time }}</p>
          </div>

          <div>
            <Label for="create-reason">Sebep (Opsiyonel)</Label>
            <Textarea
              id="create-reason"
              v-model="createForm.reason"
              placeholder="İzin sebebi..."
              rows="3"
            />
            <p v-if="createForm.errors.reason" class="text-sm text-red-600 mt-1">{{ createForm.errors.reason }}</p>
          </div>

          <p v-if="createForm.errors.overlap" class="text-sm text-red-600">{{ createForm.errors.overlap }}</p>

          <DialogFooter>
            <Button type="button" variant="outline" @click="isCreateDialogOpen = false">
              İptal
            </Button>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Kaydediliyor...' : 'Kaydet' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Dialog -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>İzin Düzenle</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <Label for="edit-staff">Personel</Label>
            <Select v-model="editForm.user_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Personel seçin" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="s in staff" :key="s.id" :value="s.id">
                  {{ s.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="editForm.errors.user_id" class="text-sm text-red-600 mt-1">{{ editForm.errors.user_id }}</p>
          </div>

          <div>
            <Label for="edit-start">Başlangıç</Label>
            <Input
              id="edit-start"
              type="datetime-local"
              v-model="editForm.start_time"
              required
            />
            <p v-if="editForm.errors.start_time" class="text-sm text-red-600 mt-1">{{ editForm.errors.start_time }}</p>
          </div>

          <div>
            <Label for="edit-end">Bitiş</Label>
            <Input
              id="edit-end"
              type="datetime-local"
              v-model="editForm.end_time"
              required
            />
            <p v-if="editForm.errors.end_time" class="text-sm text-red-600 mt-1">{{ editForm.errors.end_time }}</p>
          </div>

          <div>
            <Label for="edit-reason">Sebep (Opsiyonel)</Label>
            <Textarea
              id="edit-reason"
              v-model="editForm.reason"
              placeholder="İzin sebebi..."
              rows="3"
            />
            <p v-if="editForm.errors.reason" class="text-sm text-red-600 mt-1">{{ editForm.errors.reason }}</p>
          </div>

          <p v-if="editForm.errors.overlap" class="text-sm text-red-600">{{ editForm.errors.overlap }}</p>

          <DialogFooter>
            <Button type="button" variant="outline" @click="isEditDialogOpen = false">
              İptal
            </Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Güncelleniyor...' : 'Güncelle' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
    <Dialog v-model:open="isDeleteDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>İzni Sil</DialogTitle>
        </DialogHeader>
        <p class="text-gray-600 dark:text-gray-400">
          Bu izin kaydını silmek istediğinize emin misiniz?
        </p>
        <DialogFooter>
          <Button variant="outline" @click="isDeleteDialogOpen = false">
            İptal
          </Button>
          <Button variant="destructive" @click="submitDelete()">
            Sil
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
