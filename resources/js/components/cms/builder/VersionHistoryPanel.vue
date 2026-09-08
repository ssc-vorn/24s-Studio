<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { Check, Clock3, GitCompareArrows, History, RotateCcw, X } from 'lucide-vue-next'
import type { PageSection } from '@/types/cms'

interface VersionUser { id: number; name: string }
interface VersionItem {
  id: string
  version: number
  revision: number
  status: string
  created_by: number | null
  creator?: VersionUser | null
  created_at: string | null
  published_at: string | null
  sections?: PageSection[]
}

const props = defineProps<{
  organizationId: string
  pageId: string
  currentVersionId: string
  currentVersion: number
}>()

const emit = defineEmits<{ close: []; restored: [version: VersionItem] }>()

const versions = ref<VersionItem[]>([])
const loading = ref(true)
const error = ref('')
const restoring = ref<string | null>(null)
const compareFrom = ref<string | null>(null)
const compareTo = ref<string | null>(null)
const compareLoading = ref(false)
const compareError = ref('')
const compared = ref<{ left: VersionItem; right: VersionItem } | null>(null)

const endpoint = computed(() => `/api/v1/organizations/${props.organizationId}/pages/${props.pageId}/versions`)

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get<{ data: VersionItem[] }>(endpoint.value, { params: { per_page: 50 } })
    versions.value = response.data.data
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Unable to load version history.'
  } finally {
    loading.value = false
  }
}

const statusLabel = (status: string) => ({ draft: 'Draft', review: 'In review', approved: 'Approved', published: 'Published' }[status] ?? status)
const statusClass = (status: string) => ({
  draft: 'bg-slate-100 text-slate-700',
  review: 'bg-amber-100 text-amber-800',
  approved: 'bg-blue-100 text-blue-800',
  published: 'bg-emerald-100 text-emerald-800',
}[status] ?? 'bg-slate-100 text-slate-700')
const formatDate = (value: string | null) => value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—'
const creatorName = (item: VersionItem) => item.creator?.name ?? 'System'

const selectCompare = (id: string) => {
  if (compareFrom.value === id) compareFrom.value = null
  else if (compareTo.value === id) compareTo.value = null
  else if (!compareFrom.value) compareFrom.value = id
  else compareTo.value = id
  compared.value = null
}

const compare = async () => {
  if (!compareFrom.value || !compareTo.value) return
  compareLoading.value = true
  compareError.value = ''
  try {
    const [leftResponse, rightResponse] = await Promise.all([
      axios.get<{ data: VersionItem }>(`${endpoint.value}/${compareFrom.value}`),
      axios.get<{ data: VersionItem }>(`${endpoint.value}/${compareTo.value}`),
    ])
    compared.value = { left: leftResponse.data.data, right: rightResponse.data.data }
  } catch (e) {
    compareError.value = e instanceof Error ? e.message : 'Unable to compare versions.'
  } finally {
    compareLoading.value = false
  }
}

const sectionMap = (items: PageSection[] = []) => new Map(items.map((item) => [item.id, item]))
const diffRows = computed(() => {
  if (!compared.value) return []
  const left = sectionMap(compared.value.left.sections)
  const right = sectionMap(compared.value.right.sections)
  const ids = [...new Set([...left.keys(), ...right.keys()])]
  return ids.map((id) => {
    const a = left.get(id)
    const b = right.get(id)
    const changed = JSON.stringify(a) !== JSON.stringify(b)
    return { id, before: a, after: b, kind: !a ? 'added' : !b ? 'removed' : changed ? 'changed' : 'same' }
  }).filter((row) => row.kind !== 'same')
})

const restore = async (item: VersionItem) => {
  if (item.id === props.currentVersionId || restoring.value) return
  if (!window.confirm(`Restore Version ${item.version}? This creates a new draft version and keeps the existing history.`)) return
  restoring.value = item.id
  error.value = ''
  try {
    const response = await axios.post<{ data: VersionItem }>(`${endpoint.value}/${item.id}/restore`)
    emit('restored', response.data.data)
    await load()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Unable to restore this version.'
  } finally {
    restoring.value = null
  }
}

onMounted(load)
</script>

