<script setup lang="ts">
import { computed, ref } from 'vue'
import { Settings2 } from 'lucide-vue-next'
import type { BuilderJsonObject, PageSection } from '@/types/cms'

const props = defineProps<{ section: PageSection | null }>()
const emit = defineEmits<{ update: [patch: Partial<PageSection>] }>()
const tab = ref<'content' | 'style' | 'responsive' | 'animation'>('content')

const title = computed(() => String(props.section?.content.title ?? ''))
const description = computed(() => String(props.section?.content.description ?? props.section?.content.text ?? ''))
const updateContent = (key: string, value: string) => {
  if (!props.section) return
  emit('update', { content: { ...props.section.content, [key]: value } as BuilderJsonObject })
}
const updateStyle = (key: string, value: string) => {
  if (!props.section) return
  emit('update', { styles: { ...props.section.styles, [key]: value } as BuilderJsonObject })
}
</script>

<template>
  <aside class="flex h-full min-h-0 w-[320px] shrink-0 flex-col border-l border-slate-200 bg-white">
    <div class="flex h-12 items-center gap-2 border-b border-slate-100 px-4">
      <Settings2 class="h-4 w-4 text-slate-500" />
      <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Inspector</span>
    </div>

    <div v-if="section" class="min-h-0 flex-1 overflow-y-auto">
      <div class="border-b border-slate-100 px-4 py-4">
        <div class="text-xs uppercase tracking-wider text-slate-400">Selected</div>
        <div class="mt-1 text-sm font-semibold capitalize text-slate-900">{{ section.type }}</div>
        <div class="text-xs text-slate-400">{{ section.variant || 'Default' }}</div>
      </div>

      <div class="grid grid-cols-4 border-b border-slate-100 px-2">
        <button v-for="item in [{ key: 'content', label: 'Content' }, { key: 'style', label: 'Style' }, { key: 'responsive', label: 'Responsive' }, { key: 'animation', label: 'Motion' }]" :key="item.key" type="button" :class="['border-b-2 px-1 py-3 text-[10px] font-semibold', tab === item.key ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400']" @click="tab = item.key as typeof tab">{{ item.label }}</button>
      </div>

      <div v-if="tab === 'content'" class="space-y-5 p-4">
        <label class="block"><span class="text-xs font-medium text-slate-600">Title</span><input :value="title" class="mt-1.5 w-full rounded-md border-slate-200 text-sm focus:border-slate-900 focus:ring-slate-900" placeholder="Section title" @input="updateContent('title', ($event.target as HTMLInputElement).value)" /></label>
        <label class="block"><span class="text-xs font-medium text-slate-600">Description</span><textarea :value="description" rows="5" class="mt-1.5 w-full rounded-md border-slate-200 text-sm focus:border-slate-900 focus:ring-slate-900" placeholder="Supporting copy" @input="updateContent('description', ($event.target as HTMLTextAreaElement).value)" /></label>
        <label class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="text-xs font-medium text-slate-600">Visible</span><input :checked="section.visibility" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" @change="emit('update', { visibility: ($event.target as HTMLInputElement).checked })" /></label>
      </div>

      <div v-else-if="tab === 'style'" class="space-y-5 p-4">
        <label class="block"><span class="text-xs font-medium text-slate-600">Background</span><input :value="String(section.styles.background ?? '')" class="mt-1.5 w-full rounded-md border-slate-200 text-sm" placeholder="#ffffff" @input="updateStyle('background', ($event.target as HTMLInputElement).value)" /></label>
        <label class="block"><span class="text-xs font-medium text-slate-600">Padding</span><input :value="String(section.styles.padding ?? '')" class="mt-1.5 w-full rounded-md border-slate-200 text-sm" placeholder="clamp(48px, 8vw, 120px)" @input="updateStyle('padding', ($event.target as HTMLInputElement).value)" /></label>
        <p class="text-xs leading-5 text-slate-400">Style tokens are stored as JSON and can later map to the design-system token editor.</p>
      </div>

      <div v-else-if="tab === 'responsive'" class="p-4"><div class="rounded-lg bg-slate-50 p-4 text-xs leading-5 text-slate-500">Responsive overrides are ready in the section schema. Device-specific controls will be expanded here.</div></div>
      <div v-else class="p-4"><div class="rounded-lg bg-slate-50 p-4 text-xs leading-5 text-slate-500">Animation presets will map to GSAP/ScrollTrigger motion tokens in the next builder pass.</div></div>
    </div>

    <div v-else class="flex flex-1 items-center justify-center p-8 text-center"><div><Settings2 class="mx-auto h-8 w-8 text-slate-300" /><p class="mt-3 text-sm font-medium text-slate-600">Select a section</p><p class="mt-1 text-xs text-slate-400">Its content and design controls will appear here.</p></div></div>
  </aside>
</template>
