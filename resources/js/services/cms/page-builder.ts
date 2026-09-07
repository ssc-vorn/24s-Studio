import axios from 'axios'
import type { PageSection } from '@/types/cms'

export interface AutosaveResponse { revision: number }

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
