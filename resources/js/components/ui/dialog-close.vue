<script setup>
import { X } from 'lucide-vue-next'
import Button from './button.vue'

const emit = defineEmits(['click'])

const handleClick = () => {
  emit('click')
}
</script>

<template>
  <Button
    variant="ghost"
    size="icon"
    class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
    @click="handleClick"
  >
    <X class="h-4 w-4" />
    <span class="sr-only">Close</span>
  </Button>
</template>
