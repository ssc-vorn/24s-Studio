<script setup lang="ts">
import type { Component } from 'vue'
import { computed } from 'vue'
import { getSectionComponent } from '@/components/cms/builder/section-registry'
import type { PageSection } from '@/types/cms'

const props = defineProps<{
  page: { id: string; title: string; slug: string; template?: string | null; metadata?: Record<string, unknown> }
  version: { id: string; version: number; status?: string; published_at?: string | null }
  sections: PageSection[]
  preview?: boolean
}>()

const roots = computed(() => props.sections.filter((section) => section.parent_id === null).sort((a, b) => a.position - b.position))
const children = (id: string) => props.sections.filter((section) => section.parent_id === id).sort((a, b) => a.position - b.position)
const componentFor = (section: PageSection): Component | null => getSectionComponent(section)
</script>

<template>
  <div class="min-h-screen bg-white text-slate-950">
    <div v-if="preview" class="sticky top-0 z-50 border-b border-amber-200 bg-amber-50 px-4 py-2 text-center text-xs font-semibold text-amber-800">
      Preview · Version {{ version.version }} · {{ version.status }}
    </div>

    <main>
      <template v-for="section in roots" :key="section.id">
        <component :is="componentFor(section)" v-if="componentFor(section)" :section="section" viewport="desktop" />
        <template v-for="child in children(section.id)" :key="child.id">
          <component :is="componentFor(child)" v-if="componentFor(child)" :section="child" viewport="desktop" />
        </template>
      </template>
    </main>
  </div>
</template>
