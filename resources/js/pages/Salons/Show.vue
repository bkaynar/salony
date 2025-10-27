<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import type { Salon } from '@/types'

defineProps<{
  salon: Salon
}>()
</script>

<template>
  <AppLayout>
    <template #header>
      <Heading>{{ salon.name }}</Heading>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            Welcome to {{ salon.name }}'s page!
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
