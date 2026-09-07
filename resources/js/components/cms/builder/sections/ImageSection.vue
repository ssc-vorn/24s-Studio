<script setup lang="ts">
import type { PageSection } from '@/types/cms'
defineProps<{ section: PageSection }>()
</script>

<template>
  <figure class="overflow-hidden bg-slate-100" :style="{ background: String(section.styles.background ?? '') || undefined }">
    <img v-if="section.content.src" :src="String(section.content.src)" :alt="String(section.content.alt ?? '')" class="block h-auto max-h-[720px] w-full object-cover" />
    <div v-else class="flex min-h-[320px] items-center justify-center border-2 border-dashed border-slate-200 text-sm text-slate-400">Choose media from the inspector.</div>
    <figcaption v-if="section.content.caption" class="px-6 py-3 text-xs text-slate-500">{{ section.content.caption }}</figcaption>
  </figure>
</template>
