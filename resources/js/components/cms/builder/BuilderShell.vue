<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useBuilderStore } from '@/stores/builder'
import { useBuilderAutosave } from '@/composables/useBuilderAutosave'
import { approvePageVersion, publishPageVersion, submitPageVersionForReview } from '@/services/cms/page-builder'
import type { BuilderSectionInput, BuilderViewport, PageSection } from '@/types/cms'
import BuilderToolbar from './BuilderToolbar.vue'
import BuilderLayersPanel from './BuilderLayersPanel.vue'
import BuilderCanvas from './BuilderCanvas.vue'
import BuilderInspector from './BuilderInspector.vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps<{ organization: { id: string; name: string }; page: { id: string; title: string; slug: string }; version: { id: string; version: number; revision: number; status: string; sections: PageSection[] } }>()
const builder = useBuilderStore()
const workflowBusy = ref(false)
const workflowError = ref('')
const workflowMessage = ref('')
type WorkflowAction = 'submit-review' | 'approve' | 'publish'
const pendingWorkflowAction = ref<WorkflowAction | null>(null)
const pendingDeleteId = ref<string | null>(null)
const deletedSectionsSnapshot = ref<PageSection[] | null>(null)
const deletedSectionCount = ref(0)
let deleteUndoTimeout: ReturnType<typeof setTimeout> | null = null
const saveRecoveryBusy = ref(false)
const leaveDialogOpen = ref(false)
const leaveError = ref('')
const pendingLeaveUrl = ref<string | null>(null)
const bypassLeaveGuard = ref(false)
const copyFeedback = ref('')
const localDraftBackup = ref<{ saved_at: string; sections: PageSection[] } | null>(null)
const { scheduleSave, persist } = useBuilderAutosave({ organizationId: props.organization.id, pageId: props.page.id })
const draftBackupKey = `cms-builder-draft:${props.version.id}`
onMounted(() => {
  builder.hydrate(props.version.id, props.version.sections, props.version.revision)
  try {
    const backup = JSON.parse(sessionStorage.getItem(draftBackupKey) ?? 'null')
    if (backup?.saved_at && Array.isArray(backup.sections)) localDraftBackup.value = backup
  } catch {
    sessionStorage.removeItem(draftBackupKey)
  }
})
const selected = computed(() => builder.selectedSection)
const status = ref(props.version.status)
const canSubmitReview = computed(() => status.value === 'draft')
const canApprove = computed(() => status.value === 'review')
const canPublish = computed(() => status.value === 'approved')
const hasSaveProblem = computed(() => builder.saveState === 'error' || builder.saveState === 'conflict')
const sectionsPendingDeletion = computed(() => {
  if (!pendingDeleteId.value) return []
  const ids = new Set<string>([pendingDeleteId.value])
  let changed = true
  while (changed) {
    changed = false
    builder.sections.forEach((section) => {
      if (section.parent_id && ids.has(section.parent_id) && !ids.has(section.id)) {
        ids.add(section.id)
        changed = true
      }
    })
  }
  return builder.sections.filter((section) => ids.has(section.id))
})
const sectionLabel = (section: PageSection) => typeof section.content.title === 'string' && section.content.title.trim()
  ? section.content.title
  : section.type
