<script setup lang="ts">
import { computed, ref } from 'vue'
import { Settings2 } from 'lucide-vue-next'
import type { BuilderJsonObject, BuilderJsonValue, PageSection } from '@/types/cms'
import { getSectionDefinition, sectionRegistry } from './section-registry'
import SectionField from './SectionField.vue'

const props = defineProps<{ section: PageSection | null }>()
const emit = defineEmits<{ update: [patch: Partial<PageSection>] }>()
const tab = ref<'content' | 'style' | 'responsive' | 'animation'>('content')

const definition = computed(() => props.section ? getSectionDefinition(props.section.type) : null)
const sectionTypes = computed(() => Object.values(sectionRegistry))

const updateContent = (key: string, value: BuilderJsonValue) => {
  if (!props.section) return
  emit('update', { content: { ...props.section.content, [key]: value } as BuilderJsonObject })
}
const updateStyle = (key: string, value: string) => {
  if (!props.section) return
  emit('update', { styles: { ...props.section.styles, [key]: value } as BuilderJsonObject })
}
const updateResponsive = (key: string, value: string) => {
  if (!props.section) return
  emit('update', { responsive: { ...props.section.responsive, [key]: value } as BuilderJsonObject })
}
const updateAnimation = (key: string, value: string | boolean) => {
  if (!props.section) return
  emit('update', { animation: { ...props.section.animation, [key]: value } as BuilderJsonObject })
}
const setType = (type: string) => {
  if (!props.section || type === props.section.type) return
  const defaults = getSectionDefinition(type)?.defaults
  if (!defaults) return
  emit('update', { type, variant: defaults.variant, content: structuredClone(defaults.content), styles: structuredClone(defaults.styles), responsive: structuredClone(defaults.responsive), animation: structuredClone(defaults.animation) })
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
        <div class="text-xs uppercase tracking-wider text-slate-400">Component</div>
        <select :value="section.type" class="mt-1.5 w-full rounded-md border-slate-200 text-sm font-medium focus:border-slate-900 focus:ring-slate-900" @change="setType(($event.target as HTMLSelectElement).value)">
          <option v-for="item in sectionTypes" :key="item.type" :value="item.type">{{ item.label }}</option>
        </select>
        <p class="mt-2 text-xs leading-5 text-slate-400">{{ definition?.description || 'Custom section' }}</p>
      </div>

      <div class="grid grid-cols-4 border-b border-slate-100 px-2">
        <button v-for="item in [{ key: 'content', label: 'Content' }, { key: 'style', label: 'Style' }, { key: 'responsive', label: 'Responsive' }, { key: 'animation', label: 'Motion' }]" :key="item.key" type="button" :class="['border-b-2 px-1 py-3 text-[10px] font-semibold', tab === item.key ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400']" @click="tab = item.key as typeof tab">{{ item.label }}</button>
      </div>

      <div v-if="tab === 'content'" class="space-y-5 p-4">
        <SectionField v-if="['hero', 'text', 'cta', 'cards'].includes(section.type)" label="Title" :model-value="section.content.title ?? ''" @update:model-value="updateContent('title', $event)" />
        <SectionField v-if="['hero', 'text', 'cta'].includes(section.type)" label="Description" type="textarea" :model-value="section.content.description ?? ''" @update:model-value="updateContent('description', $event)" />
        <template v-if="section.type === 'image'">
          <SectionField label="Image URL" type="url" :model-value="section.content.src ?? ''" @update:model-value="updateContent('src', $event)" />
          <SectionField label="Alt text" :model-value="section.content.alt ?? ''" @update:model-value="updateContent('alt', $event)" />
          <SectionField label="Caption" :model-value="section.content.caption ?? ''" @update:model-value="updateContent('caption', $event)" />
        </template>
        <template v-if="section.type === 'cta'">
          <SectionField label="Button label" :model-value="section.content.label ?? ''" @update:model-value="updateContent('label', $event)" />
          <SectionField label="Button URL" type="url" :model-value="section.content.href ?? ''" @update:model-value="updateContent('href', $event)" />
        </template>
        <label class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="text-xs font-medium text-slate-600">Visible</span><input :checked="section.visibility" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" @change="emit('update', { visibility: ($event.target as HTMLInputElement).checked })" /></label>
      </div>

      <div v-else-if="tab === 'style'" class="space-y-5 p-4">
        <SectionField label="Background" :model-value="section.styles.background ?? ''" @update:model-value="updateStyle('background', String($event ?? ''))" />
        <SectionField label="Padding" :model-value="section.styles.padding ?? ''" @update:model-value="updateStyle('padding', String($event ?? ''))" />
        <SectionField label="Max width" :model-value="section.styles.maxWidth ?? ''" @update:model-value="updateStyle('maxWidth', String($event ?? ''))" />
      </div>

      <div v-else-if="tab === 'responsive'" class="space-y-5 p-4">
        <SectionField label="Mobile padding" :model-value="section.responsive.mobilePadding ?? ''" @update:model-value="updateResponsive('mobilePadding', String($event ?? ''))" />
        <SectionField label="Tablet padding" :model-value="section.responsive.tabletPadding ?? ''" @update:model-value="updateResponsive('tabletPadding', String($event ?? ''))" />
        <p class="text-xs leading-5 text-slate-400">Overrides are stored independently so desktop values remain unchanged.</p>
      </div>

      <div v-else class="space-y-5 p-4">
        <label class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="text-xs font-medium text-slate-600">Enabled</span><input :checked="section.animation.enabled !== false" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" @change="updateAnimation('enabled', ($event.target as HTMLInputElement).checked)" /></label>
        <label class="block"><span class="text-xs font-medium text-slate-600">Preset</span><select :value="String(section.animation.preset ?? 'fade-up')" class="mt-1.5 w-full rounded-md border-slate-200 text-sm" @change="updateAnimation('preset', ($event.target as HTMLSelectElement).value)"><option value="none">None</option><option value="fade-up">Fade up</option><option value="fade-in">Fade in</option><option value="slide-left">Slide left</option><option value="slide-right">Slide right</option></select></label>
      </div>
    </div>

    <div v-else class="flex flex-1 items-center justify-center p-8 text-center"><div><Settings2 class="mx-auto h-8 w-8 text-slate-300" /><p class="mt-3 text-sm font-medium text-slate-600">Select a section</p><p class="mt-1 text-xs text-slate-400">Its content and design controls will appear here.</p></div></div>
  </aside>
</template>
