<script setup>
import { ref, onMounted, computed } from 'vue'
import { getCustomers, deleteCustomer } from '../../api'
import ConfirmModal from '../../components/ConfirmModal.vue'

const customers = ref([])
const loading = ref(true)
const search = ref('')
const deleteTarget = ref(null)
const deleting = ref(false)

onMounted(async () => {
  try {
    const { data } = await getCustomers()
    customers.value = data.data
  } finally {
    loading.value = false
  }
})

const filtered = computed(() =>
  customers.value.filter(c => c.nickname?.toLowerCase().includes(search.value.toLowerCase()) || c.mobile?.includes(search.value))
)

async function confirmDelete() {
  deleting.value = true
  try {
    await deleteCustomer(deleteTarget.value.id)
    customers.value = customers.value.filter(c => c.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } finally {
    deleting.value = false
  }
}

function formatDate(val) {
  if (!val) return 'Never'
  return new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatCurrency(val) {
  return '₱' + parseFloat(val || 0).toFixed(2)
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
        <p class="text-sm text-gray-500 mt-1">{{ customers.length }} total customers</p>
      </div>
      <RouterLink to="/customers/create" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">+ Add Customer</RouterLink>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-100">
        <input v-model="search" type="text" placeholder="Search by name or mobile..." class="w-full max-w-sm px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div v-if="loading" class="px-6 py-12 text-center text-sm text-gray-400">Loading...</div>
      <div v-else-if="!filtered.length" class="px-6 py-12 text-center text-sm text-gray-400">No customers found.</div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nickname</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mobile</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Visit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spend</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="c in filtered" :key="c.id" class="hover:bg-gray-50">
            <td class="px-6 py-3 font-medium text-gray-900">{{ c.nickname }}</td>
            <td class="px-6 py-3 text-gray-600">{{ c.mobile || '—' }}</td>
            <td class="px-6 py-3 text-gray-600">{{ formatDate(c.last_visit) }}</td>
            <td class="px-6 py-3 text-gray-900 font-medium">{{ formatCurrency(c.total_spend) }}</td>
            <td class="px-6 py-3 text-right space-x-2">
              <RouterLink :to="`/customers/${c.id}/edit`" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Edit</RouterLink>
              <button @click="deleteTarget = c" class="text-red-600 hover:text-red-700 text-xs font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmModal
      v-if="deleteTarget"
      :message="`Delete customer '${deleteTarget.nickname}'? This cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>
