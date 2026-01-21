<script setup>
import { ref, computed, watch } from 'vue'
import { cn } from '@/lib/utils'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import Button from './button.vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue'])

const today = new Date()
const currentMonth = ref(today.getMonth())
const currentYear = ref(today.getFullYear())

// Sync calendar with selected date
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    try {
      const date = new Date(newValue + 'T00:00:00')
      if (!isNaN(date.getTime())) {
        currentMonth.value = date.getMonth()
        currentYear.value = date.getFullYear()
      }
    } catch (e) {
      // Ignore invalid dates
    }
  }
}, { immediate: true })

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const selectedDate = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const daysInMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
})

const firstDayOfMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value, 1).getDay()
})

const calendarDays = computed(() => {
  const days = []
  
  // Add empty cells for days before the first day of the month
  for (let i = 0; i < firstDayOfMonth.value; i++) {
    days.push(null)
  }
  
  // Add days of the month
  for (let day = 1; day <= daysInMonth.value; day++) {
    days.push(day)
  }
  
  return days
})

const isToday = (day) => {
  if (!day) return false
  const date = new Date(currentYear.value, currentMonth.value, day)
  return date.toDateString() === today.toDateString()
}

const isSelected = (day) => {
  if (!day || !selectedDate.value) return false
  const date = new Date(currentYear.value, currentMonth.value, day)
  const selected = new Date(selectedDate.value)
  return date.toDateString() === selected.toDateString()
}

const selectDate = (day) => {
  if (!day) return
  const date = new Date(currentYear.value, currentMonth.value, day)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const dayStr = String(date.getDate()).padStart(2, '0')
  selectedDate.value = `${year}-${month}-${dayStr}`
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

const goToToday = () => {
  currentMonth.value = today.getMonth()
  currentYear.value = today.getFullYear()
  selectDate(today.getDate())
}
</script>

<template>
  <div class="p-3">
    <div class="flex items-center justify-between mb-4">
      <Button
        variant="ghost"
        size="icon"
        @click="previousMonth"
        class="h-7 w-7"
      >
        <ChevronLeft class="h-4 w-4" />
      </Button>
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium">{{ monthNames[currentMonth] }} {{ currentYear }}</span>
      </div>
      <Button
        variant="ghost"
        size="icon"
        @click="nextMonth"
        class="h-7 w-7"
      >
        <ChevronRight class="h-4 w-4" />
      </Button>
    </div>
    
    <div class="grid grid-cols-7 gap-1 mb-2">
      <div
        v-for="day in dayNames"
        :key="day"
        class="text-center text-xs font-medium text-muted-foreground p-1"
      >
        {{ day }}
      </div>
    </div>
    
    <div class="grid grid-cols-7 gap-1">
      <button
        v-for="(day, index) in calendarDays"
        :key="index"
        :class="cn(
          'h-9 w-9 rounded-md text-sm font-normal transition-colors',
          !day && 'cursor-default',
          day && 'hover:bg-accent hover:text-accent-foreground cursor-pointer',
          isToday(day) && 'bg-accent font-semibold',
          isSelected(day) && 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground'
        )"
        :disabled="!day"
        @click="selectDate(day)"
      >
        {{ day || '' }}
      </button>
    </div>
    
    <div class="mt-4 pt-4 border-t">
      <Button
        variant="outline"
        size="sm"
        class="w-full"
        @click="goToToday"
      >
        Today
      </Button>
    </div>
  </div>
</template>
