<script setup lang="ts">
import type { BuilderJsonValue } from '@/types/cms'

const props = defineProps<{ label: string; modelValue: BuilderJsonValue; type?: 'text' | 'url' | 'number' | 'textarea' }>()
const emit = defineEmits<{ 'update:modelValue': [value: BuilderJsonValue] }>()
</script>

<template>
  <label class="block">
    <span class="text-xs font-medium text-slate-600">{{ label }}</span>
    <textarea v-if="type === 'textarea'" :value="String(props.modelValue ?? '')" rows="4" class="mt-1.5 w-full rounded-md border-slate-200 text-sm focus:border-slate-900 focus:ring-slate-900" @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)" />
    <input v-else :type="type ?? 'text'" :value="String(props.modelValue ?? '')" class="mt-1.5 w-full rounded-md border-slate-200 text-sm focus:border-slate-900 focus:ring-slate-900" @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)" />
  </label>
</template>
