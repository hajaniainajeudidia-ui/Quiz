<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white border-b shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
        
        <!-- Logo + Menu Desktop -->
        <div class="flex items-center gap-6 lg:gap-8">
          <router-link to="/" class="text-xl sm:text-2xl font-bold text-indigo-600">
            Quiz App
          </router-link>

          <!-- Menu Desktop (caché sur mobile) -->
          <div class="hidden md:flex items-center gap-5 lg:gap-6 text-sm font-medium">
            <router-link to="/" class="hover:text-indigo-600 transition" :class="{ 'text-indigo-600': $route.path === '/' }">
              Accueil
            </router-link>
            <router-link to="/quiz/start" class="hover:text-indigo-600 transition flex items-center gap-2">
              <PlayIcon size="18" />
              <span>Passer le Quiz</span>
            </router-link>
            <router-link to="/leaderboard" class="hover:text-indigo-600 transition flex items-center gap-2">
              <TrophyIcon size="18" />
              <span>Classement</span>
            </router-link>
            <router-link to="/my-scores" class="hover:text-indigo-600 transition flex items-center gap-2">
              <BarChart3Icon size="18" />
              <span>Mes Scores</span>
            </router-link>
            <router-link v-if="authStore.isAdmin" to="/admin" class="hover:text-indigo-600 transition flex items-center gap-2">
              <SettingsIcon size="18" />
              <span>Administration</span>
            </router-link>
          </div>
        </div>

        <!-- Droite : Nom + Déconnexion + Bouton Hamburger -->
        <div class="flex items-center gap-3 sm:gap-4">
          <span class="text-sm text-gray-600 hidden sm:block">
            {{ authStore.user?.name }}
          </span>

          <button
            @click="confirmLogout"
            class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition"
          >
            <LogOutIcon size="18" />
            <span>Déconnexion</span>
          </button>

          <!-- Bouton Hamburger (visible uniquement sur mobile) -->
          <button
            @click="toggleMobileMenu"
            class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition"
          >
            <MenuIcon v-if="!isMobileMenuOpen" size="24" />
            <XIcon v-else size="24" />
          </button>
        </div>
      </div>

      <!-- Menu Mobile -->
      <div
        v-if="isMobileMenuOpen"
        class="md:hidden border-t bg-white"
      >
        <div class="px-4 py-4 space-y-3 text-sm font-medium">
          <router-link
            to="/"
            @click="closeMobileMenu"
            class="block py-2 hover:text-indigo-600"
            :class="{ 'text-indigo-600': $route.path === '/' }"
          >
            Accueil
          </router-link>

          <router-link
            to="/quiz/start"
            @click="closeMobileMenu"
            class="flex items-center gap-2 py-2 hover:text-indigo-600"
          >
            <PlayIcon size="18" />
            Passer le Quiz
          </router-link>

          <router-link
            to="/leaderboard"
            @click="closeMobileMenu"
            class="flex items-center gap-2 py-2 hover:text-indigo-600"
          >
            <TrophyIcon size="18" />
            Classement
          </router-link>

          <router-link
            to="/my-scores"
            @click="closeMobileMenu"
            class="flex items-center gap-2 py-2 hover:text-indigo-600"
          >
            <BarChart3Icon size="18" />
            Mes Scores
          </router-link>

          <router-link
            v-if="authStore.isAdmin"
            to="/admin"
            @click="closeMobileMenu"
            class="flex items-center gap-2 py-2 hover:text-indigo-600"
          >
            <SettingsIcon size="18" />
            Administration
          </router-link>

          <div class="pt-3 border-t">
            <p class="text-gray-500 text-sm mb-2">{{ authStore.user?.name }}</p>
            <button
              @click="confirmLogout"
              class="flex items-center gap-2 text-red-600 hover:text-red-700"
            >
              <LogOutIcon size="18" />
              Déconnexion
            </button>
          </div>
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
  import { ref } from 'vue'
  import { MenuIcon, XIcon } from 'lucide-vue-next'
  import { useAuthStore } from '../../stores/auth'
  import { useRouter } from 'vue-router'
  import Swal from 'sweetalert2'
  import { HomeIcon, PlayIcon, TrophyIcon, BarChart3Icon, SettingsIcon, LogOutIcon } from 'lucide-vue-next'

  const isMobileMenuOpen = ref(false)

  const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
  }

  const closeMobileMenu = () => {
    isMobileMenuOpen.value = false
  }

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