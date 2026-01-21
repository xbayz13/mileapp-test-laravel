<script setup>
import { cn } from '@/lib/utils'
</script>

<template>
  <div :class="cn('flex flex-col space-y-1.5 text-center sm:text-left p-6 pb-4 relative')">
    <slot />
  </div>
</template>
