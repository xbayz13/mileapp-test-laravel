<script setup>
/**
 * TaskForm Component - Single Responsibility: Render task form UI only
 * Dependency Inversion: Uses composables (abstractions) instead of concrete implementations
 */
import Button from './ui/button.vue'
import Input from './ui/input.vue'
import Textarea from './ui/textarea.vue'
import Select from './ui/select.vue'
import Label from './ui/label.vue'
import DatePicker from './ui/date-picker.vue'
import { Loader2 } from 'lucide-vue-next'
import { useTaskForm } from '../composables/useTaskForm'
import { useTaskConstants } from '../composables/useTaskConstants'

const props = defineProps({
  task: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const { formData, errors, validate, getFormData, resetForm } = useTaskForm(props.task)
const { statusOptions, priorityOptions } = useTaskConstants()

const handleSubmit = () => {
  if (!validate()) {
    return
  }
  
  emit('submit', getFormData())
}

const handleCancel = () => {
  resetForm()
  emit('cancel')
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-4">
    <!-- Title -->
    <div class="space-y-2">
      <Label for="title" :required="true">Title</Label>
      <Input
        id="title"
        v-model="formData.title"
        placeholder="Enter task title"
        :error="!!errors.title"
        :disabled="loading"
      />
      <p v-if="errors.title" class="text-sm text-destructive">{{ errors.title }}</p>
    </div>

    <!-- Description -->
    <div class="space-y-2">
      <Label for="description">Description</Label>
      <Textarea
        id="description"
        v-model="formData.description"
        placeholder="Enter task description"
        :rows="4"
        :disabled="loading"
      />
    </div>

    <!-- Status and Priority -->
    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label for="status">Status</Label>
        <Select
          id="status"
          v-model="formData.status"
          :disabled="loading"
        >
          <option
            v-for="option in statusOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </Select>
      </div>

      <div class="space-y-2">
        <Label for="priority">Priority</Label>
        <Select
          id="priority"
          v-model="formData.priority"
          :disabled="loading"
        >
          <option
            v-for="option in priorityOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </Select>
      </div>
    </div>

    <!-- Due Date -->
    <div class="space-y-2">
      <Label for="due_date">Due Date</Label>
      <DatePicker
        id="due_date"
        v-model="formData.due_date"
        placeholder="Select due date"
        :disabled="loading"
      />
    </div>

    <!-- Error Message -->
    <div v-if="errors.general" class="rounded-md bg-destructive/10 border border-destructive/20 p-3">
      <p class="text-sm text-destructive font-medium">{{ errors.general }}</p>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4">
      <Button
        type="button"
        variant="outline"
        @click="handleCancel"
        :disabled="loading"
      >
        Cancel
      </Button>
      <Button
        type="submit"
        :disabled="loading"
      >
        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
        {{ task ? 'Update' : 'Create' }} Task
      </Button>
    </div>
  </form>
</template>
