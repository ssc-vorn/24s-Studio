import axios from 'axios'
import type { MediaAsset, MediaListResponse } from '@/types/cms'

const base = (organizationId: string) => `/api/v1/organizations/${organizationId}/media`

export interface MediaListParams {
  search?: string
  mime_type?: string
  per_page?: number
  page?: number
}

export const mediaApi = {
  async list(organizationId: string, params: MediaListParams = {}): Promise<MediaListResponse> {
    const response = await axios.get<MediaListResponse>(base(organizationId), { params })
    return response.data
  },

  async upload(
    organizationId: string,
    file: File,
    alt = '',
    metadata: Record<string, unknown> = {},
    onProgress?: (percent: number) => void,
  ): Promise<MediaAsset> {
    const form = new FormData()
    form.append('file', file)
    if (alt) form.append('alt', alt)
    form.append('metadata', JSON.stringify(metadata))

    const response = await axios.post<{ data: MediaAsset }>(base(organizationId), form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (event) => {
        if (event.total) onProgress?.(Math.round((event.loaded * 100) / event.total))
      },
    })

    return response.data.data
  },

  async update(organizationId: string, mediaId: string, payload: { alt?: string; metadata?: Record<string, unknown> }): Promise<MediaAsset> {
    const response = await axios.patch<{ data: MediaAsset }>(`${base(organizationId)}/${mediaId}`, payload)
    return response.data.data
  },

  async remove(organizationId: string, mediaId: string): Promise<void> {
    await axios.delete(`${base(organizationId)}/${mediaId}`)
  },
}
