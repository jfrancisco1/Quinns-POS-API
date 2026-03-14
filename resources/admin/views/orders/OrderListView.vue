<script setup>
import { ref, onMounted, computed } from 'vue'
import { getOrders } from '../../api'

const orders = ref([])
const loading = ref(true)
const dateFilter = ref('today')
const customFrom = ref('')
const customTo = ref('')
const unpaidOnly = ref(false)
const statusFilter = ref('all')

onMounted(async () => {
  try {
    const { data } = await getOrders()
    orders.value = data.data
  } finally {
    loading.value = false
  }
})

function getDateRange(filter) {
  const now = new Date()
  const today = now.toISOString().slice(0, 10)
  if (filter === 'today') return [today, today]
  if (filter === 'week') {
    const start = new Date(now); start.setDate(now.getDate() - now.getDay())
    return [start.toISOString().slice(0, 10), today]
  }
  if (filter === 'month') return [`${today.slice(0, 7)}-01`, today]
  if (filter === 'year') return [`${today.slice(0, 4)}-01-01`, today]
  if (filter === 'custom') return [customFrom.value, customTo.value]
  return [null, null]
}

const filtered = computed(() => {
  const [from, to] = getDateRange(dateFilter.value)
  return orders.value.filter(o => {
    const d = o.createdAt?.slice(0, 10)
    if (from && d < from) return false
    if (to && d > to) return false
    if (unpaidOnly.value && o.paymentStatus !== 'unpaid') return false
    if (statusFilter.value !== 'all' && o.orderStatus !== statusFilter.value) return false
    return true
  })
})

const paymentBadge = {
  unpaid: 'bg-red-100 text-red-700',
  pending: 'bg-yellow-100 text-yellow-700',
  paid_cash: 'bg-green-100 text-green-700',
  paid_gcash: 'bg-blue-100 text-blue-700',
}
const statusBadge = {
  in_progress: 'bg-yellow-100 text-yellow-700',
  ready: 'bg-blue-100 text-blue-700',
  completed: 'bg-green-100 text-green-700',
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
      <p class="text-sm text-gray-500 mt-1">{{ filtered.length }} orders shown</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 px-6 py-4 mb-4 flex flex-wrap gap-3 items-center">
      <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
        <button v-for="f in ['today','week','month','year','custom']" :key="f"
          @click="dateFilter = f"
          :class="['px-3 py-1.5 text-xs font-medium rounded-md transition-colors capitalize', dateFilter === f ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700']">
          {{ f === 'week' ? 'This Week' : f === 'month' ? 'This Month' : f === 'year' ? 'This Year' : f === 'custom' ? 'Custom' : 'Today' }}
        </button>
      </div>

      <template v-if="dateFilter === 'custom'">
        <input v-model="customFrom" type="date" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm" />
        <span class="text-gray-400 text-sm">to</span>
        <input v-model="customTo" type="date" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm" />
      </template>

      <select v-model="statusFilter" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
        <option value="all">All Status</option>
        <option value="in_progress">In Progress</option>
        <option value="ready">Ready</option>
        <option value="completed">Completed</option>
      </select>

      <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
        <input v-model="unpaidOnly" type="checkbox" class="rounded border-gray-300" />
        Unpaid only
      </label>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
      <div v-if="loading" class="px-6 py-12 text-center text-sm text-gray-400">Loading...</div>
      <div v-else-if="!filtered.length" class="px-6 py-12 text-center text-sm text-gray-400">No orders found.</div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="order in filtered" :key="order.id"
            class="hover:bg-gray-50 cursor-pointer"
            @click="$router.push(`/orders/${order.id}`)">
            <td class="px-6 py-3 font-medium">{{ order.orderNumber }}</td>
            <td class="px-6 py-3 text-gray-600">{{ order.customer?.nickname || '—' }}</td>
            <td class="px-6 py-3 text-gray-600 capitalize">{{ order.fulfillmentType?.replace('-', ' ') }}</td>
            <td class="px-6 py-3 font-medium">₱{{ parseFloat(order.total || 0).toFixed(2) }}</td>
            <td class="px-6 py-3">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="paymentBadge[order.paymentStatus]">{{ order.paymentStatus }}</span>
            </td>
            <td class="px-6 py-3">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusBadge[order.orderStatus]">{{ order.orderStatus }}</span>
            </td>
            <td class="px-6 py-3 text-gray-500">{{ order.createdAt?.slice(0, 10) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
