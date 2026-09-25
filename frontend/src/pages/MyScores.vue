<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    

    <div class="max-w-6xl mx-auto px-6 py-12">
      <h1 class="text-4xl font-bold text-gray-800 mb-10 flex items-center gap-3">
        <BarChart3Icon size="42" class="text-indigo-600" />
        Mes Scores
      </h1>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-32">
        <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full"></div>
      </div>

      <!-- Aucun score -->
      <div v-else-if="scores.length === 0" class="text-center py-20 bg-white rounded-3xl shadow">
        <p class="text-3xl mb-4 flex justify-center">
          <FrownIcon size="42" class="text-gray-400" />
        </p>
        <p class="text-xl text-gray-600">Vous n'avez pas encore complété de quiz.</p>
        <router-link 
          to="/quiz/start" 
          class="mt-6 inline-block bg-indigo-600 text-white px-8 py-4 rounded-2xl font-semibold hover:bg-indigo-700"
        >
          Commencer un Quiz
        </router-link>
      </div>

      <!-- Liste des Scores -->
      <div v-else class="space-y-8">
        <div 
          v-for="score in scores" 
          :key="score.attempt_id"
          class="bg-white rounded-3xl shadow-xl overflow-hidden"
        >
          <!-- En-tête -->
          <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-6 text-white">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-2xl font-semibold">{{ score.quiz_title }}</h3>
                <p class="text-indigo-100 text-sm mt-1">
                  {{ formatDate(score.completed_at) }}
                </p>
              </div>
              <div class="text-right">
                <div class="text-5xl font-bold">{{ score.score }}</div>
                <div class="text-xl">{{ score.percentage }}%</div>
              </div>
            </div>
          </div>

          <!-- Détails des réponses -->
          <div class="p-8">
            <h4 class="font-medium text-gray-700 mb-5">Correction détaillée</h4>
            
            <div class="space-y-5">
              <div 
                v-for="(item, index) in score.details" 
                :key="index"
                class="flex gap-5 p-5 rounded-2xl border"
                :class="item.is_correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
              >
                <div class="text-3xl flex-shrink-0">
                  <span v-if="item.is_correct">
                    <CheckCircleIcon size="32" class="text-green-600" />
                  </span>
                  <span v-else>
                    <XCircleIcon size="32" class="text-red-600" />
                  </span>
                </div>

                <div class="flex-1">
                  <p class="font-medium text-gray-800">{{ item.question }}</p>
                  
                  <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                      <span class="text-gray-500">Votre réponse :</span><br>
                      <span :class="item.is_correct ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                        {{ item.user_answer }}
                      </span>
                    </div>
                    <div v-if="!item.is_correct">
                      <span class="text-gray-500">Bonne réponse :</span><br>
                      <span class="text-green-600 font-medium">{{ item.correct_answer }}</span>
                    </div>
                  </div>
                </div>

                <div class="text-right font-semibold text-lg w-16">
                  {{ item.points }} pts
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { BarChart3Icon, CheckCircleIcon, XCircleIcon, FrownIcon } from 'lucide-vue-next'
  import { ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '../stores/auth'
  import api from '../services/api'
  import { useToast } from 'vue-toastification'

  const toast = useToast()

  const router = useRouter()
  const authStore = useAuthStore()

  const scores = ref([])
  const loading = ref(true)


  const isNetworkError = (error) => {
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
      error.message === 'Network Error' ||
      error.message?.includes('Network Error'))
    )
  } 

  const loadScores = async () => {
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

  const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }


  // Initialisation
  onMounted(() => {
    authStore.loadUserFromStorage()
    loadScores()
  })
</script>