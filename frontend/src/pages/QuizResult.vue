<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement des résultats...</p>
        </div>
      </div>

      <!-- Résultat -->
      <div v-else class="bg-white rounded-3xl shadow-xl p-8 sm:p-12 text-center">
        <!-- Icône / Message -->
        <div class="mb-8">
          <div class="mx-auto w-24 h-24 rounded-full flex items-center justify-center mb-6"
               :class="percentage >= 60 ? 'bg-green-100' : 'bg-orange-100'">
            <TrophyIcon v-if="percentage >= 80" size="48" class="text-amber-500" />
            <ThumbsUpIcon v-else-if="percentage >= 60" size="48" class="text-blue-600" />
            <DumbbellIcon v-else-if="percentage >= 40" size="48" class="text-yellow-600" />
            <BookOpenIcon v-else size="48" class="text-orange-600" />
          </div>

          <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-3">
            Résultat du Quiz
          </h1>

          <p class="text-xl font-semibold flex items-center justify-center gap-2" :class="messageColor">
            {{ message }}
          </p>
        </div>

        <!-- Score -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
          <div class="bg-indigo-50 rounded-2xl p-6">
            <p class="text-3xl font-bold text-indigo-600">{{ score }}</p>
            <p class="text-gray-600 text-sm mt-1">Points obtenus</p>
          </div>
          <div class="bg-gray-50 rounded-2xl p-6">
            <p class="text-3xl font-bold text-gray-800">{{ totalQuestions }}</p>
            <p class="text-gray-600 text-sm mt-1">Questions</p>
          </div>
          <div class="bg-emerald-50 rounded-2xl p-6">
            <p class="text-3xl font-bold text-emerald-600">{{ percentage }}%</p>
            <p class="text-gray-600 text-sm mt-1">Réussite</p>
          </div>
        </div>

        <!-- Barre de progression -->
        <div class="mb-10">
          <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-700"
              :class="percentage >= 60 ? 'bg-green-500' : percentage >= 40 ? 'bg-yellow-500' : 'bg-orange-500'"
              :style="{ width: percentage + '%' }"
            ></div>
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/"
            class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-semibold hover:bg-indigo-700 transition"
          >
            Rejouer le Quiz
          </router-link>

          <router-link
            to="/my-scores"
            class="border border-gray-300 px-8 py-4 rounded-2xl font-medium hover:bg-gray-50 transition"
          >
            Voir mes scores
          </router-link>

          <router-link
            to="/leaderboard"
            class="border border-gray-300 px-8 py-4 rounded-2xl font-medium hover:bg-gray-50 transition"
          >
            Classement
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted, computed } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import api from '../services/api'
  import { useToast } from 'vue-toastification'
  import {
    TrophyIcon,
    ThumbsUpIcon,
    DumbbellIcon,
    BookOpenIcon
  } from 'lucide-vue-next'

  const route = useRoute()
  const router = useRouter()
  const toast = useToast()

  const attemptId = route.params.attemptId || route.params.id

  const loading = ref(true)
  const score = ref(0)
  const totalQuestions = ref(0)
  const percentage = ref(0)
  const message = ref('')
  const messageColor = ref('text-gray-600')

  const isNetworkError = (error) => {
    if (!navigator.onLine) return true
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
        error.message === 'Network Error' ||
        error.message?.includes('Network Error'))
    )
  }

  const loadResult = async () => {
    loading.value = true
    try {
      const response = await api.get(`/quiz/result/${attemptId}`)

      // Adapte selon la structure exacte de ton API
      const data = response.data

      score.value = data.score ?? data.attempt?.score ?? 0
      totalQuestions.value = data.total_questions ?? data.attempt?.total_questions ?? 10

      // Calcul du pourcentage
      if (totalQuestions.value > 0) {
        percentage.value = Math.round((score.value / (totalQuestions.value * 10)) * 100)
        // Si ton backend renvoie déjà le pourcentage :
        // percentage.value = data.percentage ?? Math.round(...)
      }

      // Message selon le score
      if (percentage.value >= 80) {
        message.value = 'Excellent travail !'
        messageColor.value = 'text-green-600'
      } else if (percentage.value >= 60) {
        message.value = 'Bien joué ! Continuez comme ça'
        messageColor.value = 'text-blue-600'
      } else if (percentage.value >= 40) {
        message.value = 'Pas mal, mais vous pouvez faire mieux'
        messageColor.value = 'text-yellow-600'
      } else {
        message.value = 'Encore un effort ! Ne baissez pas les bras'
        messageColor.value = 'text-orange-600'
      }
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les résultats')
      }
      router.push('/')
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    if (!attemptId) {
      router.push('/')
      return
    }
    loadResult()
  })
</script>