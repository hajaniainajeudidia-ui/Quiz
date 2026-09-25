<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    

    <div class="max-w-6xl mx-auto px-6 py-12">
      <div class="text-center mb-12">
        <h2 class="text-5xl font-bold text-gray-800 mb-4">Choisissez votre Quiz</h2>
        <p class="text-xl text-gray-600">Sélectionnez un thème et commencez immédiatement</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement des quizzes...</p>
        </div>
      </div>

      <!-- Liste des Quizzes -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="quiz in quizzes" 
          :key="quiz.id"
          @click="startQuiz(quiz)"
          class="bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer overflow-hidden group"
        >
          <div class="h-2 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
          
          <div class="p-8">
            <div class="flex justify-between items-start mb-6">
              <div>
                <h3 class="text-2xl font-semibold text-gray-800 group-hover:text-indigo-600 transition">
                  {{ quiz.title }}
                </h3>
                <p class="text-gray-500 mt-2 line-clamp-2">{{ quiz.description }}</p>
              </div>
              <BookOpenIcon size="42" class="text-indigo-600" />
            </div>

            <div class="flex justify-between items-center text-sm">
              <div class="flex items-center gap-2 text-gray-500">
                <HelpCircleIcon size="20" class="text-gray-500" />
                <span>{{ quiz.questions_count }} questions</span>
              </div>
              <div class="flex items-center gap-2 text-gray-500">
                <ClockIcon size="20" class="text-gray-500" />
                <span>{{ Math.floor(quiz.time_limit / 60) }} min</span>
              </div>
            </div>

            <button class="mt-8 w-full bg-indigo-600 text-white py-4 rounded-2xl font-semibold hover:bg-indigo-700 transition">
              Commencer ce Quiz
            </button>
          </div>
        </div>
      </div>

      <!-- Aucun quiz -->
      <div v-if="!loading && quizzes.length === 0" class="text-center py-20">
        <p class="text-gray-500 text-xl">Aucun quiz disponible pour le moment.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '../stores/auth'
  import api from '../services/api'
  import { BookOpenIcon, HelpCircleIcon, ClockIcon } from 'lucide-vue-next'
  import { useToast } from 'vue-toastification'

  const toast = useToast()

  const router = useRouter()
  const authStore = useAuthStore()

  const quizzes = ref([])
  const loading = ref(true)   

  const isNetworkError = (error) => {
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
      error.message === 'Network Error' ||
      error.message?.includes('Network Error'))
    )
  }     

  const loadQuizzes = async () => {
    try {
      const response = await api.get('/quiz')
      quizzes.value = response.data.quizzes || []
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les quizzes')
      }
    } finally {
      loading.value = false
    }
  }

  const startQuiz = async (quiz) => {
    try {
      const response = await api.post('/quiz/start', {
        quiz_id: quiz.id
      })

      const attemptId = response.data.attempt_id
      router.push(`/quiz/take/${attemptId}`)
    } catch (error) {
        if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error(error.response?.data?.message || 'Impossible de démarrer le quiz')
      }
    }
  }



  // Initialisation
  onMounted(() => {
    authStore.loadUserFromStorage()
    loadQuizzes()
  })
</script>