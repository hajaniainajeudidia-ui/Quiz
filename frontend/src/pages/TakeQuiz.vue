<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-32">
      <div class="text-center">
        <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement...</p>
      </div>
    </div>

    <div v-else class="max-w-3xl mx-auto px-6 py-10">
      <!-- Progress Global -->
      <div class="mb-8">
        <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-indigo-600 transition-all duration-300" 
               :style="{ width: overallProgress + '%' }"></div>
        </div>
        <p class="text-center text-xs text-gray-500 mt-2">
          Progression globale : {{ Math.round(overallProgress) }}%
        </p>
      </div>

      <!-- Timer -->
      <div class="text-center mb-6">
        <div class="inline-flex items-center gap-2 bg-white px-6 py-3 rounded-2xl shadow text-lg font-mono">
          <ClockIcon size="22" class="text-indigo-600" />
          {{ formatTime(timeLeft) }}
        </div>
      </div>

      <!-- Quiz Title -->
      <h3 class="text-center text-indigo-600 font-semibold mb-2">
        {{ currentQuiz?.title }}
      </h3>

      <!-- Question -->
      <div class="bg-white rounded-3xl shadow-xl p-10 mb-10">
        <h2 class="text-xl font-semibold text-gray-800 mb-8 leading-relaxed">
          {{ currentQuestion?.question_text }}
        </h2>

        <div class="space-y-4">
          <label 
            v-for="answer in currentQuestion?.answers" 
            :key="answer.id"
            class="flex items-center gap-4 p-5 border-2 rounded-2xl cursor-pointer hover:border-indigo-200 transition-all"
            :class="{ 'border-indigo-600 bg-indigo-50': selectedAnswer === answer.id }"
          >
            <input 
              v-model="selectedAnswer"
              type="radio" 
              :value="answer.id"
              class="w-5 h-5 accent-indigo-600"
            />
            <span class="text-lg text-gray-700">{{ answer.answer_text }}</span>
          </label>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between">
        <button
          @click="goPrevious"
          :disabled="currentNumber === 1 || isSubmitting"
          class="px-8 py-4 border border-gray-300 rounded-2xl font-medium disabled:opacity-50 flex items-center gap-2"
        >
          <ArrowLeftIcon size="20" />
          Précédent
        </button>

        <button
          @click="goNext"
          :disabled="!selectedAnswer || isSubmitting"
          class="px-12 py-4 bg-indigo-600 text-white font-semibold rounded-2xl hover:bg-indigo-700 transition disabled:opacity-70 flex items-center gap-3 min-w-[180px] justify-center"
        >
          <span v-if="isSubmitting" class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"></span>
          <ArrowRightIcon v-else size="20" />
          {{ isLastQuestionOfAll ? 'Terminer Tous les Quizzes' :
            isLastQuestionOfCurrentQuiz ? 'Quiz Suivant' : 'Suivant' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted, computed } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import api from '../services/api'
  import { ClockIcon } from 'lucide-vue-next'
  import { useToast } from 'vue-toastification'
  import { ArrowLeftIcon, ArrowRightIcon } from 'lucide-vue-next'

  const route = useRoute()
  const router = useRouter()

  const quizzes = ref([])
  const currentQuizIndex = ref(0)
  const currentNumber = ref(1)

  const currentQuiz = ref(null)
  const currentQuestion = ref(null)
  const selectedAnswer = ref(null)
  const currentAttemptId = ref(null)

  const loading = ref(true)

  const timeLeft = ref(600)
  const isSubmitting = ref(false)  

  const toast = useToast()

  let timerInterval = null

  const isNetworkError = (error) => {
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
      error.message === 'Network Error' ||
      error.message?.includes('Network Error'))
    )
  }

  const overallProgress = computed(() => {
    return ((currentQuizIndex.value * 10 + currentNumber.value) / (quizzes.value.length * 10)) * 100
  })

  const isLastQuestionOfCurrentQuiz = computed(() => currentNumber.value === 10)
  const isLastQuestionOfAll = computed(() => 
    currentQuizIndex.value === quizzes.value.length - 1 && isLastQuestionOfCurrentQuiz.value
  )

  // Timer
  const startTimer = () => {
    if (timerInterval) clearInterval(timerInterval)
    timeLeft.value = 600

    timerInterval = setInterval(() => {
      timeLeft.value--
      if (timeLeft.value <= 0) {
        clearInterval(timerInterval)
        finishCurrentQuizAndGoNext()
      }
    }, 1000)
  }

  const formatTime = (seconds) => {
    const min = Math.floor(seconds / 60)
    const sec = seconds % 60
    return `${min}:${sec < 10 ? '0' : ''}${sec}`
  }

  // load quizzes
  const loadQuizzes = async () => {
    loading.value = true   // Active spinner
    try {
      const res = await api.get('/quiz')
      quizzes.value = res.data.quizzes || []
      if (quizzes.value.length > 0) {
        await startNewQuiz(0)
      }
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les quizzes')
      }
    } finally {
      loading.value = false   // Desactive le spinner
    }
  }

  const fetchQuestion = async (number) => {
    
    try {
      const res = await api.get(`/quiz/${currentAttemptId.value}/question/${number}`)
      currentQuestion.value = res.data.question
      currentNumber.value = number
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les questions')
      }
      
    }
  }

  const startNewQuiz = async (quizIndex) => {
    currentQuizIndex.value = quizIndex
    currentNumber.value = 1
    selectedAnswer.value = null
    currentQuiz.value = quizzes.value[quizIndex]

    try {
      const response = await api.post('/quiz/start', {
        quiz_id: currentQuiz.value.id
      })
      currentAttemptId.value = response.data.attempt_id
      await fetchQuestion(1)
      startTimer()

      
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de démarrer le quiz')
      }
    }
  }



  const submitCurrentAnswer = async () => {
    if (!selectedAnswer.value) return
    try {
      await api.post('/quiz/submit-answer', {
        attempt_id: currentAttemptId.value,
        question_id: currentQuestion.value.id,
        answer_id: selectedAnswer.value
      })
      toast.success('Réponse envoyée')
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Erreur lors de l\'enregistrement de la réponse')
      }
      
    }
  }

  const goNext = async () => {
    isSubmitting.value = true
    await submitCurrentAnswer()

    if (isLastQuestionOfAll.value) {
      await finishAllQuizzes()
    } else if (isLastQuestionOfCurrentQuiz.value) {
      await startNewQuiz(currentQuizIndex.value + 1)
    } else {
      selectedAnswer.value = null
      currentNumber.value++
      await fetchQuestion(currentNumber.value)
    }

    isSubmitting.value = false
  }

  const finishAllQuizzes = async () => {
    if (timerInterval) clearInterval(timerInterval)
    try {
      await api.post('/quiz/finish', { attempt_id: currentAttemptId.value })
      toast.success('Quiz terminé ! Redirection vers les résultats...')
      router.push(`/quiz/result/${currentAttemptId.value}`)
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Erreur lors de la finalisation du quiz')
      }
      
    }
  }

  const finishCurrentQuizAndGoNext = async () => {
    await finishAllQuizzes()
  }

  const goPrevious = async () => {
    await submitCurrentAnswer()
    if (currentNumber.value > 1) {
      currentNumber.value--
      await fetchQuestion(currentNumber.value)
    }
  }

  // Initialisation
  onMounted(() => {
    loadQuizzes()
  })
</script>