<template>
  <div class="fixed inset-0 z-50 flex justify-end bg-slate-950/30" role="dialog" aria-modal="true" aria-label="Version history">
    <aside class="flex h-full w-full max-w-xl flex-col border-l border-slate-200 bg-white shadow-2xl">
      <header class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
        <div>
          <div class="flex items-center gap-2 text-base font-semibold text-slate-950"><History class="h-4 w-4" /> Version history</div>
          <p class="mt-1 text-xs text-slate-500">Review, compare and restore page versions without rewriting history.</p>
        </div>
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Close" @click="emit('close')"><X class="h-4 w-4" /></button>
      </header>

      <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
        <div class="flex items-center justify-between gap-3">
          <div class="text-xs text-slate-600">Select two versions to compare.</div>
          <button type="button" :disabled="!compareFrom || !compareTo || compareLoading" class="inline-flex h-8 items-center gap-2 rounded-lg bg-slate-900 px-3 text-xs font-medium text-white disabled:opacity-40" @click="compare"><GitCompareArrows class="h-3.5 w-3.5" /> {{ compareLoading ? 'Comparing…' : 'Compare' }}</button>
        </div>
        <div v-if="compareFrom || compareTo" class="mt-2 flex gap-2 text-[11px]">
          <span v-if="compareFrom" class="rounded-full bg-blue-100 px-2 py-1 text-blue-800">A selected</span>
          <span v-if="compareTo" class="rounded-full bg-violet-100 px-2 py-1 text-violet-800">B selected</span>
        </div>
      </div>

      <div class="min-h-0 flex-1 overflow-y-auto">
        <div v-if="loading" class="p-6 text-sm text-slate-500">Loading history…</div>
        <div v-else-if="!versions.length" class="p-6 text-sm text-slate-500">No versions found.</div>
        <div v-else class="divide-y divide-slate-100">
          <article v-for="item in versions" :key="item.id" class="px-5 py-4" :class="item.id === currentVersionId ? 'bg-slate-50' : ''">
            <div class="flex items-start gap-3">
              <button type="button" class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:border-slate-400" :class="(compareFrom === item.id || compareTo === item.id) ? 'ring-2 ring-slate-900' : ''" :aria-label="`Select Version ${item.version} for comparison`" @click="selectCompare(item.id)">
                <Check v-if="compareFrom === item.id || compareTo === item.id" class="h-4 w-4" />
                <Clock3 v-else class="h-4 w-4" />
              </button>
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="text-sm font-semibold text-slate-950">Version {{ item.version }}</span>
                  <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span>
                  <span v-if="item.id === currentVersionId" class="rounded-full bg-slate-900 px-2 py-0.5 text-[11px] font-medium text-white">Current</span>
                </div>
                <div class="mt-1 text-xs text-slate-500">{{ creatorName(item) }} · {{ formatDate(item.created_at) }} · revision {{ item.revision }}</div>
                <div class="mt-3 flex flex-wrap gap-2">
                  <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50" @click="selectCompare(item.id)"><GitCompareArrows class="h-3.5 w-3.5" /> {{ compareFrom === item.id || compareTo === item.id ? 'Selected' : 'Compare' }}</button>
                  <button v-if="item.id !== currentVersionId" type="button" :disabled="restoring === item.id" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50" @click="restore(item)"><RotateCcw class="h-3.5 w-3.5" /> {{ restoring === item.id ? 'Restoring…' : 'Restore' }}</button>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>

      <div v-if="error" class="border-t border-red-200 bg-red-50 px-5 py-3 text-xs text-red-700">{{ error }}</div>
    </aside>

    <div v-if="compared" class="absolute inset-0 flex items-center justify-center bg-slate-950/40 p-4 sm:p-8">
      <section class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-label="Compare versions">
        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <div><h2 class="text-base font-semibold text-slate-950">Compare Version {{ compared.left.version }} → Version {{ compared.right.version }}</h2><p class="mt-1 text-xs text-slate-500">{{ diffRows.length }} changed section{{ diffRows.length === 1 ? '' : 's' }}</p></div>
          <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click="compared = null"><X class="h-4 w-4" /></button>
        </header>
        <div v-if="compareError" class="border-b border-red-200 bg-red-50 px-5 py-3 text-xs text-red-700">{{ compareError }}</div>
        <div class="min-h-0 flex-1 overflow-y-auto p-5">
          <div v-if="!diffRows.length" class="rounded-xl border border-slate-200 p-8 text-center text-sm text-slate-500">No section-level changes detected.</div>
          <div v-else class="space-y-3">
            <article v-for="row in diffRows" :key="row.id" class="overflow-hidden rounded-xl border border-slate-200">
              <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-2 text-xs"><span class="font-medium text-slate-700">{{ row.id }}</span><span class="font-semibold uppercase tracking-wide" :class="row.kind === 'added' ? 'text-emerald-700' : row.kind === 'removed' ? 'text-red-700' : 'text-amber-700'">{{ row.kind }}</span></div>
              <div class="grid gap-px bg-slate-200 md:grid-cols-2"><pre class="max-h-72 overflow-auto bg-white p-4 text-[11px] leading-5 text-slate-600">{{ row.before ? JSON.stringify(row.before, null, 2) : 'Section did not exist.' }}</pre><pre class="max-h-72 overflow-auto bg-white p-4 text-[11px] leading-5 text-slate-600">{{ row.after ? JSON.stringify(row.after, null, 2) : 'Section was removed.' }}</pre></div>
            </article>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
