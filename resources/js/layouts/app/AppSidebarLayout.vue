<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { LogOut, AlertCircle } from 'lucide-vue-next';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const isImpersonating = computed(() => !!page.props.auth?.impersonated_by);
const salonName = computed(() => page.props.auth?.user?.salon?.name);

function leaveImpersonate() {
    router.post('/admin/leave-impersonate');
}
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <!-- Impersonation Banner -->
            <div v-if="isImpersonating" class="sticky top-0 z-50 bg-gradient-to-r from-amber-500 to-orange-500 text-white px-6 py-3 shadow-lg">
                <div class="flex items-center justify-between max-w-7xl mx-auto">
                    <div class="flex items-center gap-3">
                        <AlertCircle class="w-5 h-5" />
                        <div>
                            <p class="font-semibold">Admin Görünümü Aktif</p>
                            <p class="text-sm text-white/90">Şu anda <strong>{{ salonName }}</strong> salonu olarak giriş yaptınız</p>
                        </div>
                    </div>
                    <Button
                        @click="leaveImpersonate"
                        variant="outline"
                        size="sm"
                        class="bg-white text-orange-600 hover:bg-orange-50 border-white"
                    >
                        <LogOut class="w-4 h-4 mr-2" />
                        Admin Hesabına Dön
                    </Button>
                </div>
            </div>

            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
