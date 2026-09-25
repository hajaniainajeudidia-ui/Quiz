import { createRouter, createWebHistory } from 'vue-router'

// Import des pages
import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'
import Home from '../pages/Home.vue'
import TakeQuiz from '../pages/TakeQuiz.vue'
import QuizResult from '../pages/QuizResult.vue'
import QuizStart from '../pages/QuizStart.vue'
import AdminDashboard from '../pages/AdminDashboard.vue'
import AdminQuiz from '../pages/AdminQuiz.vue'
import AdminQuestion from '../pages/AdminQuestion.vue'
import MyScores from '../pages/MyScores.vue'
import Leaderboard from '../pages/Leaderboard.vue'


import { useAuthStore } from '../stores/auth'

// Routes
const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { requiresGuest: true }        // Accessible seulement si NON connecté
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { requiresGuest: true }
    },
    {
        path: '/',
        name: 'Home',
        component: Home,
        meta: { requiresAuth: true }         // Protection
    },
    {
        path: '/quiz/start',
        name: 'QuizStart',
        component: QuizStart,
        meta: { requiresAuth: true }
    },
    {
        path: '/quiz/take/:attemptId',
        name: 'TakeQuiz',
        component: TakeQuiz,
        meta: { requiresAuth: true }
    },
    {
        path: '/quiz/result/:attemptId',
        name: 'QuizResult',
        component: QuizResult,
        meta: { requiresAuth: true }
    },
    {
        path: '/admin',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/quizzes',
        name: 'AdminQuiz',
        component: AdminQuiz,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/questions',
        name: 'AdminQuestion',
        component: AdminQuestion,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    
    {
        path: '/leaderboard',
        name: 'Leaderboard',
        component: Leaderboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/my-scores',
        name: 'MyScores',
        component: MyScores,
        meta: { requiresAuth: true }
    },
    
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// PROTECTION DES ROUTES

router.beforeEach((to, from, next) => {

    const auth = useAuthStore()

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return next('/login')
    }

    if (to.meta.requiresAdmin && !auth.isAdmin) {
        return next('/')
    }

    if (to.meta.requiresGuest && auth.isAuthenticated) {

        if (auth.isAdmin) {
            return next('/admin')
        }

        return next('/')
    }

    next()
})

export default router