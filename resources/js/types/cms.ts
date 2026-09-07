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

export interface MediaAsset {
  id: string
  organization_id: string
  path: string
  filename: string
  mime_type: string
  size: number
  width: number | null
  height: number | null
  alt: string | null
  metadata: BuilderJsonObject
  url: string | null
  created_by: number | null
  created_at: string
  updated_at: string
}

export interface MediaListResponse {
  data: MediaAsset[]
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface BuilderSaveState {
  status: 'idle' | 'saving' | 'saved' | 'error'
  error: string | null
  lastSavedAt: string | null
}
