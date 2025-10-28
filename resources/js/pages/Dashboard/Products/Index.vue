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
import { Package, Edit, Trash2, Plus, DollarSign, TrendingUp, Activity, Box, Barcode, ShoppingBag, AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  products: { type: Array, default: () => [] },
})

const breadcrumbItems = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Ürünler' },
]

// Dialog states
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const selectedProduct = ref(null)

// Forms
const createForm = useForm({
  name: '',
  sku: '',
  stock_level: 0,
  price: 0,
  cost: 0,
})

const editForm = useForm({
  name: '',
  sku: '',
  stock_level: 0,
  price: 0,
  cost: 0,
})

// Search & sort
const search = ref('')
const sortBy = ref('name')

const filteredProducts = computed(() => {
  let list = Array.isArray(props.products) ? props.products.slice() : []
  const q = (search.value || '').toString().trim().toLowerCase()
  if (q) {
    list = list.filter(p => {
      const name = (p.name || '').toString().toLowerCase()
      const sku = (p.sku || '').toString().toLowerCase()
      const price = (p.price || '').toString().toLowerCase()
      return name.includes(q) || sku.includes(q) || price.includes(q)
    })
  }

  if (sortBy.value === 'price') {
    list.sort((a, b) => Number(a.price || 0) - Number(b.price || 0))
  } else if (sortBy.value === 'stock') {
    list.sort((a, b) => Number(a.stock_level || 0) - Number(b.stock_level || 0))
  } else {
    list.sort((a, b) => (a.name || '').toString().localeCompare((b.name || '').toString()))
  }

  return list
})

// Stats
const stats = computed(() => {
  const total = Array.isArray(props.products) ? props.products.length : 0
  const totalStock = Array.isArray(props.products) ? props.products.reduce((sum, p) => sum + Number(p.stock_level || 0), 0) : 0
  const totalValue = Array.isArray(props.products) ? props.products.reduce((sum, p) => sum + (Number(p.price || 0) * Number(p.stock_level || 0)), 0) : 0
  const lowStock = Array.isArray(props.products) ? props.products.filter(p => Number(p.stock_level || 0) < 10).length : 0

  return {
    total,
    totalStock,
    totalValue,
    lowStock,
  }
})

// Dialog helpers
function openCreateDialog() {
  createForm.reset()
  isCreateDialogOpen.value = true
}

