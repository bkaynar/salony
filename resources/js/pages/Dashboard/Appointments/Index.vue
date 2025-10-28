<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Plus, Edit, Trash2, Calendar, Clock, DollarSign, CalendarDays, ChevronLeft, ChevronRight, CheckCircle2, CreditCard } from 'lucide-vue-next'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import trLocale from '@fullcalendar/core/locales/tr'

const route = (name, params) => {
  const routes = {
    'dashboard.appointments.store': '/dashboard/appointments',
    'dashboard.appointments.update': (id) => `/dashboard/appointments/${id}`,
    'dashboard.appointments.destroy': (id) => `/dashboard/appointments/${id}`,
    'dashboard.appointments.complete-payment': (id) => `/dashboard/appointments/${id}/complete-payment`,
  }
  if (typeof routes[name] === 'function') {
    return routes[name](params)
  }
  return routes[name]
}

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Randevular' },
]

const props = defineProps({
  staffList: { type: Array, default: () => [] },
  customers: { type: Array, default: () => [] },
  services: { type: Array, default: () => [] },
})

const viewMode = ref('calendar') // Only calendar view is enabled
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const isPaymentDialogOpen = ref(false)
const isDetailDialogOpen = ref(false)
const selectedStaffId = ref(null)
const selectedAppointment = ref(null)
// Search terms for staff and customers in create/edit dialogs
const staffSearch = ref('')
const customerSearch = ref('')
const staffDropdownOpen = ref(false)
const customerDropdownOpen = ref(false)
const serverStaffResults = ref([])
const serverCustomerResults = ref([])
const staffFetchTimer = ref(null)
const customerFetchTimer = ref(null)
const staffContainerRef = ref(null)
const customerContainerRef = ref(null)
const isCreateCustomerDialogOpen = ref(false)
const newCustomer = ref({ name: '', phone: '', email: '', notes: '' })
const staffHighlightedIndex = ref(-1)
const customerHighlightedIndex = ref(-1)

function selectStaff(staff) {
  if (isCreateDialogOpen.value) {
    createForm.staff_id = staff.id
  }
  if (isEditDialogOpen.value) {
    editForm.staff_id = staff.id
  }
  staffSearch.value = staff.name || ''
  staffDropdownOpen.value = false
}

function selectCustomer(customer) {
  if (isCreateDialogOpen.value) {
    createForm.customer_id = customer.id
  }
  if (isEditDialogOpen.value) {
    editForm.customer_id = customer.id
  }
  customerSearch.value = customer.name || ''
  customerDropdownOpen.value = false
  customerHighlightedIndex.value = -1
}

