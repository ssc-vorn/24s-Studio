import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import type { BuilderSectionInput, BuilderViewport, PageSection } from '@/types/cms'

const clone = <T>(value: T): T => structuredClone(value)

export const useBuilderStore = defineStore('builder', () => {
  const pageVersionId = ref<string | null>(null)
  const revision = ref(1)
  const sections = ref<PageSection[]>([])
  const selectedSectionId = ref<string | null>(null)
  const viewport = ref<BuilderViewport>('desktop')
  const dirty = ref(false)
  const saveState = ref<'idle' | 'saving' | 'saved' | 'error' | 'conflict'>('idle')
  const saveError = ref<string | null>(null)
  const lastSavedAt = ref<string | null>(null)
  const history = ref<PageSection[][]>([])
  const future = ref<PageSection[][]>([])

  const selectedSection = computed(() => sections.value.find((section) => section.id === selectedSectionId.value) ?? null)
  const rootSections = computed(() => [...sections.value].filter((section) => section.parent_id === null).sort((a, b) => a.position - b.position))
  const canUndo = computed(() => history.value.length > 0)
  const canRedo = computed(() => future.value.length > 0)

  function snapshot(): PageSection[] { return clone(sections.value) }
  function commit(next: PageSection[]) {
    history.value.push(snapshot())
    if (history.value.length > 50) history.value.shift()
    future.value = []
    sections.value = clone(next)
    dirty.value = true
    saveState.value = 'idle'
    saveError.value = null
  }
  function hydrate(versionId: string, incoming: PageSection[], serverRevision = 1) {
    pageVersionId.value = versionId
    revision.value = serverRevision
    sections.value = clone(incoming).sort((a, b) => a.position - b.position)
    selectedSectionId.value = null
    history.value = []
    future.value = []
    dirty.value = false
    saveState.value = 'saved'
    saveError.value = null
    lastSavedAt.value = new Date().toISOString()
  }
  function select(id: string | null) { selectedSectionId.value = id }
  function setViewport(next: BuilderViewport) { viewport.value = next }
  function addSection(input: BuilderSectionInput, id: string) {
    const next = snapshot()
    const parentId = input.parent_id ?? null
    const siblings = next.filter((section) => section.parent_id === parentId)
    const position = input.position ?? siblings.length
    siblings.filter((section) => section.position >= position).forEach((section) => { section.position += 1 })
    next.push({ id, page_version_id: pageVersionId.value ?? '', parent_id: parentId, type: input.type, variant: input.variant ?? null, position, content: clone(input.content ?? {}), styles: clone(input.styles ?? {}), responsive: clone(input.responsive ?? {}), animation: clone(input.animation ?? {}), visibility: input.visibility ?? true })
    commit(next)
    select(id)
  }
  function updateSection(id: string, patch: Partial<BuilderSectionInput>) {
    const next = snapshot()
    const index = next.findIndex((section) => section.id === id)
    if (index === -1) return
    next[index] = { ...next[index], ...clone(patch) }
    commit(next)
  }
  function removeSection(id: string) {
    const next = snapshot()
    const ids = new Set<string>([id])
    let changed = true
    while (changed) {
      changed = false
      next.forEach((section) => { if (section.parent_id && ids.has(section.parent_id) && !ids.has(section.id)) { ids.add(section.id); changed = true } })
    }
    commit(next.filter((section) => !ids.has(section.id)))
    if (ids.has(selectedSectionId.value ?? '')) select(null)
  }
  function reorder(ids: string[], parentId: string | null = null) {
    const next = snapshot()
    const ordered = new Map(ids.map((id, index) => [id, index]))
    next.forEach((section) => { if (ordered.has(section.id)) { section.parent_id = parentId; section.position = ordered.get(section.id)! } })
    commit(next)
  }
  function moveSection(id: string, targetId: string, asChild = false) {
    if (id === targetId) return
    const next = snapshot()
    const dragged = next.find((section) => section.id === id)
    const target = next.find((section) => section.id === targetId)
    if (!dragged || !target) return

    const descendantIds = new Set<string>([id])
    let changed = true
    while (changed) {
      changed = false
      next.forEach((section) => {
        if (section.parent_id && descendantIds.has(section.parent_id) && !descendantIds.has(section.id)) {
          descendantIds.add(section.id)
          changed = true
        }
      })
    }
    if (asChild && descendantIds.has(targetId)) return

    const oldParentId = dragged.parent_id
    const newParentId = asChild ? target.id : target.parent_id
    const remaining = next.filter((section) => section.id !== id)
    const siblings = remaining
      .filter((section) => section.parent_id === newParentId)
      .sort((a, b) => a.position - b.position)

    dragged.parent_id = newParentId
    const targetIndex = asChild ? siblings.length : siblings.findIndex((section) => section.id === targetId) + 1
    siblings.splice(Math.max(0, targetIndex), 0, dragged)

    siblings.forEach((section, index) => { section.position = index })
    if (oldParentId !== newParentId) {
      remaining
        .filter((section) => section.parent_id === oldParentId)
        .sort((a, b) => a.position - b.position)
        .forEach((section, index) => { section.position = index })
    }

    commit(remaining)
    select(id)
  }
  function undo() {
    const previous = history.value.pop()
    if (!previous) return
    future.value.push(snapshot())
    sections.value = clone(previous)
    dirty.value = true
    saveState.value = 'idle'
  }
  function redo() {
    const next = future.value.pop()
    if (!next) return
    history.value.push(snapshot())
    sections.value = clone(next)
    dirty.value = true
    saveState.value = 'idle'
  }
  function markSaving() { saveState.value = 'saving'; saveError.value = null }
  function markSaved(nextRevision: number) { revision.value = nextRevision; dirty.value = false; saveState.value = 'saved'; saveError.value = null; lastSavedAt.value = new Date().toISOString() }
  function markSaveError(message: string, conflict = false) { saveState.value = conflict ? 'conflict' : 'error'; saveError.value = message }
  function setRevision(nextRevision: number) { revision.value = nextRevision }
  function markConflict(message: string) { markSaveError(message, true) }

  return { pageVersionId, revision, sections, selectedSectionId, selectedSection, rootSections, viewport, dirty, saveState, saveError, lastSavedAt, canUndo, canRedo, hydrate, select, setViewport, addSection, updateSection, removeSection, reorder, moveSection, undo, redo, markSaving, markSaved, markSaveError, setRevision, markConflict }
})
