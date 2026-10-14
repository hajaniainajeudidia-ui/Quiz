<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Quiz App</h1>
        <p class="text-gray-600 mt-2">Connectez-vous pour continuer</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="exemple@email.com"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-lg transition disabled:opacity-70"
        >
          {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
        </button>
      </form>
      <div class="mt-6 text-center">
        <p class="text-gray-600">
          Vous n'avez pas encore un compte ? Veuillez s'inscrire 
          <router-link to="/register" class="text-blue-600 hover:underline">S'inscrire</router-link>
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


  const auth = useAuthStore()
  const router = useRouter()

  const email = ref('')
  const password = ref('')

  const loading = ref(false)



  const isNetworkError = (error) => {
    return (
      !error.response &&
      (error.code === 'ERR_NETWORK' ||
      error.message === 'Network Error' ||
      error.message?.includes('Network Error'))
    )
  } 


  const handleLogin = async () => {
      loading.value = true
      try {

          const user = await auth.login({
            email: email.value,
            password: password.value
          })

          console.log("user", user.is_admin)

          if (user.is_admin === 1) {
              router.push('/admin')
          } else {
              router.push('/')
          }

      } catch (error) {
          if (isNetworkError(error)) {
            toast.error('Aucun connexion internet. Vérifiez votre réseau.')
          } else {
            toast.error((error.response?.data?.message || 'Identifiant incorrect !!'))
          }
          
          
      } finally {
          loading.value = false
      }
  }


</script>