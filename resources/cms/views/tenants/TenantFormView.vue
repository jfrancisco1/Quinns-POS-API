<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getTenant, createTenant, updateTenant } from '../../api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const fetching = ref(false)
const errors = ref({})

const form = ref({
  name: '',
  slug: '',
  email: '',
  phone: '',
  address: '',
  plan: 'free',
  is_active: true,
})

onMounted(async () => {
  if (!isEdit.value) return
  fetching.value = true
  try {
    const { data } = await getTenant(route.params.id)
    const t = data.data
    form.value = {
      name: t.name,
      slug: t.slug,
      email: t.email,
      phone: t.phone || '',
      address: t.address || '',
      plan: t.plan,
      is_active: t.is_active,
    }
  } finally {
    fetching.value = false
  }
})

function autoSlug() {
  if (!isEdit.value) {
    form.value.slug = form.value.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
  }
}

async function handleSubmit() {
  errors.value = {}
  loading.value = true
  try {
    if (isEdit.value) {
      await updateTenant(route.params.id, form.value)
      router.push(`/tenants/${route.params.id}`)
    } else {
      const { data } = await createTenant(form.value)
      router.push(`/tenants/${data.data.id}/users/create?onboarding=1`)
    }
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
      <RouterLink :to="isEdit ? `/tenants/${route.params.id}` : '/tenants'" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
      </RouterLink>
      <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Tenant' : 'New Tenant' }}</h1>
    </div>

    <div v-if="fetching" class="text-sm text-gray-400 py-12 text-center">Loading...</div>

    <form v-else @submit.prevent="handleSubmit" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-5">
      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Business Name <span class="text-red-500">*</span></label>
        <input
          v-model="form.name"
          @input="autoSlug"
          type="text"
          required
          class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          :class="errors.name ? 'border-red-400' : 'border-gray-300'"
        />
        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name[0] }}</p>
      </div>

      <!-- Slug -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Slug <span class="text-red-500">*</span></label>
        <input
          v-model="form.slug"
          type="text"
          required
          class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          :class="errors.slug ? 'border-red-400' : 'border-gray-300'"
          placeholder="e.g. janes-laundry"
        />
        <p v-if="errors.slug" class="text-xs text-red-500 mt-1">{{ errors.slug[0] }}</p>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
        <input
          v-model="form.email"
          type="email"
          required
          class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          :class="errors.email ? 'border-red-400' : 'border-gray-300'"
        />
        <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email[0] }}</p>
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
        <input
          v-model="form.phone"
          type="text"
          class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          placeholder="+639..."
        />
        <p v-if="errors.phone" class="text-xs text-red-500 mt-1">{{ errors.phone[0] }}</p>
      </div>

      <!-- Address -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
        <textarea
          v-model="form.address"
          rows="2"
          class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
        />
      </div>

      <!-- Plan -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Plan <span class="text-red-500">*</span></label>
        <select
          v-model="form.plan"
          class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent bg-white"
        >
          <option value="free">Free</option>
          <option value="basic">Basic</option>
          <option value="pro">Pro</option>
        </select>
      </div>

      <!-- Active (edit only) -->
      <div v-if="isEdit" class="flex items-center gap-3">
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
          :to="isEdit ? `/tenants/${route.params.id}` : '/tenants'"
          class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Cancel
        </RouterLink>
        <button
          type="submit"
          :disabled="loading"
          class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 rounded-lg hover:bg-brand-700 disabled:opacity-50 transition-colors"
        >
          {{ loading ? 'Saving...' : isEdit ? 'Save Changes' : 'Create Tenant' }}
        </button>
      </div>
    </form>
  </div>
</template>
