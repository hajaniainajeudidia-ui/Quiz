<template>
  <div class="min-h-screen bg-gray-50">
    

    <div class="max-w-7xl mx-auto px-6 py-10">
      <h1 class="text-4xl font-bold text-gray-800 mb-10">Tableau de Bord Admin</h1>
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement des statistiques...</p>
        </div>
      </div>
      <!-- Statistiques -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-6 rounded-3xl shadow">
          <div class="text-4xl mb-3">
            <ClipboardListIcon size="42" class="text-indigo-600" />
          </div>
          <p class="text-3xl font-bold text-indigo-600">{{ stats.total_quizzes }}</p>
          <p class="text-gray-500">Quizzes</p>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow">
          <div class="text-4xl mb-3">
            <HelpCircleIcon size="42" class="text-indigo-600" />
          </div>
          <p class="text-3xl font-bold text-indigo-600">{{ stats.total_questions }}</p>
          <p class="text-gray-500">Questions</p>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow">
          <div class="text-4xl mb-3">
            <UsersIcon size="42" class="text-indigo-600" />
          </div>
          <p class="text-3xl font-bold text-indigo-600">{{ stats.total_users }}</p>
          <p class="text-gray-500">Utilisateurs</p>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow">
          <div class="text-4xl mb-3">
            <TrophyIcon size="42" class="text-indigo-600" />
          </div>
          <p class="text-3xl font-bold text-indigo-600">{{ stats.total_attempts }}</p>
          <p class="text-gray-500">Tentatives</p>
        </div>
      </div>

      <!-- Actions Rapides -->
      <h2 class="text-2xl font-semibold text-gray-800 mb-6">Actions Rapides</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <router-link
          to="/admin/quizzes"
          class="bg-white p-8 rounded-3xl shadow hover:shadow-xl transition group flex items-start gap-6"
        >
          <div class="text-5xl">
            <ClipboardListIcon size="48" class="text-indigo-600 group-hover:scale-110 transition" />
          </div>
          <div>
            <h3 class="text-2xl font-semibold mb-2">Gérer les Quizzes</h3>
            <p class="text-gray-600">Créer, modifier et supprimer des quizzes</p>
          </div>
        </router-link>

        <router-link
          to="/admin/questions"
          class="bg-white p-8 rounded-3xl shadow hover:shadow-xl transition group flex items-start gap-6"
        >
          <div class="text-5xl">
            <HelpCircleIcon size="48" class="text-indigo-600 group-hover:scale-110 transition" />
          </div>
          <div>
            <h3 class="text-2xl font-semibold mb-2">Gérer les Questions</h3>
            <p class="text-gray-600">Ajouter et organiser les questions par quiz</p>
          </div>
        </router-link>
      </div>

      <div class="mt-10 text-center">
        <router-link
          to="/leaderboard"
          class="text-indigo-600 hover:underline font-medium flex items-center gap-2 justify-center"
        >
          <TrophyIcon size="20" />
          Voir le Classement Général →
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useAuthStore } from '../stores/auth'
  import { useRouter } from 'vue-router'
  import api from '../services/api'
  import { ClipboardListIcon, HelpCircleIcon, UsersIcon, TrophyIcon, LogOutIcon } from 'lucide-vue-next'
  import { useToast } from 'vue-toastification'

  const toast = useToast()

  const authStore = useAuthStore()
  const router = useRouter()
  const loading = ref(true)

  //Initialisation de statistiques
  const stats = ref({
    total_quizzes: 0,
    total_questions: 0,
    total_users: 0,
    total_attempts: 0,
    recent_attempts: 0,
    top_score: null
  })

  const isNetworkError = (error) => {
      return (
        !error.response &&
        (error.code === 'ERR_NETWORK' ||
        error.message === 'Network Error' ||
        error.message?.includes('Network Error'))
      )
    } 

  //Recuperation de statistique
  const loadStats = async () => {
    loading.value = true   
    try {
      const response = await api.get('/admin/dashboard/stats')
      stats.value = response.data.stats
      console.log("stats:", stats.value)
    } catch (error) {
      if (isNetworkError(error)) {
          toast.error('Aucun connexion internet. Vérifiez votre réseau.')
        } else {
          toast.error('Erreur de chargement de State')
      }
      
    } finally {
      loading.value = false   
    }
  }



  onMounted(() => {
    authStore.loadUserFromStorage()
    loadStats()
  })
</script>