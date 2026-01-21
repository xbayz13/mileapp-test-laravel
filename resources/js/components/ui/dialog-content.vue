<script setup>
import { cn } from '@/lib/utils'
</script>

<template>
  <div :class="cn('px-6 pb-6')">
    <slot />
  </div>
</template>
