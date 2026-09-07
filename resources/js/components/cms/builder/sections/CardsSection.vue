<script setup lang="ts">
import type { PageSection, BuilderJsonValue } from '@/types/cms'

defineProps<{ section: PageSection }>()

const items = (value: BuilderJsonValue): Array<Record<string, BuilderJsonValue>> => Array.isArray(value) ? value.filter((item): item is Record<string, BuilderJsonValue> => typeof item === 'object' && item !== null && !Array.isArray(item)) : []
</script>

<template>
  <section class="p-8 md:p-16" :style="{ background: String(section.styles.background ?? '') || undefined, padding: String(section.styles.padding ?? '') || undefined }">
    <div class="mb-8 max-w-2xl">
      <h2 class="text-2xl font-semibold tracking-tight text-slate-950 md:text-4xl">{{ section.content.title || 'Selected work' }}</h2>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
      <article v-for="(item, index) in items(section.content.items)" :key="index" class="rounded-xl border border-slate-200 bg-white p-6">
        <h3 class="font-semibold text-slate-900">{{ item.title || `Card ${index + 1}` }}</h3>
        <p v-if="item.description" class="mt-2 text-sm leading-6 text-slate-500">{{ item.description }}</p>
      </article>
      <div v-if="items(section.content.items).length === 0" class="rounded-xl border border-dashed border-slate-300 p-8 text-sm text-slate-400 md:col-span-2">Add card items from the inspector.</div>
    </div>
  </section>
</template>
