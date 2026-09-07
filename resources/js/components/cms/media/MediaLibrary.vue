<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { ImagePlus, Search, Trash2, Upload, X } from 'lucide-vue-next'
import { mediaApi } from '@/services/cms/media'
import type { MediaAsset } from '@/types/cms'

const props = withDefaults(defineProps<{ organizationId: string; selectable?: boolean; selectedId?: string | null }>(), { selectable: false, selectedId: null })
const emit = defineEmits<{ select: [media: MediaAsset]; close: [] }>()
const assets = ref<MediaAsset[]>([])
const query = ref('')
const mime = ref('')
const page = ref(1)
const lastPage = ref(1)
const loading = ref(false)
const uploading = ref(false)
const progress = ref(0)
const error = ref<string | null>(null)
const editing = ref<MediaAsset | null>(null)
const editAlt = ref('')

const load = async (targetPage = page.value) => {
  loading.value = true; error.value = null
  try {
    const result = await mediaApi.list(props.organizationId, { search: query.value || undefined, mime_type: mime.value || undefined, per_page: 48, page: targetPage })
    assets.value = result.data
    page.value = result.meta?.current_page ?? targetPage
    lastPage.value = result.meta?.last_page ?? 1
  } catch { error.value = 'Unable to load media.' }
  finally { loading.value = false }
}
const upload = async (event: Event) => {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files ?? [])
  if (!files.length) return
  uploading.value = true; error.value = null
  try {
    for (const file of files) {
      progress.value = 0
      const item = await mediaApi.upload(props.organizationId, file, '', {}, (value) => { progress.value = value })
      assets.value.unshift(item)
      if (props.selectable) emit('select', item)
    }
  } catch { error.value = 'One or more uploads failed. Check the allowed file types and size limit.' }
  finally { uploading.value = false; progress.value = 0; input.value = '' }
}
const edit = (asset: MediaAsset) => { editing.value = asset; editAlt.value = asset.alt ?? '' }
const saveAlt = async () => {
  if (!editing.value) return
  try {
    const updated = await mediaApi.update(props.organizationId, editing.value.id, { alt: editAlt.value })
    const index = assets.value.findIndex((item) => item.id === updated.id)
    if (index >= 0) assets.value[index] = updated
    editing.value = null
  } catch { error.value = 'Unable to save asset metadata.' }
}
const remove = async (asset: MediaAsset) => {
  if (!window.confirm(`Delete ${asset.filename}?`)) return
  try { await mediaApi.remove(props.organizationId, asset.id); assets.value = assets.value.filter((item) => item.id !== asset.id) }
  catch { error.value = 'Unable to delete this asset.' }
}
onMounted(() => void load())
</script>

