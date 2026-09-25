<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    

    <div class="max-w-4xl mx-auto px-6 py-12">
      <div class="flex justify-between items-end mb-8">
        <div>
          <h1 class="text-4xl font-bold text-gray-800 flex items-center gap-3">
            <TrophyIcon size="42" class="text-amber-500" />
            Classement Général
          </h1>
          <p class="text-gray-600 mt-1">Top 20 des meilleurs scores</p>
        </div>
        <button
          @click="refreshLeaderboard"
          class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2"
        >
          <RefreshCwIcon size="20" /> Actualiser
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement du classement...</p>
        </div>
      </div>

      <!-- Tableau -->
      <div v-else class="bg-white rounded-3xl shadow-xl overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-8 py-5 text-left text-sm font-semibold text-gray-600 w-20">Rang</th>
              <th class="px-8 py-5 text-left text-sm font-semibold text-gray-600">Joueur</th>
              <th class="px-8 py-5 text-center text-sm font-semibold text-gray-600">Score</th>
              <th class="px-8 py-5 text-center text-sm font-semibold text-gray-600">Pourcentage</th>
              <th class="px-8 py-5 text-right text-sm font-semibold text-gray-600">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr 
              v-for="(entry, index) in leaderboard" 
              :key="index"
              class="hover:bg-gray-50 transition"
              :class="{ 'bg-indigo-50 font-medium': isCurrentUser(entry) }"
            >
              <td class="px-8 py-6">
                <span 
                  class="inline-flex items-center justify-center w-9 h-9 rounded-full text-lg font-bold"
                  :class="getRankStyle(index + 1)"
                >
                  {{ index + 1 }}
                </span>
              </td>
              <td class="px-8 py-6">
                <div class="font-medium text-gray-800">{{ entry.user_name }}</div>
              </td>
              <td class="px-8 py-6 text-center font-bold text-indigo-600 text-lg">
                {{ entry.score }}
              </td>
              <td class="px-8 py-6 text-center">
                <span class="inline-block bg-emerald-100 text-emerald-700 px-4 py-1 rounded-full text-sm font-medium">
                  {{ entry.percentage }}%
                </span>
              </td>
              <td class="px-8 py-6 text-right text-sm text-gray-500">
                {{ formatDate(entry.completed_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Aucun résultat -->
      <div v-if="!loading && leaderboard.length === 0" class="text-center py-20 bg-white rounded-3xl shadow">
        <p class="text-2xl mb-2 flex justify-center">
          <FrownIcon size="42" class="text-gray-400" />
        </p>
        <p class="text-gray-500">Aucun score enregistré pour le moment.</p>
        <p class="text-sm text-gray-400 mt-2">Soyez le premier à compléter le quiz !</p>
      </div>

      <!-- Boutons d'action -->
      <div class="flex justify-center gap-4 mt-12">
        <router-link
          to="/quiz/start"
          class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-semibold hover:bg-indigo-700 transition shadow-lg"
        >
          Rejouer le Quiz
        </router-link>
        <router-link
          to="/"
          class="border border-gray-300 px-8 py-4 rounded-2xl font-medium hover:bg-white transition"
        >
          Retour à l'accueil
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { TrophyIcon, RefreshCwIcon, FrownIcon } from 'lucide-vue-next'
  import { ref, onMounted } from 'vue'
  import { useAuthStore } from '../stores/auth'
  import api from '../services/api'
  import { useRouter } from 'vue-router'
  import { useToast } from 'vue-toastification'

  const toast = useToast()

  const authStore = useAuthStore()
  const router = useRouter()

  const leaderboard = ref([])
  const loading = ref(true)

  const isNetworkError = (error) => {
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
      error.message === 'Network Error' ||
      error.message?.includes('Network Error'))
    )
  } 

  //load leaderboard
  const loadLeaderboard = async () => {
    loading.value = true
    try {
      const response = await api.get('/leaderboard')
      leaderboard.value = response.data
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger le classement')
      }
      
    } finally {
      loading.value = false
    }
  }

  const isCurrentUser = (entry) => {
    return entry.user_id === authStore.user?.id
  }

  const getRankStyle = (rank) => {
    if (rank === 1) return 'bg-yellow-400 text-white'
    if (rank === 2) return 'bg-gray-300 text-white'
    if (rank === 3) return 'bg-amber-600 text-white'
    return 'bg-gray-100 text-gray-700'
  }

  const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR', { 
      day: '2-digit', 
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  const refreshLeaderboard = () => {
    loadLeaderboard()
  }


  // Initialisation
  onMounted(() => {
    authStore.loadUserFromStorage()
    loadLeaderboard()
  })
</script>