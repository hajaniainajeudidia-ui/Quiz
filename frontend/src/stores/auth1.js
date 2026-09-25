import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', () => {

    // États
    const user = ref(null)
    const token = ref(localStorage.getItem('auth_token') || null)
    const isAuthenticated = ref(!!localStorage.getItem('auth_token'))

    const isAdmin = computed(() => user.value?.is_admin === true)

    // Actions
    const setUser = (userData) => {
        user.value = userData
        isAuthenticated.value = true
    }

    const login = async (credentials) => {
        try {

            const router = useRouter()
            const response = await api.post('/login', credentials)

            const { token: newToken, user: userData } = response.data

            // Sauvegarder dans localStorage
            localStorage.setItem('auth_token', newToken)
            localStorage.setItem('user', JSON.stringify(userData))

            // Mettre à jour le store
            token.value = newToken
            user.value = userData
            if(user.value.is_admin === true){
                router.push('/admin')
            }
            else{
                router.push('/')
            }
            console.log("user", user.value)
            isAuthenticated.value = true

            return response.data
        } catch (error) {
            throw error
        }
    }

    // const redirectAfterLogin = () => {
    //     const router = useRouter()
        
    //     if (isAdmin.value) {
    //         router.push('/admin')
    //     } else {
    //         router.push('/')
    //     }
    // }

    const register = async (userData) => {
        try {
            const response = await api.post('/register', userData)

            const { token: newToken, user: newUser } = response.data

            localStorage.setItem('auth_token', newToken)
            localStorage.setItem('user', JSON.stringify(newUser))

            token.value = newToken
            user.value = newUser
            isAuthenticated.value = true

            return response.data
        } catch (error) {
            throw error
        }
    }

    const logout = async () => {
        try {
            // Appel API pour supprimer le token côté serveur
            await api.post('/logout')
        } catch (error) {
            console.error('Erreur lors de la déconnexion', error)
        } finally {
            // Nettoyage local
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')

            token.value = null
            user.value = null
            isAuthenticated.value = false
        }
    }

    const loadUserFromStorage = () => {
        const savedUser = localStorage.getItem('user')
        if (savedUser) {
            user.value = JSON.parse(savedUser)
        }
    }

    // Retourner tout ce dont on aura besoin
    return {
        user,
        token,
        isAuthenticated,
        isAdmin,
        login,
        register,
        logout,
        setUser,
        loadUserFromStorage
    }
})