<template>
  <div class="flex min-h-0 flex-1 flex-col bg-white">
    <header class="flex flex-wrap items-center gap-3 border-b border-slate-200 px-6 py-4">
      <div class="mr-auto"><h1 class="text-lg font-semibold tracking-tight text-slate-950">Media Library</h1><p class="mt-1 text-xs text-slate-400">Manage images and other creative assets.</p></div>
      <div class="relative w-full sm:w-64"><Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input v-model="query" class="w-full rounded-md border-slate-200 pl-9 text-sm" placeholder="Search assets" @keyup.enter="load(1)" /></div>
      <select v-model="mime" class="rounded-md border-slate-200 text-sm" @change="load(1)"><option value="">All files</option><option value="image/">Images</option><option value="video/">Video</option><option value="application/pdf">PDF</option></select>
      <label class="inline-flex cursor-pointer items-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-xs font-semibold text-white"><Upload class="h-4 w-4" />Upload<input type="file" multiple accept="image/*,video/mp4,video/webm,application/pdf" class="hidden" @change="upload" /></label>
    </header>
    <div v-if="uploading" class="border-b border-slate-100 bg-slate-50 px-6 py-2 text-xs text-slate-500">Uploading… {{ progress }}%</div>
    <div v-if="error" class="mx-6 mt-4 rounded-md bg-red-50 p-3 text-xs text-red-700">{{ error }}</div>
    <main class="min-h-0 flex-1 overflow-y-auto p-6">
      <div v-if="loading" class="py-20 text-center text-sm text-slate-400">Loading media…</div>
      <div v-else-if="assets.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        <article v-for="asset in assets" :key="asset.id" class="group overflow-hidden rounded-lg border bg-white" :class="asset.id === selectedId ? 'border-slate-900 ring-2 ring-slate-900/10' : 'border-slate-200'">
          <button v-if="selectable" type="button" class="block w-full text-left" :aria-label="`Select ${asset.filename}`" @click="emit('select', asset)">
            <div class="aspect-square bg-slate-100"><img v-if="asset.url && asset.mime_type.startsWith('image/')" :src="asset.url" :alt="asset.alt ?? asset.filename" class="h-full w-full object-cover" /><div v-else class="flex h-full items-center justify-center text-xs font-semibold uppercase text-slate-400">{{ asset.mime_type.split('/')[1] }}</div></div>
            <div class="truncate px-3 py-2 text-xs font-medium text-slate-700">{{ asset.filename }}</div>
          </button>
          <template v-else><div class="aspect-square bg-slate-100"><img v-if="asset.url && asset.mime_type.startsWith('image/')" :src="asset.url" :alt="asset.alt ?? asset.filename" class="h-full w-full object-cover" /><div v-else class="flex h-full items-center justify-center text-xs font-semibold uppercase text-slate-400">{{ asset.mime_type.split('/')[1] }}</div></div><div class="p-3"><div class="truncate text-xs font-medium text-slate-700">{{ asset.filename }}</div><div class="mt-2 flex gap-1 opacity-0 transition group-hover:opacity-100 group-focus-within:opacity-100"><button type="button" class="flex-1 rounded border border-slate-200 px-2 py-1 text-[10px] font-semibold" @click="edit(asset)">Edit</button><button type="button" class="rounded border border-red-100 p-1 text-red-500" :aria-label="`Delete ${asset.filename}`" @click="remove(asset)"><Trash2 class="h-3.5 w-3.5" /></button></div></div></template>
        </article>
      </div>
      <div v-else class="py-24 text-center"><ImagePlus class="mx-auto h-10 w-10 text-slate-300" /><p class="mt-4 text-sm font-medium text-slate-700">No assets found</p><p class="mt-1 text-xs text-slate-400">Upload creative files to build your media library.</p></div>
      <div v-if="lastPage > 1" class="mt-6 flex items-center justify-center gap-3 text-xs"><button type="button" class="rounded border px-3 py-1.5 disabled:opacity-40" :disabled="page <= 1 || loading" @click="load(page - 1)">Previous</button><span class="text-slate-500">Page {{ page }} of {{ lastPage }}</span><button type="button" class="rounded border px-3 py-1.5 disabled:opacity-40" :disabled="page >= lastPage || loading" @click="load(page + 1)">Next</button></div>
    </main>
    <Teleport to="body"><div v-if="editing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="editing = null"><div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl"><div class="flex items-center justify-between"><h2 class="text-sm font-semibold">Edit asset</h2><button type="button" @click="editing = null"><X class="h-4 w-4 text-slate-400" /></button></div><img v-if="editing.url && editing.mime_type.startsWith('image/')" :src="editing.url" :alt="editing.alt ?? editing.filename" class="mt-4 max-h-56 w-full rounded-lg object-contain bg-slate-100" /><label class="mt-4 block text-xs font-medium text-slate-600">Alt text<input v-model="editAlt" maxlength="255" class="mt-1.5 w-full rounded-md border-slate-200 text-sm" /></label><div class="mt-5 flex justify-end gap-2"><button type="button" class="rounded-md border px-3 py-2 text-xs" @click="editing = null">Cancel</button><button type="button" class="rounded-md bg-slate-900 px-3 py-2 text-xs font-semibold text-white" @click="saveAlt">Save</button></div></div></div></Teleport>
  </div>
</template>
