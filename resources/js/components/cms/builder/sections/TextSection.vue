<script setup lang="ts">
import type { PageSection } from '@/types/cms'
defineProps<{ section: PageSection }>()
</script>

<template>
  <section class="p-8 md:p-16" :style="{ background: String(section.styles.background ?? '') || undefined, padding: String(section.styles.padding ?? '') || undefined }">
    <div class="max-w-3xl">
      <h2 class="text-2xl font-semibold tracking-tight text-slate-950 md:text-4xl">{{ section.content.title || 'Text section' }}</h2>
      <p v-if="section.content.description || section.content.text" class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-500 md:text-base">{{ section.content.description || section.content.text }}</p>
    </div>
  </section>
</template>
