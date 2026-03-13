<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getTenant, getBranches, deleteBranch, getUsers, toggleUser, deleteUser } from '../../api'
import ConfirmModal from '../../components/ConfirmModal.vue'

const route = useRoute()
const router = useRouter()

const tenant = ref(null)
const branches = ref([])
const users = ref([])
const loading = ref(true)
const activeTab = ref('branches')
const deleteTarget = ref(null)
const deleting = ref(false)

onMounted(fetchAll)

async function fetchAll() {
    loading.value = true
    try {
        const [tenantRes, branchRes, userRes] = await Promise.all([
            getTenant(route.params.id),
            getBranches(route.params.id),
            getUsers(route.params.id),
        ])
        tenant.value = tenantRes.data.data
        branches.value = branchRes.data.data
        users.value = userRes.data.data
    } finally {
        loading.value = false
    }
}

async function confirmDelete() {
    deleting.value = true
    try {
        if (deleteTarget.value.type === 'branch') {
            await deleteBranch(deleteTarget.value.item.id)
            branches.value = branches.value.filter(b => b.id !== deleteTarget.value.item.id)
        } else {
            await deleteUser(tenant.value.id, deleteTarget.value.item.id)
            users.value = users.value.filter(u => u.id !== deleteTarget.value.item.id)
        }
        deleteTarget.value = null
    } finally {
        deleting.value = false
    }
}

async function handleToggleUser(user) {
    const { data } = await toggleUser(tenant.value.id, user.id)
    const idx = users.value.findIndex(u => u.id === user.id)
    if (idx !== -1) users.value[idx] = data.data
}

const planColors = {
    free:  'bg-gray-100 text-gray-600',
    basic: 'bg-blue-100 text-blue-700',
    pro:   'bg-purple-100 text-purple-700',
}

const roleColors = {
    admin:    'bg-purple-100 text-purple-700',
    staff:    'bg-blue-100 text-blue-700',
    delivery: 'bg-orange-100 text-orange-700',
}
</script>

<template>
    <div>
        <div v-if="loading" class="py-12 text-center text-sm text-gray-400">Loading...</div>

        <template v-else-if="tenant">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-3">
                    <RouterLink to="/tenants" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </RouterLink>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-2xl font-bold text-gray-900">{{ tenant.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="planColors[tenant.plan]">{{ tenant.plan }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="tenant.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                                {{ tenant.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">{{ tenant.slug }}</p>
                    </div>
                </div>
                <RouterLink
                    :to="`/tenants/${tenant.id}/edit`"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </RouterLink>
            </div>

            <!-- Info cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-500 mb-1">Email</div>
                    <div class="text-sm font-medium text-gray-900">{{ tenant.email }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-500 mb-1">Phone</div>
                    <div class="text-sm font-medium text-gray-900">{{ tenant.phone || '—' }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-500 mb-1">Address</div>
                    <div class="text-sm font-medium text-gray-900">{{ tenant.address || '—' }}</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 mb-4 border-b border-gray-200">
                <button
                    v-for="tab in ['branches', 'users']"
                    :key="tab"
                    @click="activeTab = tab"
                    class="px-4 py-2.5 text-sm font-medium capitalize border-b-2 transition-colors -mb-px"
                    :class="activeTab === tab ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    {{ tab }}
                    <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full" :class="activeTab === tab ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-500'">
                        {{ tab === 'branches' ? branches.length : users.length }}
                    </span>
                </button>
            </div>

            <!-- Branches tab -->
            <div v-show="activeTab === 'branches'" class="bg-white rounded-xl border border-gray-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Branches</h2>
                    <RouterLink
                        :to="`/tenants/${tenant.id}/branches/create`"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Branch
                    </RouterLink>
                </div>
                <div v-if="!branches.length" class="px-6 py-10 text-center text-sm text-gray-400">
                    No branches yet.
                    <RouterLink :to="`/tenants/${tenant.id}/branches/create`" class="text-brand-600 hover:underline">Add one.</RouterLink>
                </div>
                <ul v-else class="divide-y divide-gray-100">
                    <li v-for="branch in branches" :key="branch.id" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ branch.name }}</div>
                                <div class="text-xs text-gray-500">{{ branch.address || 'No address' }} · {{ branch.users_count }} users</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="branch.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                                {{ branch.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <RouterLink :to="`/branches/${branch.id}/edit`" class="p-1.5 text-gray-400 hover:text-brand-600 rounded-lg hover:bg-brand-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </RouterLink>
                            <button @click="deleteTarget = { type: 'branch', item: branch }" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Users tab -->
            <div v-show="activeTab === 'users'" class="bg-white rounded-xl border border-gray-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Users</h2>
                    <RouterLink
                        :to="`/tenants/${tenant.id}/users/create`"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add User
                    </RouterLink>
                </div>
                <div v-if="!users.length" class="px-6 py-10 text-center text-sm text-gray-400">
                    No users yet.
                    <RouterLink :to="`/tenants/${tenant.id}/users/create`" class="text-brand-600 hover:underline">Add one.</RouterLink>
                </div>
                <ul v-else class="divide-y divide-gray-100">
                    <li v-for="user in users" :key="user.id" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                <div class="text-xs text-gray-500">
                                    @{{ user.username }}
                                    <span v-if="user.branch"> · {{ user.branch.name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize" :class="roleColors[user.role]">
                                {{ user.role }}
                            </span>
                            <button
                                @click="handleToggleUser(user)"
                                :title="user.is_active ? 'Deactivate' : 'Activate'"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                :class="user.is_active ? 'bg-brand-600' : 'bg-gray-300'"
                            >
                                <span class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform" :class="user.is_active ? 'translate-x-4' : 'translate-x-1'" />
                            </button>
                            <RouterLink
                                :to="`/tenants/${tenant.id}/users/${user.id}/edit`"
                                class="p-1.5 text-gray-400 hover:text-brand-600 rounded-lg hover:bg-brand-50 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </RouterLink>
                            <button
                                @click="deleteTarget = { type: 'user', item: user }"
                                class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </template>

        <ConfirmModal
            v-if="deleteTarget"
            :message="deleteTarget.type === 'branch'
                ? `Delete branch '${deleteTarget.item.name}'?`
                : `Delete user '${deleteTarget.item.name}'? They will be signed out immediately.`"
            :loading="deleting"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />
    </div>
</template>
