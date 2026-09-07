import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { storeToRefs } from 'pinia'
import { useBuilderStore } from '@/stores/builder'
import { pageSectionsApi } from '@/services/cms/page-sections'
import type { BuilderSectionInput, PageSection } from '@/types/cms'

export interface BuilderAutosaveOptions {
  organizationId: string
  pageId: string
  debounceMs?: number
}

const editableFields = (section: PageSection): BuilderSectionInput => ({
  parent_id: section.parent_id,
  type: section.type,
  variant: section.variant,
  position: section.position,
  content: section.content,
  styles: section.styles,
  responsive: section.responsive,
  animation: section.animation,
  visibility: section.visibility,
})

export function useBuilderAutosave(options: BuilderAutosaveOptions) {
  const builder = useBuilderStore()
  const { pageVersionId, sections, dirty } = storeToRefs(builder)
  const saving = ref(false)
  const queued = ref(false)
  let savePromise: Promise<void> | null = null

  const persist = async () => {
    const versionId = pageVersionId.value
    if (!versionId || !dirty.value) return

    if (savePromise) {
      queued.value = true
      return savePromise
    }

    const snapshot = structuredClone(sections.value)
    const snapshotIds = new Set(snapshot.map((section) => section.id))

    builder.markSaving()

    savePromise = (async () => {
      try {
        const serverSections = await pageSectionsApi.list(options.organizationId, options.pageId, versionId)
        const serverIds = new Set(serverSections.map((section) => section.id))

        for (const section of snapshot) {
          if (serverIds.has(section.id)) {
            await pageSectionsApi.update(
              options.organizationId,
              options.pageId,
              versionId,
              section.id,
              editableFields(section),
            )
          } else {
            await pageSectionsApi.create(
              options.organizationId,
              options.pageId,
              versionId,
              editableFields(section),
            )
          }
        }

        for (const serverSection of serverSections) {
          if (!snapshotIds.has(serverSection.id)) {
            await pageSectionsApi.remove(
              options.organizationId,
              options.pageId,
              versionId,
              serverSection.id,
            )
          }
        }

        const orderedIds = snapshot
          .filter((section) => section.parent_id === null)
          .sort((a, b) => a.position - b.position)
          .map((section) => section.id)

        if (orderedIds.length > 0) {
          await pageSectionsApi.reorder(
            options.organizationId,
            options.pageId,
            versionId,
            orderedIds,
          )
        }

        if (structuredClone(sections.value).every((section, index) =>
          JSON.stringify(section) === JSON.stringify(snapshot[index]),
        )) {
          builder.markSaved()
        } else {
          queued.value = true
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : 'Unable to autosave builder changes.'
        builder.markSaveError(message)
      } finally {
        savePromise = null
        saving.value = false

        if (queued.value) {
          queued.value = false
          scheduleSave()
        }
      }
    })()

    saving.value = true
    return savePromise
  }

  const scheduleSave = useDebounceFn(() => {
    void persist()
  }, options.debounceMs ?? 1500)

  watch(
    sections,
    () => {
      if (dirty.value) scheduleSave()
    },
    { deep: true },
  )

  const flushBeforeLeave = (event: BeforeUnloadEvent) => {
    if (!dirty.value) return
    event.preventDefault()
    event.returnValue = ''
  }

  onMounted(() => window.addEventListener('beforeunload', flushBeforeLeave))
  onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', flushBeforeLeave)
    scheduleSave.cancel()
  })

  return {
    saving,
    queued,
    persist,
    scheduleSave,
  }
}
