import axios from 'axios'

const api = axios.create({
    baseURL: '/api/v1',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('admin_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

api.interceptors.response.use(
    (res) => res,
    (err) => {
        if (err.response?.status === 401) {
            localStorage.removeItem('admin_token')
            window.location.href = '/admin/login'
        }
        return Promise.reject(err)
    }
)

// Auth
export const login = (credentials) => api.post('/login', credentials)
export const logout = () => api.post('/logout')

// Branches (for admin branch selector)
export const getBranches = () => api.get('/branches')

// Customers
export const getCustomers = () => api.get('/customers')
export const getCustomer = (id) => api.get(`/customers/${id}`)
export const createCustomer = (data) => api.post('/customers', data)
export const updateCustomer = (id, data) => api.put(`/customers/${id}`, data)
export const deleteCustomer = (id) => api.delete(`/customers/${id}`)

// Categories
export const getCategories = () => api.get('/categories')
export const getCategory = (id) => api.get(`/categories/${id}`)
export const createCategory = (data) => api.post('/categories', data)
export const updateCategory = (id, data) => api.put(`/categories/${id}`, data)
export const deleteCategory = (id) => api.delete(`/categories/${id}`)

// Items
export const getItems = () => api.get('/items')
export const getItem = (id) => api.get(`/items/${id}`)
export const createItem = (data) => api.post('/items', data)
export const updateItem = (id, data) => api.put(`/items/${id}`, data)
export const deleteItem = (id) => api.delete(`/items/${id}`)

// Orders
export const getOrders = (params = {}) => api.get('/orders', { params })
export const getOrder = (id) => api.get(`/orders/${id}`)
export const createOrder = (data) => api.post('/orders', data)
export const updateOrder = (id, data) => api.put(`/orders/${id}`, data)
export const deleteOrder = (id) => api.delete(`/orders/${id}`)

// Expenses
export const getExpenses = (params = {}) => api.get('/expenses', { params })
export const getExpense = (id) => api.get(`/expenses/${id}`)
export const createExpense = (data) => api.post('/expenses', data)
export const updateExpense = (id, data) => api.put(`/expenses/${id}`, data)
export const deleteExpense = (id) => api.delete(`/expenses/${id}`)

// Expense Categories
export const getExpenseCategories = () => api.get('/expense-categories')
export const getExpenseCategory = (id) => api.get(`/expense-categories/${id}`)
export const createExpenseCategory = (data) => api.post('/expense-categories', data)
export const updateExpenseCategory = (id, data) => api.put(`/expense-categories/${id}`, data)
export const deleteExpenseCategory = (id) => api.delete(`/expense-categories/${id}`)