const workflowConfirmation = computed(() => {
  if (pendingWorkflowAction.value === 'submit-review') return {
    title: 'Submit this version for review?',
    description: 'This sends the current draft into the review stage. Make sure the content is ready for review.',
    confirm: 'Submit for review',
  }
  if (pendingWorkflowAction.value === 'approve') return {
    title: 'Approve this version?',
    description: 'This confirms the reviewed content is ready to be published.',
    confirm: 'Approve version',
  }
  return {
    title: 'Publish this version?',
    description: 'This will make this approved version live and replace the currently published version for this page.',
    confirm: 'Publish now',
  }
})
const addSection = (parentId: string | null) => {
  const id = crypto.randomUUID()
  const input: BuilderSectionInput = { parent_id: parentId, type: 'hero', variant: 'default', content: { title: parentId ? 'Nested section' : 'Ideas That Inspire.', description: 'Designs That Deliver.' }, styles: {}, responsive: {}, animation: {}, is_visible: true }
  builder.addSection(input, id)
  scheduleSave()
}
const updateSelected = (patch: Partial<PageSection>) => { if (builder.selectedSectionId) { builder.updateSection(builder.selectedSectionId, patch); scheduleSave() } }
const toggle = (id: string) => { const section = builder.sections.find((item) => item.id === id); if (section) { builder.updateSection(id, { is_visible: !section.is_visible }); scheduleSave() } }
const requestDelete = (id: string) => { pendingDeleteId.value = id }
const confirmDelete = () => {
  if (!pendingDeleteId.value) return
  deletedSectionsSnapshot.value = structuredClone(builder.sections)
  deletedSectionCount.value = sectionsPendingDeletion.value.length
  builder.removeSection(pendingDeleteId.value)
  pendingDeleteId.value = null
  scheduleSave()
  if (deleteUndoTimeout) clearTimeout(deleteUndoTimeout)
  deleteUndoTimeout = setTimeout(() => { deletedSectionsSnapshot.value = null }, 10_000)
}
const undoDelete = () => {
  if (!deletedSectionsSnapshot.value) return
  builder.restoreLocalDraft(deletedSectionsSnapshot.value)
  deletedSectionsSnapshot.value = null
  if (deleteUndoTimeout) clearTimeout(deleteUndoTimeout)
  deleteUndoTimeout = null
  scheduleSave()
}
const move = (id: string, targetId: string, asChild: boolean) => { builder.moveSection(id, targetId, asChild); scheduleSave() }
const requestLeave = (url: string) => {
  leaveError.value = ''
  if (builder.dirty) {
    pendingLeaveUrl.value = url
    leaveDialogOpen.value = true
    return
  }
  bypassLeaveGuard.value = true
  router.visit(url)
}
const leaveBuilder = () => requestLeave('/dashboard')
const completeLeave = () => {
  const destination = pendingLeaveUrl.value ?? '/dashboard'
  bypassLeaveGuard.value = true
  router.visit(destination)
}
const goBack = () => leaveBuilder()
const removeBeforeVisitListener = router.on('before', (event: any) => {
  if (bypassLeaveGuard.value || !builder.dirty) return
  event.preventDefault()
  pendingLeaveUrl.value = event.detail.visit.url.href
  leaveDialogOpen.value = true
})
onBeforeUnmount(() => {
  removeBeforeVisitListener()
  if (deleteUndoTimeout) clearTimeout(deleteUndoTimeout)
})
const saveAndLeave = async () => {
  saveRecoveryBusy.value = true
  leaveError.value = ''
  try {
    await persist()
    if (builder.dirty || hasSaveProblem.value || builder.saveState === 'saving') {
      leaveError.value = builder.saveError ?? 'Your changes are not saved yet. Resolve the save issue before leaving.'
      return
    }
    completeLeave()
  } finally {
    saveRecoveryBusy.value = false
  }
}
const retrySave = async () => {
  saveRecoveryBusy.value = true
  copyFeedback.value = ''
  try {
    await persist()
  } finally {
    saveRecoveryBusy.value = false
  }
}
const backUpLocalDraft = () => {
  sessionStorage.setItem(draftBackupKey, JSON.stringify({ saved_at: new Date().toISOString(), sections: builder.sections }))
}
const copyLocalDraft = async () => {
  copyFeedback.value = ''
  try {
    await navigator.clipboard.writeText(JSON.stringify(builder.sections, null, 2))
    copyFeedback.value = 'Local draft copied to your clipboard.'
  } catch {
    copyFeedback.value = 'Unable to copy automatically. Your local draft remains open in this tab.'
  }
}
const copyDraftBackup = async () => {
  if (!localDraftBackup.value) return
  try {
    await navigator.clipboard.writeText(JSON.stringify(localDraftBackup.value.sections, null, 2))
    copyFeedback.value = 'Backed-up local draft copied to your clipboard.'
  } catch {
    copyFeedback.value = 'Unable to copy automatically. Keep this tab open while you recover the draft.'
  }
}
const discardDraftBackup = () => {
  sessionStorage.removeItem(draftBackupKey)
  localDraftBackup.value = null
}
const restoreDraftBackup = () => {
  if (!localDraftBackup.value) return
  builder.restoreLocalDraft(localDraftBackup.value.sections)
  discardDraftBackup()
  copyFeedback.value = 'Local draft restored. It will be saved automatically.'
}
const reloadLatest = () => {
  backUpLocalDraft()
  window.location.reload()
}
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
  workflowMessage.value = ''
  try {
    await ensureSaved()
    await action()
    status.value = nextStatus
    workflowMessage.value = nextStatus === 'published'
      ? 'Version published successfully. The live page is now available.'
      : `Version moved to ${nextStatus === 'review' ? 'review' : 'approved'} successfully.`
  } catch (error) {
    workflowError.value = error instanceof Error ? error.message : 'Workflow action failed.'
  } finally {
    workflowBusy.value = false
  }
}
const submitReview = () => runWorkflow(() => submitPageVersionForReview(props.organization.id, props.page.id, props.version.id), 'review')
const approve = () => runWorkflow(() => approvePageVersion(props.organization.id, props.page.id, props.version.id), 'approved')
const publish = () => runWorkflow(() => publishPageVersion(props.organization.id, props.page.id, props.version.id), 'published')
const requestWorkflow = (action: WorkflowAction) => {
  workflowError.value = ''
  workflowMessage.value = ''
  pendingWorkflowAction.value = action
}
const confirmWorkflow = async () => {
  const action = pendingWorkflowAction.value
  if (!action) return
  pendingWorkflowAction.value = null
  if (action === 'submit-review') await submitReview()
  if (action === 'approve') await approve()
  if (action === 'publish') await publish()
}
const openLivePage = () => window.open(`/pages/${props.page.slug}`, '_blank', 'noopener,noreferrer')
</script>

