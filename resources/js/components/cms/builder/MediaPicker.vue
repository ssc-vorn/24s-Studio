<script setup lang="ts">
import { watch } from 'vue'
import { X } from 'lucide-vue-next'
import MediaLibrary from '@/components/cms/media/MediaLibrary.vue'
import type { MediaAsset } from '@/types/cms'

const props = defineProps<{ open: boolean; organizationId: string; selectedId?: string | null }>()
const emit = defineEmits<{ close: []; select: [media: MediaAsset] }>()

watch(() => props.open, (open) => {
  if (open) document.body.classList.add('overflow-hidden')
  else document.body.classList.remove('overflow-hidden')
})
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.esc="emit('close')">
      <section role="dialog" aria-modal="true" aria-labelledby="media-picker-title" class="flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
          <div><h2 id="media-picker-title" class="text-sm font-semibold text-slate-900">Choose media</h2><p class="mt-1 text-xs text-slate-400">Select an asset for this section.</p></div>
          <button type="button" aria-label="Close media picker" class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="emit('close')"><X class="h-4 w-4" /></button>
        </header>
        <MediaLibrary :organization-id="organizationId" selectable :selected-id="selectedId" @select="emit('select', $event)" />
      </section>
    </div>
  </Teleport>
</template>
