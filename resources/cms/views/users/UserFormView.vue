<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getBranches, getUsers, createUser, updateUser } from '../../api'

const route = useRoute()
const router = useRouter()

const tenantId = computed(() => route.params.tenantId)
const userId = computed(() => route.params.userId)
const isEdit = computed(() => !!userId.value)

const loading = ref(false)
const fetching = ref(false)
const errors = ref({})
const branches = ref([])

const form = ref({
    name: '',
    username: '',
    password: '',
    role: 'admin',
    branch_id: null,
    is_active: true,
})

// Admin users don't need a branch
const needsBranch = computed(() => ['staff', 'delivery'].includes(form.value.role))

watch(() => form.value.role, (role) => {
    if (role === 'admin') form.value.branch_id = null
})

onMounted(async () => {
    // Load branches for this tenant
    const { data } = await getBranches(tenantId.value)
    branches.value = data.data

    if (!isEdit.value) return

    fetching.value = true
    try {
        const { data: usersData } = await getUsers(tenantId.value)
        const user = usersData.data.find(u => u.id === parseInt(userId.value))
        if (!user) return router.push(`/tenants/${tenantId.value}`)

        form.value = {
            name: user.name,
            username: user.username,
            password: '',
            role: user.role,
            branch_id: user.branch_id,
            is_active: user.is_active,
        }
    } finally {
        fetching.value = false
    }
})

async function handleSubmit() {
    errors.value = {}
    loading.value = true

    const payload = { ...form.value }
    // Don't send empty password on edit
    if (isEdit.value && !payload.password) delete payload.password

    try {
        if (isEdit.value) {
            await updateUser(tenantId.value, userId.value, payload)
        } else {
            await createUser(tenantId.value, payload)
        }
        router.push(`/tenants/${tenantId.value}`)
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {}
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div>
        <div class="flex items-center gap-3 mb-6">
            <RouterLink :to="`/tenants/${tenantId}`" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </RouterLink>
            <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit User' : 'New User' }}</h1>
        </div>

        <div v-if="fetching" class="py-12 text-center text-sm text-gray-400">Loading...</div>

        <form v-else @submit.prevent="handleSubmit" class="bg-white rounded-xl border border-gray-200 p-6 max-w-xl space-y-5">
            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    :class="errors.name ? 'border-red-400' : 'border-gray-300'"
                />
                <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name[0] }}</p>
            </div>

            <!-- Username -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                <input
                    v-model="form.username"
                    type="text"
                    required
                    class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    :class="errors.username ? 'border-red-400' : 'border-gray-300'"
                    placeholder="e.g. jane.admin"
                />
                <p v-if="errors.username" class="text-xs text-red-500 mt-1">{{ errors.username[0] }}</p>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Password
                    <span v-if="!isEdit" class="text-red-500">*</span>
                    <span v-else class="text-gray-400 font-normal">(leave blank to keep current)</span>
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    :required="!isEdit"
                    autocomplete="new-password"
                    class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    :class="errors.password ? 'border-red-400' : 'border-gray-300'"
                    placeholder="Min. 8 characters"
                />
                <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password[0] }}</p>
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    <label
                        v-for="r in ['admin', 'staff', 'delivery']"
                        :key="r"
                        class="flex items-center justify-center gap-2 px-3 py-2.5 border rounded-lg cursor-pointer text-sm font-medium transition-colors"
                        :class="form.role === r
                            ? 'border-brand-500 bg-brand-50 text-brand-700'
                            : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                    >
                        <input v-model="form.role" :value="r" type="radio" class="sr-only" />
                        <span class="capitalize">{{ r }}</span>
                    </label>
                </div>
            </div>

            <!-- Branch (only for staff/delivery) -->
            <div v-if="needsBranch">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Branch <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.branch_id"
                    :required="needsBranch"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    :class="errors.branch_id ? 'border-red-400' : 'border-gray-300'"
                >
                    <option :value="null">Select branch</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                <p v-if="errors.branch_id" class="text-xs text-red-500 mt-1">{{ errors.branch_id[0] }}</p>
            </div>

            <div v-else class="text-xs text-gray-400 bg-gray-50 rounded-lg px-3.5 py-2.5">
                Admin users have access to all branches.
            </div>

            <!-- Active toggle -->
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="form.is_active = !form.is_active"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                    :class="form.is_active ? 'bg-brand-600' : 'bg-gray-300'"
                >
                    <span class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform" :class="form.is_active ? 'translate-x-4' : 'translate-x-1'" />
                </button>
                <span class="text-sm font-medium text-gray-700">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <RouterLink
                    :to="`/tenants/${tenantId}`"
                    class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </RouterLink>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 rounded-lg hover:bg-brand-700 disabled:opacity-50 transition-colors"
                >
                    {{ loading ? 'Saving...' : isEdit ? 'Save Changes' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</template>
