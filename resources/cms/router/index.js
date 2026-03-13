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
            {
                path: '',
                name: 'Dashboard',
                component: () => import('../views/DashboardView.vue'),
            },
            {
                path: 'tenants',
                name: 'Tenants',
                component: () => import('../views/tenants/TenantListView.vue'),
            },
            {
                path: 'tenants/create',
                name: 'TenantCreate',
                component: () => import('../views/tenants/TenantFormView.vue'),
            },
            {
                path: 'tenants/:id',
                name: 'TenantDetail',
                component: () => import('../views/tenants/TenantDetailView.vue'),
            },
            {
                path: 'tenants/:id/edit',
                name: 'TenantEdit',
                component: () => import('../views/tenants/TenantFormView.vue'),
            },
            {
                path: 'tenants/:tenantId/branches/create',
                name: 'BranchCreate',
                component: () => import('../views/branches/BranchFormView.vue'),
            },
            {
                path: 'branches/:branchId/edit',
                name: 'BranchEdit',
                component: () => import('../views/branches/BranchFormView.vue'),
            },
            {
                path: 'tenants/:tenantId/users/create',
                name: 'UserCreate',
                component: () => import('../views/users/UserFormView.vue'),
            },
            {
                path: 'tenants/:tenantId/users/:userId/edit',
                name: 'UserEdit',
                component: () => import('../views/users/UserFormView.vue'),
            },
        ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
    history: createWebHistory('/cms'),
    routes,
})

router.beforeEach((to) => {
    const token = localStorage.getItem('owner_token')
    if (to.meta.requiresAuth && !token) return { name: 'Login' }
    if (to.meta.guest && token) return { name: 'Dashboard' }
})

export default router
