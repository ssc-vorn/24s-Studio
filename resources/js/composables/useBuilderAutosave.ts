import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { storeToRefs } from 'pinia'
import { useBuilderStore } from '@/stores/builder'
import { autosavePageSections } from '@/services/cms/page-builder'

export interface BuilderAutosaveOptions {
  organizationId: string
  pageId: string
  debounceMs?: number
}

export function useBuilderAutosave(options: BuilderAutosaveOptions) {
  const builder = useBuilderStore()
  const { pageVersionId, sections, revision, dirty } = storeToRefs(builder)
  const saving = ref(false)
  const queued = ref(false)
  let savePromise: Promise<void> | null = null
  let stopped = false

  const persist = async () => {
    const versionId = pageVersionId.value
    if (stopped || !versionId || !dirty.value) return

    if (savePromise) {
      queued.value = true
      return savePromise
    }

    const snapshot = structuredClone(sections.value)
    const expectedRevision = revision.value
    builder.markSaving()

    savePromise = (async () => {
      try {
        const result = await autosavePageSections(
          options.organizationId,
          options.pageId,
          versionId,
          expectedRevision,
          snapshot,
        )

        const current = structuredClone(sections.value)
        if (JSON.stringify(current) === JSON.stringify(snapshot)) {
          builder.markSaved(result.revision)
        } else {
          builder.setRevision(result.revision)
          queued.value = true
        }
      } catch (error: any) {
        const status = error?.response?.status
        builder.markSaveError(
          status === 409
            ? 'Another editor changed this page. Reload the latest version before continuing.'
            : 'Autosave failed. Your local changes are still available; retry when the connection is restored.',
          status === 409,
        )
      } finally {
        savePromise = null
        saving.value = false
        if (queued.value && !stopped) {
          queued.value = false
          scheduleSave()
        }
      }
    })()

    saving.value = true
    return savePromise
  }

  const scheduleSave = useDebounceFn(() => { void persist() }, options.debounceMs ?? 1500)

  watch(sections, () => {
    if (dirty.value) scheduleSave()
  }, { deep: true })

  const flushBeforeLeave = (event: BeforeUnloadEvent) => {
    if (!dirty.value) return
    event.preventDefault()
    event.returnValue = ''
  }

  onMounted(() => window.addEventListener('beforeunload', flushBeforeLeave))
  onBeforeUnmount(() => {
    stopped = true
    window.removeEventListener('beforeunload', flushBeforeLeave)
    scheduleSave.cancel()
  })

  return { saving, queued, persist, scheduleSave }
}
