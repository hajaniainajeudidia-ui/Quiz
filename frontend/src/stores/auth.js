import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {

    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.is_admin === 1
    },

    actions: {

        async login(credentials) {

            const response = await api.post('/login', credentials)

            this.user = response.data.user
            this.token = response.data.token

            localStorage.setItem('user', JSON.stringify(this.user))
            localStorage.setItem('token', this.token)

            return this.user
        },
        loadUserFromStorage() {
            const savedUser = localStorage.getItem('user')
            if (savedUser) {
                this.user = JSON.parse(savedUser)
            }
        },
        async register(userData) {
            try {
                const response = await api.post('/register', userData)

                const { token, user } = response.data

                localStorage.setItem('token', token)
                localStorage.setItem('user', JSON.stringify(user))

                token.value = token
                user.value = user
                isAuthenticated.value = true

                return response.data
            } catch (error) {
                throw error  // ← obligatoire
            }
        },

        logout() {

            localStorage.removeItem('user')
            localStorage.removeItem('token')

            this.user = null
            this.token = null
        }
    }
})