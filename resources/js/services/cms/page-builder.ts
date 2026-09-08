import axios from 'axios'
import type { PageSection } from '@/types/cms'

export interface AutosaveResponse { revision: number }
export interface PageVersionSummary { id: string; version: number; revision: number; status: string; created_by: number | null; created_at: string | null; published_at: string | null; creator?: { id: number; name: string } | null; sections?: PageSection[] }

export async function autosavePageSections(
  organizationId: string,
  pageId: string,
  versionId: string,
  revision: number,
  sections: PageSection[],
): Promise<AutosaveResponse> {
  const response = await axios.post<{ data: AutosaveResponse }>(
    `/api/v1/organizations/${organizationId}/pages/${pageId}/versions/${versionId}/sections/autosave`,
    { revision, sections },
  )
  return response.data.data
}

async function transitionPageVersion(organizationId: string, pageId: string, versionId: string, transition: 'submit-review' | 'approve'): Promise<void> {
  await axios.post(`/api/v1/organizations/${organizationId}/pages/${pageId}/versions/${versionId}/${transition}`)
}

export async function submitPageVersionForReview(organizationId: string, pageId: string, versionId: string): Promise<void> { await transitionPageVersion(organizationId, pageId, versionId, 'submit-review') }
export async function approvePageVersion(organizationId: string, pageId: string, versionId: string): Promise<void> { await transitionPageVersion(organizationId, pageId, versionId, 'approve') }
export async function publishPageVersion(organizationId: string, pageId: string, versionId: string): Promise<void> { await axios.post(`/api/v1/organizations/${organizationId}/pages/${pageId}/versions/${versionId}/publish`) }

export async function listPageVersions(organizationId: string, pageId: string): Promise<PageVersionSummary[]> {
  const response = await axios.get<{ data: PageVersionSummary[] }>(`/api/v1/organizations/${organizationId}/pages/${pageId}/versions`, { params: { per_page: 50 } })
  return response.data.data
}

export async function getPageVersion(organizationId: string, pageId: string, versionId: string): Promise<PageVersionSummary> {
  const response = await axios.get<{ data: PageVersionSummary }>(`/api/v1/organizations/${organizationId}/pages/${pageId}/versions/${versionId}`)
  return response.data.data
}

export async function restorePageVersion(organizationId: string, pageId: string, versionId: string): Promise<PageVersionSummary> {
  const response = await axios.post<{ data: PageVersionSummary }>(`/api/v1/organizations/${organizationId}/pages/${pageId}/versions/${versionId}/restore`)
  return response.data.data
}
