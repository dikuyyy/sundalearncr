import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'

export interface MaterialItem {
  id: number
  sunda_script: string
  latin_name: string
  pronunciation: string
  description: string
  examples: Array<{ latin: string; note?: string }>
  order: number
}

export interface Material {
  id: number
  title: string
  description: string
  category: 'huruf_dasar' | 'rarangken' | 'angka' | 'contoh_kata'
  order: number
  is_published: boolean
  created_by: string
  items_count: number
  items: MaterialItem[]
  created_at: string
}

export const useMaterialStore = defineStore('material', () => {
  const materials   = ref<Material[]>([])
  const current     = ref<Material | null>(null)
  const loading     = ref(false)
  const error       = ref<string | null>(null)

  async function fetchAll(category?: string) {
    loading.value = true
    error.value   = null
    try {
      const { data } = await api.get('/materials', { params: { category } })
      materials.value = data.data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Gagal memuat materi.'
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id: number) {
    loading.value = true
    error.value   = null
    try {
      const { data } = await api.get(`/materials/${id}`)
      current.value = data.data
      return data.data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Materi tidak ditemukan.'
    } finally {
      loading.value = false
    }
  }

  async function create(payload: Partial<Material> & { items?: Partial<MaterialItem>[] }) {
    const { data } = await api.post('/materials', payload)
    materials.value.unshift(data.data)
    return data
  }

  async function update(id: number, payload: Partial<Material>) {
    const { data } = await api.put(`/materials/${id}`, payload)
    const idx = materials.value.findIndex((m) => m.id === id)
    if (idx !== -1) materials.value[idx] = data.data
    return data
  }

  async function remove(id: number) {
    await api.delete(`/materials/${id}`)
    materials.value = materials.value.filter((m) => m.id !== id)
  }

  async function markComplete(id: number) {
    return api.put(`/materials/${id}/complete`)
  }

  return {
    materials, current, loading, error,
    fetchAll, fetchOne, create, update, remove, markComplete,
  }
})
