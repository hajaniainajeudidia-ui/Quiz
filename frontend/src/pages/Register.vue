<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Créer un compte</h1>
        <p class="text-gray-600 mt-2">Rejoignez le Quiz App</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
          <input v-model="name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="email" type="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
          <input v-model="password" type="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
          <input v-model="passwordConfirmation" type="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-lg transition disabled:opacity-70"
        >
          {{ loading ? 'Inscription en cours...' : "S'inscrire" }}
        </button>
      </form>

      <div class="mt-6 text-center">
        <p class="text-gray-600">
          Déjà un compte ? 
          <router-link to="/login" class="text-blue-600 hover:underline">Se connecter</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue'
  import { useAuthStore } from '../stores/auth'
  import { useRouter } from 'vue-router'
  import { useToast } from 'vue-toastification'

  const toast = useToast()

  const authStore = useAuthStore()
  const router = useRouter()

  const name = ref('')
  const email = ref('')
  const password = ref('')
  const passwordConfirmation = ref('')
  const loading = ref(false)

 

  const handleRegister = async () => {
    if (password.value !== passwordConfirmation.value) {
      toast.error('Les mots de passe ne correspondent pas')
      return
    }

    loading.value = true

    try {
      await authStore.register({
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value
      })

      toast.success('Inscription réussie !')
      router.push('/')
    } catch (error) {
        if (error.response?.status === 422) {
          // Erreurs de validation Laravel
          const errors = error.response.data.errors
          if (errors) {
            const firstError = Object.values(errors)[0]
            toast.error(Array.isArray(firstError) ? firstError[0] : firstError)
          } else {
            toast.error(error.response.data.message || 'Données invalides')
          }
      } else {
        toast.success('Inscription réussie !')
      }
    } finally {
      loading.value = false
    }
  }
</script>