<script setup lang="ts">
import { computed, ref } from 'vue'
import { ImagePlus, Plus, Settings2, Trash2, X } from 'lucide-vue-next'
import type { BuilderJsonObject, BuilderJsonValue, BuilderViewport, MediaAsset, PageSection } from '@/types/cms'
import { getSectionDefinition, sectionRegistry } from './section-registry'
import SectionField from './SectionField.vue'
import MediaPicker from './MediaPicker.vue'

const props = defineProps<{ section: PageSection | null; viewport?: BuilderViewport; organizationId: string }>()
const emit = defineEmits<{ update: [patch: Partial<PageSection>] }>()
const tab = ref<'content' | 'style' | 'responsive' | 'animation'>('content')
const viewport = computed(() => props.viewport ?? 'desktop')
const definition = computed(() => props.section ? getSectionDefinition(props.section.type) : null)
const sectionTypes = computed(() => Object.values(sectionRegistry))
const responsiveValues = computed(() => (props.section?.responsive?.[viewport.value] as BuilderJsonObject | undefined) ?? {})
const cardItems = computed(() => {
  const value = props.section?.content.items
  return Array.isArray(value)
    ? value.filter((item): item is BuilderJsonObject => typeof item === 'object' && item !== null && !Array.isArray(item))
    : []
})
const mediaOpen = ref(false)
const selectedMedia = ref<MediaAsset | null>(null)
const cardMediaIndex = ref<number | null>(null)

