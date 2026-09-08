<script setup lang="ts">
import { ArrowLeft, Check, Eye, History, Redo2, Send, Undo2, Upload } from 'lucide-vue-next'
import BuilderViewportSwitcher from './BuilderViewportSwitcher.vue'
import type { BuilderViewport } from '@/types/cms'

withDefaults(defineProps<{
  title: string
  version: number
  status: string
  viewport: BuilderViewport
  canUndo: boolean
  canRedo: boolean
  saveState: 'idle' | 'saving' | 'saved' | 'error' | 'conflict'
  dirty: boolean
  workflowBusy?: boolean
  canSubmitReview?: boolean
  canApprove?: boolean
  canPublish?: boolean
}>(), { dirty: false, workflowBusy: false, canSubmitReview: false, canApprove: false, canPublish: false })

const emit = defineEmits<{
  back: []
  'update:viewport': [value: BuilderViewport]
  undo: []
  redo: []
  preview: []
  history: []
  'submit-review': []
  approve: []
  publish: []
}>()

const statusLabel = (state: 'idle' | 'saving' | 'saved' | 'error' | 'conflict', dirty: boolean) => {
  if (state === 'saving') return 'Saving…'
  if (state === 'conflict') return 'Conflict'
  if (state === 'error') return 'Save failed'
  if (dirty) return 'Unsaved changes'
  return 'Saved'
}
const workflowLabel = (status: string) => ({ review: 'In review', approved: 'Approved', published: 'Published' }[status] ?? 'Draft')
</script>

<template>
  <header class="flex min-h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4">
    <div class="flex min-w-0 items-center gap-3">
      <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Back" @click="emit('back')"><ArrowLeft class="h-4 w-4" /></button>
      <div class="min-w-0"><div class="truncate text-sm font-semibold text-slate-900">{{ title }}</div><div class="flex items-center gap-2 text-xs text-slate-500"><span>Version {{ version }}</span><span class="rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-700">{{ workflowLabel(status) }}</span></div></div>
    </div>
    <BuilderViewportSwitcher :model-value="viewport" @update:model-value="emit('update:viewport', $event)" />
    <div class="flex items-center gap-2">
      <span :class="['hidden text-xs sm:inline', saveState === 'error' || saveState === 'conflict' ? 'text-red-600' : 'text-slate-500']">{{ statusLabel(saveState, dirty) }}</span>
      <button type="button" :disabled="!canUndo" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Undo" @click="emit('undo')"><Undo2 class="h-4 w-4" /></button>
      <button type="button" :disabled="!canRedo" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Redo" @click="emit('redo')"><Redo2 class="h-4 w-4" /></button>
      <button type="button" class="hidden h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50 md:inline-flex" @click="emit('preview')"><Eye class="h-4 w-4" /> Preview</button>
      <button type="button" class="hidden h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50 lg:inline-flex" @click="emit('history')"><History class="h-4 w-4" /> History</button>
      <button v-if="status === 'draft'" type="button" :disabled="workflowBusy || !canSubmitReview" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50" @click="emit('submit-review')"><Send class="h-4 w-4" /> {{ workflowBusy ? 'Submitting…' : 'Submit for review' }}</button>
      <button v-else-if="status === 'review'" type="button" :disabled="workflowBusy || !canApprove" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50" @click="emit('approve')"><Check class="h-4 w-4" /> {{ workflowBusy ? 'Approving…' : 'Approve' }}</button>
      <button v-else-if="status === 'approved'" type="button" :disabled="workflowBusy || !canPublish" class="inline-flex h-9 items-center gap-2 rounded-lg bg-slate-900 px-3 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50" @click="emit('publish')"><Upload class="h-4 w-4" /> {{ workflowBusy ? 'Publishing…' : 'Publish' }}</button>
    </div>
  </header>
</template>
