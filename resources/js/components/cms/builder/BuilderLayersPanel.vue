<script setup lang="ts">
import { Eye, EyeOff, GripVertical, Plus, Trash2 } from 'lucide-vue-next'
import { computed } from 'vue'
import type { PageSection } from '@/types/cms'

const props = defineProps<{ sections: PageSection[]; selectedId: string | null }>()
const emit = defineEmits<{ select: [id: string]; add: [parentId: string | null]; remove: [id: string]; toggle: [id: string] }>()

const roots = computed(() => props.sections.filter((section) => section.parent_id === null).sort((a, b) => a.position - b.position))
const children = (parentId: string) => props.sections.filter((section) => section.parent_id === parentId).sort((a, b) => a.position - b.position)
const label = (section: PageSection) => section.variant ? `${section.type} · ${section.variant}` : section.type
</script>

<template>
  <aside class="flex h-full min-h-0 w-[280px] shrink-0 flex-col border-r border-slate-200 bg-white">
    <div class="flex h-12 items-center justify-between border-b border-slate-100 px-4">
      <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Layers</div>
        <div class="text-xs text-slate-400">{{ sections.length }} sections</div>
      </div>
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-slate-900 text-white hover:bg-slate-800" aria-label="Add section" @click="emit('add', null)">
        <Plus class="h-4 w-4" />
      </button>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto p-2">
      <div v-if="roots.length === 0" class="rounded-lg border border-dashed border-slate-300 p-5 text-center">
        <p class="text-sm font-medium text-slate-700">No sections yet</p>
        <p class="mt-1 text-xs text-slate-400">Add a section to start building.</p>
      </div>

      <div v-for="section in roots" :key="section.id" class="space-y-1">
        <button
          type="button"
          :class="['group flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm transition', selectedId === section.id ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100']"
          @click="emit('select', section.id)"
        >
          <GripVertical class="h-4 w-4 shrink-0 opacity-40" />
          <span class="min-w-0 flex-1 truncate font-medium">{{ label(section) }}</span>
          <button type="button" class="rounded p-1 opacity-60 hover:bg-black/10" :aria-label="section.visibility ? 'Hide section' : 'Show section'" @click.stop="emit('toggle', section.id)">
            <Eye v-if="section.visibility" class="h-3.5 w-3.5" />
            <EyeOff v-else class="h-3.5 w-3.5" />
          </button>
          <button type="button" class="rounded p-1 opacity-60 hover:bg-black/10" aria-label="Delete section" @click.stop="emit('remove', section.id)">
            <Trash2 class="h-3.5 w-3.5" />
          </button>
        </button>

        <div v-for="child in children(section.id)" :key="child.id" class="ml-5">
          <button type="button" :class="['flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-xs', selectedId === child.id ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100']" @click="emit('select', child.id)">
            <span class="h-px w-3 bg-current opacity-30" />
            <span class="min-w-0 flex-1 truncate">{{ label(child) }}</span>
            <Eye v-if="child.visibility" class="h-3 w-3 opacity-50" />
            <EyeOff v-else class="h-3 w-3 opacity-50" />
          </button>
        </div>
      </div>
    </div>
  </aside>
</template>
