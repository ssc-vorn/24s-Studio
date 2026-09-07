<script setup lang="ts">
import { ArrowLeft, Eye, Redo2, Undo2, Upload } from 'lucide-vue-next'
import BuilderViewportSwitcher from './BuilderViewportSwitcher.vue'
import type { BuilderViewport } from '@/types/cms'

withDefaults(defineProps<{
  title: string
  version: number
  viewport: BuilderViewport
  canUndo: boolean
  canRedo: boolean
  saveState: 'idle' | 'saving' | 'saved' | 'error' | 'conflict'
  dirty: boolean
  publishing?: boolean
  canPublish?: boolean
}>(), { dirty: false, publishing: false, canPublish: false })

const emit = defineEmits<{
  back: []
  'update:viewport': [value: BuilderViewport]
  undo: []
  redo: []
  preview: []
  publish: []
}>()

const statusLabel = (state: 'idle' | 'saving' | 'saved' | 'error' | 'conflict', dirty: boolean) => {
  if (state === 'saving') return 'Saving…'
  if (state === 'conflict') return 'Conflict'
  if (state === 'error') return 'Save failed'
  if (dirty) return 'Unsaved changes'
  return 'Saved'
}
</script>

<template>
  <header class="flex min-h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4">
    <div class="flex min-w-0 items-center gap-3">
      <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Back" @click="emit('back')">
        <ArrowLeft class="h-4 w-4" />
      </button>
      <div class="min-w-0">
        <div class="truncate text-sm font-semibold text-slate-900">{{ title }}</div>
        <div class="text-xs text-slate-500">Version {{ version }}</div>
      </div>
    </div>

    <BuilderViewportSwitcher :model-value="viewport" @update:model-value="emit('update:viewport', $event)" />

    <div class="flex items-center gap-2">
      <span :class="['hidden text-xs sm:inline', saveState === 'error' || saveState === 'conflict' ? 'text-red-600' : 'text-slate-500']">
        {{ statusLabel(saveState, dirty) }}
      </span>
      <button type="button" :disabled="!canUndo" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Undo" @click="emit('undo')">
        <Undo2 class="h-4 w-4" />
      </button>
      <button type="button" :disabled="!canRedo" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Redo" @click="emit('redo')">
        <Redo2 class="h-4 w-4" />
      </button>
      <button type="button" class="hidden h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50 md:inline-flex" @click="emit('preview')">
        <Eye class="h-4 w-4" /> Preview
      </button>
      <button type="button" :disabled="publishing || !canPublish" class="inline-flex h-9 items-center gap-2 rounded-lg bg-slate-900 px-3 text-sm font-medium text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50" @click="emit('publish')">
        <Upload class="h-4 w-4" /> {{ publishing ? 'Publishing…' : 'Publish' }}
      </button>
    </div>
  </header>
</template>
