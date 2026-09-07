<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { useBuilderStore } from '@/stores/builder'
import { useBuilderAutosave } from '@/composables/useBuilderAutosave'
import type { BuilderSectionInput, BuilderViewport, PageSection } from '@/types/cms'
import BuilderToolbar from './BuilderToolbar.vue'
import BuilderLayersPanel from './BuilderLayersPanel.vue'
import BuilderCanvas from './BuilderCanvas.vue'
import BuilderInspector from './BuilderInspector.vue'

const props = defineProps<{ organization: { id: string; name: string }; page: { id: string; title: string; slug: string }; version: { id: string; version: number; revision: number; status: string; sections: PageSection[] } }>()
const builder = useBuilderStore()
const publishing = ref(false)
const { scheduleSave, persist } = useBuilderAutosave({ organizationId: props.organization.id, pageId: props.page.id })
onMounted(() => builder.hydrate(props.version.id, props.version.sections, props.version.revision))
const selected = computed(() => builder.selectedSection)
const canPublish = computed(() => props.version.status === 'approved')
const addSection = (parentId: string | null) => {
  const id = crypto.randomUUID()
  const input: BuilderSectionInput = { parent_id: parentId, type: 'hero', variant: 'default', content: { title: parentId ? 'Nested section' : 'Ideas That Inspire.', description: 'Designs That Deliver.' }, styles: {}, responsive: {}, animation: {}, is_visible: true }
  builder.addSection(input, id)
  scheduleSave()
}
const updateSelected = (patch: Partial<PageSection>) => { if (builder.selectedSectionId) { builder.updateSection(builder.selectedSectionId, patch); scheduleSave() } }
const toggle = (id: string) => { const section = builder.sections.find((item) => item.id === id); if (section) { builder.updateSection(id, { is_visible: !section.is_visible }); scheduleSave() } }
const remove = (id: string) => { if (window.confirm('Delete this section and its nested sections?')) { builder.removeSection(id); scheduleSave() } }
const move = (id: string, targetId: string, asChild: boolean) => { builder.moveSection(id, targetId, asChild); scheduleSave() }
const goBack = () => router.visit('/dashboard')
const preview = () => window.open(`/admin/cms/organizations/${props.organization.id}/pages/${props.page.id}/builder/${props.version.id}/preview`, '_blank', 'noopener,noreferrer')
const publish = async () => {
  if (publishing.value || !canPublish.value) return
  publishing.value = true
  try {
    await persist()
    if (builder.dirty || builder.saveState === 'saving') return
    await axios.post(`/api/v1/organizations/${props.organization.id}/pages/${props.page.id}/versions/${props.version.id}/publish`)
    window.location.reload()
  } finally {
    publishing.value = false
  }
}
</script>

<template>
  <div class="flex h-screen min-h-[620px] flex-col overflow-hidden bg-slate-100 text-slate-900">
    <BuilderToolbar :title="page.title" :version="version.version" :viewport="builder.viewport" :can-undo="builder.canUndo" :can-redo="builder.canRedo" :save-state="builder.saveState" :dirty="builder.dirty" :publishing="publishing" :can-publish="canPublish" @back="goBack" @update:viewport="(value: BuilderViewport) => builder.setViewport(value)" @undo="builder.undo" @redo="builder.redo" @preview="preview" @publish="publish" />
    <div class="flex min-h-0 flex-1">
      <BuilderLayersPanel :sections="builder.sections" :selected-id="builder.selectedSectionId" @select="builder.select" @add="addSection" @remove="remove" @toggle="toggle" @move="move" />
      <BuilderCanvas :sections="builder.sections" :selected-id="builder.selectedSectionId" :viewport="builder.viewport" @select="builder.select" @add="addSection" />
      <BuilderInspector :section="selected" :viewport="builder.viewport" :organization-id="organization.id" @update="updateSelected" />
    </div>
  </div>
</template>
