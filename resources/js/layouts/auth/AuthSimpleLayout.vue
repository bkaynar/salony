<script setup lang="ts">
import { home } from '@/routes'
import { Link } from '@inertiajs/vue3'
import { Zap } from 'lucide-vue-next'

defineProps<{
    title?: string
    description?: string
}>()
</script>

<template>
    <div class="min-h-svh w-full lg:grid lg:grid-cols-2">
        <div class="flex items-center justify-center p-6 md:p-10">
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <Link :href="home()" class="flex items-center gap-2 font-medium">
                        <Zap class="h-8 w-8 text-blue-600" />
                        <span class="text-2xl font-bold text-gray-900">e-makas</span>
                    </Link>
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight">{{ title }}</h1>
                        <p class="text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
        <div class="hidden bg-muted lg:block">
            <img
                src="https://images.unsplash.com/photo-1599351542848-7a1915a43837?q=80&w=1974&auto=format&fit=crop"
                alt="Image"
                width="1920"
                height="1080"
                class="h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
            />
        </div>
    </div>
</template>