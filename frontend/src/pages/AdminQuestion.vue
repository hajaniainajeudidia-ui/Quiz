<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <router-link to="/admin" class="font-semibold text-indigo-600 hover:underline">← Tableau de bord</router-link>
          <span class="text-gray-400">|</span>
          <span class="font-semibold text-gray-700">Gestion des Questions</span>
        </div>
        <button
          @click="openModal()"
          class="bg-indigo-600 text-white px-5 py-2.5 rounded-2xl font-medium hover:bg-indigo-700 transition flex items-center gap-2"
        >
          + Nouvelle Question
        </button>
      </div>
    </nav>

    <!-- content -->
    <div class="max-w-7xl mx-auto px-6 py-10">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Gestion des Questions</h1>

        <!-- Sélecteur de Quiz -->
        <div class="flex items-center gap-3">
          <label class="text-sm font-medium text-gray-600">Quiz :</label>
          <select
            v-model="selectedQuizId"
            @change="loadQuestions"
            class="px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white min-w-[280px]"
          >
            <option v-for="quiz in quizzes" :key="quiz.id" :value="quiz.id">
              {{ quiz.title }}
            </option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement des questions...</p>
        </div>
      </div>

      <!-- Tableau des Questions -->
      <div v-else-if="questions.length > 0" class="bg-white rounded-3xl shadow overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left">Question</th>
              <th class="px-6 py-4 text-center">Points</th>
              <th class="px-6 py-4 text-center">Ordre</th>
              <th class="px-6 py-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="question in questions" :key="question.id" class="hover:bg-gray-50">
              <td class="px-6 py-5">
                <p class="font-medium text-gray-800 line-clamp-2">{{ question.question_text }}</p>
              </td>
              <td class="px-6 py-5 text-center font-semibold">{{ question.points }}</td>
              <td class="px-6 py-5 text-center">{{ question.order }}</td>
              <td class="px-6 py-5 text-center flex justify-center gap-4">
                <button @click="openModal(question)" class="text-blue-600 hover:text-blue-700 flex items-center gap-1">
                  <EditIcon size="18" />
                  Modifier
                </button>
                <button @click="deleteQuestion(question)" class="text-red-600 hover:text-red-700 flex items-center gap-1">
                  <Trash2Icon size="18" />
                  Supprimer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Aucun résultat -->
      <div v-else-if="!loading && questions.length === 0" class="text-center py-20 bg-white rounded-3xl shadow">
        <p class="text-gray-500 text-xl">Aucune question trouvée pour ce quiz.</p>
      </div>
    </div>

    <!-- Modal Simple avec Animation -->
    <transition name="modal">
      <div v-if="showModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
        <transition name="modal-content">
          <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b flex justify-between items-center">
              <h2 class="text-2xl font-bold">
                {{ isEditing ? 'Modifier la Question' : 'Nouvelle Question' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">✕</button>
            </div>

            <!-- Contenu scrollable -->
            <div class="p-8 overflow-y-auto flex-1">
              <form @submit.prevent="saveQuestion" class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Texte de la Question</label>
                  <textarea v-model="form.question_text" required rows="5" class="w-full px-5 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Points</label>
                    <input v-model="form.points" type="number" min="1" required class="w-full px-5 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ordre</label>
                    <input v-model="form.order" type="number" min="1" required class="w-full px-5 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  </div>
                </div>

                <!-- Réponses -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-4">Réponses (4 réponses - une seule correcte)</label>
                  <div v-for="(answer, index) in form.answers" :key="index" class="flex gap-4 mb-4">
                    <input v-model="answer.answer_text" type="text" required class="flex-1 px-5 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500" :placeholder="`Réponse ${index + 1}`" />
                    <label class="flex items-center gap-2 cursor-pointer whitespace-nowrap">
                      <input type="radio" :checked="answer.is_correct" @change="setCorrectAnswer(index)" class="w-5 h-5 accent-indigo-600" />
                      Correcte
                    </label>
                  </div>
                </div>
              </form>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t flex gap-4">
              <button type="button" @click="closeModal" class="flex-1 py-3.5 border border-gray-300 rounded-2xl font-medium hover:bg-gray-50">Annuler</button>
              <button type="button" @click="saveQuestion" :disabled="saving" class="flex-1 bg-indigo-600 text-white py-3.5 rounded-2xl font-semibold hover:bg-indigo-700 disabled:opacity-70">
                {{ saving ? 'Enregistrement...' : (isEditing ? 'Mettre à jour' : 'Créer la Question') }}
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script setup>
  import Swal from 'sweetalert2'
  import { EditIcon, Trash2Icon } from 'lucide-vue-next'
  import { ref, onMounted } from 'vue'
  import { useAuthStore } from '../stores/auth'
  import { useRouter } from 'vue-router'
  import api from '../services/api'
  import { useToast } from 'vue-toastification'

  const toast = useToast()

  const authStore = useAuthStore()
  const router = useRouter()

  const quizzes = ref([])
  const questions = ref([])
  const selectedQuizId = ref(null)

  const showModal = ref(false)
  const isEditing = ref(false)
  const saving = ref(false)
  const loading = ref(true)        

  const form = ref({
    id: null,
    quiz_id: null,
    question_text: '',
    points: 10,
    order: 1,
    answers: [
      { answer_text: '', is_correct: false },
      { answer_text: '', is_correct: false },
      { answer_text: '', is_correct: false },
      { answer_text: '', is_correct: false }
    ]
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
    try {
      const res = await api.get('/admin/quizzes')
      quizzes.value = res.data
      if (quizzes.value.length > 0 && !selectedQuizId.value) {
        selectedQuizId.value = quizzes.value[0].id
      }
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les quizzes')
      }
    }
  }

  //load Question
  const loadQuestions = async () => {
    if (!selectedQuizId.value) return
    loading.value = true
    try {
      const res = await api.get(`/admin/questions?quiz_id=${selectedQuizId.value}`)
      questions.value = res.data.questions || []
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les questions')
      }
    } finally {
      loading.value = false
    }
  }

  //open Modal
  const openModal = (question = null) => {
    form.value.quiz_id = selectedQuizId.value

    if (question) {
      isEditing.value = true
      form.value = { ...question, answers: question.answers.map(a => ({ ...a })) }
    } else {
      isEditing.value = false
      form.value = {
        id: null,
        quiz_id: selectedQuizId.value,
        question_text: '',
        points: 10,
        order: questions.value.length + 1,
        answers: [
          { answer_text: '', is_correct: false },
          { answer_text: '', is_correct: false },
          { answer_text: '', is_correct: false },
          { answer_text: '', is_correct: false }
        ]
      }
    }
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
  }

  const setCorrectAnswer = (index) => {
    form.value.answers.forEach((ans, i) => ans.is_correct = i === index)
  }

  //save Question
  const saveQuestion = async () => {
    saving.value = true
    try {
      if (isEditing.value) {
        await api.put(`/admin/questions/${form.value.id}`, form.value)
        toast.success('Questions modifié avec succès !')
      } else {
        await api.post('/admin/questions', form.value)
        toast.success('Questions créé avec succès !')
      }
      closeModal()
      loadQuestions()
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Erreur lors de l\'enregistrement')
      }
      
    } finally {
      saving.value = false
    }
  }

  //delete Question
  const deleteQuestion = async (question) => {
    const result = await Swal.fire({
      title: 'Suppression',
      text: `Voulez-vous vraiment supprimer le quiz "${question.question_text}" ?`,
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
        toast.success('Question supprimé avec succès !')
        await api.delete(`/admin/questions/${question.id}`)
        loadQuestions()
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
    loadQuizzes().then(() => {
      if (selectedQuizId.value) loadQuestions()
    })
  })
</script>

<style scoped>

  .modal-enter-active,
  .modal-leave-active {
    transition: opacity 0.3s ease;
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
    transform: translateY(-80px) scale(0.9);
  }
</style>