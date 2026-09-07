<script setup lang="ts">
import { computed } from 'vue'
import { EyeOff, GripVertical, Plus } from 'lucide-vue-next'
import type { PageSection } from '@/types/cms'

const props = defineProps<{ section: PageSection; selected: boolean; children: PageSection[]; viewport: string }>()
const emit = defineEmits<{ select: [id: string]; add: [parentId: string]; }>()

const title = computed(() => String(props.section.content.title ?? props.section.type ?? 'Section'))
const description = computed(() => String(props.section.content.description ?? props.section.content.text ?? ''))
const minHeight = computed(() => props.section.type === 'hero' ? 'min-h-[420px]' : 'min-h-[180px]')
</script>

<template>
  <article
    :class="['group relative border transition', selected ? 'border-slate-900 ring-2 ring-slate-900/10' : 'border-slate-200 hover:border-slate-400', !section.visibility ? 'opacity-45' : '', minHeight]"
    @click.stop="emit('select', section.id)"
  >
    <div v-if="selected" class="absolute -top-7 left-0 z-10 flex h-7 items-center gap-2 rounded-t-md bg-slate-900 px-2 text-[10px] font-semibold uppercase tracking-wider text-white">
      <GripVertical class="h-3 w-3" /> {{ section.type }}
    </div>
    <div v-if="!section.visibility" class="absolute right-3 top-3 z-10 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-1 text-[10px] font-medium text-slate-500 shadow-sm">
      <EyeOff class="h-3 w-3" /> Hidden
    </div>

    <div class="flex h-full flex-col justify-center p-8 md:p-12">
      <template v-if="section.type === 'hero'">
        <div class="max-w-2xl">
          <span class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Hero section</span>
          <h2 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 md:text-6xl">{{ title || 'Untitled hero' }}</h2>
          <p v-if="description" class="mt-4 max-w-xl text-base leading-7 text-slate-500">{{ description }}</p>
        </div>
      </template>
      <template v-else-if="section.type === 'text'">
        <div class="max-w-3xl">
          <h3 class="text-2xl font-semibold text-slate-950">{{ title || 'Text section' }}</h3>
          <p class="mt-3 text-sm leading-6 text-slate-500">{{ description || 'Add content from the inspector.' }}</p>
        </div>
      </template>
      <template v-else>
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-8 text-center">
          <div class="text-sm font-semibold capitalize text-slate-700">{{ section.type || 'Section' }}</div>
          <div class="mt-1 text-xs text-slate-400">{{ section.variant || 'Default variant' }} · {{ viewport }}</div>
        </div>
      </template>

      <div v-if="children.length" class="mt-8 space-y-3 border-l-2 border-slate-200 pl-4">
        <div v-for="child in children" :key="child.id" class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
          <span class="font-medium text-slate-800">{{ child.type }}</span>
          <span class="ml-2 text-xs text-slate-400">nested</span>
        </div>
      </div>
    </div>

    <button type="button" class="absolute -bottom-3 left-1/2 z-10 inline-flex h-7 w-7 -translate-x-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 opacity-0 shadow-sm transition group-hover:opacity-100 hover:bg-slate-900 hover:text-white" aria-label="Add nested section" @click.stop="emit('add', section.id)">
      <Plus class="h-3.5 w-3.5" />
    </button>
  </article>
</template>
