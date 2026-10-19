<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
      <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-8 flex items-center gap-3">
        <BarChart3Icon size="40" class="text-indigo-600" />
        Mes Scores
      </h1>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement de vos scores...</p>
        </div>
      </div>

      <!-- Aucun score -->
      <div v-else-if="scores.length === 0" class="bg-white rounded-3xl shadow p-12 text-center">
        <FrownIcon size="48" class="text-gray-400 mx-auto mb-4" />
        <p class="text-xl text-gray-500">Aucun score enregistré pour le moment.</p>
        <router-link
          to="/"
          class="inline-block mt-6 bg-indigo-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-indigo-700 transition"
        >
          Commencer un Quiz
        </router-link>
      </div>

      <!-- Liste des scores -->
      <div v-else class="space-y-6">
        <div
          v-for="item in scores"
          :key="item.attempt_id"
          class="bg-white rounded-3xl shadow overflow-hidden"
        >
          <!-- En-tête du quiz -->
          <div
            class="p-6 cursor-pointer flex justify-between items-center hover:bg-gray-50 transition"
            @click="toggleDetails(item.attempt_id)"
          >
            <div>
              <h2 class="text-xl font-semibold text-gray-800">{{ item.quiz_title }}</h2>
              <p class="text-sm text-gray-500 mt-1">
                {{ formatDate(item.completed_at) }}
              </p>
            </div>

            <div class="flex items-center gap-6">
              <div class="text-right">
                <p class="text-2xl font-bold text-indigo-600">{{ item.percentage }}%</p>
                <p class="text-sm text-gray-500">
                  {{ item.correct_answers }}/{{ item.total_questions }}
                </p>
              </div>
              <ChevronDownIcon
                size="24"
                class="text-gray-400 transition-transform"
                :class="{ 'rotate-180': openId === item.attempt_id }"
              />
            </div>
          </div>

          <!-- Détails (corrections) -->
          <div v-if="openId === item.attempt_id" class="border-t px-6 py-4 space-y-4">
            <div
              v-for="(detail, index) in item.details"
              :key="index"
              class="flex gap-4 p-4 rounded-2xl"
              :class="detail.is_correct ? 'bg-green-50' : 'bg-red-50'"
            >
              <div class="flex-shrink-0 mt-1">
                <CheckCircleIcon v-if="detail.is_correct" size="24" class="text-green-600" />
                <XCircleIcon v-else size="24" class="text-red-600" />
              </div>

              <div class="flex-1">
                <p class="font-medium text-gray-800 mb-2">
                  {{ index + 1 }}. {{ detail.question }}
                </p>
                <p class="text-sm">
                  <span class="text-gray-500">Votre réponse :</span>
                  <span :class="detail.is_correct ? 'text-green-700' : 'text-red-700'">
                    {{ detail.user_answer }}
                  </span>
                </p>
                <p v-if="!detail.is_correct" class="text-sm mt-1">
                  <span class="text-gray-500">Bonne réponse :</span>
                  <span class="text-green-700">{{ detail.correct_answer }}</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">
                  {{ detail.points }} point(s)
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import api from '../services/api'
  import { useToast } from 'vue-toastification'
  import {
    BarChart3Icon,
    FrownIcon,
    ChevronDownIcon,
    CheckCircleIcon,
    XCircleIcon
  } from 'lucide-vue-next'

  const toast = useToast()

  const scores = ref([])
  const loading = ref(true)
  const openId = ref(null)

  const isNetworkError = (error) => {
    if (!navigator.onLine) return true
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
        error.message === 'Network Error' ||
        error.message?.includes('Network Error'))
    )
  }

  const loadScores = async () => {
    loading.value = true
    try {
      const response = await api.get('/quiz/my-scores')
      scores.value = response.data.scores || []
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger vos scores')
      }
    } finally {
      loading.value = false
    }
  }

  const toggleDetails = (id) => {
    openId.value = openId.value === id ? null : id
  }

  const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  onMounted(() => {
    loadScores()
  })
</script>