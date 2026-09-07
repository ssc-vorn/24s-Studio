<script setup lang="ts">
import { Monitor, Smartphone, Tablet } from 'lucide-vue-next'
import type { BuilderViewport } from '@/types/cms'

const props = defineProps<{ modelValue: BuilderViewport }>()
const emit = defineEmits<{ 'update:modelValue': [value: BuilderViewport] }>()

const items: { value: BuilderViewport; label: string; icon: typeof Monitor }[] = [
  { value: 'desktop', label: 'Desktop', icon: Monitor },
  { value: 'tablet', label: 'Tablet', icon: Tablet },
  { value: 'mobile', label: 'Mobile', icon: Smartphone },
]
</script>

<template>
  <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
    <button
      v-for="item in items"
      :key="item.value"
      type="button"
      :title="item.label"
      :aria-label="item.label"
      :class="[
        'inline-flex h-8 w-9 items-center justify-center rounded-md transition',
        props.modelValue === item.value ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900',
      ]"
      @click="emit('update:modelValue', item.value)"
    >
      <component :is="item.icon" class="h-4 w-4" />
    </button>
  </div>
</template>
