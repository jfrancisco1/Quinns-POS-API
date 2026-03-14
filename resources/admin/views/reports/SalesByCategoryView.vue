<script setup>
import { ref, onMounted, computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'
import { getOrders, getItems } from '../../api'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const orders = ref([])
const items = ref([])
const loading = ref(true)
const dateFilter = ref('month')
const customFrom = ref('')
const customTo = ref('')

onMounted(async () => {
  try {
    const [ordersRes, itemsRes] = await Promise.all([getOrders(), getItems()])
    orders.value = ordersRes.data.data
    items.value = itemsRes.data.data
  } finally {
    loading.value = false
  }
})

// Build item → category lookup by item name
const itemCategoryMap = computed(() => {
  const map = {}
  items.value.forEach(item => {
    map[item.name] = item.category?.name || 'Uncategorized'
  })
  return map
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

const categorySummary = computed(() => {
  const map = {}
  filteredOrders.value.forEach(order => {
    const orderItems = order.items || order.orderItems || []
    orderItems.forEach(item => {
      const itemName = item.label || item.item?.name || 'Unknown'
      const category = item.item?.category?.name || itemCategoryMap.value[itemName] || 'Uncategorized'
      if (!map[category]) map[category] = { units: 0, revenue: 0 }
      map[category].units += parseInt(item.quantity || item.qty || 1)
      map[category].revenue += parseFloat(item.subtotal || (parseFloat(item.price || 0) * parseInt(item.quantity || item.qty || 1)))
    })
  })
  return Object.entries(map).map(([category, d]) => ({ category, ...d })).sort((a, b) => b.revenue - a.revenue)
})

const colors = [
  'rgba(59,130,246,0.7)',
  'rgba(16,185,129,0.7)',
  'rgba(245,158,11,0.7)',
  'rgba(239,68,68,0.7)',
  'rgba(139,92,246,0.7)',
  'rgba(236,72,153,0.7)',
]

const chartData = computed(() => ({
  labels: categorySummary.value.map(c => c.category),
  datasets: [
    {
      label: 'Revenue (₱)',
      data: categorySummary.value.map(c => c.revenue),
      backgroundColor: categorySummary.value.map((_, i) => colors[i % colors.length]),
    },
  ]
}))

const chartOptions = { responsive: true, plugins: { legend: { position: 'top' } } }
function fmt(v) { return '₱' + parseFloat(v || 0).toFixed(2) }
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Sales by Category</h1>
      <p class="text-sm text-gray-500 mt-1">Revenue breakdown per category</p>
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
        <h2 class="font-semibold text-gray-900 mb-4">Revenue by Category</h2>
        <Bar v-if="chartData.labels.length" :data="chartData" :options="chartOptions" />
        <div v-else class="text-center text-sm text-gray-400 py-8">No data for selected period.</div>
      </div>

      <!-- Summary Table -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Category Summary</h2>
        </div>
        <div v-if="!categorySummary.length" class="px-6 py-8 text-center text-sm text-gray-400">No data.</div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Units Sold</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="cat in categorySummary" :key="cat.category" class="hover:bg-gray-50">
              <td class="px-6 py-3 font-medium text-gray-900">{{ cat.category }}</td>
              <td class="px-6 py-3 text-gray-600">{{ cat.units }}</td>
              <td class="px-6 py-3 font-medium text-gray-900">{{ fmt(cat.revenue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
