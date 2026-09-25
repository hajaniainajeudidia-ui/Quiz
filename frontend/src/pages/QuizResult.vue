<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    

    <div class="max-w-4xl mx-auto px-6 py-12">
      <div class="bg-white rounded-3xl shadow-xl p-10">
        <!-- Score Principal -->
        <div class="text-center mb-10">
          <h1 class="text-4xl font-bold text-gray-800 mb-2">Votre Résultat</h1>
          <div class="text-7xl font-bold text-indigo-600 mt-6 mb-2">
            {{ score }} <span class="text-4xl text-gray-400">/ 100</span>
          </div>
          <div class="text-2xl font-semibold text-gray-600">
            {{ percentage }}%
          </div>
        </div>

        <!-- Message motivant -->
        <p :class="['text-2xl font-semibold flex items-center justify-center gap-2', messageColor]">
          <PartyPopperIcon v-if="messageIcon === 'party'" size="28" />
          <ThumbsUpIcon v-else-if="messageIcon === 'thumbs-up'" size="28" />
          <DumbbellIcon v-else-if="messageIcon === 'muscle'" size="28" />
          <BookOpenIcon v-else-if="messageIcon === 'book'" size="28" />
          {{ message }}
        </p>

        <!-- Récapitulatif -->
        <div class="mb-10">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Récapitulatif des réponses</h3>
          
          <div v-for="(item, index) in resultDetails" :key="index" 
               class="flex items-start gap-6 p-5 border border-gray-100 rounded-2xl mb-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                :class="item.isCorrect ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
              <CheckCircleIcon v-if="item.isCorrect" size="20" />
              <XCircleIcon v-else size="20" />
            </div>
            
            <div class="flex-1">
              <p class="font-medium text-gray-800">{{ item.question }}</p>
              <p class="text-sm mt-2">
                Votre réponse : 
                <span :class="item.isCorrect ? 'text-green-600' : 'text-red-600'">
                  {{ item.userAnswer }}
                </span>
              </p>
              <p v-if="!item.isCorrect" class="text-sm text-green-600 mt-1">
                Bonne réponse : {{ item.correctAnswer }}
              </p>
            </div>
          </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex flex-col sm:flex-row gap-4">
          <button
            @click="restartQuiz"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-4 rounded-2xl transition"
          >
            Recommencer le Quiz
          </button>
          
          <router-link
            to="/"
            class="flex-1 border border-gray-300 hover:bg-gray-50 font-semibold py-4 rounded-2xl text-center transition"
          >
            Retour à l'accueil
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ThumbsUpIcon, CheckCircleIcon, XCircleIcon, PartyPopperIcon, DumbbellIcon, BookOpenIcon } from 'lucide-vue-next'
  import { ref, onMounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import api from '../services/api'
  import { useAuthStore } from '../stores/auth'
  import { useToast } from 'vue-toastification'

  const toast = useToast()

  const route = useRoute()
  const router = useRouter()
  const authStore = useAuthStore()

  const attemptId = ref(route.params.attemptId)
  const score = ref(0)
  const percentage = ref(0)
  const resultDetails = ref([])

  const message = ref('')
  const messageColor = ref('')

  const messageIcon = ref('')

  const isNetworkError = (error) => {
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
      error.message === 'Network Error' ||
      error.message?.includes('Network Error'))
    )
  }  

  const fetchResult = async () => {
    try {
      const response = await api.get(`/quiz/result/${attemptId.value}`)
      const data = response.data

      score.value = data.attempt.score
      percentage.value = data.score_percentage

      // Message selon le score
      if (percentage.value >= 80) {
        message.value = "Excellent travail !"
        messageColor.value = "text-green-600"
        messageIcon.value = "party"
      } else if (percentage.value >= 60) {
        message.value = "Bien joué ! Continuez comme ça"
        messageColor.value = "text-blue-600"
        messageIcon.value = "thumbs-up"
      } else if (percentage.value >= 40) {
        message.value = "Pas mal, mais vous pouvez faire mieux"
        messageColor.value = "text-yellow-600"
        messageIcon.value = "muscle"
      } else {
        message.value = "Encore un effort ! Ne baissez pas les bras"
        messageColor.value = "text-orange-600"
        messageIcon.value = "book"
      }

      // Récapitulatif (simplifié pour l'instant)
      resultDetails.value = data.attempt.userAnswers?.map(ua => ({
        question: ua.question.question_text,
        userAnswer: ua.answer.answer_text,
        correctAnswer: ua.question.answers.find(a => a.is_correct)?.answer_text || '',
        isCorrect: ua.answer.id === ua.question.answers.find(a => a.is_correct)?.id
      })) || []

    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les résultats')
      }
      
      
    }
  }

  const restartQuiz = () => {
    router.push('/quiz/start')
  }


  // Initialisation
  onMounted(() => {
    authStore.loadUserFromStorage()
    fetchResult()
  })
</script>