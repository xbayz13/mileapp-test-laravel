<script setup>
import { cn } from '@/lib/utils'
</script>

<template>
  <div :class="cn('flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 px-6 pb-6')">
    <slot />
  </div>
</template>
