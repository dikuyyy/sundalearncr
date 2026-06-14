<template>
  <div class="max-w-4xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Transliterasi Aksara Sunda</h1>
      <p class="text-gray-500 mt-1">Konversi teks dua arah: Latin ↔ Aksara Sunda</p>
    </div>

    <!-- Direction Toggle -->
    <div class="flex items-center justify-center mb-6">
      <div class="bg-white border border-gray-200 rounded-xl p-1 flex shadow-sm">
        <button
          @click="direction = 'latin_to_sunda'"
          class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all"
          :class="direction === 'latin_to_sunda'
            ? 'bg-sunda-600 text-white shadow'
            : 'text-gray-600 hover:text-gray-800'"
        >
          Latin → Sunda
        </button>
        <button
          @click="direction = 'sunda_to_latin'"
          class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all"
          :class="direction === 'sunda_to_latin'
            ? 'bg-sunda-600 text-white shadow'
            : 'text-gray-600 hover:text-gray-800'"
        >
          Sunda → Latin
        </button>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
      <!-- Input -->
      <div class="space-y-2">
        <label class="block text-sm font-semibold text-gray-700">
          {{ direction === 'latin_to_sunda' ? '📝 Input Latin' : '📜 Input Aksara Sunda' }}
        </label>
        <textarea
          ref="inputRef"
          v-model="inputText"
          :class="direction === 'sunda_to_latin' ? 'font-sunda text-xl' : 'text-base'"
          class="input-field min-h-[160px] resize-none leading-relaxed"
          :placeholder="direction === 'latin_to_sunda'
            ? 'Ketik teks Latin di sini...\nContoh: basa sunda'
            : 'Ketik atau masukkan aksara Sunda di sini...'"
        />
        <p class="text-xs text-gray-400 text-right">{{ inputText.length }} karakter</p>
      </div>

      <!-- Output -->
      <div class="space-y-2">
        <label class="block text-sm font-semibold text-gray-700">
          {{ direction === 'latin_to_sunda' ? '📜 Hasil Aksara Sunda' : '📝 Hasil Latin' }}
        </label>
        <div
          class="relative min-h-[160px] bg-gradient-to-br from-sunda-50 to-green-50 border border-sunda-200 rounded-lg p-4"
          :class="direction === 'latin_to_sunda' ? 'font-sunda text-2xl' : 'text-base'"
        >
          <span v-if="store.loading" class="text-gray-400 animate-pulse">Mengonversi...</span>
          <span v-else-if="outputText" class="text-gray-800 leading-relaxed break-all">
            {{ outputText }}
          </span>
          <span v-else class="text-gray-400 text-sm font-sans">Hasil akan muncul di sini...</span>

          <!-- Copy Button -->
          <button
            v-if="outputText"
            @click="copyOutput"
            class="absolute top-2 right-2 p-1.5 text-sunda-600 hover:bg-sunda-100 rounded-lg transition-colors text-xs"
            :title="copied ? 'Disalin!' : 'Salin'"
          >
            {{ copied ? '✅' : '📋' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Keyboard Aksara (untuk mode Sunda → Latin) -->
    <div v-if="direction === 'sunda_to_latin'" class="mb-6">
      <h3 class="text-sm font-semibold text-gray-700 mb-3">⌨️ Virtual Keyboard Aksara Sunda</h3>
      <SundaKeyboard
        @insert="handleKeyInsert"
        @backspace="handleBackspace"
        @clear="handleClear"
      />
    </div>

    <!-- Transliterate Button -->
    <div class="flex gap-3 mb-8">
      <button
        @click="handleTransliterate"
        :disabled="!inputText.trim() || store.loading"
        class="btn-primary px-8 py-3 flex-1 sm:flex-none text-base"
      >
        <span v-if="store.loading">⏳ Mengonversi...</span>
        <span v-else>🔄 Transliterasi</span>
      </button>
      <button
        @click="handleClear"
        class="btn-secondary px-6 py-3"
      >
        Bersihkan
      </button>
    </div>

    <!-- Error -->
    <div v-if="store.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
      {{ store.error }}
    </div>

    <!-- Contoh Penggunaan -->
    <div class="card">
      <h3 class="font-semibold text-gray-800 mb-4">💡 Contoh Kata</h3>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <button
          v-for="ex in examples"
          :key="ex.latin"
          @click="fillExample(ex)"
          class="p-3 bg-sunda-50 hover:bg-sunda-100 border border-sunda-100 rounded-lg text-center transition-colors cursor-pointer"
        >
          <p class="font-sunda text-xl text-sunda-700">{{ ex.sunda }}</p>
          <p class="text-xs text-gray-600 mt-1">{{ ex.latin }}</p>
          <p class="text-xs text-gray-400">{{ ex.arti }}</p>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useTransliterationStore } from '@/stores/transliteration'
import SundaKeyboard from '@/components/keyboard/SundaKeyboard.vue'
import type { Direction } from '@/stores/transliteration'

const store     = useTransliterationStore()
const inputText = ref('')
const outputText = ref('')
const direction  = ref<Direction>('latin_to_sunda')
const copied     = ref(false)
const inputRef   = ref<HTMLTextAreaElement | null>(null)

const examples = [
  { latin: 'sunda',  sunda: 'ᮞᮥᮔ',  arti: 'Sunda' },
  { latin: 'basa',   sunda: 'ᮘᮞ',    arti: 'bahasa' },
  { latin: 'sakola', sunda: 'ᮞᮊᮩᮜ', arti: 'sekolah' },
  { latin: 'ngaran', sunda: 'ᮍᮛᮔᮺ', arti: 'nama' },
  { latin: 'buku',   sunda: 'ᮘᮥᮊᮥ', arti: 'buku' },
  { latin: 'padi',   sunda: 'ᮕᮓᮤ',  arti: 'padi' },
  { latin: 'rasa',   sunda: 'ᮛᮞ',   arti: 'rasa' },
  { latin: 'cai',    sunda: 'ᮎᮣ',   arti: 'air' },
]

watch(direction, () => {
  inputText.value  = ''
  outputText.value = ''
  store.clear()
})

async function handleTransliterate() {
  if (!inputText.value.trim()) return
  const result = await store.transliterate(inputText.value, direction.value)
  if (result) outputText.value = result.output
}

function handleKeyInsert(char: string) {
  inputText.value += char
  inputRef.value?.focus()
}

function handleBackspace() {
  const chars = [...inputText.value]
  chars.pop()
  inputText.value = chars.join('')
}

function handleClear() {
  inputText.value  = ''
  outputText.value = ''
  store.clear()
}

function fillExample(ex: { latin: string; sunda: string }) {
  if (direction.value === 'latin_to_sunda') {
    inputText.value = ex.latin
  } else {
    inputText.value = ex.sunda
  }
  handleTransliterate()
}

async function copyOutput() {
  if (!outputText.value) return
  await navigator.clipboard.writeText(outputText.value)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}
</script>
