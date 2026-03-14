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

const grossSales = computed(() => filteredOrders.value.reduce((s, o) => s + parseFloat(o.subtotal || 0), 0))
const discounts = computed(() => filteredOrders.value.reduce((s, o) => s + parseFloat(o.discount || 0), 0))
const netSales = computed(() => grossSales.value - discounts.value)
const grossProfit = computed(() => netSales.value)

const groupedByDate = computed(() => {
  const map = {}
  filteredOrders.value.forEach(o => {
    const d = o.createdAt?.slice(0, 10)
    if (!d) return
    if (!map[d]) map[d] = { gross: 0, net: 0 }
    map[d].gross += parseFloat(o.subtotal || 0)
    map[d].net += parseFloat(o.total || 0)
  })
  return map
})

const chartData = computed(() => {
  const labels = Object.keys(groupedByDate.value).sort()
  return {
    labels,
    datasets: [
      { label: 'Gross Sales', data: labels.map(d => groupedByDate.value[d].gross), backgroundColor: 'rgba(59,130,246,0.7)' },
      { label: 'Net Sales', data: labels.map(d => groupedByDate.value[d].net), backgroundColor: 'rgba(16,185,129,0.7)' },
    ]
  }
})

const chartOptions = { responsive: true, plugins: { legend: { position: 'top' } } }

function fmt(v) { return '₱' + v.toFixed(2) }
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Sales Summary</h1>
      <p class="text-sm text-gray-500 mt-1">Revenue overview for the selected period</p>
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
      <!-- Summary Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold text-blue-600">{{ fmt(grossSales) }}</div>
          <div class="text-sm text-gray-500 mt-1">Gross Sales</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold text-red-500">{{ fmt(discounts) }}</div>
          <div class="text-sm text-gray-500 mt-1">Discounts</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold text-green-600">{{ fmt(netSales) }}</div>
          <div class="text-sm text-gray-500 mt-1">Net Sales</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold text-purple-600">{{ fmt(grossProfit) }}</div>
          <div class="text-sm text-gray-500 mt-1">Gross Profit</div>
        </div>
      </div>

      <!-- Bar Chart -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Sales Over Time</h2>
        <Bar v-if="chartData.labels.length" :data="chartData" :options="chartOptions" />
        <div v-else class="text-center text-sm text-gray-400 py-8">No data for selected period.</div>
      </div>
    </template>
  </div>
</template>
