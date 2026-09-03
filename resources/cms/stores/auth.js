import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login as apiLogin, logout as apiLogout } from '../api'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('superadmin_token') || null)
    const user = ref(JSON.parse(localStorage.getItem('superadmin_user') || 'null'))

    const isAuthenticated = computed(() => !!token.value)

    async function login(credentials) {
        const { data } = await apiLogin(credentials)

        if (data.user?.role !== 'superadmin') {
            throw new Error('Access denied. Superadmin account required.')
        }

        token.value = data.token
        user.value = data.user
        localStorage.setItem('superadmin_token', data.token)
        localStorage.setItem('superadmin_user', JSON.stringify(data.user))
    }

    async function logout() {
        try {
            await apiLogout()
        } finally {
            token.value = null
            user.value = null
            localStorage.removeItem('superadmin_token')
            localStorage.removeItem('superadmin_user')
        }
    }

    return { token, user, isAuthenticated, login, logout }
})
