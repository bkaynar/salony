<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Building2, Users, TrendingUp, Activity, Package, DollarSign } from 'lucide-vue-next'

const props = defineProps({
  stats: Object,
  recentSalons: Array,
  planStats: Array,
})

const breadcrumbItems = [
  { title: 'Admin Panel' },
  { title: 'Dashboard' },
]
</script>

<template>
  <Head title="Admin Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbItems">
    <div class="space-y-6 p-6 md:p-8">
      <!-- Header -->
      <div class="rounded-lg border bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-800 shadow-sm p-8">
        <div class="flex items-center gap-4">
          <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-800 shadow-sm">
            <Building2 class="w-8 h-8 text-slate-700 dark:text-slate-100" />
          </div>
          <div>
            <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 dark:text-white">
              Admin Dashboard
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 font-medium">
              Sistem genelinde istatistikler ve yönetim
            </p>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card class="border-2 border-gray-200 dark:border-slate-800">
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Salon</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.totalSalons }}</p>
              </div>
              <div class="p-3 rounded-xl bg-blue-500 shadow-lg">
                <Building2 class="w-6 h-6 text-white" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-2 border-gray-200 dark:border-slate-800">
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Aktif Salon</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.activeSalons }}</p>
              </div>
              <div class="p-3 rounded-xl bg-green-500 shadow-lg">
                <Activity class="w-6 h-6 text-white" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-2 border-gray-200 dark:border-slate-800">
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Kullanıcı</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">{{ stats.totalUsers }}</p>
              </div>
              <div class="p-3 rounded-xl bg-purple-500 shadow-lg">
                <Users class="w-6 h-6 text-white" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-2 border-gray-200 dark:border-slate-800">
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Toplam Gelir</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">₺{{ stats.totalRevenue.toFixed(0) }}</p>
              </div>
              <div class="p-3 rounded-xl bg-amber-500 shadow-lg">
                <TrendingUp class="w-6 h-6 text-white" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Salons & Plan Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Salons -->
        <Card class="border-2 border-gray-200 dark:border-slate-800">
          <CardHeader class="border-b border-gray-200 dark:border-slate-800">
            <CardTitle class="text-xl font-bold text-slate-900 dark:text-white">Son Eklenen Salonlar</CardTitle>
          </CardHeader>
          <CardContent class="p-6">
            <div v-if="recentSalons.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              Henüz salon eklenmemiş
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="salon in recentSalons"
                :key="salon.id"
                class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
              >
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white">{{ salon.name }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ salon.subdomain }}.salony.com</p>
                  <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ salon.created_at }}</p>
                </div>
                <div class="text-right">
                  <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                    {{ salon.plan_name || 'Plan Yok' }}
                  </span>
                  <p v-if="salon.subscription_ends_at" class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    Bitiş: {{ salon.subscription_ends_at }}
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Plan Statistics -->
        <Card class="border-2 border-gray-200 dark:border-slate-800">
          <CardHeader class="border-b border-gray-200 dark:border-slate-800">
            <CardTitle class="text-xl font-bold text-slate-900 dark:text-white">Plan İstatistikleri</CardTitle>
          </CardHeader>
          <CardContent class="p-6">
            <div v-if="planStats.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              Henüz plan eklenmemiş
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="plan in planStats"
                :key="plan.name"
                class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-slate-700"
              >
                <div class="flex items-center gap-3">
                  <div class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700">
                    <Package class="w-5 h-5 text-slate-700 dark:text-slate-300" />
                  </div>
                  <span class="font-semibold text-slate-900 dark:text-white">{{ plan.name }}</span>
                </div>
                <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ plan.count }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
