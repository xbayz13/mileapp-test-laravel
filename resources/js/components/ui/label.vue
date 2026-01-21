<script setup>
import { cn } from '@/lib/utils'

const props = defineProps({
  for: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <label
    :for="props.for"
    :class="cn(
      'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70'
    )"
  >
    <slot />
    <span v-if="required" class="text-destructive ml-1">*</span>
  </label>
</template>
