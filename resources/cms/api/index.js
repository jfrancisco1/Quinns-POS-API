import axios from 'axios'

const api = axios.create({
    baseURL: '/api/v1',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('superadmin_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

api.interceptors.response.use(
    (res) => res,
    (err) => {
        if (err.response?.status === 401) {
            localStorage.removeItem('superadmin_token')
            window.location.href = '/cms/login'
        }
        return Promise.reject(err)
    }
)

// Auth
export const login = (credentials) => api.post('/login', credentials)
export const logout = () => api.post('/logout')

// Tenants
export const getTenants = () => api.get('/superadmin/tenants')
export const getTenant = (id) => api.get(`/superadmin/tenants/${id}`)
export const createTenant = (data) => api.post('/superadmin/tenants', data)
export const updateTenant = (id, data) => api.put(`/superadmin/tenants/${id}`, data)
export const deleteTenant = (id) => api.delete(`/superadmin/tenants/${id}`)

// Branches
export const getBranches = (tenantId) => api.get(`/superadmin/tenants/${tenantId}/branches`)
export const getBranch = (tenantId, branchId) => api.get(`/superadmin/tenants/${tenantId}/branches/${branchId}`)
export const createBranch = (tenantId, data) => api.post(`/superadmin/tenants/${tenantId}/branches`, data)
export const updateBranch = (branchId, data) => api.put(`/superadmin/branches/${branchId}`, data)
export const deleteBranch = (branchId) => api.delete(`/superadmin/branches/${branchId}`)

// Users
export const getUsers = (tenantId) => api.get(`/superadmin/tenants/${tenantId}/users`)
export const createUser = (tenantId, data) => api.post(`/superadmin/tenants/${tenantId}/users`, data)
export const updateUser = (tenantId, userId, data) => api.put(`/superadmin/tenants/${tenantId}/users/${userId}`, data)
export const toggleUser = (tenantId, userId) => api.patch(`/superadmin/tenants/${tenantId}/users/${userId}/toggle`)
export const deleteUser = (tenantId, userId) => api.delete(`/superadmin/tenants/${tenantId}/users/${userId}`)