function submitCreate() {
  createForm.post('/dashboard/products', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

function openEditDialog(product) {
  selectedProduct.value = product
  editForm.reset()
  editForm.fill({
    name: product.name || '',
    sku: product.sku || '',
    stock_level: product.stock_level || 0,
    price: product.price || 0,
    cost: product.cost || 0,
  })
  isEditDialogOpen.value = true
}

function submitEdit() {
  if (!selectedProduct.value) return
  editForm.put(`/dashboard/products/${selectedProduct.value.id}`, {
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
    },
  })
}

function openDeleteDialog(product) {
  selectedProduct.value = product
  isDeleteDialogOpen.value = true
}

function submitDelete() {
  if (!selectedProduct.value) return
  router.delete(`/dashboard/products/${selectedProduct.value.id}`, {
    onSuccess: () => {
      isDeleteDialogOpen.value = false
    },
  })
}

function getStockStatus(stock) {
  if (stock === 0) return { label: 'Tükendi', class: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 border border-red-300 dark:border-red-700' }
  if (stock < 10) return { label: 'Düşük Stok', class: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700' }
  return { label: 'Stokta', class: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 border border-green-300 dark:border-green-700' }
}
</script>

<template>
  <Head title="Ürünler" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="space-y-6 p-6 md:p-8">
      <!-- Header -->
      <div class="rounded-lg border bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="relative p-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
              <div class="relative">
                <div class="absolute inset-0 bg-slate-50 dark:bg-slate-800 rounded-2xl opacity-20"></div>
                <div class="relative p-4 rounded-2xl bg-slate-100 dark:bg-slate-800 shadow-sm">
                  <Package class="w-8 h-8 text-slate-700 dark:text-slate-100" />
                </div>
              </div>
              <div>
                <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 dark:text-white">
                  Ürünler
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 font-medium">
                  Envanter ve stok yönetimi
                </p>
              </div>
            </div>
            <Button
              @click="openCreateDialog()"
              size="lg"
              class="bg-slate-800 text-white dark:bg-slate-50 dark:text-slate-900 hover:opacity-95 shadow-sm transition-all duration-150 font-semibold"
            >
              <Plus class="w-5 h-5 mr-2" />
              Yeni Ürün Ekle
            </Button>
          </div>

          <!-- Stats Overview -->
          <div v-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-8">
            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition-shadow duration-150">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Ürün</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.total }}</p>
                </div>
                <div class="p-3 rounded-xl bg-emerald-500 shadow-lg">
                  <Package class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500"></div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition-shadow duration-150">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Stok</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.totalStock }}</p>
                </div>
                <div class="p-3 rounded-xl bg-blue-500 shadow-lg">
                  <Box class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500"></div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition-shadow duration-150">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Değer</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">₺{{ stats.totalValue.toFixed(0) }}</p>
                </div>
                <div class="p-3 rounded-xl bg-amber-500 shadow-lg">
                  <TrendingUp class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-500"></div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition-shadow duration-150">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Düşük Stok</p>
                  <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.lowStock }}</p>
                </div>
                <div class="p-3 rounded-xl bg-red-500 shadow-lg">
                  <AlertTriangle class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-500"></div>
            </div>
          </div>

          <!-- Search & Filter -->
          <div class="mt-6 flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <Input
                v-model="search"
                placeholder="🔍 Ürün ara (ad, SKU, fiyat)"
                class="bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700"
              />
            </div>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sırala:</label>
                <select
                  v-model="sortBy"
                  class="bg-transparent border-0 text-sm font-medium text-gray-900 dark:text-white focus:ring-0 cursor-pointer"
                >
                  <option value="name">Ada göre</option>
                  <option value="price">Fiyata göre</option>
                  <option value="stock">Stok seviyesine göre</option>
                </select>
              </div>
              <Button
                variant="outline"
                @click="search = ''"
                class="bg-white dark:bg-slate-900"
              >
                Temizle
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="products.length === 0">
        <div class="relative overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-slate-900">
          <div class="relative flex flex-col items-center justify-center py-20">
            <div class="p-8 rounded-2xl bg-slate-100 dark:bg-slate-800 mb-4">
              <Package class="w-16 h-16 text-slate-600 dark:text-slate-400" />
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              Henüz Ürün Eklenmemiş
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 text-center max-w-md">
              Salonunuzda kullandığınız ürünleri ekleyerek stok takibi yapın
            </p>
            <Button
              @click="openCreateDialog()"
              class="bg-slate-800 text-white dark:bg-slate-50 dark:text-slate-900 hover:opacity-95"
              size="lg"
            >
              <Plus class="w-5 h-5 mr-2" />
              İlk Ürünü Ekle
            </Button>
          </div>
        </div>
      </div>

      <div v-else-if="filteredProducts.length === 0" class="grid grid-cols-1">
        <div class="relative overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-slate-900 p-12">
          <div class="flex flex-col items-center justify-center">
            <div class="p-6 rounded-2xl bg-slate-100 dark:bg-slate-800 mb-4">
              <Package class="w-12 h-12 text-slate-500 dark:text-slate-400" />
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">Aramanıza uygun ürün bulunamadı.</p>
          </div>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
          v-for="product in filteredProducts"
          :key="product.id"
          class="relative overflow-hidden border-2 shadow border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900"
        >
          <CardHeader class="relative pb-4 pt-4 space-y-2 px-6">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-white">
                  <ShoppingBag class="w-6 h-6" />
                </div>
                <div>
                  <CardTitle class="text-lg font-semibold text-slate-900 dark:text-white leading-tight">
                    {{ product.name }}
                  </CardTitle>
                  <Badge
                    v-if="product.sku"
                    class="mt-2 text-sm font-medium px-2 py-0.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                  >
                    <Barcode class="w-3 h-3 mr-1" />
                    {{ product.sku }}
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Stock Status Badge -->
            <div class="flex items-center gap-2">
              <Badge :class="getStockStatus(product.stock_level).class" class="font-semibold px-3 py-1">
                {{ getStockStatus(product.stock_level).label }}
              </Badge>
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ product.stock_level }} adet</span>
            </div>
          </CardHeader>

          <CardContent class="relative space-y-5 pb-6 px-6">
            <!-- Price & Cost Grid -->
            <div class="grid grid-cols-2 gap-4">
              <div class="relative rounded-md p-3 bg-transparent border border-gray-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Satış</span>
                  <DollarSign class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ product.price.toFixed(2) }}<span class="text-base">₺</span></div>
              </div>

              <div class="relative rounded-md p-3 bg-transparent border border-gray-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Maliyet</span>
                  <TrendingUp class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ product.cost.toFixed(2) }}<span class="text-base">₺</span></div>
              </div>
            </div>

            <!-- Profit Margin -->
            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
              <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Kar Marjı</span>
                <span class="text-lg font-bold text-slate-900 dark:text-white">
                  {{ product.price > 0 ? (((product.price - product.cost) / product.price) * 100).toFixed(1) : 0 }}%
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 pt-2">
              <Button
                @click="openEditDialog(product)"
                variant="outline"
                size="sm"
                class="flex-1 font-medium"
              >
                <Edit class="w-4 h-4 mr-1.5" />
                Düzenle
              </Button>
              <Button
                @click="openDeleteDialog(product)"
                variant="outline"
                size="sm"
                class="font-medium text-red-600"
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
            Yeni Ürün Ekle
          </DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitCreate" class="space-y-5 pt-4">
          <div>
            <Label for="create-name">Ürün Adı *</Label>
            <Input
              id="create-name"
              v-model="createForm.name"
              placeholder="Örn: Saç Spreyi, Şampuan..."
              required
            />
            <p v-if="createForm.errors.name" class="text-sm text-red-600 mt-1">{{ createForm.errors.name }}</p>
          </div>

          <div>
            <Label for="create-sku">SKU / Stok Kodu</Label>
            <Input
              id="create-sku"
              v-model="createForm.sku"
              placeholder="Örn: PRD-001"
            />
            <p v-if="createForm.errors.sku" class="text-sm text-red-600 mt-1">{{ createForm.errors.sku }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label for="create-stock">Stok *</Label>
              <Input
                id="create-stock"
                type="number"
                v-model="createForm.stock_level"
                placeholder="0"
                required
              />
              <p v-if="createForm.errors.stock_level" class="text-sm text-red-600 mt-1">{{ createForm.errors.stock_level }}</p>
            </div>

            <div>
              <Label for="create-price">Satış Fiyatı (₺) *</Label>
              <Input
                id="create-price"
                type="number"
                step="0.01"
                v-model="createForm.price"
                placeholder="0.00"
                required
              />
              <p v-if="createForm.errors.price" class="text-sm text-red-600 mt-1">{{ createForm.errors.price }}</p>
            </div>

            <div>
              <Label for="create-cost">Maliyet (₺) *</Label>
              <Input
                id="create-cost"
                type="number"
                step="0.01"
                v-model="createForm.cost"
                placeholder="0.00"
                required
              />
              <p v-if="createForm.errors.cost" class="text-sm text-red-600 mt-1">{{ createForm.errors.cost }}</p>
            </div>
          </div>

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
      <DialogContent class="max-w-2xl">
        <DialogHeader class="pb-4 border-b border-gray-200 dark:border-slate-800">
          <DialogTitle class="text-2xl font-bold text-slate-900 dark:text-white">
            Ürün Düzenle
          </DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitEdit" class="space-y-5 pt-4">
          <div>
            <Label for="edit-name">Ürün Adı *</Label>
            <Input
              id="edit-name"
              v-model="editForm.name"
              placeholder="Örn: Saç Spreyi, Şampuan..."
              required
            />
            <p v-if="editForm.errors.name" class="text-sm text-red-600 mt-1">{{ editForm.errors.name }}</p>
          </div>

          <div>
            <Label for="edit-sku">SKU / Stok Kodu</Label>
            <Input
              id="edit-sku"
              v-model="editForm.sku"
              placeholder="Örn: PRD-001"
            />
            <p v-if="editForm.errors.sku" class="text-sm text-red-600 mt-1">{{ editForm.errors.sku }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label for="edit-stock">Stok *</Label>
              <Input
                id="edit-stock"
                type="number"
                v-model="editForm.stock_level"
                placeholder="0"
                required
              />
              <p v-if="editForm.errors.stock_level" class="text-sm text-red-600 mt-1">{{ editForm.errors.stock_level }}</p>
            </div>

            <div>
              <Label for="edit-price">Satış Fiyatı (₺) *</Label>
              <Input
                id="edit-price"
                type="number"
                step="0.01"
                v-model="editForm.price"
                placeholder="0.00"
                required
              />
              <p v-if="editForm.errors.price" class="text-sm text-red-600 mt-1">{{ editForm.errors.price }}</p>
            </div>

            <div>
              <Label for="edit-cost">Maliyet (₺) *</Label>
              <Input
                id="edit-cost"
                type="number"
                step="0.01"
                v-model="editForm.cost"
                placeholder="0.00"
                required
              />
              <p v-if="editForm.errors.cost" class="text-sm text-red-600 mt-1">{{ editForm.errors.cost }}</p>
            </div>
          </div>

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
      <DialogContent class="max-w-md">
        <DialogHeader class="pb-4 border-b border-gray-200 dark:border-slate-800">
          <DialogTitle class="text-2xl font-bold text-red-600 dark:text-red-400">
            Ürünü Sil
          </DialogTitle>
        </DialogHeader>

        <div class="py-4">
          <div class="p-4 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900">
            <p class="text-gray-700 dark:text-gray-300 mb-2">
              Aşağıdaki ürünü kalıcı olarak silmek istediğinize emin misiniz?
            </p>
            <div class="flex items-center gap-2 mt-3 p-3 rounded-lg bg-white dark:bg-slate-900 border border-red-300 dark:border-red-800">
              <Package class="w-5 h-5 text-red-600 dark:text-red-400" />
              <strong class="text-red-700 dark:text-red-300">{{ selectedProduct?.name }}</strong>
            </div>
            <p class="text-sm text-red-600 dark:text-red-400 mt-3">
              ⚠️ Bu işlem geri alınamaz ve tüm stok bilgileri silinecektir!
            </p>
          </div>
        </div>

        <DialogFooter class="gap-2">
          <Button
            variant="outline"
            @click="isDeleteDialogOpen = false"
            class="border-2"
          >
            İptal
          </Button>
          <Button
            variant="destructive"
            @click="submitDelete()"
          >
            <Trash2 class="w-4 h-4 mr-2" />
            Evet, Sil
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
