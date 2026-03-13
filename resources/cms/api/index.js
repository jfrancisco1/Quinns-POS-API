import axios from 'axios'

const api = axios.create({
    baseURL: '/api/v1',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('owner_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

api.interceptors.response.use(
    (res) => res,
    (err) => {
        if (err.response?.status === 401) {
            localStorage.removeItem('owner_token')
            window.location.href = '/cms/login'
        }
        return Promise.reject(err)
    }
)

// Auth
export const login = (credentials) => api.post('/login', credentials)
export const logout = () => api.post('/logout')

// Tenants
export const getTenants = () => api.get('/owner/tenants')
export const getTenant = (id) => api.get(`/owner/tenants/${id}`)
export const createTenant = (data) => api.post('/owner/tenants', data)
export const updateTenant = (id, data) => api.put(`/owner/tenants/${id}`, data)
export const deleteTenant = (id) => api.delete(`/owner/tenants/${id}`)

// Branches
export const getBranches = (tenantId) => api.get(`/owner/tenants/${tenantId}/branches`)
export const getBranch = (tenantId, branchId) => api.get(`/owner/tenants/${tenantId}/branches/${branchId}`)
export const createBranch = (tenantId, data) => api.post(`/owner/tenants/${tenantId}/branches`, data)
export const updateBranch = (branchId, data) => api.put(`/owner/branches/${branchId}`, data)
export const deleteBranch = (branchId) => api.delete(`/owner/branches/${branchId}`)

// Users
export const getUsers = (tenantId) => api.get(`/owner/tenants/${tenantId}/users`)
export const createUser = (tenantId, data) => api.post(`/owner/tenants/${tenantId}/users`, data)
export const updateUser = (tenantId, userId, data) => api.put(`/owner/tenants/${tenantId}/users/${userId}`, data)
export const toggleUser = (tenantId, userId) => api.patch(`/owner/tenants/${tenantId}/users/${userId}/toggle`)
export const deleteUser = (tenantId, userId) => api.delete(`/owner/tenants/${tenantId}/users/${userId}`)
