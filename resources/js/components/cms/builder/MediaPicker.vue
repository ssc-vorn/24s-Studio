<script setup lang="ts">
import { computed, ref } from 'vue'
import { ImagePlus, Search, Upload, X } from 'lucide-vue-next'
import { mediaApi, type MediaAsset } from '@/services/cms/media'

const props = defineProps<{ open: boolean; organizationId: string; selectedId?: string | null }>()
const emit = defineEmits<{ close: []; select: [media: MediaAsset] }>()
const query = ref('')
const assets = ref<MediaAsset[]>([])
const loading = ref(false)
const uploading = ref(false)
const error = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const filtered = computed(() => {
  const value = query.value.trim().toLowerCase()
  return value ? assets.value.filter((item) => item.filename.toLowerCase().includes(value) || (item.alt ?? '').toLowerCase().includes(value)) : assets.value
})

const load = async () => {
  if (!props.open) return
  loading.value = true; error.value = null
  try { assets.value = await mediaApi.list(props.organizationId, { search: query.value || undefined }) }
  catch { error.value = 'Unable to load media.' }
  finally { loading.value = false }
}

const upload = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  uploading.value = true; error.value = null
  try { const media = await mediaApi.upload(props.organizationId, file); assets.value = [media, ...assets.value]; emit('select', media) }
  catch { error.value = 'Upload failed. Check the file type and size.' }
  finally { uploading.value = false; if (fileInput.value) fileInput.value.value = '' }
}

const openPicker = () => fileInput.value?.click()

watch(() => props.open, (open) => { if (open) void load() }, { immediate: true })
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
      <section class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <div><h2 class="text-sm font-semibold text-slate-900">Media Library</h2><p class="mt-1 text-xs text-slate-400">Choose an asset for this section.</p></div>
          <button type="button" class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="emit('close')"><X class="h-4 w-4" /></button>
        </header>
        <div class="flex gap-2 border-b border-slate-100 p-4">
          <div class="relative flex-1"><Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input v-model="query" placeholder="Search filename or alt text" class="w-full rounded-md border-slate-200 pl-9 text-sm" @keyup.enter="load" /></div>
          <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="upload" />
          <button type="button" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-xs font-semibold text-white disabled:opacity-50" :disabled="uploading" @click="openPicker"><Upload class="h-4 w-4" />{{ uploading ? 'Uploading…' : 'Upload' }}</button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto p-4">
          <p v-if="error" class="mb-3 rounded-md bg-red-50 p-3 text-xs text-red-700">{{ error }}</p>
          <div v-if="loading" class="py-16 text-center text-xs text-slate-400">Loading media…</div>
          <div v-else-if="filtered.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
            <button v-for="item in filtered" :key="item.id" type="button" class="group overflow-hidden rounded-lg border text-left" :class="item.id === selectedId ? 'border-slate-900 ring-2 ring-slate-900/10' : 'border-slate-200'" @click="emit('select', item)">
              <div class="aspect-[4/3] bg-slate-100"><img :src="item.url" :alt="item.alt ?? item.filename" class="h-full w-full object-cover transition group-hover:scale-[1.02]" /></div>
              <div class="truncate px-3 py-2 text-xs font-medium text-slate-700">{{ item.filename }}</div>
            </button>
          </div>
          <div v-else class="py-16 text-center"><ImagePlus class="mx-auto h-8 w-8 text-slate-300" /><p class="mt-3 text-sm font-medium text-slate-600">No media yet</p><p class="mt-1 text-xs text-slate-400">Upload your first image to use it in the builder.</p></div>
        </div>
      </section>
    </div>
  </Teleport>
</template>
