import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'

export type Direction = 'latin_to_sunda' | 'sunda_to_latin'

export interface TransliterationResult {
  input: string
  output: string
  direction: Direction
}

export const useTransliterationStore = defineStore('transliteration', () => {
  const result  = ref<TransliterationResult | null>(null)
  const loading = ref(false)
  const error   = ref<string | null>(null)
  const history = ref<TransliterationResult[]>([])

  async function transliterate(text: string, direction: Direction) {
    loading.value = true
    error.value   = null
    try {
      const { data } = await api.post('/transliterate', { text, direction })
      result.value = data
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Transliterasi gagal.'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchMappings() {
    const { data } = await api.get('/transliterate/mappings')
    return data.data
  }

  async function fetchHurufDasar() {
    const { data } = await api.get('/transliterate/huruf-dasar')
    return data.data
  }

  async function fetchRarangken() {
    const { data } = await api.get('/transliterate/rarangken')
    return data.data
  }

  async function fetchAngkaSunda() {
    const { data } = await api.get('/transliterate/angka')
    return data.data
  }

  function clear() {
    result.value = null
    error.value  = null
  }

  return {
    result, loading, error, history,
    transliterate, fetchMappings, fetchHurufDasar, fetchRarangken, fetchAngkaSunda, clear,
  }
})
