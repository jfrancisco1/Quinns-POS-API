<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getBranch, createBranch, updateBranch } from '../../api'

const route = useRoute()
const router = useRouter()

const tenantId = computed(() => route.params.tenantId)
const branchId = computed(() => route.params.branchId)
const isEdit = computed(() => !!branchId.value)

const loading = ref(false)
const fetching = ref(false)
const errors = ref({})

const form = ref({
  name: '',
  address: '',
  phone: '',
  is_active: true,
})

// We store tenantId when editing (needed for back link)
const resolvedTenantId = ref(tenantId.value || null)

onMounted(async () => {
  if (!isEdit.value) return
  fetching.value = true
  try {
    // For edit, we don't have tenantId in the URL directly,
    // so load branch and use its tenant_id
    const { data } = await getBranch(resolvedTenantId.value || 0, branchId.value)
    const b = data.data
    resolvedTenantId.value = b.tenant_id
    form.value = {
      name: b.name,
      address: b.address || '',
      phone: b.phone || '',
      is_active: b.is_active,
    }
  } finally {
    fetching.value = false
  }
})

async function handleSubmit() {
  errors.value = {}
  loading.value = true
  try {
    if (isEdit.value) {
      await updateBranch(branchId.value, form.value)
    } else {
      await createBranch(tenantId.value, form.value)
    }
    router.push(`/tenants/${resolvedTenantId.value}`)
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
      <RouterLink :to="`/tenants/${resolvedTenantId}`" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
      </RouterLink>
      <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Branch' : 'New Branch' }}</h1>
    </div>

    <div v-if="fetching" class="py-12 text-center text-sm text-gray-400">Loading...</div>

    <form v-else @submit.prevent="handleSubmit" class="bg-white rounded-xl border border-gray-200 p-6 max-w-xl space-y-5">
      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Branch Name <span class="text-red-500">*</span></label>
        <input
          v-model="form.name"
          type="text"
          required
          class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          :class="errors.name ? 'border-red-400' : 'border-gray-300'"
          placeholder="e.g. Branch A — Makati"
        />
        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name[0] }}</p>
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

      <!-- Phone -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
        <input
          v-model="form.phone"
          type="text"
          class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          placeholder="+639..."
        />
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
          :to="`/tenants/${resolvedTenantId}`"
          class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Cancel
        </RouterLink>
        <button
          type="submit"
          :disabled="loading"
          class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 rounded-lg hover:bg-brand-700 disabled:opacity-50 transition-colors"
        >
          {{ loading ? 'Saving...' : isEdit ? 'Save Changes' : 'Create Branch' }}
        </button>
      </div>
    </form>
  </div>
</template>
