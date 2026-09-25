<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
        <router-link to="/" class="text-2xl font-bold text-indigo-600">Quiz App</router-link>
        
        <div class="flex items-center gap-4">
          <span class="text-gray-700">{{ authStore.user?.name }}</span>
          
        </div>
      </div>
    </nav>

    <div class="max-w-2xl mx-auto px-6 py-20">
      <div class="bg-white rounded-3xl shadow-xl p-12 text-center">
        <!-- Icône -->
        <div class="mx-auto w-28 h-28 bg-indigo-100 rounded-3xl flex items-center justify-center mb-8">
          <RocketIcon size="64" class="text-indigo-600" />
        </div>

        <h1 class="text-4xl font-bold text-gray-800 mb-4">Prêt à commencer ?</h1>
        <p class="text-lg text-gray-600 mb-10">Choisissez un quiz pour commencer</p>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-16">
          <div class="text-center">
            <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600">Chargement des quizzes...</p>
          </div>
        </div>

        <!-- Liste des Quizzes -->
        <div v-else class="space-y-4">
          <div 
            v-for="quiz in quizzes" 
            :key="quiz.id"
            @click="startQuiz(quiz)"
            class="border border-gray-200 hover:border-indigo-300 rounded-2xl p-6 cursor-pointer transition-all hover:shadow-md flex justify-between items-center"
          >
            <div class="text-left">
              <h3 class="font-semibold text-xl text-gray-800">{{ quiz.title }}</h3>
              <p class="text-gray-500 text-sm mt-1">{{ quiz.description }}</p>
            </div>
            <div class="text-right">
              <span class="text-indigo-600 font-medium">{{ Math.floor(quiz.time_limit / 60) }} min</span>
            </div>
          </div>
        </div>

        <!-- Aucun quiz -->
        <div v-if="!loading && quizzes.length === 0" class="text-center py-16">
          <p class="text-gray-500 text-xl">Aucun quiz disponible pour le moment.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '../stores/auth'
  import api from '../services/api'
  import { RocketIcon } from 'lucide-vue-next'
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
    loading.value = true
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
        toast.error((error.response?.data?.message || 'Impossible de démarrer le quiz'))
      }
        
    }
  }


  // Initialisation
  onMounted(() => {
    authStore.loadUserFromStorage()
    loadQuizzes()
  })
</script>