async function submitNewCustomer() {
  // Basic client-side validation
  if (!newCustomer.value.name || newCustomer.value.name.trim() === '') return

  const payload = {
    name: newCustomer.value.name,
    phone: newCustomer.value.phone,
    email: newCustomer.value.email,
    notes: newCustomer.value.notes,
  }

  const tokenMeta = document.querySelector('meta[name="csrf-token"]')
  const csrf = tokenMeta ? tokenMeta.getAttribute('content') : ''

  try {
    const res = await fetch('/dashboard/customers', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    if (res.ok) {
      // Close modal
      isCreateCustomerDialogOpen.value = false
      // Clear input
      const nameQuery = payload.name
      newCustomer.value = { name: '', phone: '', email: '', notes: '' }
      // Refresh server results and select the newly created customer if found
      await fetchCustomersFromServer(nameQuery)
      // pick first matching result
      if (serverCustomerResults.value && serverCustomerResults.value.length) {
        const first = serverCustomerResults.value[0]
        createForm.customer_id = first.id
        customerSearch.value = first.name
      }
    } else {
      // Try to parse JSON errors
      // ignore for now — could show feedback
    }
  } catch (e) {
    // ignore network errors for now
  }
}

function resetStaffHighlight() {
  staffHighlightedIndex.value = -1
}

function resetCustomerHighlight() {
  customerHighlightedIndex.value = -1
}

// Debounced fetchers (server-side typeahead)
async function fetchStaffFromServer(q) {
  if (!q || q.length < 3) {
    serverStaffResults.value = []
    return
  }
  try {
    const res = await fetch(`/dashboard/appointments/search/staff?q=${encodeURIComponent(q)}`, {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (res.ok) {
      serverStaffResults.value = await res.json()
    }
  } catch (e) {
    // ignore network errors silently
    serverStaffResults.value = []
  }
}

async function fetchCustomersFromServer(q) {
  if (!q || q.length < 3) {
    serverCustomerResults.value = []
    return
  }
  try {
    const res = await fetch(`/dashboard/appointments/search/customers?q=${encodeURIComponent(q)}`, {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (res.ok) {
      serverCustomerResults.value = await res.json()
    }
  } catch (e) {
    serverCustomerResults.value = []
  }
}

function onStaffInput() {
  staffDropdownOpen.value = true
  if (staffFetchTimer.value) clearTimeout(staffFetchTimer.value)
  const q = staffSearch.value.trim()
  if (q.length < 3) {
    serverStaffResults.value = []
    resetStaffHighlight()
    return
  }
  staffFetchTimer.value = setTimeout(() => fetchStaffFromServer(q), 300)
}

function onCustomerInput() {
  customerDropdownOpen.value = true
  if (customerFetchTimer.value) clearTimeout(customerFetchTimer.value)
  const q = customerSearch.value.trim()
  if (q.length < 3) {
    serverCustomerResults.value = []
    resetCustomerHighlight()
    return
  }
  customerFetchTimer.value = setTimeout(() => fetchCustomersFromServer(q), 300)
}

// Keyboard handlers for dropdown navigation
function onStaffKeydown(e) {
  const list = filteredStaffList.value || []
  if (!staffDropdownOpen.value) staffDropdownOpen.value = true
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    staffHighlightedIndex.value = Math.min(staffHighlightedIndex.value + 1, list.length - 1)
    return
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault()
    staffHighlightedIndex.value = Math.max(staffHighlightedIndex.value - 1, 0)
    return
  }
  if (e.key === 'Enter') {
    e.preventDefault()
    if (list[staffHighlightedIndex.value]) selectStaff(list[staffHighlightedIndex.value])
    else if (list.length === 1) selectStaff(list[0])
    return
  }
  if (e.key === 'Escape') {
    staffDropdownOpen.value = false
    resetStaffHighlight()
    return
  }
}

function onCustomerKeydown(e) {
  const list = filteredCustomers.value || []
  if (!customerDropdownOpen.value) customerDropdownOpen.value = true
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    customerHighlightedIndex.value = Math.min(customerHighlightedIndex.value + 1, list.length - 1)
    return
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault()
    customerHighlightedIndex.value = Math.max(customerHighlightedIndex.value - 1, 0)
    return
  }
  if (e.key === 'Enter') {
    e.preventDefault()
    if (list[customerHighlightedIndex.value]) selectCustomer(list[customerHighlightedIndex.value])
    else if (list.length === 1) selectCustomer(list[0])
    return
  }
  if (e.key === 'Escape') {
    customerDropdownOpen.value = false
    resetCustomerHighlight()
    return
  }
}

// Close dropdowns when clicking outside
function handleDocumentClick(e) {
  const sEl = staffContainerRef.value
  if (sEl && !sEl.contains(e.target)) {
    staffDropdownOpen.value = false
  }
  const cEl = customerContainerRef.value
  if (cEl && !cEl.contains(e.target)) {
    customerDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
})

onUnmounted(() => {
  document.removeEventListener('click', handleDocumentClick)
})

const paymentForm = useForm({
  payment_method: 'cash',
  amount_paid: 0,
  notes: '',
})

const createForm = useForm({
  staff_id: null,
  customer_id: '',
  start_time: '',
  services: [],
  notes: '',
})

const editForm = useForm({
  staff_id: null,
  customer_id: '',
  start_time: '',
  services: [],
  status: '',
  notes: '',
})

function openCreateDialog(staffId = null) {
  selectedStaffId.value = staffId
  // reset search inputs when opening create dialog
  staffSearch.value = ''
  customerSearch.value = ''
  // If staffId is provided, pre-select it, otherwise leave it null for user to select
  createForm.staff_id = staffId || (props.staffList.length === 1 ? props.staffList[0].id : null)
  createForm.customer_id = ''
  createForm.start_time = ''
  createForm.services = []
  createForm.notes = ''
  isCreateDialogOpen.value = true
}

function submitCreate() {
  createForm.post(route('dashboard.appointments.store'), {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

function openEditDialog(appointment) {
  selectedAppointment.value = appointment
  // reset search inputs when opening edit dialog
  staffSearch.value = ''
  customerSearch.value = ''
  editForm.staff_id = appointment.staff_id
  editForm.customer_id = appointment.customer_id

  // Convert to local datetime format without timezone offset
  if (appointment.start) {
    const date = new Date(appointment.start)
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    editForm.start_time = `${year}-${month}-${day}T${hours}:${minutes}`
  } else {
    editForm.start_time = ''
  }

  editForm.services = appointment.services.map(s => ({ service_id: s.service_id }))
  editForm.status = appointment.status
  editForm.notes = appointment.notes || ''
  isEditDialogOpen.value = true
}

function submitEdit() {
  if (!selectedAppointment.value) return

  // Create a copy of form data
  const formData = {
    staff_id: editForm.staff_id,
    customer_id: editForm.customer_id,
    start_time: editForm.start_time,
    status: editForm.status,
    notes: editForm.notes,
  }

  // Only include services if they were modified (array has items)
  if (editForm.services && editForm.services.length > 0) {
    formData.services = editForm.services
  }

  console.log('Submitting edit with data:', formData)
  console.log('Services count:', editForm.services?.length)

  // Use transform to send custom data
  editForm.transform(() => formData)
    .put(route('dashboard.appointments.update', selectedAppointment.value.id), {
      onSuccess: () => {
        isEditDialogOpen.value = false
        editForm.reset()
        selectedAppointment.value = null
      },
    })
}

function openDetailDialog(appointment) {
  selectedAppointment.value = appointment
  isDetailDialogOpen.value = true
}

function openDeleteDialog(appointment) {
  selectedAppointment.value = appointment
  isDeleteDialogOpen.value = true
}

function confirmDelete() {
  if (!selectedAppointment.value) return
  router.delete(route('dashboard.appointments.destroy', selectedAppointment.value.id), {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
      selectedAppointment.value = null
    },
  })
}

function openPaymentDialog(appointment) {
  selectedAppointment.value = appointment
  paymentForm.amount_paid = appointment.total_price || 0 // Already in TL from backend
  paymentForm.payment_method = 'cash'
  paymentForm.notes = appointment.notes || ''
  isPaymentDialogOpen.value = true
}

function submitPayment() {
  if (!selectedAppointment.value) return

  // Complete appointment and record payment
  paymentForm.post(route('dashboard.appointments.complete-payment', selectedAppointment.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      isPaymentDialogOpen.value = false
      isDetailDialogOpen.value = false
      selectedAppointment.value = null
      paymentForm.reset()
    },
  })
}

function formatDateTime(dateString) {
  if (!dateString) return '—'
  const d = new Date(dateString)
  return d.toLocaleString('tr-TR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatPrice(amount) {
  if (amount == null) return '—'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
  }).format(amount)
}

function getStatusColor(status) {
  const colors = {
    confirmed: 'bg-blue-500',
    completed: 'bg-green-500',
    cancelled: 'bg-red-500',
    no_show: 'bg-gray-500',
  }
  return colors[status] || 'bg-gray-500'
}

function getStatusText(status) {
  const texts = {
    confirmed: 'Onaylandı',
    completed: 'Tamamlandı',
    cancelled: 'İptal',
    no_show: 'Gelmedi',
  }
  return texts[status] || status
}

const totalSelectedPrice = computed(() => {
  const form = isCreateDialogOpen.value ? createForm : editForm
  if (!form.services.length) return 0
  return form.services.reduce((sum, s) => {
    const service = props.services.find(srv => srv.id === s.service_id)
    return sum + (service?.price || 0)
  }, 0)
})

const totalSelectedDuration = computed(() => {
  const form = isCreateDialogOpen.value ? createForm : editForm
  if (!form.services.length) return 0
  return form.services.reduce((sum, s) => {
    const service = props.services.find(srv => srv.id === s.service_id)
    return sum + (service?.duration_minutes || 0)
  }, 0)
})

// Filtered lists for selects (searchable)
const filteredStaffList = computed(() => {
  const q = staffSearch.value.trim()
  if (!q) return props.staffList
  // If query is long enough, prefer server results (typeahead)
  if (q.length >= 3) {
    return serverStaffResults.value
  }
  const lower = q.toLowerCase()
  return props.staffList.filter(s => (s.name || '').toLowerCase().includes(lower))
})

const filteredCustomers = computed(() => {
  const q = customerSearch.value.trim()
  if (!q) return props.customers
  if (q.length >= 3) {
    return serverCustomerResults.value
  }
  const lower = q.toLowerCase()
  return props.customers.filter(c => {
    const name = (c.name || '').toLowerCase()
    const phone = (c.phone || '').toLowerCase()
    return name.includes(lower) || phone.includes(lower)
  })
})

function toggleService(serviceId) {
  const form = isCreateDialogOpen.value ? createForm : editForm
  const index = form.services.findIndex(s => s.service_id === serviceId)
  if (index > -1) {
    form.services.splice(index, 1)
  } else {
    form.services.push({ service_id: serviceId })
  }
}

function isServiceSelected(serviceId) {
  const form = isCreateDialogOpen.value ? createForm : editForm
  return form.services.some(s => s.service_id === serviceId)
}

// Calendar view type
const calendarView = ref('timeGridWeek')
const calendarRef = ref(null)

// FullCalendar setup
const calendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  locale: trLocale,
  headerToolbar: false, // We'll use custom toolbar
  slotMinTime: '08:00:00',
  slotMaxTime: '20:00:00',
  height: 'auto',
  expandRows: true,
  nowIndicator: true,
  editable: false,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  slotDuration: '00:30:00',
  slotLabelInterval: '01:00',
  allDaySlot: false,
  slotLabelFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  },
  businessHours: {
    daysOfWeek: [1, 2, 3, 4, 5, 6],
    startTime: '09:00',
    endTime: '18:00',
  },
  eventClick: (info) => {
    const appointment = info.event.extendedProps.appointment
    openDetailDialog(appointment)
  },
  select: (info) => {
    // When user selects a time slot
    const staffId = info.resource ? info.resource.id : props.staffList[0]?.id
    if (staffId) {
      selectedStaffId.value = staffId
      createForm.staff_id = staffId
      // Convert to local datetime format without timezone offset
      const date = new Date(info.start)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      createForm.start_time = `${year}-${month}-${day}T${hours}:${minutes}`
      isCreateDialogOpen.value = true
    }
  },
}

// Calendar title
const calendarTitle = ref('')

// Get calendar API
const getCalendarApi = () => {
  if (calendarRef.value) {
    return calendarRef.value.getApi()
  }
  return null
}

// Update calendar title
const updateCalendarTitle = () => {
  const api = getCalendarApi()
  if (api) {
    calendarTitle.value = api.view.title
  }
}

async function changeCalendarView(view) {
  calendarView.value = view
  const api = getCalendarApi()
  if (api) {
    api.changeView(view)
    await nextTick()
    updateCalendarTitle()
  }
}

async function calendarPrev() {
  const api = getCalendarApi()
  if (api) {
    api.prev()
    await nextTick()
    updateCalendarTitle()
  }
}

async function calendarNext() {
  const api = getCalendarApi()
  if (api) {
    api.next()
    await nextTick()
    updateCalendarTitle()
  }
}

async function calendarToday() {
  const api = getCalendarApi()
  if (api) {
    api.today()
    await nextTick()
    updateCalendarTitle()
  }
}

// Prepare events for calendar from all staff
const calendarEvents = computed(() => {
  const events = []
  props.staffList.forEach(staff => {
    staff.appointments.forEach(appointment => {
      events.push({
        id: appointment.id,
        title: `${appointment.customer_name || 'Müşteri'} - ${staff.name}`,
        start: appointment.start,
        end: appointment.end,
        backgroundColor: getStatusColorForCalendar(appointment.status),
        borderColor: getStatusColorForCalendar(appointment.status),
        extendedProps: {
          appointment: appointment,
          staffName: staff.name,
        }
      })
    })
  })
  return events
})

// Initialize calendar title on mount
onMounted(async () => {
  await nextTick()
  setTimeout(() => {
    updateCalendarTitle()
  }, 100)
})

function getStatusColorForCalendar(status) {
  const colors = {
    confirmed: '#3b82f6', // blue
    completed: '#22c55e', // green
    cancelled: '#ef4444', // red
    no_show: '#6b7280', // gray
  }
  return colors[status] || '#3b82f6'
}
</script>

<template>
  <Head title="Randevular" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="p-6 space-y-6">
      <!-- Calendar View -->
      <div v-if="viewMode === 'calendar' && staffList && staffList.length">
        <Card class="modern-calendar-card">
          <CardHeader class="border-b bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
            <!-- Custom Calendar Toolbar -->
            <div class="space-y-4">
              <!-- Top Row: Title and Create Buttons -->
              <div class="flex justify-between items-center">
                <CardTitle class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                  Randevu Takvimi
                </CardTitle>
                <Button @click="openCreateDialog()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800">
                  <Plus class="w-4 h-4 mr-2" />
                  Yeni Randevu
                </Button>
              </div>

              <!-- Bottom Row: Navigation and View Controls -->
              <div class="flex justify-between items-center">
                <!-- Navigation Controls -->
                <div class="flex items-center gap-3">
                  <Button @click="calendarPrev" variant="outline" size="sm" class="calendar-nav-btn">
                    <ChevronLeft class="w-5 h-5" />
                  </Button>
                  <Button @click="calendarToday" variant="outline" size="sm" class="calendar-today-btn px-4">
                    Bugün
                  </Button>
                  <Button @click="calendarNext" variant="outline" size="sm" class="calendar-nav-btn">
                    <ChevronRight class="w-5 h-5" />
                  </Button>
                  <div class="ml-4 text-xl font-semibold text-gray-700 dark:text-gray-200">
                    {{ calendarTitle }}
                  </div>
                </div>

                <!-- View Type Controls -->
                <div class="flex items-center gap-2 bg-white dark:bg-slate-800 rounded-lg p-1 shadow-sm border border-gray-200 dark:border-gray-700">
                  <Button
                    @click="changeCalendarView('dayGridMonth')"
                    :variant="calendarView === 'dayGridMonth' ? 'default' : 'ghost'"
                    size="sm"
                    class="calendar-view-btn"
                  >
                    <Calendar class="w-4 h-4 mr-2" />
                    Aylık
                  </Button>
                  <Button
                    @click="changeCalendarView('timeGridWeek')"
                    :variant="calendarView === 'timeGridWeek' ? 'default' : 'ghost'"
                    size="sm"
                    class="calendar-view-btn"
                  >
                    <CalendarDays class="w-4 h-4 mr-2" />
                    Haftalık
                  </Button>
                  <Button
                    @click="changeCalendarView('timeGridDay')"
                    :variant="calendarView === 'timeGridDay' ? 'default' : 'ghost'"
                    size="sm"
                    class="calendar-view-btn"
                  >
                    <Clock class="w-4 h-4 mr-2" />
                    Günlük
                  </Button>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent class="p-6">
            <div class="calendar-wrapper">
              <FullCalendar
                ref="calendarRef"
                :options="{ ...calendarOptions, events: calendarEvents }"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <div v-else class="text-center py-12 text-muted-foreground">
        <p>Henüz personel bulunmuyor.</p>
      </div>

      <!-- Create Appointment Dialog -->
      <Dialog v-model:open="isCreateDialogOpen">
        <DialogContent class="max-w-3xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Plus class="w-6 h-6" />
                </div>
                Yeni Randevu Oluştur
              </DialogTitle>
            </DialogHeader>
          </div>

          <form @submit.prevent="submitCreate" class="flex-1 overflow-y-auto px-6 py-4 space-y-6 custom-scrollbar">
            <!-- Staff Selection -->
            <div class="form-group-modern">
              <Label for="create-staff" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-indigo-500 rounded-full"></span>
                Personel
              </Label>
              <div class="relative" ref="staffContainerRef">
                <input
                  v-model="staffSearch"
                  @focus="() => { staffDropdownOpen = true; resetStaffHighlight() }"
                  @input="onStaffInput"
                  @keydown="onStaffKeydown"
                  type="text"
                  placeholder="Personel ara (isim)"
                  class="modern-input mb-2 w-full"
                />

                <div v-if="staffDropdownOpen" class="search-dropdown">
                  <ul>
                    <li v-for="(staff, idx) in filteredStaffList" :key="staff.id" class="search-item" @click="selectStaff(staff)" @mouseover="staffHighlightedIndex = idx" :class="{ 'active': staffHighlightedIndex === idx }">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold">{{ (staff.name || '').charAt(0) }}</div>
                        <div class="flex-1">
                          <div class="font-medium">{{ staff.name }}</div>
                          <div class="text-xs text-muted-foreground">{{ staff.email || '' }}</div>
                        </div>
                      </div>
                    </li>
                    <li v-if="filteredStaffList.length === 0" class="search-empty">Personel bulunamadı</li>
                  </ul>
                </div>
              </div>

            </div>

            <!-- Customer Selection -->
            <div class="form-group-modern">
              <Label for="create-customer" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                Müşteri
              </Label>
              <div class="relative" ref="customerContainerRef">
                <input
                  v-model="customerSearch"
                  @focus="() => { customerDropdownOpen = true; resetCustomerHighlight() }"
                  @input="onCustomerInput"
                  @keydown="onCustomerKeydown"
                  type="text"
                  placeholder="Müşteri ara (isim veya telefon)"
                  class="modern-input mb-2 w-full"
                />

                <div v-if="customerDropdownOpen" class="search-dropdown">
                  <ul>
                              <li class="search-item" @click="isCreateCustomerDialogOpen = true" style="border-bottom:1px solid rgba(0,0,0,0.04); margin-bottom:0.5rem;">
                                <div class="flex items-center gap-3">
                                  <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-semibold">+</div>
                                  <div class="flex-1">
                                    <div class="font-medium text-green-700">Yeni müşteri oluştur</div>
                                    <div class="text-xs text-muted-foreground">Hızlı ekle</div>
                                  </div>
                                </div>
                              </li>
                              <li v-for="(customer, idx) in filteredCustomers" :key="customer.id" class="search-item" @click="selectCustomer(customer)" @mouseover="customerHighlightedIndex = idx" :class="{ 'active': customerHighlightedIndex === idx }">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">{{ (customer.name || '').charAt(0) }}</div>
                        <div class="flex-1">
                          <div class="font-medium">{{ customer.name }}</div>
                          <div class="text-xs text-muted-foreground">{{ customer.phone || customer.email || '' }}</div>
                        </div>
                      </div>
                    </li>
                    <li v-if="filteredCustomers.length === 0" class="search-empty">Müşteri bulunamadı</li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Date & Time -->
            <div class="form-group-modern">
              <Label for="create-datetime" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                Tarih ve Saat
              </Label>
              <Input
                v-model="createForm.start_time"
                id="create-datetime"
                type="datetime-local"
                class="modern-input"
                required
              />
            </div>

            <!-- Services Selection -->
            <div class="form-group-modern">
              <Label class="text-base font-semibold mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded-full"></span>
                Hizmetler
              </Label>
              <div class="grid grid-cols-1 gap-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                <div
                  v-for="service in services"
                  :key="service.id"
                  @click="toggleService(service.id)"
                  class="service-card"
                  :class="{ 'service-card-selected': isServiceSelected(service.id) }"
                >
                  <div class="flex-1">
                    <p class="font-semibold text-base mb-1">{{ service.name }}</p>
                    <div class="flex items-center gap-3 text-sm text-muted-foreground">
                      <span class="flex items-center gap-1">
                        <DollarSign class="w-4 h-4" />
                        {{ formatPrice(service.price) }}
                      </span>
                      <span class="flex items-center gap-1">
                        <Clock class="w-4 h-4" />
                        {{ service.duration_minutes }} dk
                      </span>
                    </div>
                  </div>
                  <div class="service-checkbox" :class="{ 'service-checkbox-active': isServiceSelected(service.id) }">
                    <svg v-if="isServiceSelected(service.id)" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Total Summary -->
              <div class="mt-4 summary-card">
                <div class="flex items-center justify-between">
                  <span class="font-semibold text-lg">Toplam</span>
                  <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1 text-base font-medium">
                      <DollarSign class="w-5 h-5 text-green-600" />
                      {{ formatPrice(totalSelectedPrice) }}
                    </span>
                    <span class="flex items-center gap-1 text-base font-medium">
                      <Clock class="w-5 h-5 text-blue-600" />
                      {{ totalSelectedDuration }} dk
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="form-group-modern">
              <Label for="create-notes" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                Not (Opsiyonel)
              </Label>
              <textarea
                v-model="createForm.notes"
                id="create-notes"
                rows="3"
                class="modern-textarea"
                placeholder="Randevu ile ilgili notlarınızı buraya yazabilirsiniz..."
              />
            </div>
          </form>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t flex-shrink-0">
            <Button type="button" variant="outline" @click="isCreateDialogOpen = false" class="min-w-[100px]">
              İptal
            </Button>
            <Button type="submit" @click="submitCreate" :disabled="createForm.processing" class="min-w-[140px] bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800">
              <Plus class="w-4 h-4 mr-2" />
              Randevu Oluştur
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Create Customer Modal (quick add) -->
      <Dialog v-model:open="isCreateCustomerDialogOpen">
        <DialogContent class="max-w-md modern-modal">
          <div class="modal-header-gradient flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">+</div>
                Yeni Müşteri
              </DialogTitle>
            </DialogHeader>
          </div>

          <form @submit.prevent="submitNewCustomer" class="px-6 py-6 space-y-4">
            <div>
              <Label for="new-customer-name" class="text-sm font-semibold mb-2 block">Ad Soyad</Label>
              <Input id="new-customer-name" v-model="newCustomer.name" class="modern-input w-full" required />
            </div>
            <div>
              <Label for="new-customer-phone" class="text-sm font-semibold mb-2 block">Telefon</Label>
              <Input id="new-customer-phone" v-model="newCustomer.phone" class="modern-input w-full" />
            </div>
            <div>
              <Label for="new-customer-email" class="text-sm font-semibold mb-2 block">E-posta</Label>
              <Input id="new-customer-email" v-model="newCustomer.email" class="modern-input w-full" />
            </div>
          </form>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t">
            <Button variant="outline" @click="isCreateCustomerDialogOpen = false">İptal</Button>
            <Button @click="submitNewCustomer" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800">Oluştur</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Edit Appointment Dialog -->
      <Dialog v-model:open="isEditDialogOpen">
        <DialogContent class="max-w-3xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient-edit flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Edit class="w-6 h-6" />
                </div>
                Randevu Düzenle
              </DialogTitle>
            </DialogHeader>
          </div>

          <form @submit.prevent="submitEdit" class="flex-1 overflow-y-auto px-6 py-4 space-y-6 custom-scrollbar">
            <!-- Staff Selection -->
            <div class="form-group-modern">
              <Label for="edit-staff" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-indigo-500 rounded-full"></span>
                Personel
              </Label>
              <div class="relative">
                <input
                  v-model="staffSearch"
                  @focus="staffDropdownOpen = true"
                  @input="staffDropdownOpen = true"
                  type="text"
                  placeholder="Personel ara (isim)"
                  class="modern-input mb-2 w-full"
                />

                <div v-if="staffDropdownOpen" class="search-dropdown">
                  <ul>
                    <li v-for="staff in filteredStaffList" :key="staff.id" class="search-item" @click="selectStaff(staff)">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold">{{ (staff.name || '').charAt(0) }}</div>
                        <div class="flex-1">
                          <div class="font-medium">{{ staff.name }}</div>
                          <div class="text-xs text-muted-foreground">{{ staff.email || '' }}</div>
                        </div>
                      </div>
                    </li>
                    <li v-if="filteredStaffList.length === 0" class="search-empty">Personel bulunamadı</li>
                  </ul>
                </div>
              </div>

            </div>

            <!-- Customer Selection -->
            <div class="form-group-modern">
              <Label for="edit-customer" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                Müşteri
              </Label>
              <div class="relative">
                <input
                  v-model="customerSearch"
                  @focus="customerDropdownOpen = true"
                  @input="customerDropdownOpen = true"
                  type="text"
                  placeholder="Müşteri ara (isim veya telefon)"
                  class="modern-input mb-2 w-full"
                />

                <div v-if="customerDropdownOpen" class="search-dropdown">
                  <ul>
                    <li class="search-item" @click="isCreateCustomerDialogOpen = true" style="border-bottom:1px solid rgba(0,0,0,0.04); margin-bottom:0.5rem;">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-semibold">+</div>
                        <div class="flex-1">
                          <div class="font-medium text-green-700">Yeni müşteri oluştur</div>
                          <div class="text-xs text-muted-foreground">Hızlı ekle</div>
                        </div>
                      </div>
                    </li>
                    <li v-for="customer in filteredCustomers" :key="customer.id" class="search-item" @click="selectCustomer(customer)">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">{{ (customer.name || '').charAt(0) }}</div>
                        <div class="flex-1">
                          <div class="font-medium">{{ customer.name }}</div>
                          <div class="text-xs text-muted-foreground">{{ customer.phone || customer.email || '' }}</div>
                        </div>
                      </div>
                    </li>
                    <li v-if="filteredCustomers.length === 0" class="search-empty">Müşteri bulunamadı</li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Date & Time -->
            <div class="form-group-modern">
              <Label for="edit-datetime" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                Tarih ve Saat
              </Label>
              <Input
                v-model="editForm.start_time"
                id="edit-datetime"
                type="datetime-local"
                class="modern-input"
                required
              />
            </div>

            <!-- Status Selection -->
            <div class="form-group-modern">
              <Label for="edit-status" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-amber-500 rounded-full"></span>
                Durum
              </Label>
              <select
                v-model="editForm.status"
                id="edit-status"
                class="modern-select"
              >
                <option value="" disabled>Durum seçin</option>
                <option value="confirmed">Onaylandı</option>
                <option value="completed">Tamamlandı</option>
                <option value="cancelled">İptal</option>
                <option value="no_show">Gelmedi</option>
              </select>
            </div>

            <!-- Services Selection -->
            <div class="form-group-modern">
              <Label class="text-base font-semibold mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded-full"></span>
                Hizmetler
              </Label>
              <div class="grid grid-cols-1 gap-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                <div
                  v-for="service in services"
                  :key="service.id"
                  @click="toggleService(service.id)"
                  class="service-card"
                  :class="{ 'service-card-selected': isServiceSelected(service.id) }"
                >
                  <div class="flex-1">
                    <p class="font-semibold text-base mb-1">{{ service.name }}</p>
                    <div class="flex items-center gap-3 text-sm text-muted-foreground">
                      <span class="flex items-center gap-1">
                        <DollarSign class="w-4 h-4" />
                        {{ formatPrice(service.price) }}
                      </span>
                      <span class="flex items-center gap-1">
                        <Clock class="w-4 h-4" />
                        {{ service.duration_minutes }} dk
                      </span>
                    </div>
                  </div>
                  <div class="service-checkbox" :class="{ 'service-checkbox-active': isServiceSelected(service.id) }">
                    <svg v-if="isServiceSelected(service.id)" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Total Summary -->
              <div class="mt-4 summary-card">
                <div class="flex items-center justify-between">
                  <span class="font-semibold text-lg">Toplam</span>
                  <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1 text-base font-medium">
                      <DollarSign class="w-5 h-5 text-green-600" />
                      {{ formatPrice(totalSelectedPrice) }}
                    </span>
                    <span class="flex items-center gap-1 text-base font-medium">
                      <Clock class="w-5 h-5 text-blue-600" />
                      {{ totalSelectedDuration }} dk
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="form-group-modern">
              <Label for="edit-notes" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                Not (Opsiyonel)
              </Label>
              <textarea
                v-model="editForm.notes"
                id="edit-notes"
                rows="3"
                class="modern-textarea"
                placeholder="Randevu ile ilgili notlarınızı buraya yazabilirsiniz..."
              />
            </div>
          </form>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t">
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
        <DialogContent class="max-w-md modern-modal">
          <div class="modal-header-gradient-delete">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Trash2 class="w-6 h-6" />
                </div>
                Randevuyu Sil
              </DialogTitle>
            </DialogHeader>
          </div>

          <div class="px-6 py-6">
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
                  Bu randevuyu silmek istediğinizden emin misiniz? Tüm randevu bilgileri kalıcı olarak silinecektir.
                </p>
              </div>
            </div>
          </div>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t">
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

      <!-- Appointment Detail Dialog -->
      <Dialog v-model:open="isDetailDialogOpen">
        <DialogContent class="max-w-2xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <Calendar class="w-6 h-6" />
                </div>
                Randevu Detayları
              </DialogTitle>
            </DialogHeader>
          </div>

          <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6 custom-scrollbar" v-if="selectedAppointment">
            <!-- Status Badge -->
            <div class="flex justify-center">
              <Badge :class="getStatusColor(selectedAppointment.status)" class="text-white px-6 py-2 text-base">
                {{ getStatusText(selectedAppointment.status) }}
              </Badge>
            </div>

            <!-- Customer Info -->
            <div class="p-5 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border-2 border-blue-200 dark:border-blue-800">
              <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">Müşteri Bilgileri</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium">Ad Soyad:</span>
                  <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ selectedAppointment.customer_name }}</span>
                </div>
                <div v-if="selectedAppointment.customer_phone" class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium">Telefon:</span>
                  <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ selectedAppointment.customer_phone }}</span>
                </div>
              </div>
            </div>

            <!-- Appointment Info -->
            <div class="p-5 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-2 border-purple-200 dark:border-purple-800">
              <h3 class="text-lg font-bold text-purple-900 dark:text-purple-100 mb-4">Randevu Bilgileri</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    Tarih ve Saat:
                  </span>
                  <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ formatDateTime(selectedAppointment.start) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium flex items-center gap-2">
                    <Clock class="w-4 h-4" />
                    Süre:
                  </span>
                  <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ selectedAppointment.total_duration }} dakika</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium flex items-center gap-2">
                    <DollarSign class="w-4 h-4" />
                    Toplam Tutar:
                  </span>
                  <span class="text-gray-900 dark:text-gray-100 font-bold text-xl text-green-600">{{ formatPrice(selectedAppointment.total_price) }}</span>
                </div>
              </div>
            </div>

            <!-- Services -->
            <div v-if="selectedAppointment.services && selectedAppointment.services.length" class="p-5 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border-2 border-green-200 dark:border-green-800">
              <h3 class="text-lg font-bold text-green-900 dark:text-green-100 mb-4">Hizmetler</h3>
              <div class="space-y-2">
                <div v-for="(service, idx) in selectedAppointment.services" :key="idx" class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded-lg">
                  <span class="font-medium">{{ service.name }}</span>
                  <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <span>{{ service.duration_minutes }} dk</span>
                    <span class="font-semibold text-green-600">{{ formatPrice(service.price) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="selectedAppointment.notes" class="p-5 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border-2 border-amber-200 dark:border-amber-800">
              <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100 mb-3">Not</h3>
              <p class="text-gray-700 dark:text-gray-300">{{ selectedAppointment.notes }}</p>
            </div>
          </div>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t flex-shrink-0">
            <div class="flex justify-between items-center w-full">
              <Button type="button" variant="outline" @click="isDetailDialogOpen = false" class="min-w-[100px]">
                Kapat
              </Button>
              <div class="flex gap-2">
                <Button
                  v-if="selectedAppointment && selectedAppointment.status === 'confirmed'"
                  @click="() => { isDetailDialogOpen = false; openPaymentDialog(selectedAppointment); }"
                  class="min-w-[140px] bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800"
                >
                  <CheckCircle2 class="w-4 h-4 mr-2" />
                  Ödeme Al
                </Button>
                <Button
                  v-if="selectedAppointment"
                  @click="() => { isDetailDialogOpen = false; openEditDialog(selectedAppointment); }"
                  variant="outline"
                  class="min-w-[120px]"
                >
                  <Edit class="w-4 h-4 mr-2" />
                  Düzenle
                </Button>
                <Button
                  v-if="selectedAppointment"
                  @click="() => { isDetailDialogOpen = false; openDeleteDialog(selectedAppointment); }"
                  variant="destructive"
                  class="min-w-[100px]"
                >
                  <Trash2 class="w-4 h-4 mr-2" />
                  Sil
                </Button>
              </div>
            </div>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Payment Dialog -->
      <Dialog v-model:open="isPaymentDialogOpen">
        <DialogContent class="max-w-2xl max-h-[95vh] overflow-hidden p-0 modern-modal flex flex-col">
          <div class="modal-header-gradient-payment flex-shrink-0">
            <DialogHeader class="p-6 pb-4">
              <DialogTitle class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                  <CreditCard class="w-6 h-6" />
                </div>
                Ödeme Al ve Randevuyu Tamamla
              </DialogTitle>
            </DialogHeader>
          </div>

          <form @submit.prevent="submitPayment" class="flex-1 overflow-y-auto px-6 py-6 space-y-6 custom-scrollbar">
            <!-- Appointment Summary -->
            <div class="p-5 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border-2 border-blue-200 dark:border-blue-800">
              <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-4 flex items-center gap-2">
                <Calendar class="w-5 h-5" />
                Randevu Özeti
              </h3>
              <div class="space-y-3" v-if="selectedAppointment">
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium">Müşteri:</span>
                  <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ selectedAppointment.customer_name }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium">Tarih:</span>
                  <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ formatDateTime(selectedAppointment.start) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 dark:text-gray-300 font-medium">Toplam Süre:</span>
                  <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ selectedAppointment.total_duration }} dakika</span>
                </div>
                <div class="border-t-2 border-blue-300 dark:border-blue-700 pt-3 mt-3">
                  <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">Toplam Tutar:</span>
                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatPrice(selectedAppointment.total_price) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Method -->
            <div class="form-group-modern">
              <Label for="payment-method" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded-full"></span>
                Ödeme Yöntemi
              </Label>
              <select
                v-model="paymentForm.payment_method"
                id="payment-method"
                class="modern-select"
                required
              >
                <option value="cash">Nakit</option>
                <option value="credit_card">Kredi Kartı</option>
                <option value="debit_card">Banka Kartı</option>
                <option value="online_payment">Online Ödeme</option>
              </select>
            </div>

            <!-- Amount Paid -->
            <div class="form-group-modern">
              <Label for="amount-paid" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-yellow-500 rounded-full"></span>
                Ödenen Tutar (₺)
              </Label>
              <Input
                v-model="paymentForm.amount_paid"
                id="amount-paid"
                type="number"
                step="0.01"
                min="0"
                class="modern-input text-lg font-bold"
                required
              />
            </div>

            <!-- Payment Notes -->
            <div class="form-group-modern">
              <Label for="payment-notes" class="text-base font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                Not (Opsiyonel)
              </Label>
              <textarea
                v-model="paymentForm.notes"
                id="payment-notes"
                rows="3"
                class="modern-textarea"
                placeholder="Ödeme ile ilgili notlar..."
              />
            </div>
          </form>

          <DialogFooter class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t flex-shrink-0">
            <Button type="button" variant="outline" @click="isPaymentDialogOpen = false" class="min-w-[100px]">
              İptal
            </Button>
            <Button type="submit" @click="submitPayment" :disabled="paymentForm.processing" class="min-w-[180px] bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800">
              <CheckCircle2 class="w-4 h-4 mr-2" />
              Ödemeyi Onayla
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Modern Calendar Card */
.modern-calendar-card {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  border-radius: 1rem;
  overflow: hidden;
}

