<script setup>
import { cn } from '@/lib/utils'
</script>

<template>
  <h3 :class="cn('text-lg font-semibold leading-none tracking-tight')">
    <slot />
  </h3>
</template>
