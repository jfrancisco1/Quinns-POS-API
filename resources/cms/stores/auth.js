import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login as apiLogin, logout as apiLogout } from '../api'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('owner_token') || null)
    const user = ref(JSON.parse(localStorage.getItem('owner_user') || 'null'))

    const isAuthenticated = computed(() => !!token.value)

    async function login(credentials) {
        const { data } = await apiLogin(credentials)

        if (data.user?.role !== 'owner') {
            throw new Error('Access denied. Owner account required.')
        }

        token.value = data.token
        user.value = data.user
        localStorage.setItem('owner_token', data.token)
        localStorage.setItem('owner_user', JSON.stringify(data.user))
    }

    async function logout() {
        try {
            await apiLogout()
        } finally {
            token.value = null
            user.value = null
            localStorage.removeItem('owner_token')
            localStorage.removeItem('owner_user')
        }
    }

    return { token, user, isAuthenticated, login, logout }
})
