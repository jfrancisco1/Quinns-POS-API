<script setup>
import { ref, onMounted, computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'
import { getExpenses } from '../../api'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const expenses = ref([])
const loading = ref(true)
const dateFilter = ref('month')
const customFrom = ref('')
const customTo = ref('')

onMounted(async () => {
  try {
    const { data } = await getExpenses()
    expenses.value = data.data
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

const filteredExpenses = computed(() => {
  const [from, to] = getDateRange(dateFilter.value)
  return expenses.value.filter(e => {
    const d = e.expense_date
    if (from && d < from) return false
    if (to && d > to) return false
    return true
  })
})

const totalExpenses = computed(() => filteredExpenses.value.reduce((s, e) => s + parseFloat(e.amount || 0), 0))

const groupedByDate = computed(() => {
  const map = {}
  filteredExpenses.value.forEach(e => {
    const d = e.expense_date
    if (!d) return
    if (!map[d]) map[d] = 0
    map[d] += parseFloat(e.amount || 0)
  })
  return map
})

const chartData = computed(() => {
  const labels = Object.keys(groupedByDate.value).sort()
  return {
    labels,
    datasets: [
      {
        label: 'Expenses (₱)',
        data: labels.map(d => groupedByDate.value[d]),
        backgroundColor: 'rgba(239,68,68,0.7)',
      },
    ]
  }
})

const chartOptions = { responsive: true, plugins: { legend: { position: 'top' } } }
function fmt(v) { return '₱' + parseFloat(v || 0).toFixed(2) }
function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Expenses Report</h1>
      <p class="text-sm text-gray-500 mt-1">Expense overview for the selected period</p>
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
      <!-- Summary Card -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold text-red-500">{{ fmt(totalExpenses) }}</div>
          <div class="text-sm text-gray-500 mt-1">Total Expenses</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold text-gray-900">{{ filteredExpenses.length }}</div>
          <div class="text-sm text-gray-500 mt-1">Total Records</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-2xl font-bold text-gray-700">{{ filteredExpenses.length ? fmt(totalExpenses / filteredExpenses.length) : '₱0.00' }}</div>
          <div class="text-sm text-gray-500 mt-1">Average per Record</div>
        </div>
      </div>

      <!-- Chart -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-4">Expenses Over Time</h2>
        <Bar v-if="chartData.labels.length" :data="chartData" :options="chartOptions" />
        <div v-else class="text-center text-sm text-gray-400 py-8">No data for selected period.</div>
      </div>

      <!-- Expense List -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Expense Records</h2>
        </div>
        <div v-if="!filteredExpenses.length" class="px-6 py-8 text-center text-sm text-gray-400">No expenses for this period.</div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="expense in filteredExpenses" :key="expense.id" class="hover:bg-gray-50">
              <td class="px-6 py-3 font-medium text-gray-900">{{ expense.description }}</td>
              <td class="px-6 py-3 font-medium text-red-600">{{ fmt(expense.amount) }}</td>
              <td class="px-6 py-3 text-gray-600">{{ formatDate(expense.expense_date) }}</td>
              <td class="px-6 py-3 text-gray-500 max-w-xs truncate">{{ expense.note || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
