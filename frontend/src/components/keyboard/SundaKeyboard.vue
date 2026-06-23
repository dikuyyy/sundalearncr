<template>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <!-- Header Tabs -->
    <div class="flex border-b border-gray-200">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        @click="activeTab = tab.id"
        class="flex-1 py-2.5 text-sm font-medium transition-colors"
        :class="activeTab === tab.id
          ? 'bg-sunda-50 text-sunda-700 border-b-2 border-sunda-600'
          : 'text-gray-500 hover:text-gray-700'"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Keyboard Grid -->
    <div class="p-3">
      <!-- Huruf Dasar -->
      <div v-if="activeTab === 'huruf'" class="grid grid-cols-6 sm:grid-cols-9 gap-1.5">
        <KeyButton
          v-for="key in hurufDasar"
          :key="key.sunda"
          :sunda="key.sunda"
          :label="key.latin"
          @click="emit('insert', key.sunda)"
        />
      </div>

      <!-- Vokal Mandiri -->
      <div v-if="activeTab === 'vokal'" class="grid grid-cols-6 gap-1.5">
        <KeyButton
          v-for="v in vokalMandiri"
          :key="v.sunda"
          :sunda="v.sunda"
          :label="v.latin"
          @click="emit('insert', v.sunda)"
        />
      </div>

      <!-- Rarangken -->
      <div v-if="activeTab === 'rarangken'" class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 gap-y-2">
        <KeyButton
          v-for="r in rarangken"
          :key="r.sunda"
          :sunda="r.sunda"
          :label="r.name"
          :description="r.latin"
          @click="emit('insert', r.sunda)"
        />
      </div>

      <!-- Angka -->
      <div v-if="activeTab === 'angka'" class="grid grid-cols-5 sm:grid-cols-10 gap-1.5">
        <KeyButton
          v-for="n in angkaSunda"
          :key="n.sunda"
          :sunda="n.sunda"
          :label="String(n.digit)"
          @click="emit('insert', n.sunda)"
        />
      </div>
    </div>

    <!-- Control Buttons -->
    <div class="flex gap-2 px-3 pb-3">
      <button
        type="button"
        @click="emit('insert', ' ')"
        class="flex-1 py-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
      >
        ⎵ Spasi
      </button>
      <button
        type="button"
        @click="emit('backspace')"
        class="px-4 py-2 text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors"
      >
        ⌫ Hapus
      </button>
      <button
        type="button"
        @click="emit('clear')"
        class="px-4 py-2 text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg transition-colors"
      >
        Hapus Semua
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import KeyButton from './KeyButton.vue'

const emit = defineEmits<{
  insert: [char: string]
  backspace: []
  clear: []
}>()

const activeTab = ref('huruf')

const tabs = [
  { id: 'huruf',    label: 'Huruf Dasar' },
  { id: 'vokal',    label: 'Vokal' },
  { id: 'rarangken', label: 'Rarangken' },
  { id: 'angka',    label: 'Angka' },
]

const hurufDasar = [
  { sunda: 'ᮊ', latin: 'ka' }, { sunda: 'ᮌ', latin: 'ga' },
  { sunda: 'ᮍ', latin: 'nga' }, { sunda: 'ᮎ', latin: 'ca' },
  { sunda: 'ᮏ', latin: 'ja' }, { sunda: 'ᮑ', latin: 'nya' },
  { sunda: 'ᮒ', latin: 'ta' }, { sunda: 'ᮓ', latin: 'da' },
  { sunda: 'ᮔ', latin: 'na' }, { sunda: 'ᮕ', latin: 'pa' },
  { sunda: 'ᮖ', latin: 'fa' }, { sunda: 'ᮗ', latin: 'va' },
  { sunda: 'ᮘ', latin: 'ba' }, { sunda: 'ᮙ', latin: 'ma' },
  { sunda: 'ᮚ', latin: 'ya' }, { sunda: 'ᮛ', latin: 'ra' },
  { sunda: 'ᮜ', latin: 'la' }, { sunda: 'ᮝ', latin: 'wa' },
  { sunda: 'ᮞ', latin: 'sa' }, { sunda: 'ᮠ', latin: 'ha' },
]

const vokalMandiri = [
  { sunda: 'ᮃ', latin: 'a' }, { sunda: 'ᮄ', latin: 'i' },
  { sunda: 'ᮅ', latin: 'u' }, { sunda: 'ᮆ', latin: 'eu' },
  { sunda: 'ᮇ', latin: 'o' }, { sunda: 'ᮈ', latin: 'e' },
]

const rarangken = [
  // Rarangken vokal
  { sunda: 'ᮤ', name: 'Panghulu',   latin: 'i'    },
  { sunda: 'ᮨ', name: 'Pamepet',    latin: 'e'    },
  { sunda: 'ᮩ', name: 'Paneuleung', latin: 'eu'   },
  { sunda: 'ᮦ', name: 'Panéléng',   latin: 'é'    },
  { sunda: 'ᮥ', name: 'Panyuku',    latin: 'u'    },
  { sunda: 'ᮧ', name: 'Panolong',   latin: 'o'    },
  // Rarangken konsonan akhir
  { sunda: 'ᮁ', name: 'Panglayar',  latin: '+r'   },
  { sunda: 'ᮀ', name: 'Panyecek',   latin: '+ng'  },
  { sunda: 'ᮂ', name: 'Pangwisad',  latin: '+h'   },
  // Rarangken konsonan tengah
  { sunda: 'ᮢ', name: 'Panyakra',   latin: '+ra'  },
  { sunda: 'ᮣ', name: 'Panyiku',    latin: '+la'  },
  { sunda: 'ᮡ', name: 'Pamingkal',  latin: '+ya'  },
  // Rarangken pematian vokal
  { sunda: '᮪', name: 'Patén',      latin: 'mati' },
]

const angkaSunda = [
  { sunda: '᮰', digit: 0 }, { sunda: '᮱', digit: 1 },
  { sunda: '᮲', digit: 2 }, { sunda: '᮳', digit: 3 },
  { sunda: '᮴', digit: 4 }, { sunda: '᮵', digit: 5 },
  { sunda: '᮶', digit: 6 }, { sunda: '᮷', digit: 7 },
  { sunda: '᮸', digit: 8 }, { sunda: '᮹', digit: 9 },
]
</script>