.calendar-wrapper {
  border-radius: 0.75rem;
  overflow: hidden;
}

/* Calendar Navigation Buttons */
.calendar-nav-btn {
  transition: all 0.3s;
  border-radius: 0.5rem;
  min-width: 2.5rem;
}

.calendar-nav-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.calendar-today-btn {
  transition: all 0.3s;
  border-radius: 0.5rem;
  font-weight: 600;
}

.calendar-today-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

/* Calendar View Buttons */
.calendar-view-btn {
  transition: all 0.2s;
  border-radius: 0.5rem;
}

.calendar-view-btn:hover {
  transform: translateY(-1px);
}

/* Modern Modal Styles */
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

/* Modal Header Gradients */
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

.modal-header-gradient-payment {
  background: linear-gradient(135deg, #059669 0%, #10b981 50%, #14b8a6 100%);
  position: relative;
  overflow: hidden;
}

.modal-header-gradient-payment::before {
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

/* Form Groups */
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

/* Modern Select */
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
  border-color: rgb(59 130 246);
  box-shadow: 0 0 0 4px rgb(59 130 246 / 0.2);
}

:root.dark .modern-select {
  border-color: rgb(55 65 81);
  background-color: rgb(31 41 55);
}

:root.dark .modern-select:hover {
  border-color: rgb(75 85 99);
}

/* Modern Input */
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

/* Modern Textarea */
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

/* Service Cards */
.service-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-radius: 0.75rem;
  border: 2px solid rgb(229 231 235);
  background-color: white;
  cursor: pointer;
  transition: all 0.3s;
}

