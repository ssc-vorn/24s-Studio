<script setup lang="ts">
import type { PageSection } from '@/types/cms'
defineProps<{ section: PageSection }>()
</script>

<template>
  <section class="p-8 md:p-16" :style="{ background: String(section.styles.background ?? '') || undefined, padding: String(section.styles.padding ?? '') || undefined }">
    <div class="flex flex-col gap-6 rounded-2xl border border-slate-200 bg-slate-50 p-8 md:flex-row md:items-end md:justify-between md:p-12">
      <div class="max-w-2xl">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-950 md:text-4xl">{{ section.content.title || 'Start a conversation.' }}</h2>
        <p v-if="section.content.description" class="mt-3 text-sm leading-6 text-slate-500">{{ section.content.description }}</p>
      </div>
      <a :href="String(section.content.href || '/contact')" class="inline-flex shrink-0 items-center justify-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">{{ section.content.label || 'Get in touch' }}</a>
    </div>
  </section>
</template>
