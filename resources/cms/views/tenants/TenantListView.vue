<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { getTenants, deleteTenant } from '../../api'
import ConfirmModal from '../../components/ConfirmModal.vue'

const router = useRouter()
const tenants = ref([])
const loading = ref(true)
const search = ref('')
const deleteTarget = ref(null)
const deleting = ref(false)

onMounted(fetchTenants)

async function fetchTenants() {
  loading.value = true
  try {
    const { data } = await getTenants()
    tenants.value = data.data
  } finally {
    loading.value = false
  }
}

const filtered = computed(() =>
  tenants.value.filter(t =>
    t.name.toLowerCase().includes(search.value.toLowerCase()) ||
    t.email.toLowerCase().includes(search.value.toLowerCase())
  )
)

async function confirmDelete() {
  deleting.value = true
  try {
    await deleteTenant(deleteTarget.value.id)
    tenants.value = tenants.value.filter(t => t.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } finally {
    deleting.value = false
  }
}

const planColors = {
  free: 'bg-gray-100 text-gray-600',
  basic: 'bg-blue-100 text-blue-700',
  pro: 'bg-purple-100 text-purple-700',
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Tenants</h1>
        <p class="text-sm text-gray-500 mt-1">Manage all laundry tenants</p>
      </div>
      <RouterLink
        to="/tenants/create"
        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Tenant
      </RouterLink>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl border border-gray-200 mb-4">
      <div class="relative px-4 py-3">
        <svg class="absolute left-7 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="search"
          type="text"
          placeholder="Search tenants..."
          class="w-full pl-8 text-sm text-gray-700 focus:outline-none"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div v-if="loading" class="px-6 py-12 text-center text-sm text-gray-400">Loading...</div>

      <div v-else-if="!filtered.length" class="px-6 py-12 text-center text-sm text-gray-400">
        {{ search ? 'No tenants match your search.' : 'No tenants yet.' }}
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tenant</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Branches</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="tenant in filtered"
            :key="tenant.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-xs">
                  {{ tenant.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="font-medium text-gray-900">{{ tenant.name }}</div>
                  <div class="text-xs text-gray-500">{{ tenant.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="planColors[tenant.plan]">
                {{ tenant.plan }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-600">{{ tenant.branches_count }}</td>
            <td class="px-6 py-4">
              <span
                class="text-xs px-2 py-0.5 rounded-full font-medium"
                :class="tenant.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
              >
                {{ tenant.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-2">
                <RouterLink
                  :to="`/tenants/${tenant.id}`"
                  class="p-1.5 text-gray-400 hover:text-brand-600 rounded-lg hover:bg-brand-50 transition-colors"
                  title="View"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </RouterLink>
                <RouterLink
                  :to="`/tenants/${tenant.id}/edit`"
                  class="p-1.5 text-gray-400 hover:text-brand-600 rounded-lg hover:bg-brand-50 transition-colors"
                  title="Edit"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </RouterLink>
                <button
                  @click="deleteTarget = tenant"
                  class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                  title="Delete"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmModal
      v-if="deleteTarget"
      :message="`Delete tenant '${deleteTarget.name}'? This will also delete all branches and users.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>
