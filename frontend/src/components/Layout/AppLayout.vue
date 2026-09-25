<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white border-b shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-8">
          <router-link to="/" class="text-2xl font-bold text-indigo-600">
            Quiz App
          </router-link>

          <div class="hidden md:flex items-center gap-6 text-sm font-medium">
            <router-link to="/" class="hover:text-indigo-600 transition" :class="{ 'text-indigo-600': $route.path === '/' }">Accueil</router-link>
            <router-link to="/quiz/start" class="hover:text-indigo-600 transition">
              <div class="flex gap-2">
                <PlayIcon size="18" />
                <div>
                  Passer le Quiz
                </div>
              </div>
            </router-link>
            <router-link to="/leaderboard" class="hover:text-indigo-600 transition">
              <div class="flex gap-2">
                <TrophyIcon size="18" />
                <div>
                  Classement
                </div>
              </div>
            </router-link>
            <router-link to="/my-scores" class="hover:text-indigo-600 transition">
              <div class="flex gap-2">
                <BarChart3Icon size="18" /> 
                <div>
                  Mes Scores
                </div>
              </div>
            </router-link>

            <router-link v-if="authStore.isAdmin" to="/admin" class="hover:text-indigo-600 transition">
              <div class="flex gap-2">
                <SettingsIcon size="18" /> 
                <div>
                  Administration
                </div>
              </div>
            </router-link>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600 hidden sm:block">
            {{ authStore.user?.name }}
          </span>
          <button
            @click="confirmLogout"
            class="px-5 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition"
          >
            <div class="flex gap-2">
              <LogOutIcon size="18" />
              <div>
              Déconnexion
              </div>
            </div>
          </button>
        </div>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
  import { useAuthStore } from '../../stores/auth'
  import { useRouter } from 'vue-router'
  import Swal from 'sweetalert2'
  import { HomeIcon, PlayIcon, TrophyIcon, BarChart3Icon, SettingsIcon, LogOutIcon } from 'lucide-vue-next'

  const authStore = useAuthStore()
  const router = useRouter()

  const confirmLogout = async () => {
    const result = await Swal.fire({
      title: 'Déconnexion',
      text: "Êtes-vous sûr de vouloir vous déconnecter ?",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#4f46e5',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Oui, se déconnecter',
      cancelButtonText: 'Annuler',
      reverseButtons: true
    })

    if (result.isConfirmed) {
      await authStore.logout()
      router.push('/login')
    }
  }
</script>