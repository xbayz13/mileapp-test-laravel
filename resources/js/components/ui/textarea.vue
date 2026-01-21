<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  placeholder: {
    type: String,
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
  rows: {
    type: Number,
    default: 3,
  },
})

const emit = defineEmits(['update:modelValue'])

const textareaValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const textareaClasses = computed(() =>
  cn(
    'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    props.error && 'border-destructive focus-visible:ring-destructive'
  )
)
</script>

<template>
  <textarea
    :class="textareaClasses"
    :placeholder="placeholder"
    :disabled="disabled"
    :rows="rows"
    :value="textareaValue"
    @input="textareaValue = $event.target.value"
  />
</template>
