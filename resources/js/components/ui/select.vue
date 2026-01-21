<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const selectValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const selectClasses = computed(() =>
  cn(
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    props.error && 'border-destructive focus-visible:ring-destructive'
  )
)
</script>

<template>
  <select
    :class="selectClasses"
    :disabled="disabled"
    :value="selectValue"
    @change="selectValue = $event.target.value"
  >
    <slot />
  </select>
</template>
