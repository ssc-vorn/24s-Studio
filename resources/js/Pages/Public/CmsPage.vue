<script setup lang="ts">
import { computed } from 'vue'
import type { Component } from 'vue'
import { Head } from '@inertiajs/vue3'
import type { PageSection } from '@/types/cms'
import { getSectionComponent } from '@/components/cms/builder/section-registry'

const props = defineProps<{
  page: { id: string; title: string; slug: string; template: string; metadata: Record<string, unknown> }
  version: { id: string; version: number; published_at: string | null }
  sections: PageSection[]
}>()

const roots = computed(() => props.sections.filter((section) => section.parent_id === null).sort((a, b) => a.position - b.position))
const children = (id: string) => props.sections.filter((section) => section.parent_id === id).sort((a, b) => a.position - b.position)
const componentFor = (section: PageSection): Component | null => getSectionComponent(section)
</script>

<template>
  <Head :title="page.title" />
  <main class="min-h-screen bg-white text-slate-950">
    <template v-for="section in roots" :key="section.id">
      <component
        :is="componentFor(section)"
        v-if="componentFor(section)"
        :section="section"
        viewport="desktop"
      />
      <template v-for="child in children(section.id)" :key="child.id">
        <component
          :is="componentFor(child)"
          v-if="componentFor(child)"
          :section="child"
          viewport="desktop"
        />
      </template>
    </template>
  </main>
</template>
