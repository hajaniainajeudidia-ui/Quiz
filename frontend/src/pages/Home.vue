<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    <div class="max-w-3xl mx-auto px-6 py-16">
      <div class="bg-white rounded-3xl shadow-xl p-10 text-center">
        <!-- Icône -->
        <div class="mx-auto w-24 h-24 bg-indigo-100 rounded-3xl flex items-center justify-center mb-8">
          <CodeIcon size="48" class="text-indigo-600" />
        </div>

        <h1 class="text-4xl font-bold text-gray-800 mb-4">
          Quiz Développeur
        </h1>
        <p class="text-lg text-gray-600 mb-8">
          Testez vos connaissances en HTML, CSS, JavaScript, Vue.js, Laravel, Git, SQL et API REST.
        </p>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full"></div>
        </div>

        <!-- Contenu -->
        <div v-else>
          <p class="text-gray-500 mb-8">
            {{ quizzes.length }} quiz disponibles • Parcours dans l’ordre de création
          </p>

          <button
            @click="startFirstQuiz"
            :disabled="quizzes.length === 0 || starting"
            class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-semibold text-lg hover:bg-indigo-700 transition disabled:opacity-60 flex items-center gap-3 mx-auto"
          >
            <span v-if="starting" class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"></span>
            <PlayIcon v-else size="22" />
            {{ starting ? 'Démarrage...' : 'Commencer le Quiz' }}
          </button>

          <p v-if="quizzes.length === 0" class="text-red-500 mt-6">
            Aucun quiz disponible pour le moment.
          </p>
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
  import { useToast } from 'vue-toastification'
  import { CodeIcon, PlayIcon } from 'lucide-vue-next'

  const router = useRouter()
  const authStore = useAuthStore()
  const toast = useToast()

  const quizzes = ref([])
  const loading = ref(true)
  const starting = ref(false)

  const isNetworkError = (error) => {
    if (!navigator.onLine) return true
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
      // Tri croissant par date de création (sécurité côté frontend)
      quizzes.value = (response.data.quizzes || []).sort((a, b) => {
        return new Date(a.created_at) - new Date(b.created_at)
      })
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

  const startFirstQuiz = async () => {
    if (quizzes.value.length === 0) return

    starting.value = true
    const firstQuiz = quizzes.value[0] // Premier par created_at croissant

    try {
      

      const response = await api.post('/quiz/start', {
        quiz_id: firstQuiz.id
      })

      const attemptId = response.data.attempt_id
      router.push(`/quiz/take/${attemptId}`)
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error(error.response?.data?.message || 'Impossible de démarrer le quiz')
      }
    } finally {
      starting.value = false
    }
  }

  onMounted(() => {
    authStore.loadUserFromStorage()
    loadQuizzes()
  })
</script>