import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/LoginView.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('../components/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', name: 'Dashboard', component: () => import('../views/DashboardView.vue') },
            { path: 'customers', name: 'Customers', component: () => import('../views/customers/CustomerListView.vue') },
            { path: 'customers/create', name: 'CustomerCreate', component: () => import('../views/customers/CustomerFormView.vue') },
            { path: 'customers/:id/edit', name: 'CustomerEdit', component: () => import('../views/customers/CustomerFormView.vue') },
            { path: 'categories', name: 'Categories', component: () => import('../views/categories/CategoryListView.vue') },
            { path: 'categories/create', name: 'CategoryCreate', component: () => import('../views/categories/CategoryFormView.vue') },
            { path: 'categories/:id/edit', name: 'CategoryEdit', component: () => import('../views/categories/CategoryFormView.vue') },
            { path: 'items', name: 'Items', component: () => import('../views/items/ItemListView.vue') },
            { path: 'items/create', name: 'ItemCreate', component: () => import('../views/items/ItemFormView.vue') },
            { path: 'items/:id/edit', name: 'ItemEdit', component: () => import('../views/items/ItemFormView.vue') },
            { path: 'orders', name: 'Orders', component: () => import('../views/orders/OrderListView.vue') },
            { path: 'orders/:id', name: 'OrderDetail', component: () => import('../views/orders/OrderDetailView.vue') },
            { path: 'expenses', name: 'Expenses', component: () => import('../views/expenses/ExpenseListView.vue') },
            { path: 'expenses/create', name: 'ExpenseCreate', component: () => import('../views/expenses/ExpenseFormView.vue') },
            { path: 'expenses/:id/edit', name: 'ExpenseEdit', component: () => import('../views/expenses/ExpenseFormView.vue') },
            { path: 'reports/sales', name: 'ReportSales', component: () => import('../views/reports/SalesSummaryView.vue') },
            { path: 'reports/by-item', name: 'ReportByItem', component: () => import('../views/reports/SalesByItemView.vue') },
            { path: 'reports/by-category', name: 'ReportByCategory', component: () => import('../views/reports/SalesByCategoryView.vue') },
            { path: 'reports/by-payment', name: 'ReportByPayment', component: () => import('../views/reports/SalesByPaymentView.vue') },
            { path: 'reports/expenses', name: 'ReportExpenses', component: () => import('../views/reports/ExpensesReportView.vue') },
        ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
    history: createWebHistory('/admin'),
    routes,
})

router.beforeEach((to) => {
    const token = localStorage.getItem('admin_token')
    if (to.meta.requiresAuth && !token) return { name: 'Login' }
    if (to.meta.guest && token) return { name: 'Dashboard' }
})

export default router
