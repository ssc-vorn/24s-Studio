import axios from 'axios'
import type { BuilderSectionInput, PageSection } from '@/types/cms'

const base = (organizationId: string, pageId: string, versionId: string) =>
  `/api/v1/organizations/${organizationId}/pages/${pageId}/versions/${versionId}/sections`

export const pageSectionsApi = {
  async list(organizationId: string, pageId: string, versionId: string) {
    const response = await axios.get<{ data: PageSection[] }>(base(organizationId, pageId, versionId))
    return response.data.data
  },

  async create(organizationId: string, pageId: string, versionId: string, data: BuilderSectionInput) {
    const response = await axios.post<{ data: PageSection }>(base(organizationId, pageId, versionId), data)
    return response.data.data
  },

  async update(organizationId: string, pageId: string, versionId: string, sectionId: string, data: Partial<BuilderSectionInput>) {
    const response = await axios.patch<{ data: PageSection }>(`${base(organizationId, pageId, versionId)}/${sectionId}`, data)
    return response.data.data
  },

  async remove(organizationId: string, pageId: string, versionId: string, sectionId: string) {
    await axios.delete(`${base(organizationId, pageId, versionId)}/${sectionId}`)
  },

  async reorder(organizationId: string, pageId: string, versionId: string, sectionIds: string[]) {
    await axios.post(`${base(organizationId, pageId, versionId)}/reorder`, { section_ids: sectionIds })
  },
}
