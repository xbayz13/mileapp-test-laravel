<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { cn } from '@/lib/utils'
import { Calendar as CalendarIcon, X } from 'lucide-vue-next'
import Input from './input.vue'
import Button from './button.vue'
import Calendar from './calendar.vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Pick a date',
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

const isOpen = ref(false)
const popoverRef = ref(null)

const displayValue = computed(() => {
  if (!props.modelValue) return ''
  try {
    // Handle both YYYY-MM-DD format and date strings
    const dateStr = props.modelValue.includes(' ') 
      ? props.modelValue.split(' ')[0] 
      : props.modelValue
    const date = new Date(dateStr + 'T00:00:00')
    if (isNaN(date.getTime())) return props.modelValue
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  } catch {
    return props.modelValue
  }
})

const formattedValue = computed(() => {
  if (!props.modelValue) return ''
  // Return in YYYY-MM-DD format
  if (props.modelValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
    return props.modelValue
  }
  // Try to parse and format
  try {
    const date = new Date(props.modelValue)
    if (isNaN(date.getTime())) return ''
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  } catch {
    return ''
  }
})

const toggle = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
}

const close = () => {
  isOpen.value = false
}

const clear = (e) => {
  e.stopPropagation()
  emit('update:modelValue', '')
}

const handleClickOutside = (event) => {
  if (popoverRef.value && !popoverRef.value.contains(event.target)) {
    close()
  }
}

watch(() => props.modelValue, () => {
  if (props.modelValue) {
    close()
  }
})

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div ref="popoverRef" class="relative">
    <div class="relative">
      <Input
        :model-value="displayValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :error="error"
        readonly
        @click="toggle"
        class="cursor-pointer pr-10"
      />
      <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center gap-1">
        <button
          v-if="modelValue && !disabled"
          type="button"
          @click="clear"
          class="text-muted-foreground hover:text-foreground"
        >
          <X class="h-4 w-4" />
        </button>
        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
      </div>
    </div>
    
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-2 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-gray-200 dark:border-slate-700"
        @click.stop
      >
        <Calendar
          :model-value="formattedValue"
          @update:model-value="emit('update:modelValue', $event)"
        />
      </div>
    </Transition>
  </div>
</template>
