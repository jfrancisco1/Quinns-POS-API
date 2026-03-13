<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getTenants } from '../api'

const router = useRouter()
const tenants = ref([])
const loading = ref(true)

const stats = ref({ total: 0, active: 0, inactive: 0, totalBranches: 0 })

onMounted(async () => {
  try {
    const { data } = await getTenants()
    tenants.value = data.data
    stats.value = {
      total: data.data.length,
      active: data.data.filter(t => t.is_active).length,
      inactive: data.data.filter(t => !t.is_active).length,
      totalBranches: data.data.reduce((sum, t) => sum + (t.branches_count || 0), 0),
    }
  } finally {
    loading.value = false
  }
})

const planColors = {
  free: 'bg-gray-100 text-gray-600',
  basic: 'bg-blue-100 text-blue-700',
  pro: 'bg-purple-100 text-purple-700',
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Overview of all tenants</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-8">
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-bold text-gray-900">{{ stats.total }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Tenants</div>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-bold text-green-600">{{ stats.active }}</div>
        <div class="text-sm text-gray-500 mt-1">Active</div>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-bold text-red-500">{{ stats.inactive }}</div>
        <div class="text-sm text-gray-500 mt-1">Inactive</div>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-bold text-brand-600">{{ stats.totalBranches }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Branches</div>
      </div>
    </div>

    <!-- Recent tenants -->
    <div class="bg-white rounded-xl border border-gray-200">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">All Tenants</h2>
        <RouterLink to="/tenants/create" class="text-sm font-medium text-brand-600 hover:text-brand-700">
          + Add Tenant
        </RouterLink>
      </div>

      <div v-if="loading" class="px-6 py-12 text-center text-sm text-gray-400">Loading...</div>

      <div v-else-if="!tenants.length" class="px-6 py-12 text-center text-sm text-gray-400">
        No tenants yet. <RouterLink to="/tenants/create" class="text-brand-600 hover:underline">Create one.</RouterLink>
      </div>

      <ul v-else class="divide-y divide-gray-100">
        <li
          v-for="tenant in tenants"
          :key="tenant.id"
          class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 cursor-pointer transition-colors"
          @click="router.push(`/tenants/${tenant.id}`)"
        >
          <div class="flex items-center gap-4">
            <div class="w-9 h-9 rounded-lg bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-sm">
              {{ tenant.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <div class="text-sm font-medium text-gray-900">{{ tenant.name }}</div>
              <div class="text-xs text-gray-500">{{ tenant.email }}</div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500">{{ tenant.branches_count }} branches</span>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="planColors[tenant.plan]">
              {{ tenant.plan }}
            </span>
            <span
              class="text-xs px-2 py-0.5 rounded-full font-medium"
              :class="tenant.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
            >
              {{ tenant.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>