<template>
  <div class="flex h-screen min-h-[620px] flex-col overflow-hidden bg-slate-100 text-slate-900">
    <BuilderToolbar :title="page.title" :version="version.version" :status="status" :viewport="builder.viewport" :can-undo="builder.canUndo" :can-redo="builder.canRedo" :save-state="builder.saveState" :dirty="builder.dirty" :workflow-busy="workflowBusy" :can-submit-review="canSubmitReview" :can-approve="canApprove" :can-publish="canPublish" @back="goBack" @update:viewport="(value: BuilderViewport) => builder.setViewport(value)" @undo="builder.undo" @redo="builder.redo" @preview="preview" @submit-review="requestWorkflow('submit-review')" @approve="requestWorkflow('approve')" @publish="requestWorkflow('publish')" />
    <div v-if="workflowError" class="border-b border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700" role="alert">{{ workflowError }}</div>
    <div v-if="workflowMessage" class="flex flex-wrap items-center gap-3 border-b border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800" role="status" aria-live="polite">
      <p class="font-medium">{{ workflowMessage }}</p>
      <button v-if="status === 'published'" type="button" class="rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100" @click="openLivePage">Open live page</button>
    </div>
    <div v-if="localDraftBackup" class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
      <p class="font-medium">A local draft backup from {{ new Date(localDraftBackup.saved_at).toLocaleString() }} is available.</p>
      <div class="flex gap-2">
        <button type="button" class="rounded-md border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100" @click="restoreDraftBackup">Restore backup</button>
        <button type="button" class="rounded-md border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100" @click="copyDraftBackup">Copy backup</button>
        <button type="button" class="rounded-md px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100" @click="discardDraftBackup">Dismiss</button>
      </div>
    </div>
    <div v-if="hasSaveProblem" class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert" aria-live="assertive">
      <p class="font-medium">{{ builder.saveError ?? 'Your latest changes could not be saved.' }}</p>
      <div class="flex flex-wrap gap-2">
        <button v-if="builder.saveState === 'error'" type="button" :disabled="saveRecoveryBusy" class="rounded-md bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-60" @click="retrySave">{{ saveRecoveryBusy ? 'Retrying…' : 'Retry save' }}</button>
        <button type="button" class="rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100" @click="copyLocalDraft">Copy local draft</button>
        <button v-if="builder.saveState === 'conflict'" type="button" class="rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100" @click="reloadLatest">Back up and reload latest</button>
      </div>
      <p v-if="copyFeedback" class="w-full text-xs text-red-700">{{ copyFeedback }}</p>
    </div>
    <div class="flex min-h-0 flex-1">
      <BuilderLayersPanel :sections="builder.sections" :selected-id="builder.selectedSectionId" @select="builder.select" @add="addSection" @remove="requestDelete" @toggle="toggle" @move="move" />
      <BuilderCanvas :sections="builder.sections" :selected-id="builder.selectedSectionId" :viewport="builder.viewport" @select="builder.select" @add="addSection" />
      <BuilderInspector :section="selected" :viewport="builder.viewport" :organization-id="organization.id" @update="updateSelected" />
    </div>
    <Modal :show="leaveDialogOpen" max-width="md" @close="leaveDialogOpen = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-slate-900">Leave with unsaved changes?</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Your recent edits have not been confirmed on the server. Save before leaving to keep them in this page version.</p>
        <p v-if="leaveError" class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700" role="alert">{{ leaveError }}</p>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
          <SecondaryButton type="button" @click="leaveDialogOpen = false">Stay editing</SecondaryButton>
          <button type="button" class="rounded-md px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" @click="completeLeave">Leave without saving</button>
          <PrimaryButton type="button" :disabled="saveRecoveryBusy" @click="saveAndLeave">{{ saveRecoveryBusy ? 'Saving…' : 'Save and leave' }}</PrimaryButton>
        </div>
      </div>
    </Modal>
    <Modal :show="pendingWorkflowAction !== null" max-width="md" @close="pendingWorkflowAction = null">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-slate-900">{{ workflowConfirmation.title }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ workflowConfirmation.description }}</p>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
          <SecondaryButton type="button" :disabled="workflowBusy" @click="pendingWorkflowAction = null">Cancel</SecondaryButton>
          <PrimaryButton type="button" :disabled="workflowBusy" @click="confirmWorkflow">{{ workflowBusy ? 'Working…' : workflowConfirmation.confirm }}</PrimaryButton>
        </div>
      </div>
    </Modal>
    <Modal :show="pendingDeleteId !== null" max-width="md" @close="pendingDeleteId = null">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-slate-900">Delete {{ sectionsPendingDeletion.length }} {{ sectionsPendingDeletion.length === 1 ? 'section' : 'sections' }}?</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">This includes nested sections. You can undo this action for the next 10 seconds.</p>
        <ul class="mt-4 max-h-32 space-y-1 overflow-y-auto rounded-md bg-slate-50 p-3 text-sm text-slate-700">
          <li v-for="section in sectionsPendingDeletion" :key="section.id">{{ sectionLabel(section) }}</li>
        </ul>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
          <SecondaryButton type="button" @click="pendingDeleteId = null">Cancel</SecondaryButton>
          <button type="button" class="rounded-md bg-red-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" @click="confirmDelete">Delete sections</button>
        </div>
      </div>
    </Modal>
    <div v-if="deletedSectionsSnapshot" class="fixed bottom-4 left-1/2 z-50 flex -translate-x-1/2 items-center gap-4 rounded-lg bg-slate-900 px-4 py-3 text-sm text-white shadow-lg" role="status" aria-live="polite">
      <span>{{ deletedSectionCount }} {{ deletedSectionCount === 1 ? 'section was' : 'sections were' }} deleted.</span>
      <button type="button" class="font-semibold text-white underline underline-offset-2" @click="undoDelete">Undo</button>
    </div>
  </div>
</template>