.service-card:hover {
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  transform: scale(1.02);
  border-color: rgb(209 213 219);
}

:root.dark .service-card {
  border-color: rgb(55 65 81);
  background-color: rgb(31 41 55);
}

:root.dark .service-card:hover {
  border-color: rgb(75 85 99);
}

.service-card-selected {
  border-color: rgb(59 130 246);
  background-color: rgb(239 246 255);
  box-shadow: 0 10px 15px -3px rgb(59 130 246 / 0.1), 0 4px 6px -4px rgb(59 130 246 / 0.1);
  transform: scale(1.02);
}

:root.dark .service-card-selected {
  background-color: rgb(30 58 138 / 0.2);
}

/* Service Checkbox */
.service-checkbox {
  flex-shrink: 0;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 0.5rem;
  border: 2px solid rgb(209 213 219);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.service-checkbox-active {
  background-color: rgb(59 130 246);
  border-color: rgb(59 130 246);
  box-shadow: 0 10px 15px -3px rgb(59 130 246 / 0.3), 0 4px 6px -4px rgb(59 130 246 / 0.3);
}

/* Summary Card */
.summary-card {
  padding: 1.25rem;
  border-radius: 0.75rem;
  background: linear-gradient(to bottom right, rgb(249 250 251), rgb(243 244 246));
  border: 2px solid rgb(229 231 235);
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  animation: summaryPulse 2s ease-in-out infinite;
}

:root.dark .summary-card {
  background: linear-gradient(to bottom right, rgb(31 41 55), rgb(17 24 39));
  border-color: rgb(55 65 81);
}

@keyframes summaryPulse {
  0%, 100% {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  }
  50% {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  }
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #cbd5e1, #94a3b8);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #94a3b8, #64748b);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #475569, #334155);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #64748b, #475569);
}
</style>

<style>
/* Searchable dropdown styles */
.search-dropdown {
  position: absolute;
  z-index: 40;
  width: 100%;
  max-height: 240px;
  overflow: auto;
  background: var(--card-bg, white);
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 0.75rem;
  box-shadow: 0 8px 24px rgba(2,6,23,0.08);
  padding: 0.5rem;
}
.search-dropdown ul { list-style: none; margin: 0; padding: 0; }
.search-item {
  padding: 0.5rem; cursor: pointer; border-radius: 0.5rem;
}
.search-item:hover { background: rgba(59,130,246,0.06); }
.search-empty { padding: 0.75rem; color: #6b7280; }

.search-item.active {
  background: rgba(59,130,246,0.12);
  outline: 2px solid rgba(59,130,246,0.12);
}

:root.dark .search-dropdown { background: #0f172a; border-color: rgba(255,255,255,0.06); }

/* FullCalendar Modern Styling */
.fc {
  --fc-border-color: rgb(226 232 240);
  --fc-button-bg-color: hsl(var(--primary));
  --fc-button-border-color: hsl(var(--primary));
  --fc-button-hover-bg-color: hsl(var(--primary) / 0.9);
  --fc-button-hover-border-color: hsl(var(--primary) / 0.9);
  --fc-button-active-bg-color: hsl(var(--primary) / 0.8);
  --fc-button-active-border-color: hsl(var(--primary) / 0.8);
  --fc-today-bg-color: rgb(239 246 255);
  --fc-page-bg-color: white;
  --fc-neutral-bg-color: rgb(248 250 252);
  font-family: inherit;
}

:root.dark .fc {
  --fc-border-color: rgb(51 65 85);
  --fc-today-bg-color: rgb(30 58 138 / 0.2);
  --fc-page-bg-color: rgb(15 23 42);
  --fc-neutral-bg-color: rgb(30 41 59);
}

/* Calendar Header */
.fc .fc-toolbar-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: rgb(15 23 42);
}

:root.dark .fc .fc-toolbar-title {
  color: rgb(226 232 240);
}

/* Column Headers */
.fc-col-header-cell {
  background: linear-gradient(180deg, rgb(248 250 252), rgb(241 245 249)) !important;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 1rem 0.5rem !important;
  border-color: rgb(226 232 240) !important;
}

:root.dark .fc-col-header-cell {
  background: linear-gradient(180deg, rgb(30 41 59), rgb(15 23 42)) !important;
  border-color: rgb(51 65 85) !important;
}

.fc-col-header-cell-cushion {
  color: rgb(71 85 105);
  font-weight: 600;
}

:root.dark .fc-col-header-cell-cushion {
  color: rgb(148 163 184);
}

/* Day Grid (Monthly View) */
.fc .fc-daygrid-day-number {
  font-weight: 600;
  font-size: 0.875rem;
  padding: 0.5rem;
  color: rgb(51 65 81);
}

:root.dark .fc .fc-daygrid-day-number {
  color: rgb(226 232 240);
}

.fc .fc-daygrid-day.fc-day-today {
  background-color: rgb(239 246 255) !important;
}

:root.dark .fc .fc-daygrid-day.fc-day-today {
  background-color: rgb(30 58 138 / 0.2) !important;
}

/* Time Grid Slots */
.fc-timegrid-slot {
  height: 3.5rem !important;
  border-color: rgb(241 245 249) !important;
}

:root.dark .fc-timegrid-slot {
  border-color: rgb(30 41 59) !important;
}

.fc-timegrid-slot-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: rgb(100 116 139);
}

:root.dark .fc-timegrid-slot-label {
  color: rgb(148 163 184);
}

/* Current Time Indicator */
.fc .fc-timegrid-now-indicator-line {
  border-color: rgb(239 68 68);
  border-width: 2px;
}

.fc .fc-timegrid-now-indicator-arrow {
  border-color: rgb(239 68 68);
  border-width: 6px;
}

/* Events */
.fc-event {
  border-radius: 0.5rem !important;
  padding: 0.25rem 0.5rem !important;
  font-size: 0.875rem !important;
  font-weight: 500 !important;
  border: none !important;
  box-shadow: 0 2px 4px rgb(0 0 0 / 0.1) !important;
  cursor: pointer !important;
  transition: all 0.2s !important;
}

.fc-event:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.15) !important;
}

