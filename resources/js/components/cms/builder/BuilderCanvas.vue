<script setup lang="ts">
import { computed } from 'vue'
import { Plus } from 'lucide-vue-next'
import type { BuilderViewport, PageSection } from '@/types/cms'
import { getSectionComponent } from './section-registry'
import BuilderSectionCard from './BuilderSectionCard.vue'

const props = defineProps<{ sections: PageSection[]; selectedId: string | null; viewport: BuilderViewport }>()
const emit = defineEmits<{ select: [id: string]; add: [parentId: string | null] }>()

const roots = computed(() => props.sections.filter((section) => section.parent_id === null).sort((a, b) => a.position - b.position))
const children = (id: string) => props.sections.filter((section) => section.parent_id === id).sort((a, b) => a.position - b.position)
const frameClass = computed(() => ({ desktop: 'w-full', tablet: 'w-[768px]', mobile: 'w-[390px]' })[props.viewport])
</script>

<template>
  <main class="min-w-0 flex-1 overflow-auto bg-slate-100 p-6 md:p-10">
    <div class="mx-auto transition-all duration-300" :class="frameClass">
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
        <div class="flex h-7 items-center gap-1 border-b border-slate-100 bg-slate-50 px-3">
          <span class="h-2 w-2 rounded-full bg-slate-300" /><span class="h-2 w-2 rounded-full bg-slate-300" /><span class="h-2 w-2 rounded-full bg-slate-300" />
          <span class="ml-2 text-[10px] font-medium uppercase tracking-wider text-slate-400">{{ viewport }} preview</span>
        </div>
        <div v-if="roots.length" class="space-y-0">
          <BuilderSectionCard v-for="section in roots" :key="section.id" :section="section" :children="children(section.id)" :selected="selectedId === section.id" :viewport="viewport" :renderer="getSectionComponent(section)" @select="emit('select', $event)" @add="emit('add', $event)" />
        </div>
        <button v-else type="button" class="flex min-h-[520px] w-full flex-col items-center justify-center gap-3 border-2 border-dashed border-slate-200 bg-white text-center hover:bg-slate-50" @click="emit('add', null)">
          <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500"><Plus class="h-5 w-5" /></span>
          <span class="text-sm font-semibold text-slate-800">Start building your page</span>
          <span class="max-w-xs text-xs leading-5 text-slate-400">Add your first section, then configure its content and styling from the inspector.</span>
        </button>
      </div>
    </div>
  </main>
</template>
