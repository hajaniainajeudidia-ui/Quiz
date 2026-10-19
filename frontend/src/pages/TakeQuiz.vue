<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
        <router-link to="/" class="text-xl sm:text-2xl font-bold text-indigo-600">
          Quiz App
        </router-link>

        <div class="flex items-center gap-4 sm:gap-6 text-sm">
          <div>
            Quiz
            <span class="font-bold">{{ currentQuizIndex + 1 }} / {{ quizzes.length }}</span>
          </div>
          <div>
            Question
            <span class="font-bold">{{ currentNumber }} / 10</span>
          </div>
        </div>
      </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
      <!-- Loading global -->
      <div v-if="loading" class="flex justify-center items-center py-32">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600">Chargement du quiz...</p>
        </div>
      </div>

      <!-- Contenu du quiz -->
      <div v-else>
        <!-- Progress globale -->
        <div class="mb-8">
          <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
            <div
              class="h-full bg-indigo-600 transition-all duration-300"
              :style="{ width: overallProgress + '%' }"
            ></div>
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

        <!-- Titre du quiz courant -->
        <h3 class="text-center text-indigo-600 font-semibold mb-2">
          {{ currentQuiz?.title }}
        </h3>

        <!-- Question -->
        <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-10 mb-8">
          <h2 class="text-xl font-semibold text-gray-800 mb-8 leading-relaxed">
            {{ currentQuestion?.question_text }}
          </h2>

          <div class="space-y-4">
            <label
              v-for="answer in currentQuestion?.answers"
              :key="answer.id"
              class="flex items-center gap-4 p-4 sm:p-5 border-2 rounded-2xl cursor-pointer hover:border-indigo-200 transition-all"
              :class="{ 'border-indigo-600 bg-indigo-50': selectedAnswer === answer.id }"
            >
              <input
                v-model="selectedAnswer"
                type="radio"
                :value="answer.id"
                class="w-5 h-5 accent-indigo-600"
              />
              <span class="text-base sm:text-lg text-gray-700">{{ answer.answer_text }}</span>
            </label>
          </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between gap-4">
          <button
            @click="goPrevious"
            :disabled="currentNumber === 1 || isSubmitting"
            class="px-6 sm:px-8 py-4 border border-gray-300 rounded-2xl font-medium disabled:opacity-50 flex items-center gap-2"
          >
            <ArrowLeftIcon size="20" />
            Précédent
          </button>

          <button
            @click="goNext"
            :disabled="!selectedAnswer || isSubmitting"
            class="px-8 sm:px-12 py-4 bg-indigo-600 text-white font-semibold rounded-2xl hover:bg-indigo-700 transition disabled:opacity-70 flex items-center gap-3 min-w-[160px] justify-center"
          >
            <span
              v-if="isSubmitting"
              class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"
            ></span>
            <ArrowRightIcon v-else size="20" />
            {{
              isLastQuestionOfAll
                ? 'Terminer'
                : isLastQuestionOfCurrentQuiz
                  ? 'Quiz suivant'
                  : 'Suivant'
            }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted, computed, onUnmounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import api from '../services/api'
  import { useToast } from 'vue-toastification'
  import { ClockIcon, ArrowLeftIcon, ArrowRightIcon } from 'lucide-vue-next'

  const route = useRoute()
  const router = useRouter()
  const toast = useToast()

  const quizzes = ref([])
  const currentQuizIndex = ref(0)
  const currentNumber = ref(1)

  const currentQuiz = ref(null)
  const currentQuestion = ref(null)
  const selectedAnswer = ref(null)
  const currentAttemptId = ref(route.params.attemptId || null)

  const timeLeft = ref(600)
  const loading = ref(true)
  const isSubmitting = ref(false)

  let timerInterval = null

  // Computed
  const overallProgress = computed(() => {
    if (quizzes.value.length === 0) return 0
    return ((currentQuizIndex.value * 10 + currentNumber.value) / (quizzes.value.length * 10)) * 100
  })

  const isLastQuestionOfCurrentQuiz = computed(() => currentNumber.value === 10)

  const isLastQuestionOfAll = computed(() => {
    return currentQuizIndex.value === quizzes.value.length - 1 && isLastQuestionOfCurrentQuiz.value
  })

  // Helpers
  const formatTime = (seconds) => {
    const min = Math.floor(seconds / 60)
    const sec = seconds % 60
    return `${min}:${sec < 10 ? '0' : ''}${sec}`
  }

  const isNetworkError = (error) => {
    if (!navigator.onLine) return true
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
        error.message === 'Network Error' ||
        error.message?.includes('Network Error'))
    )
  }

  // Timer
  const startTimer = () => {
    if (timerInterval) clearInterval(timerInterval)
    timeLeft.value = currentQuiz.value?.time_limit || 600

    timerInterval = setInterval(() => {
      timeLeft.value--
      if (timeLeft.value <= 0) {
        clearInterval(timerInterval)
        finishAllQuizzes()
      }
    }, 1000)
  }

  // Charger tous les quizzes (ordre croissant created_at)
  const loadQuizzes = async () => {
    try {
      const res = await api.get('/quiz')
      quizzes.value = (res.data.quizzes || []).sort((a, b) => {
        return new Date(a.created_at) - new Date(b.created_at)
      })
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger les quizzes')
      }
    }
  }

  // Démarrer un quiz (par index)
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
      toast.success('Quiz démarré : ' + currentQuiz.value.title)
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de démarrer le quiz')
      }
    }
  }

  // Charger une question
  const fetchQuestion = async (number) => {
    try {
      const res = await api.get(`/quiz/${currentAttemptId.value}/question/${number}`)
      currentQuestion.value = res.data.question
      currentNumber.value = number
      selectedAnswer.value = null
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Impossible de charger la question')
      }
    }
  }

  // Soumettre la réponse courante
  const submitCurrentAnswer = async () => {
    if (!selectedAnswer.value) return

    try {
      await api.post('/quiz/submit-answer', {
        attempt_id: currentAttemptId.value,
        question_id: currentQuestion.value.id,
        answer_id: selectedAnswer.value
      })
    } catch (error) {
      // On continue même en cas d'erreur mineure
      console.error(error)
    }
  }

  // Bouton Suivant
  const goNext = async () => {
    if (!selectedAnswer.value) {
      toast.error('Veuillez sélectionner une réponse')
      return
    }

    isSubmitting.value = true

    try {
      await submitCurrentAnswer()

      if (isLastQuestionOfAll.value) {
        await finishAllQuizzes()
      } else if (isLastQuestionOfCurrentQuiz.value) {
        // Passer au quiz suivant
        await startNewQuiz(currentQuizIndex.value + 1)
      } else {
        // Question suivante du même quiz
        currentNumber.value++
        await fetchQuestion(currentNumber.value)
      }
    } finally {
      isSubmitting.value = false
    }
  }

  // Bouton Précédent
  const goPrevious = async () => {
    if (currentNumber.value <= 1) return

    isSubmitting.value = true
    try {
      await submitCurrentAnswer()
      currentNumber.value--
      await fetchQuestion(currentNumber.value)
    } finally {
      isSubmitting.value = false
    }
  }

  // Terminer tous les quizzes
  const finishAllQuizzes = async () => {
    if (timerInterval) clearInterval(timerInterval)

    try {
      await api.post('/quiz/finish', {
        attempt_id: currentAttemptId.value
      })
      toast.success('Quiz terminé ! Redirection...')
      router.push(`/quiz/result/${currentAttemptId.value}`)
    } catch (error) {
      if (isNetworkError(error)) {
        toast.error('Aucun connexion internet. Vérifiez votre réseau.')
      } else {
        toast.error('Erreur lors de la finalisation')
      }
    }
  }

  // Initialisation
  onMounted(async () => {
    loading.value = true
    try {
      await loadQuizzes()

      if (quizzes.value.length === 0) {
        toast.error('Aucun quiz disponible')
        router.push('/')
        return
      }

      // Si on arrive avec un attemptId (depuis Home), on charge la question 1
      // Sinon on démarre le premier quiz
      if (currentAttemptId.value) {
        // Trouver quel quiz correspond à cet attempt (option simple : on repart du premier)
        currentQuiz.value = quizzes.value[0]
        currentQuizIndex.value = 0
        await fetchQuestion(1)
        startTimer()
      } else {
        await startNewQuiz(0)
      }
    } finally {
      loading.value = false
    }
  })

  onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval)
  })
</script>