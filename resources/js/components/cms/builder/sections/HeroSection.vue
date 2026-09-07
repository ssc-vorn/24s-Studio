<script setup lang="ts">
import type { PageSection, BuilderViewport } from '@/types/cms'

defineProps<{ section: PageSection; viewport: BuilderViewport }>()
</script>

<template>
  <section
    class="relative flex min-h-[420px] items-center overflow-hidden p-8 md:p-16"
    :style="{
      background: String(section.styles.background ?? '') || undefined,
      padding: String(section.styles.padding ?? '') || undefined,
      maxWidth: String(section.styles.maxWidth ?? '') || undefined,
      textAlign: String(section.styles.textAlign ?? '') || undefined,
      borderRadius: String(section.styles.radius ?? '') || undefined,
    }"
  >
    <img v-if="section.content.src" :src="String(section.content.src)" :alt="String(section.content.alt ?? '')" class="absolute inset-0 h-full w-full object-cover" />
    <div v-if="section.content.src" class="absolute inset-0 bg-white/70" />
    <div class="relative max-w-3xl">
      <span class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">24 SEVEN SOLUTION</span>
      <h2 class="mt-5 text-4xl font-semibold tracking-[-0.04em] text-slate-950 md:text-6xl">{{ section.content.title || 'Untitled hero' }}</h2>
      <p v-if="section.content.description" class="mt-5 max-w-2xl text-base leading-7 text-slate-500 md:text-lg">{{ section.content.description }}</p>
    </div>
  </section>
</template>
