<template>
  <div>
    <!-- Tabs -->
    <div class="flex gap-1 border-b border-gray-200 mb-6">
      <button
        type="button"
        @click="tab = 'bank'"
        class="px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px"
        :class="tab === 'bank'
          ? 'border-sunda-600 text-sunda-700'
          : 'border-transparent text-gray-500 hover:text-gray-700'"
      >
        🗄️ Bank Soal
      </button>
      <button
        type="button"
        @click="tab = 'quiz'"
        class="px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px"
        :class="tab === 'quiz'
          ? 'border-sunda-600 text-sunda-700'
          : 'border-transparent text-gray-500 hover:text-gray-700'"
      >
        ⚙️ Pengaturan Quiz
      </button>
    </div>

    <!-- Konten Tab -->
    <BankSoalPage v-if="tab === 'bank'" />
    <QuizSettingsPage v-else />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BankSoalPage from './BankSoalPage.vue'
import QuizSettingsPage from './QuizSettingsPage.vue'

const tab = ref<'bank' | 'quiz'>('bank')
</script>
