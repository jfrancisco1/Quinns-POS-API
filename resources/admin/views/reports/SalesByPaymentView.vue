<script setup>
import { ref, onMounted, computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'
import { getOrders } from '../../api'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const orders = ref([])
const loading = ref(true)
const dateFilter = ref('month')
const customFrom = ref('')
const customTo = ref('')

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
  if (filter === 'week') { const s = new Date(now); s.setDate(now.getDate() - now.getDay()); return [s.toISOString().slice(0, 10), today] }
  if (filter === 'month') return [`${today.slice(0, 7)}-01`, today]
  if (filter === 'year') return [`${today.slice(0, 4)}-01-01`, today]
  if (filter === 'custom') return [customFrom.value, customTo.value]
  return [null, null]
}

const filteredOrders = computed(() => {
  const [from, to] = getDateRange(dateFilter.value)
  return orders.value.filter(o => {
    const d = o.createdAt?.slice(0, 10)
    if (from && d < from) return false
    if (to && d > to) return false
    return true
  })
})

const paymentMethods = ['unpaid', 'pending', 'paid_cash', 'paid_gcash']

const paymentSummary = computed(() => {
  const map = {}
  paymentMethods.forEach(m => { map[m] = { count: 0, total: 0 } })
  filteredOrders.value.forEach(o => {
    const method = o.paymentStatus || 'unpaid'
    if (!map[method]) map[method] = { count: 0, total: 0 }
    map[method].count++
    map[method].total += parseFloat(o.total || 0)
  })
  return Object.entries(map).map(([method, d]) => ({ method, ...d }))
})

const paymentColors = {
  unpaid: 'rgba(239,68,68,0.7)',
  pending: 'rgba(245,158,11,0.7)',
  paid_cash: 'rgba(16,185,129,0.7)',
  paid_gcash: 'rgba(59,130,246,0.7)',
}

const paymentLabels = {
  unpaid: 'Unpaid',
  pending: 'Pending',
  paid_cash: 'Paid (Cash)',
  paid_gcash: 'Paid (GCash)',
}

const chartData = computed(() => ({
  labels: paymentSummary.value.map(p => paymentLabels[p.method] || p.method),
  datasets: [
    {
      label: 'Total Revenue (₱)',
      data: paymentSummary.value.map(p => p.total),
      backgroundColor: paymentSummary.value.map(p => paymentColors[p.method] || 'rgba(107,114,128,0.7)'),
    },
  ]
}))

const chartOptions = { responsive: true, plugins: { legend: { position: 'top' } } }
function fmt(v) { return '₱' + parseFloat(v || 0).toFixed(2) }
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Sales by Payment Method</h1>
      <p class="text-sm text-gray-500 mt-1">Revenue breakdown per payment status</p>
    </div>

    <!-- Date filter -->
    <div class="bg-white rounded-xl border border-gray-200 px-6 py-4 mb-4 flex flex-wrap gap-3 items-center">
      <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
        <button v-for="f in ['today','week','month','year','custom']" :key="f"
          @click="dateFilter = f"
          :class="['px-3 py-1.5 text-xs font-medium rounded-md transition-colors', dateFilter === f ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700']">
          {{ f === 'week' ? 'This Week' : f === 'month' ? 'This Month' : f === 'year' ? 'This Year' : f === 'custom' ? 'Custom' : 'Today' }}
        </button>
      </div>
      <template v-if="dateFilter === 'custom'">
        <input v-model="customFrom" type="date" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm" />
        <span class="text-gray-400 text-sm">to</span>
        <input v-model="customTo" type="date" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm" />
      </template>
    </div>

    <div v-if="loading" class="text-center text-sm text-gray-400 py-12">Loading...</div>
    <template v-else>
      <!-- Chart -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-4">Revenue by Payment Method</h2>
        <Bar v-if="filteredOrders.length" :data="chartData" :options="chartOptions" />
        <div v-else class="text-center text-sm text-gray-400 py-8">No data for selected period.</div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div v-for="p in paymentSummary" :key="p.method" class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold" :class="{
            'text-red-500': p.method === 'unpaid',
            'text-yellow-600': p.method === 'pending',
            'text-green-600': p.method === 'paid_cash',
            'text-blue-600': p.method === 'paid_gcash',
          }">{{ fmt(p.total) }}</div>
          <div class="text-sm text-gray-500 mt-1">{{ paymentLabels[p.method] || p.method }}</div>
          <div class="text-xs text-gray-400 mt-0.5">{{ p.count }} orders</div>
        </div>
      </div>

      <!-- Summary Table -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Payment Summary</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="p in paymentSummary" :key="p.method" class="hover:bg-gray-50">
              <td class="px-6 py-3 font-medium text-gray-900">{{ paymentLabels[p.method] || p.method }}</td>
              <td class="px-6 py-3 text-gray-600">{{ p.count }}</td>
              <td class="px-6 py-3 font-medium text-gray-900">{{ fmt(p.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
