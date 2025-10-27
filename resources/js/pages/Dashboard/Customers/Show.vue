<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { ArrowLeft, Phone, Mail, Calendar, DollarSign, CheckCircle, XCircle, Clock } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    customer: Object,
    appointments: Array,
    stats: Object,
})

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('tr-TR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date)
}

const formatDateTime = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('tr-TR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

const formatPrice = (price) => {
    if (!price) return '0 ₺'
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
    }).format(price)
}

const getStatusColor = (status) => {
    const colors = {
        confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        no_show: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    }
    return colors[status] || colors.confirmed
}

const getStatusText = (status) => {
    const texts = {
        confirmed: 'Onaylandı',
        completed: 'Tamamlandı',
        cancelled: 'İptal Edildi',
        no_show: 'Gelmedi',
    }
    return texts[status] || status
}

const upcomingAppointments = computed(() => {
    const now = new Date()
    return props.appointments.filter(apt => new Date(apt.start_time) > now)
})

const pastAppointments = computed(() => {
    const now = new Date()
    return props.appointments.filter(apt => new Date(apt.start_time) <= now)
})
</script>

<template>
    <AppLayout title="Müşteri Detayı">
        <div class="container mx-auto p-6 space-y-6">
            <!-- Back Button -->
            <div>
                <Link :href="'/dashboard/customers'">
                    <Button variant="ghost" class="gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Müşterilere Dön
                    </Button>
                </Link>
            </div>

            <!-- Customer Info Header -->
            <div class="customer-header-gradient rounded-lg p-8 text-white">
                <div class="flex items-start justify-between">
                    <div class="space-y-3">
                        <h1 class="text-3xl font-bold">{{ customer.name }}</h1>
                        <div class="flex flex-wrap gap-4 text-white/90">
                            <div v-if="customer.phone" class="flex items-center gap-2">
                                <Phone class="h-4 w-4" />
                                <span>{{ customer.phone }}</span>
                            </div>
                            <div v-if="customer.email" class="flex items-center gap-2">
                                <Mail class="h-4 w-4" />
                                <span>{{ customer.email }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Calendar class="h-4 w-4" />
                                <span>Kayıt: {{ formatDate(customer.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="customer.notes" class="mt-4 p-4 bg-white/10 rounded-lg backdrop-blur-sm">
                    <p class="text-sm text-white/90">{{ customer.notes }}</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <Card class="stat-card">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Toplam Harcama
                        </CardTitle>
                        <DollarSign class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ formatPrice(stats.total_spent) }}
                        </div>
                    </CardContent>
                </Card>

                <Card class="stat-card">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Toplam Randevu
                        </CardTitle>
                        <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ stats.total_appointments }}
                        </div>
                    </CardContent>
                </Card>

                <Card class="stat-card">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Tamamlanan
                        </CardTitle>
                        <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ stats.completed_appointments }}
                        </div>
                    </CardContent>
                </Card>

                <Card class="stat-card">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            İptal Edilen
                        </CardTitle>
                        <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ stats.cancelled_appointments }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Upcoming Appointments -->
            <Card v-if="upcomingAppointments.length > 0" class="modern-card">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Yaklaşan Randevular
                    </CardTitle>
                    <CardDescription>Gelecek randevular</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="appointment in upcomingAppointments"
                            :key="appointment.id"
                            class="appointment-card p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-gray-500" />
                                        <span class="font-medium">{{ formatDateTime(appointment.start_time) }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Personel: {{ appointment.staff_name }}
                                    </div>
                                </div>
                                <Badge :class="getStatusColor(appointment.status)">
                                    {{ getStatusText(appointment.status) }}
                                </Badge>
                            </div>

                            <div class="space-y-2">
                                <div class="font-medium text-sm text-gray-700 dark:text-gray-300">Hizmetler:</div>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="(service, idx) in appointment.services"
                                        :key="idx"
                                        variant="outline"
                                        class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20"
                                    >
                                        {{ service.name }} - {{ formatPrice(service.price) }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Süre: {{ appointment.total_duration }} dk
                                </div>
                                <div class="font-bold text-lg text-green-600 dark:text-green-400">
                                    {{ formatPrice(appointment.total_price) }}
                                </div>
                            </div>

                            <div v-if="appointment.notes" class="mt-3 p-2 bg-gray-50 dark:bg-gray-800 rounded text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">Not:</span> {{ appointment.notes }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Past Appointments History -->
            <Card class="modern-card">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        Randevu Geçmişi
                    </CardTitle>
                    <CardDescription>Tüm geçmiş randevular</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="pastAppointments.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Henüz geçmiş randevu bulunmuyor
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="appointment in pastAppointments"
                            :key="appointment.id"
                            class="appointment-card p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-gray-500" />
                                        <span class="font-medium">{{ formatDateTime(appointment.start_time) }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Personel: {{ appointment.staff_name }}
                                    </div>
                                </div>
                                <Badge :class="getStatusColor(appointment.status)">
                                    {{ getStatusText(appointment.status) }}
                                </Badge>
                            </div>

                            <div class="space-y-2">
                                <div class="font-medium text-sm text-gray-700 dark:text-gray-300">Hizmetler:</div>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="(service, idx) in appointment.services"
                                        :key="idx"
                                        variant="outline"
                                        class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20"
                                    >
                                        {{ service.name }} - {{ formatPrice(service.price) }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Süre: {{ appointment.total_duration }} dk
                                </div>
                                <div class="font-bold text-lg text-green-600 dark:text-green-400">
                                    {{ formatPrice(appointment.total_price) }}
                                </div>
                            </div>

                            <div v-if="appointment.notes" class="mt-3 p-2 bg-gray-50 dark:bg-gray-800 rounded text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">Not:</span> {{ appointment.notes }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.customer-header-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 10px 40px -10px rgba(102, 126, 234, 0.4);
}

:root.dark .customer-header-gradient {
    background: linear-gradient(135deg, #4c51bf 0%, #553c9a 100%);
}

.stat-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
}

:root.dark .stat-card {
    border-color: rgba(255, 255, 255, 0.1);
}

.modern-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

:root.dark .modern-card {
    border-color: rgba(255, 255, 255, 0.1);
}

.appointment-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
    transition: all 0.3s ease;
}

.appointment-card:hover {
    transform: translateX(4px);
    border-color: rgba(102, 126, 234, 0.5);
}

:root.dark .appointment-card {
    background: linear-gradient(135deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.9) 100%);
}

:root.dark .appointment-card:hover {
    border-color: rgba(102, 126, 234, 0.5);
}
</style>
