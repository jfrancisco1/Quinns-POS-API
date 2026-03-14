import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login as apiLogin, logout as apiLogout } from '../api'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('admin_token') || null)
    const user = ref(JSON.parse(localStorage.getItem('admin_user') || 'null'))

    const isAuthenticated = computed(() => !!token.value)

    async function login(credentials) {
        const { data } = await apiLogin(credentials)
        if (data.user?.role !== 'admin') {
            throw new Error('Access denied. Admin account required.')
        }
        token.value = data.token
        user.value = data.user
        localStorage.setItem('admin_token', data.token)
        localStorage.setItem('admin_user', JSON.stringify(data.user))
    }

    async function logout() {
        try {
            await apiLogout()
        } finally {
            token.value = null
            user.value = null
            localStorage.removeItem('admin_token')
            localStorage.removeItem('admin_user')
        }
    }

    return { token, user, isAuthenticated, login, logout }
})
