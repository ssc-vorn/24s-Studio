export type BuilderViewport = 'desktop' | 'tablet' | 'mobile'

export type BuilderJsonValue = string | number | boolean | null | BuilderJsonObject | BuilderJsonValue[]
export type BuilderJsonObject = { [key: string]: BuilderJsonValue }

export interface PageSection {
  id: string
  page_version_id: string
  parent_id: string | null
  type: string
  variant: string | null
  position: number
  content: BuilderJsonObject
  styles: BuilderJsonObject
  responsive: BuilderJsonObject
  animation: BuilderJsonObject
  visibility: boolean
  created_at?: string
  updated_at?: string
}

export interface BuilderSectionInput {
  parent_id?: string | null
  type: string
  variant?: string | null
  position?: number
  content?: BuilderJsonObject
  styles?: BuilderJsonObject
  responsive?: BuilderJsonObject
  animation?: BuilderJsonObject
  visibility?: boolean
}

export interface BuilderSaveState {
  status: 'idle' | 'saving' | 'saved' | 'error'
  error: string | null
  lastSavedAt: string | null
}
