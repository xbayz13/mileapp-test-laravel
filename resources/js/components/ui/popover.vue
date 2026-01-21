<script setup>
import { ref, computed, watch } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const open = () => {
  isOpen.value = true
}

const close = () => {
  isOpen.value = false
}

defineExpose({
  open,
  close,
})
</script>

<template>
  <div class="relative">
    <slot :open="open" :close="close" :isOpen="isOpen" />
  </div>
</template>
