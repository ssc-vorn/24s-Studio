<script setup lang="ts">
import type { PageSection, BuilderJsonValue } from '@/types/cms'

defineProps<{ section: PageSection }>()

const items = (value: BuilderJsonValue): Array<Record<string, BuilderJsonValue>> => Array.isArray(value) ? value.filter((item): item is Record<string, BuilderJsonValue> => typeof item === 'object' && item !== null && !Array.isArray(item)) : []
</script>

<template>
  <section
    class="p-8 md:p-16"
    :style="{
      background: String(section.styles.background ?? '') || undefined,
      padding: String(section.styles.padding ?? '') || undefined,
      maxWidth: String(section.styles.maxWidth ?? '') || undefined,
      textAlign: String(section.styles.textAlign ?? '') || undefined,
      borderRadius: String(section.styles.radius ?? '') || undefined,
    }"
  >
    <div class="mb-8 max-w-2xl">
      <h2 class="text-2xl font-semibold tracking-tight text-slate-950 md:text-4xl">{{ section.content.title || 'Selected work' }}</h2>
    </div>
    <div
      :class="[
        'grid gap-4',
        section.variant === 'two-column' ? 'md:grid-cols-2' : section.variant === 'featured' ? 'md:grid-cols-3' : 'md:grid-cols-2 lg:grid-cols-3',
      ]"
    >
      <article v-for="(item, index) in items(section.content.items)" :key="index" class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <img v-if="item.image" :src="String(item.image)" :alt="String(item.alt ?? item.title ?? '')" class="aspect-[16/9] w-full object-cover" />
        <div class="p-6">
          <h3 class="font-semibold text-slate-900">{{ item.title || `Card ${index + 1}` }}</h3>
          <p v-if="item.description" class="mt-2 text-sm leading-6 text-slate-500">{{ item.description }}</p>
          <a v-if="item.href" :href="String(item.href)" class="mt-4 inline-flex text-sm font-semibold text-slate-900 underline underline-offset-4">Learn more</a>
        </div>
      </article>
      <div v-if="items(section.content.items).length === 0" class="rounded-xl border border-dashed border-slate-300 p-8 text-sm text-slate-400 md:col-span-2 lg:col-span-3">Add card items from the inspector.</div>
    </div>
  </section>
</template>
