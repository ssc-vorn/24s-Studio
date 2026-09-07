<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useBuilderStore } from '@/stores/builder'
import { useBuilderAutosave } from '@/composables/useBuilderAutosave'
import { approvePageVersion, publishPageVersion, submitPageVersionForReview } from '@/services/cms/page-builder'
import type { BuilderSectionInput, BuilderViewport, PageSection } from '@/types/cms'
import BuilderToolbar from './BuilderToolbar.vue'
import BuilderLayersPanel from './BuilderLayersPanel.vue'
import BuilderCanvas from './BuilderCanvas.vue'
import BuilderInspector from './BuilderInspector.vue'

const props = defineProps<{ organization: { id: string; name: string }; page: { id: string; title: string; slug: string }; version: { id: string; version: number; revision: number; status: string; sections: PageSection[] } }>()
const builder = useBuilderStore()
const workflowBusy = ref(false)
const workflowError = ref('')
const { scheduleSave, persist } = useBuilderAutosave({ organizationId: props.organization.id, pageId: props.page.id })
onMounted(() => builder.hydrate(props.version.id, props.version.sections, props.version.revision))
const selected = computed(() => builder.selectedSection)
const status = ref(props.version.status)
const canSubmitReview = computed(() => status.value === 'draft')
const canApprove = computed(() => status.value === 'review')
const canPublish = computed(() => status.value === 'approved')
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
const ensureSaved = async () => {
  workflowError.value = ''
  await persist()
  if (builder.dirty || builder.saveState === 'saving' || builder.saveState === 'error' || builder.saveState === 'conflict') {
    throw new Error('Please resolve the current save state before changing the version workflow.')
  }
}
const runWorkflow = async (action: () => Promise<void>, nextStatus: string) => {
  if (workflowBusy.value) return
  workflowBusy.value = true
  workflowError.value = ''
  try {
    await ensureSaved()
    await action()
    status.value = nextStatus
  } catch (error) {
    workflowError.value = error instanceof Error ? error.message : 'Workflow action failed.'
  } finally {
    workflowBusy.value = false
  }
}
const submitReview = () => runWorkflow(() => submitPageVersionForReview(props.organization.id, props.page.id, props.version.id), 'review')
const approve = () => runWorkflow(() => approvePageVersion(props.organization.id, props.page.id, props.version.id), 'approved')
const publish = () => runWorkflow(() => publishPageVersion(props.organization.id, props.page.id, props.version.id), 'published')
</script>

<template>
  <div class="flex h-screen min-h-[620px] flex-col overflow-hidden bg-slate-100 text-slate-900">
    <BuilderToolbar :title="page.title" :version="version.version" :status="status" :viewport="builder.viewport" :can-undo="builder.canUndo" :can-redo="builder.canRedo" :save-state="builder.saveState" :dirty="builder.dirty" :workflow-busy="workflowBusy" :can-submit-review="canSubmitReview" :can-approve="canApprove" :can-publish="canPublish" @back="goBack" @update:viewport="(value: BuilderViewport) => builder.setViewport(value)" @undo="builder.undo" @redo="builder.redo" @preview="preview" @submit-review="submitReview" @approve="approve" @publish="publish" />
    <div v-if="workflowError" class="border-b border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700" role="alert">{{ workflowError }}</div>
    <div class="flex min-h-0 flex-1">
      <BuilderLayersPanel :sections="builder.sections" :selected-id="builder.selectedSectionId" @select="builder.select" @add="addSection" @remove="remove" @toggle="toggle" @move="move" />
      <BuilderCanvas :sections="builder.sections" :selected-id="builder.selectedSectionId" :viewport="builder.viewport" @select="builder.select" @add="addSection" />
      <BuilderInspector :section="selected" :viewport="builder.viewport" :organization-id="organization.id" @update="updateSelected" />
    </div>
  </div>
</template>