const updateContent = (key: string, value: BuilderJsonValue) => {
  if (!props.section) return
  emit('update', { content: { ...props.section.content, [key]: value } as BuilderJsonObject })
}
const updateStyle = (key: string, value: string) => {
  if (!props.section) return
  emit('update', { styles: { ...props.section.styles, [key]: value } as BuilderJsonObject })
}
const updateResponsive = (key: string, value: BuilderJsonValue) => {
  if (!props.section) return
  emit('update', { responsive: { ...props.section.responsive, [viewport.value]: { ...responsiveValues.value, [key]: value } } as BuilderJsonObject })
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
const setVariant = (variant: string) => emit('update', { variant })
const openMedia = () => { selectedMedia.value = null; cardMediaIndex.value = null; mediaOpen.value = true }
const openCardMedia = (index: number) => { selectedMedia.value = null; cardMediaIndex.value = index; mediaOpen.value = true }
const chooseMedia = (media: MediaAsset) => {
  selectedMedia.value = media
  if (!props.section) return
  if (cardMediaIndex.value !== null) {
    const items = cardItems.value.map((item) => ({ ...item }))
    items[cardMediaIndex.value] = { ...items[cardMediaIndex.value], media_id: media.id, image: media.url ?? '', alt: items[cardMediaIndex.value].alt || media.alt || '' }
    updateContent('items', items)
  } else {
    emit('update', { content: { ...props.section.content, media_id: media.id, src: media.url ?? '', alt: props.section.content.alt || media.alt || '' } as BuilderJsonObject })
  }
  mediaOpen.value = false
  cardMediaIndex.value = null
}
const removeMedia = () => {
  if (!props.section) return
  const content = { ...props.section.content }
  delete content.media_id
  delete content.src
  delete content.alt
  emit('update', { content })
}
const addCard = () => updateContent('items', [...cardItems.value, { title: `Card ${cardItems.value.length + 1}`, description: '', href: '', image: '', alt: '' }])
const updateCard = (index: number, key: string, value: BuilderJsonValue) => {
  const items = cardItems.value.map((item) => ({ ...item }))
  items[index] = { ...items[index], [key]: value }
  updateContent('items', items)
}
const removeCard = (index: number) => updateContent('items', cardItems.value.filter((_, itemIndex) => itemIndex !== index))
</script>

<template>
  <aside class="flex h-full min-h-0 w-[320px] shrink-0 flex-col border-l border-slate-200 bg-white">
    <div class="flex h-12 items-center gap-2 border-b border-slate-100 px-4"><Settings2 class="h-4 w-4 text-slate-500" /><span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Inspector</span></div>
    <div v-if="section" class="min-h-0 flex-1 overflow-y-auto">
      <div class="space-y-3 border-b border-slate-100 px-4 py-4">
        <div><div class="text-xs uppercase tracking-wider text-slate-400">Component</div><select :value="section.type" class="mt-1.5 w-full rounded-md border-slate-200 text-sm font-medium" @change="setType(($event.target as HTMLSelectElement).value)"><option v-for="item in sectionTypes" :key="item.type" :value="item.type">{{ item.label }}</option></select></div>
        <div><div class="text-xs uppercase tracking-wider text-slate-400">Variant</div><select :value="section.variant ?? definition?.variants[0]?.value" class="mt-1.5 w-full rounded-md border-slate-200 text-sm" @change="setVariant(($event.target as HTMLSelectElement).value)"><option v-for="item in definition?.variants ?? []" :key="item.value" :value="item.value">{{ item.label }}</option></select></div>
        <p class="text-xs leading-5 text-slate-400">{{ definition?.description || 'Custom section' }}</p>
      </div>
      <div class="grid grid-cols-4 border-b border-slate-100 px-2">
        <button v-for="item in [{ key: 'content', label: 'Content' }, { key: 'style', label: 'Style' }, { key: 'responsive', label: 'Responsive' }, { key: 'animation', label: 'Motion' }]" :key="item.key" type="button" :class="['border-b-2 px-1 py-3 text-[10px] font-semibold', tab === item.key ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400']" @click="tab = item.key as typeof tab">{{ item.label }}</button>
      </div>
      <div v-if="tab === 'content'" class="space-y-5 p-4">
        <template v-if="section.type === 'image'">
          <div class="space-y-3 rounded-lg border border-slate-200 p-3">
            <div v-if="section.content.src" class="relative overflow-hidden rounded-md bg-slate-100"><img :src="String(section.content.src)" :alt="String(section.content.alt ?? '')" class="aspect-[4/3] w-full object-cover" /><button type="button" class="absolute right-2 top-2 rounded-md bg-white/90 p-1.5 text-slate-600 shadow-sm hover:bg-white" title="Remove image" @click="removeMedia"><X class="h-4 w-4" /></button></div>
            <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="openMedia"><ImagePlus class="h-4 w-4" />{{ section.content.src ? 'Replace media' : 'Choose media' }}</button>
            <p v-if="section.content.media_id" class="truncate text-[10px] text-slate-400">Media ID: {{ section.content.media_id }}</p>
          </div>
        </template>
        <SectionField v-for="field in definition?.contentFields ?? []" :key="field.key" :label="field.label" :type="field.type" :model-value="section.content[field.key] ?? ''" @update:model-value="updateContent(field.key, $event)" />
        <div v-if="section.type === 'cards'" class="space-y-3 rounded-lg border border-slate-200 p-3">
          <div class="flex items-center justify-between"><div><div class="text-xs font-semibold text-slate-700">Cards</div><p class="mt-0.5 text-[10px] text-slate-400">Edit title, copy, link and media.</p></div><button type="button" class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1.5 text-[10px] font-semibold text-slate-700 hover:bg-slate-50" @click="addCard"><Plus class="h-3.5 w-3.5" />Add</button></div>
          <div v-if="cardItems.length" class="space-y-3">
            <div v-for="(item, index) in cardItems" :key="index" class="space-y-2 rounded-md border border-slate-100 bg-slate-50 p-2.5">
              <div class="flex items-center justify-between"><span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Card {{ index + 1 }}</span><button type="button" class="rounded p-1 text-slate-400 hover:bg-white hover:text-slate-700" title="Remove card" @click="removeCard(index)"><Trash2 class="h-3.5 w-3.5" /></button></div>
              <SectionField label="Title" :model-value="item.title ?? ''" @update:model-value="updateCard(index, 'title', $event)" />
              <SectionField label="Description" type="textarea" :model-value="item.description ?? ''" @update:model-value="updateCard(index, 'description', $event)" />
              <SectionField label="Link" type="url" :model-value="item.href ?? ''" @update:model-value="updateCard(index, 'href', $event)" />
              <div class="space-y-2"><div v-if="item.image" class="overflow-hidden rounded-md bg-white"><img :src="String(item.image)" :alt="String(item.alt ?? '')" class="aspect-[16/9] w-full object-cover" /></div><button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-slate-200 bg-white px-2 py-1.5 text-[10px] font-semibold text-slate-600 hover:bg-slate-50" @click="openCardMedia(index)"><ImagePlus class="h-3.5 w-3.5" />{{ item.image ? 'Replace image' : 'Choose image' }}</button></div>
            </div>
          </div>
          <div v-else class="rounded-md border border-dashed border-slate-200 p-4 text-center text-[10px] text-slate-400">No cards yet. Add the first card.</div>
        </div>
        <label class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="text-xs font-medium text-slate-600">Visible</span><input :checked="section.is_visible" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" @change="emit('update', { is_visible: ($event.target as HTMLInputElement).checked })" /></label>
      </div>
      <div v-else-if="tab === 'style'" class="space-y-5 p-4">
        <SectionField label="Background" :model-value="section.styles.background ?? ''" @update:model-value="updateStyle('background', String($event ?? ''))" />
        <SectionField label="Padding" :model-value="section.styles.padding ?? ''" @update:model-value="updateStyle('padding', String($event ?? ''))" />
        <SectionField label="Max width" :model-value="section.styles.maxWidth ?? ''" @update:model-value="updateStyle('maxWidth', String($event ?? ''))" />
        <SectionField label="Border radius" :model-value="section.styles.radius ?? ''" @update:model-value="updateStyle('radius', String($event ?? ''))" />
        <SectionField label="Text align" :model-value="section.styles.textAlign ?? ''" @update:model-value="updateStyle('textAlign', String($event ?? ''))" />
      </div>
      <div v-else-if="tab === 'responsive'" class="space-y-5 p-4">
        <div class="rounded-lg bg-slate-50 p-3 text-xs text-slate-500">Editing <strong class="text-slate-800">{{ viewport }}</strong> overrides.</div>
        <SectionField label="Padding" :model-value="responsiveValues.padding ?? ''" @update:model-value="updateResponsive('padding', $event)" />
        <SectionField label="Max width" :model-value="responsiveValues.maxWidth ?? ''" @update:model-value="updateResponsive('maxWidth', $event)" />
        <SectionField label="Text align" :model-value="responsiveValues.textAlign ?? ''" @update:model-value="updateResponsive('textAlign', $event)" />
        <label class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="text-xs font-medium text-slate-600">Visible</span><input :checked="responsiveValues.visible !== false" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" @change="updateResponsive('visible', ($event.target as HTMLInputElement).checked)" /></label>
      </div>
      <div v-else class="space-y-5 p-4">
        <label class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="text-xs font-medium text-slate-600">Enabled</span><input :checked="section.animation.enabled !== false" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" @change="updateAnimation('enabled', ($event.target as HTMLInputElement).checked)" /></label>
        <label class="block"><span class="text-xs font-medium text-slate-600">Preset</span><select :value="String(section.animation.preset ?? 'fade-up')" class="mt-1.5 w-full rounded-md border-slate-200 text-sm" @change="updateAnimation('preset', ($event.target as HTMLSelectElement).value)"><option value="none">None</option><option value="fade-up">Fade up</option><option value="fade-in">Fade in</option><option value="slide-left">Slide left</option><option value="slide-right">Slide right</option></select></label>
        <SectionField label="Duration (ms)" type="number" :model-value="section.animation.duration ?? ''" @update:model-value="updateAnimation('duration', String($event ?? ''))" />
        <SectionField label="Delay (ms)" type="number" :model-value="section.animation.delay ?? ''" @update:model-value="updateAnimation('delay', String($event ?? ''))" />
      </div>
    </div>
    <div v-else class="flex flex-1 items-center justify-center p-8 text-center"><div><Settings2 class="mx-auto h-8 w-8 text-slate-300" /><p class="mt-3 text-sm font-medium text-slate-600">Select a section</p><p class="mt-1 text-xs text-slate-400">Its content and design controls will appear here.</p></div></div>
    <MediaPicker :open="mediaOpen" :organization-id="organizationId" :selected-id="selectedMedia?.id ?? (section?.content.media_id as string | undefined)" @close="mediaOpen = false" @select="chooseMedia" />
  </aside>
</template>
