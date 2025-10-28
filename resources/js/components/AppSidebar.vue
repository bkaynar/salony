    <script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Calendar, Users, TrendingUp, UserCog, CalendarOff, Scissors, Clock, Package, CreditCard, Building2, Shield } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Check if user has admin role
const isAdmin = computed(() => {
    return user.value?.roles?.some((role: any) => role.name === 'admin');
});

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Randevular',
        href: '/dashboard/appointments',
        icon: Calendar,
    },
    {
        title: 'Müşteriler',
        href: '/dashboard/customers',
        icon: Users,
    },
    {
        title: 'Personel',
        href: '/dashboard/staff',
        icon: UserCog,
    },
    {
        title: 'Personel İzinleri',
        href: '/dashboard/time-offs',
        icon: CalendarOff,
    },
    {
        title: 'Hizmetler',
        href: '/dashboard/services',
        icon: Scissors,
    },
    // 'Çalışma Saatleri' removed per request
    {
        title: 'Ürünler',
        href: '/dashboard/products',
        icon: Package,
    },
    {
        title: 'Gelir Raporları',
        href: '/dashboard/reports',
        icon: TrendingUp,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
        icon: Shield,
    },
    {
        title: 'Paketler',
        href: '/admin/plans',
        icon: Package,
    },
    {
        title: 'Salonlar',
        href: '/admin/salons',
        icon: Building2,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain v-if="!isAdmin" :items="mainNavItems" />
            <NavMain v-if="isAdmin" :items="adminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
