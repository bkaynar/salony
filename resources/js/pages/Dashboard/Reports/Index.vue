<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { TrendingUp, TrendingDown, DollarSign, Calendar, CreditCard, Banknote, Smartphone, RefreshCw, Plus, Trash2, Edit, Receipt, Home, FileText, Package } from 'lucide-vue-next'
import { useForm } from '@inertiajs/vue3'

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Gelir Raporları' },
]

const props = defineProps({
  stats: Object,
  dailyRevenue: Array,
  monthlyRevenue: Array,
  paymentMethods: Array,
  expenses: Array,
  expensesByCategory: Array,
  dateRange: Object,
})

const startDate = ref(props.dateRange?.start || '')
const endDate = ref(props.dateRange?.end || '')

const formatPrice = (price) => {
  if (!price) return '0 ₺'
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
  }).format(price)
}

const getPaymentMethodLabel = (method) => {
  const labels = {
    cash: 'Nakit',
    credit_card: 'Kredi Kartı',
    debit_card: 'Banka Kartı',
    online_payment: 'Online Ödeme',
  }
  return labels[method] || method
}

const getPaymentMethodIcon = (method) => {
  const icons = {
    cash: Banknote,
    credit_card: CreditCard,
    debit_card: CreditCard,
    online_payment: Smartphone,
  }
  return icons[method] || CreditCard
}

const getPaymentMethodColor = (method) => {
  const colors = {
    cash: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    credit_card: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    debit_card: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    online_payment: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
  }
  return colors[method] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
}

