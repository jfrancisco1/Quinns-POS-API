<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getTenant, getBranches, getUsers, createUser, updateUser } from '../../api'

const route = useRoute()
const router = useRouter()

const tenantId = computed(() => route.params.tenantId)
const userId = computed(() => route.params.userId)
const isEdit = computed(() => !!userId.value)
const isOnboarding = computed(() => route.query.onboarding === '1' && !isEdit.value)

const loading = ref(false)
const fetching = ref(false)
const errors = ref({})
const branches = ref([])
const tenant = ref(null)
const showPassword = ref(false)

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

function slugify(str) {
    return (str || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
}

function generatePassword(length = 8) {
    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'
    const bytes = new Uint32Array(length)
    crypto.getRandomValues(bytes)
    return Array.from(bytes, (b) => chars[b % chars.length]).join('')
}

function regeneratePassword() {
    form.value.password = generatePassword()
    showPassword.value = true
}

// Owner-admin accounts are created by the operator on the tenant's behalf and are
// forced to change their password on first login, so suggest credentials instead
// of making the operator invent them. Passwords aren't recoverable once hashed —
// if one is lost, generating a new one here (and forcing another change) is the
// only way back in, both on create and later from the edit screen.
function regenerateCredentials() {
    const base = tenant.value?.slug || slugify(tenant.value?.name) || 'tenant'
    form.value.username = `${base}.admin`
    regeneratePassword()
}

watch(() => form.value.role, (role) => {
    if (role === 'admin') form.value.branch_id = null
    if (!isEdit.value && role === 'admin' && !form.value.username && !form.value.password) {
        regenerateCredentials()
    }
})

onMounted(async () => {
    if (!isEdit.value) {
        const { data: tenantData } = await getTenant(tenantId.value)
        tenant.value = tenantData.data
        if (form.value.role === 'admin') regenerateCredentials()
    }

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
            <h1 class="text-2xl font-bold text-gray-900">{{ isOnboarding ? 'Create Owner-Admin' : isEdit ? 'Edit User' : 'New User' }}</h1>
        </div>

        <div v-if="isOnboarding" class="mb-6 bg-brand-50 border border-brand-200 rounded-xl px-4 py-3 text-sm text-brand-800">
            Step 2 of 2 — create the owner-admin account for
            <strong>{{ tenant?.name || 'this tenant' }}</strong>.
            They'll use it to log in and set up their own branches, items, and staff.
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
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-gray-700">Username <span class="text-red-500">*</span></label>
                    <button
                        v-if="!isEdit && form.role === 'admin'"
                        type="button"
                        @click="regenerateCredentials"
                        class="text-xs font-medium text-brand-600 hover:text-brand-700"
                    >
                        Suggest new
                    </button>
                </div>
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
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-gray-700">
                        Password
                        <span v-if="!isEdit" class="text-red-500">*</span>
                        <span v-else class="text-gray-400 font-normal">(leave blank to keep current)</span>
                    </label>
                    <button
                        v-if="form.role === 'admin'"
                        type="button"
                        @click="regeneratePassword"
                        class="text-xs font-medium text-brand-600 hover:text-brand-700"
                    >
                        {{ isEdit ? 'Generate new password' : 'Suggest new' }}
                    </button>
                </div>
                <div class="relative">
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        :required="!isEdit"
                        autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 pr-10 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        :class="errors.password ? 'border-red-400' : 'border-gray-300'"
                        placeholder="Min. 8 characters"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        :title="showPassword ? 'Hide password' : 'Show password'"
                    >
                        <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
                <p v-if="form.role === 'admin' && form.password" class="text-xs text-gray-500 mt-1">
                    Share this with the owner-admin — they'll be required to set their own on next login.
                </p>
                <p v-else-if="isEdit && form.role === 'admin'" class="text-xs text-gray-400 mt-1">
                    Password isn't recoverable once set. Lost it? Generate a new one above.
                </p>
                <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password[0] }}</p>
            </div>

            <!-- Role (locked to admin during onboarding) -->
            <div v-if="!isOnboarding">
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
            <div v-if="!isOnboarding && needsBranch">
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