.fc-event-main {
  color: white !important;
}

.fc-event-title {
  font-weight: 600 !important;
  overflow: hidden;
  text-overflow: ellipsis;
}

.fc-event-time {
  font-weight: 500 !important;
  font-size: 0.75rem !important;
}

/* Borders */
.fc-theme-standard td,
.fc-theme-standard th {
  border-color: rgb(226 232 240);
}

:root.dark .fc-theme-standard td,
:root.dark .fc-theme-standard th {
  border-color: rgb(51 65 85);
}

.fc-theme-standard .fc-scrollgrid {
  border-color: rgb(226 232 240);
  border-radius: 0.75rem;
  overflow: hidden;
}

:root.dark .fc-theme-standard .fc-scrollgrid {
  border-color: rgb(51 65 85);
}

/* Background Events (Business Hours) */
.fc .fc-non-business {
  background-color: rgb(248 250 252);
}

:root.dark .fc .fc-non-business {
  background-color: rgb(15 23 42);
}

/* Scrollbars */
.fc-scroller::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.fc-scroller::-webkit-scrollbar-track {
  background: rgb(241 245 249);
  border-radius: 4px;
}

.fc-scroller::-webkit-scrollbar-thumb {
  background: rgb(203 213 225);
  border-radius: 4px;
}

.fc-scroller::-webkit-scrollbar-thumb:hover {
  background: rgb(148 163 184);
}

:root.dark .fc-scroller::-webkit-scrollbar-track {
  background: rgb(30 41 59);
}

:root.dark .fc-scroller::-webkit-scrollbar-thumb {
  background: rgb(51 65 85);
}

:root.dark .fc-scroller::-webkit-scrollbar-thumb:hover {
  background: rgb(71 85 105);
}

/* More Link (in monthly view) */
.fc .fc-more-link {
  color: rgb(59 130 246);
  font-weight: 600;
  font-size: 0.75rem;
}

.fc .fc-more-link:hover {
  color: rgb(37 99 235);
  text-decoration: underline;
}

/* Popover */
.fc .fc-popover {
  border-radius: 0.75rem;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  border-color: rgb(226 232 240);
}

:root.dark .fc .fc-popover {
  background-color: rgb(30 41 59);
  border-color: rgb(51 65 85);
}
</style>