const filterReports = () => {
  router.get('/dashboard/reports', {
    start_date: startDate.value,
    end_date: endDate.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  const today = new Date()
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
  const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0)

  startDate.value = firstDay.toISOString().split('T')[0]
  endDate.value = lastDay.toISOString().split('T')[0]
  filterReports()
}

// Expense management
const isExpenseDialogOpen = ref(false)
const isEditExpenseDialogOpen = ref(false)
const selectedExpense = ref(null)

const expenseForm = useForm({
  category: 'diger',
  amount: '',
  description: '',
  expense_date: new Date().toISOString().split('T')[0],
})

const editExpenseForm = useForm({
  category: '',
  amount: '',
  description: '',
  expense_date: '',
})

const openCreateExpenseDialog = () => {
  expenseForm.reset()
  expenseForm.expense_date = new Date().toISOString().split('T')[0]
  isExpenseDialogOpen.value = true
}

const submitExpense = () => {
  expenseForm.post(route('dashboard.expenses.store'), {
    onSuccess: () => {
      isExpenseDialogOpen.value = false
      expenseForm.reset()
    },
  })
}

const openEditExpenseDialog = (expense) => {
  selectedExpense.value = expense
  editExpenseForm.category = expense.category
  editExpenseForm.amount = expense.amount
  editExpenseForm.description = expense.description
  editExpenseForm.expense_date = expense.expense_date
  isEditExpenseDialogOpen.value = true
}

const submitEditExpense = () => {
  if (!selectedExpense.value) return

  editExpenseForm.put(route('dashboard.expenses.update', selectedExpense.value.id), {
    onSuccess: () => {
      isEditExpenseDialogOpen.value = false
      editExpenseForm.reset()
      selectedExpense.value = null
    },
  })
}

const deleteExpense = (expenseId) => {
  if (!confirm('Bu gideri silmek istediğinizden emin misiniz?')) return

  router.delete(route('dashboard.expenses.destroy', expenseId), {
    preserveScroll: true,
  })
}

const getCategoryLabel = (category) => {
  const labels = {
    personel: 'Personel Gideri',
    kira: 'Kira Gideri',
    fatura: 'Fatura',
    diger: 'Diğer Giderler',
  }
  return labels[category] || category
}

const getCategoryIcon = (category) => {
  const icons = {
    personel: DollarSign,
    kira: Home,
    fatura: Receipt,
    diger: Package,
  }
  return icons[category] || Package
}

const getCategoryColor = (category) => {
  const colors = {
    personel: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    kira: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    fatura: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    diger: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
  }
  return colors[category] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
}
</script>

<template>
  <Head title="Gelir Raporları" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
            Gelir Raporları
          </h1>
          <p class="text-muted-foreground mt-1">Finansal performans ve gelir analizleri</p>
        </div>
      </div>

      <!-- Date Filter -->
      <Card class="modern-card">
        <CardContent class="p-6">
          <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
              <Label for="start-date" class="mb-2 block">Başlangıç Tarihi</Label>
              <Input
                v-model="startDate"
                id="start-date"
                type="date"
                class="modern-input"
              />
            </div>
            <div class="flex-1 min-w-[200px]">
              <Label for="end-date" class="mb-2 block">Bitiş Tarihi</Label>
              <Input
                v-model="endDate"
                id="end-date"
                type="date"
                class="modern-input"
              />
            </div>
            <Button @click="filterReports" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800">
              <Calendar class="w-4 h-4 mr-2" />
              Filtrele
            </Button>
            <Button @click="resetFilters" variant="outline">
              <RefreshCw class="w-4 h-4 mr-2" />
              Sıfırla
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <Card class="stat-card border-l-4 border-l-green-500">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Toplam Gelir
            </CardTitle>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
              <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-green-600 dark:text-green-400">
              {{ formatPrice(stats.total_revenue) }}
            </div>
            <p class="text-xs text-muted-foreground mt-2">
              Seçili dönem içinde
            </p>
          </CardContent>
        </Card>

        <!-- Total Expenses -->
        <Card class="stat-card border-l-4 border-l-red-500">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Toplam Gider
            </CardTitle>
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
              <TrendingDown class="h-6 w-6 text-red-600 dark:text-red-400" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-red-600 dark:text-red-400">
              {{ formatPrice(stats.total_expenses) }}
            </div>
            <p class="text-xs text-muted-foreground mt-2">
              Seçili dönem içinde
            </p>
          </CardContent>
        </Card>

        <!-- Net Income -->
        <Card class="stat-card border-l-4 border-l-blue-500">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Net Kar
            </CardTitle>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
              <DollarSign class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
              {{ formatPrice(stats.net_income) }}
            </div>
            <p class="text-xs text-muted-foreground mt-2">
              Gelir - Gider
            </p>
          </CardContent>
        </Card>

        <!-- Total Appointments -->
        <Card class="stat-card border-l-4 border-l-purple-500">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Toplam Randevu
            </CardTitle>
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
              <Calendar class="h-6 w-6 text-purple-600 dark:text-purple-400" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
              {{ stats.total_appointments }}
            </div>
            <p class="text-xs text-muted-foreground mt-2">
              Tamamlanan randevular
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Methods -->
        <Card class="modern-card">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <CreditCard class="h-5 w-5 text-blue-600 dark:text-blue-400" />
              Ödeme Yöntemleri
            </CardTitle>
            <CardDescription>Ödeme yöntemlerine göre dağılım</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="paymentMethods && paymentMethods.length > 0" class="space-y-4">
              <div
                v-for="(method, index) in paymentMethods"
                :key="index"
                class="payment-method-card p-4 rounded-lg border hover:shadow-md transition-all duration-200"
              >
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg text-white">
                      <component :is="getPaymentMethodIcon(method.method)" class="w-5 h-5" />
                    </div>
                    <div>
                      <div class="font-semibold">{{ getPaymentMethodLabel(method.method) }}</div>
                      <div class="text-sm text-muted-foreground">{{ method.count }} işlem</div>
                    </div>
                  </div>
                  <Badge :class="getPaymentMethodColor(method.method)" class="text-lg font-bold px-3 py-1">
                    {{ formatPrice(method.total) }}
                  </Badge>
                </div>
                <div class="mt-2">
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div
                      class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                      :style="{ width: `${(method.total / stats.total_revenue) * 100}%` }"
                    ></div>
                  </div>
                  <div class="text-xs text-muted-foreground mt-1 text-right">
                    {{ ((method.total / stats.total_revenue) * 100).toFixed(1) }}% toplam gelirden
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              Bu dönemde ödeme bulunmuyor
            </div>
          </CardContent>
        </Card>

        <!-- Monthly Revenue Chart -->
        <Card class="modern-card">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <TrendingUp class="h-5 w-5 text-green-600 dark:text-green-400" />
              Aylık Gelir Trendi
            </CardTitle>
            <CardDescription>Son 6 ayın performansı</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="monthlyRevenue && monthlyRevenue.length > 0" class="space-y-3">
              <div
                v-for="(month, index) in monthlyRevenue"
                :key="index"
                class="monthly-item p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium">{{ month.month }}</span>
                  <span class="font-bold text-green-600 dark:text-green-400">{{ formatPrice(month.revenue) }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                  <Calendar class="w-4 h-4" />
                  <span>{{ month.appointments }} randevu</span>
                </div>
                <div class="mt-2">
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div
                      class="bg-gradient-to-r from-green-500 to-emerald-600 h-1.5 rounded-full transition-all duration-500"
                      :style="{ width: `${(month.revenue / Math.max(...monthlyRevenue.map(m => m.revenue))) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              Veri bulunmuyor
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Daily Revenue Table -->
      <Card class="modern-card">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Calendar class="h-5 w-5 text-purple-600 dark:text-purple-400" />
            Günlük Gelir Detayı
          </CardTitle>
          <CardDescription>Seçili döneme ait günlük gelir dağılımı</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="dailyRevenue && dailyRevenue.length > 0" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                  <th class="text-left py-3 px-4 font-semibold text-muted-foreground">Tarih</th>
                  <th class="text-center py-3 px-4 font-semibold text-muted-foreground">Randevu Sayısı</th>
                  <th class="text-right py-3 px-4 font-semibold text-muted-foreground">Gelir</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(day, index) in dailyRevenue"
                  :key="index"
                  class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                  <td class="py-3 px-4">{{ new Date(day.date).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</td>
                  <td class="py-3 px-4 text-center">
                    <Badge variant="outline">{{ day.count }}</Badge>
                  </td>
                  <td class="py-3 px-4 text-right font-bold text-green-600 dark:text-green-400">{{ formatPrice(day.total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-8 text-muted-foreground">
            Bu dönemde gelir bulunmuyor
          </div>
        </CardContent>
      </Card>

      <!-- Expense Management Section -->
      <div class="mt-8">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-red-600 to-rose-600 bg-clip-text text-transparent">
              Gider Yönetimi
            </h2>
            <p class="text-muted-foreground mt-1">Salon giderlerinizi takip edin</p>
          </div>
          <Button @click="openCreateExpenseDialog" class="bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800">
            <Plus class="w-4 h-4 mr-2" />
            Yeni Gider Ekle
          </Button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Expenses by Category -->
          <Card class="modern-card">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Receipt class="h-5 w-5 text-red-600 dark:text-red-400" />
                Kategorilere Göre Giderler
              </CardTitle>
              <CardDescription>Gider kategorilerinin dağılımı</CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="expensesByCategory && expensesByCategory.length > 0" class="space-y-4">
                <div
                  v-for="(category, index) in expensesByCategory"
                  :key="index"
                  class="expense-category-card p-4 rounded-lg border hover:shadow-md transition-all duration-200"
                >
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                      <div class="p-2 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg text-white">
                        <component :is="getCategoryIcon(category.category)" class="w-5 h-5" />
                      </div>
                      <div>
                        <div class="font-semibold">{{ getCategoryLabel(category.category) }}</div>
                        <div class="text-sm text-muted-foreground">{{ category.count }} gider</div>
                      </div>
                    </div>
                    <Badge :class="getCategoryColor(category.category)" class="text-lg font-bold px-3 py-1">
                      {{ formatPrice(category.total) }}
                    </Badge>
                  </div>
                  <div class="mt-2">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                      <div
                        class="bg-gradient-to-r from-red-500 to-rose-600 h-2 rounded-full transition-all duration-500"
                        :style="{ width: stats.total_expenses > 0 ? `${(category.total / stats.total_expenses) * 100}%` : '0%' }"
                      ></div>
                    </div>
                    <div class="text-xs text-muted-foreground mt-1 text-right">
                      {{ stats.total_expenses > 0 ? ((category.total / stats.total_expenses) * 100).toFixed(1) : 0 }}% toplam giderden
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-muted-foreground">
                Bu dönemde gider bulunmuyor
              </div>
            </CardContent>
          </Card>

          <!-- Expense List -->
          <Card class="modern-card">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5 text-red-600 dark:text-red-400" />
                Gider Listesi
              </CardTitle>
              <CardDescription>Tüm gider kayıtları</CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="expenses && expenses.length > 0" class="space-y-3 max-h-[500px] overflow-y-auto">
                <div
                  v-for="expense in expenses"
                  :key="expense.id"
                  class="expense-item p-4 rounded-lg border hover:shadow-md transition-all duration-200"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 mb-1">
                        <Badge :class="getCategoryColor(expense.category)">
                          {{ getCategoryLabel(expense.category) }}
                        </Badge>
                        <span class="text-sm text-muted-foreground">
                          {{ new Date(expense.expense_date).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </span>
                      </div>
                      <div class="text-xl font-bold text-red-600 dark:text-red-400">
                        {{ formatPrice(expense.amount) }}
                      </div>
                      <div v-if="expense.description" class="text-sm text-muted-foreground mt-1">
                        {{ expense.description }}
                      </div>
                    </div>
                    <div class="flex gap-2">
                      <Button
                        @click="openEditExpenseDialog(expense)"
                        variant="outline"
                        size="sm"
                        class="h-8 w-8 p-0"
                      >
                        <Edit class="h-4 w-4" />
                      </Button>
                      <Button
                        @click="deleteExpense(expense.id)"
                        variant="destructive"
                        size="sm"
                        class="h-8 w-8 p-0"
                      >
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-muted-foreground">
                Henüz gider eklenmemiş
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Create Expense Dialog -->
    <Dialog v-model:open="isExpenseDialogOpen">
      <DialogContent class="max-w-md">
        <DialogHeader class="modern-gradient-header">
          <DialogTitle class="text-white flex items-center gap-2">
            <Plus class="w-5 h-5" />
            Yeni Gider Ekle
          </DialogTitle>
          <DialogDescription class="text-white/90">
            Yeni bir gider kaydı oluşturun
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 p-4">
          <div>
            <Label for="expense-category" class="mb-2 block">Kategori</Label>
            <Select v-model="expenseForm.category">
              <SelectTrigger class="modern-input">
                <SelectValue placeholder="Kategori seçin" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="personel">Personel Gideri</SelectItem>
                <SelectItem value="kira">Kira Gideri</SelectItem>
                <SelectItem value="fatura">Fatura</SelectItem>
                <SelectItem value="diger">Diğer Giderler</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="expense-amount" class="mb-2 block">Tutar (₺)</Label>
            <Input
              v-model="expenseForm.amount"
              id="expense-amount"
              type="number"
              step="0.01"
              placeholder="0.00"
              class="modern-input"
            />
          </div>

          <div>
            <Label for="expense-date" class="mb-2 block">Tarih</Label>
            <Input
              v-model="expenseForm.expense_date"
              id="expense-date"
              type="date"
              class="modern-input"
            />
          </div>

          <div>
            <Label for="expense-description" class="mb-2 block">Açıklama (Opsiyonel)</Label>
            <Textarea
              v-model="expenseForm.description"
              id="expense-description"
              placeholder="Gider açıklaması..."
              class="modern-input"
              rows="3"
            />
          </div>

          <div class="flex gap-3 pt-4">
            <Button
              @click="submitExpense"
              :disabled="expenseForm.processing"
              class="flex-1 bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800"
            >
              Gider Ekle
            </Button>
            <Button @click="isExpenseDialogOpen = false" variant="outline" class="flex-1">
              İptal
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Edit Expense Dialog -->
    <Dialog v-model:open="isEditExpenseDialogOpen">
      <DialogContent class="max-w-md">
        <DialogHeader class="modern-gradient-header">
          <DialogTitle class="text-white flex items-center gap-2">
            <Edit class="w-5 h-5" />
            Gider Düzenle
          </DialogTitle>
          <DialogDescription class="text-white/90">
            Gider bilgilerini güncelleyin
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 p-4">
          <div>
            <Label for="edit-expense-category" class="mb-2 block">Kategori</Label>
            <Select v-model="editExpenseForm.category">
              <SelectTrigger class="modern-input">
                <SelectValue placeholder="Kategori seçin" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="personel">Personel Gideri</SelectItem>
                <SelectItem value="kira">Kira Gideri</SelectItem>
                <SelectItem value="fatura">Fatura</SelectItem>
                <SelectItem value="diger">Diğer Giderler</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="edit-expense-amount" class="mb-2 block">Tutar (₺)</Label>
            <Input
              v-model="editExpenseForm.amount"
              id="edit-expense-amount"
              type="number"
              step="0.01"
              placeholder="0.00"
              class="modern-input"
            />
          </div>

          <div>
            <Label for="edit-expense-date" class="mb-2 block">Tarih</Label>
            <Input
              v-model="editExpenseForm.expense_date"
              id="edit-expense-date"
              type="date"
              class="modern-input"
            />
          </div>

          <div>
            <Label for="edit-expense-description" class="mb-2 block">Açıklama (Opsiyonel)</Label>
            <Textarea
              v-model="editExpenseForm.description"
              id="edit-expense-description"
              placeholder="Gider açıklaması..."
              class="modern-input"
              rows="3"
            />
          </div>

          <div class="flex gap-3 pt-4">
            <Button
              @click="submitEditExpense"
              :disabled="editExpenseForm.processing"
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800"
            >
              Güncelle
            </Button>
            <Button @click="isEditExpenseDialogOpen = false" variant="outline" class="flex-1">
              İptal
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.stat-card {
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
}

.modern-card {
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

:root.dark .modern-card {
  border-color: rgba(255, 255, 255, 0.1);
}

.payment-method-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
  transition: all 0.3s ease;
}

.payment-method-card:hover {
  transform: translateX(4px);
}

:root.dark .payment-method-card {
  background: linear-gradient(135deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.9) 100%);
}

.monthly-item {
  border: 1px solid transparent;
  transition: all 0.3s ease;
}

.monthly-item:hover {
  border-color: rgba(59, 130, 246, 0.3);
}

.modern-input {
  height: 2.75rem;
  border-radius: 0.5rem;
  border: 2px solid rgb(229 231 235);
  transition: all 0.3s;
}

.modern-input:hover {
  border-color: rgb(209 213 219);
}

.modern-input:focus {
  outline: none;
  border-color: rgb(59 130 246);
  box-shadow: 0 0 0 3px rgb(59 130 246 / 0.2);
}

:root.dark .modern-input {
  border-color: rgb(55 65 81);
}

:root.dark .modern-input:hover {
  border-color: rgb(75 85 99);
}

.expense-category-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
  transition: all 0.3s ease;
}

.expense-category-card:hover {
  transform: translateX(4px);
}

:root.dark .expense-category-card {
  background: linear-gradient(135deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.9) 100%);
}

.expense-item {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
  transition: all 0.3s ease;
  border-left: 3px solid rgb(239 68 68);
}

.expense-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

:root.dark .expense-item {
  background: linear-gradient(135deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.9) 100%);
}

.modern-gradient-header {
  background: linear-gradient(135deg, #dc2626 0%, #be123c 100%);
  padding: 1.5rem;
  margin: -1.5rem -1.5rem 0 -1.5rem;
  border-radius: 0.5rem 0.5rem 0 0;
}
</style>
