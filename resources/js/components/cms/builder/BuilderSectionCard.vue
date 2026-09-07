<script setup lang="ts">
import { computed, type Component } from 'vue'
import { EyeOff, GripVertical, Plus } from 'lucide-vue-next'
import type { BuilderViewport, PageSection } from '@/types/cms'

const props = defineProps<{ section: PageSection; selected: boolean; children: PageSection[]; viewport: BuilderViewport; renderer: Component | null }>()
const emit = defineEmits<{ select: [id: string]; add: [parentId: string] }>()
const title = computed(() => String(props.section.content.title ?? props.section.type ?? 'Section'))
</script>

<template>
  <article :class="['group relative border transition', selected ? 'border-slate-900 ring-2 ring-slate-900/10' : 'border-slate-200 hover:border-slate-400', !section.visibility ? 'opacity-45' : '']" @click.stop="emit('select', section.id)">
    <div v-if="selected" class="absolute -top-7 left-0 z-10 flex h-7 items-center gap-2 rounded-t-md bg-slate-900 px-2 text-[10px] font-semibold uppercase tracking-wider text-white">
      <GripVertical class="h-3 w-3" /> {{ section.type }}
    </div>
    <div v-if="!section.visibility" class="absolute right-3 top-3 z-20 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-1 text-[10px] font-medium text-slate-500 shadow-sm">
      <EyeOff class="h-3 w-3" /> Hidden
    </div>

    <component v-if="renderer" :is="renderer" :section="section" :viewport="viewport" />
    <section v-else class="min-h-[180px] p-8 md:p-12"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center"><div class="text-sm font-semibold capitalize text-slate-700">{{ title }}</div><div class="mt-1 text-xs text-slate-400">Unknown section type</div></div></section>

    <div v-if="children.length" class="absolute inset-x-4 bottom-4 space-y-2 border-l-2 border-slate-200 pl-4">
      <div v-for="child in children" :key="child.id" class="rounded-lg border border-slate-200 bg-white/95 px-4 py-3 text-sm text-slate-600 shadow-sm">
        <span class="font-medium text-slate-800">{{ child.type }}</span><span class="ml-2 text-xs text-slate-400">nested</span>
      </div>
    </div>

    <button type="button" class="absolute -bottom-3 left-1/2 z-30 inline-flex h-7 w-7 -translate-x-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 opacity-0 shadow-sm transition group-hover:opacity-100 hover:bg-slate-900 hover:text-white" aria-label="Add nested section" @click.stop="emit('add', section.id)"><Plus class="h-3.5 w-3.5" /></button>
  </article>
</template>
