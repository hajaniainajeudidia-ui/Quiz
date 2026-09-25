<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <router-link to="/admin" class="font-semibold text-indigo-600 hover:underline">← Tableau de bord</router-link>
          <span class="text-gray-400">|</span>
          <span class="font-semibold text-gray-700">Gestion des Quizzes</span>
        </div>
        <button
          @click="openModal()"
          class="bg-indigo-600 text-white px-5 py-2.5 rounded-2xl font-medium hover:bg-indigo-700 transition flex items-center gap-2"
        >
          + Nouveau Quiz
        </button>
      </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-10">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Liste des Quizzes</h1>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement des quizzes...</p>
        </div>
      </div>

      <!-- Tableau -->
      <div v-else class="bg-white rounded-3xl shadow overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left">Titre</th>
              <th class="px-6 py-4 text-left">Description</th>
              <th class="px-6 py-4 text-center">Durée</th>
              <th class="px-6 py-4 text-center">Statut</th>
              <th class="px-6 py-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="quiz in quizzes" :key="quiz.id" class="hover:bg-gray-50">
              <td class="px-6 py-5 font-medium">{{ quiz.title }}</td>
              <td class="px-6 py-5 text-gray-600">{{ quiz.description || '-' }}</td>
              <td class="px-6 py-5 text-center">{{ Math.floor(quiz.time_limit / 60) }} min</td>
              <td class="px-6 py-5 text-center">
                <span class="px-3 py-1 text-xs font-medium rounded-full"
                  :class="quiz.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                  {{ quiz.is_active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="px-6 py-5 text-center flex justify-center gap-4">
                <button @click="openModal(quiz)" class="text-blue-600 hover:text-blue-700 flex items-center gap-1">
                  <EditIcon size="18" />
                  Modifier
                </button>
                <button @click="deleteQuiz(quiz)" class="text-red-600 hover:text-red-700 flex items-center gap-1">
                  <Trash2Icon size="18" />
                  Supprimer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Simple avec Animation Top to Center -->
    <transition name="modal">
      <div v-if="showModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <transition name="modal-content">
          <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-8">
              <h2 class="text-2xl font-bold mb-6">
                {{ isEditing ? 'Modifier le Quiz' : 'Nouveau Quiz' }}
              </h2>

              <form @submit.prevent="saveQuiz" class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Titre du Quiz</label>
                  <input v-model="form.title" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                  <textarea v-model="form.description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée (minutes)</label>
                    <input v-model="form.time_limit" type="number" required class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select v-model="form.is_active" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                      <option :value="true">Actif</option>
                      <option :value="false">Inactif</option>
                    </select>
                  </div>
                </div>

                <div class="flex gap-4 pt-4">
                  <button type="button" @click="closeModal" class="flex-1 py-3.5 border border-gray-300 rounded-2xl font-medium hover:bg-gray-50">Annuler</button>
                  <button type="submit" :disabled="saving" class="flex-1 bg-indigo-600 text-white py-3.5 rounded-2xl font-semibold hover:bg-indigo-700 disabled:opacity-70">
                    {{ saving ? 'Enregistrement...' : (isEditing ? 'Mettre à jour' : 'Créer') }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script setup>
  import Swal from 'sweetalert2'
  import { ref, onMounted } from 'vue'
  import { EditIcon, Trash2Icon } from 'lucide-vue-next'
  import { useAuthStore } from '../stores/auth'
  import { useRouter } from 'vue-router'
  import api from '../services/api'
  import { useToast } from 'vue-toastification'

  const toast = useToast()
  const authStore = useAuthStore()
  const router = useRouter()

  const quizzes = ref([])
  const loading = ref(true)

  const showModal = ref(false)
  const isEditing = ref(false)
  const saving = ref(false)

  const form = ref({
    id: null,
    title: '',
    description: '',
    time_limit: 600,
    is_active: true
  })


  const isNetworkError = (error) => {
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
      error.message === 'Network Error' ||
      error.message?.includes('Network Error'))
    )
  }

  //load Quiz
  const loadQuizzes = async () => {
    loading.value = true
    try {
      const res = await api.get('/admin/quizzes')
      quizzes.value = res.data
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

  //open modal
  const openModal = (quiz = null) => {
    if (quiz) {
      isEditing.value = true
      form.value = { ...quiz }
    } else {
      isEditing.value = false
      form.value = { id: null, title: '', description: '', time_limit: 600, is_active: true }
    }
    showModal.value = true
  }

  //close modal
  const closeModal = () => {
    showModal.value = false
  }

  //save Quiz
  const saveQuiz = async () => {
    saving.value = true
    try {
      if (isEditing.value) {
        await api.put(`/admin/quizzes/${form.value.id}`, form.value)
        toast.success('Quiz modifié avec succès !')
      } else {
        await api.post('/admin/quizzes', form.value)
        toast.success('Quiz créé avec succès !')
      }
      closeModal()
      loadQuizzes()
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error((error.response?.data?.message || 'Erreur lors de l\'enregistrement'))
      }
      
    } finally {
      saving.value = false
    }
  }

  //delete Quiz
  const deleteQuiz = async (quiz) => {
    const result = await Swal.fire({
      title: 'Suppression',
      text: `Voulez-vous vraiment supprimer le quiz "${quiz.title}" ?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#4f46e5',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler',
      reverseButtons: true
    })

    if (result.isConfirmed) {
      try {
        await api.delete(`/admin/quizzes/${quiz.id}`)
        toast.success('Quiz supprimé avec succès !')
        loadQuizzes()
      } catch (error) {
          if (isNetworkError(error)) {
          toast.error('Aucun connexion internet. Vérifiez votre réseau.')
        } else {
          toast.error('Erreur lors de la suppression')
        }
        
      }
    }
    

    
  }



  onMounted(() => {
    authStore.loadUserFromStorage()
    loadQuizzes()
  })
</script>

<style scoped>
  /* Animation Top to Center */
  .modal-enter-active,
  .modal-leave-active {
    transition: opacity 0.25s ease;
  }

  .modal-enter-from,
  .modal-leave-to {
    opacity: 0;
  }

  .modal-content-enter-active,
  .modal-content-leave-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .modal-content-enter-from,
  .modal-content-leave-to {
    opacity: 0;
    transform: translateY(-100px) scale(0.85);
  }
</style>