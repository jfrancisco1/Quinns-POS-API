<script setup>
import { ref, onMounted, computed } from 'vue'
import { getOrders, getCustomers, getExpenses } from '../api'

const orders = ref([])
const customers = ref([])
const expenses = ref([])
const loading = ref(true)

const today = new Date().toISOString().slice(0, 10)
const thisMonth = new Date().toISOString().slice(0, 7)

const todayOrders = computed(() => orders.value.filter(o => o.createdAt?.slice(0, 10) === today))
const todayRevenue = computed(() => todayOrders.value.reduce((sum, o) => sum + parseFloat(o.total || 0), 0))
const monthExpenses = computed(() => expenses.value.filter(e => e.expense_date?.startsWith(thisMonth)).reduce((sum, e) => sum + parseFloat(e.amount || 0), 0))
const recentOrders = computed(() => [...orders.value].sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt)).slice(0, 5))

onMounted(async () => {
  try {
    const [ordersRes, customersRes, expensesRes] = await Promise.all([getOrders(), getCustomers(), getExpenses()])
    orders.value = ordersRes.data.data
    customers.value = customersRes.data.data
    expenses.value = expensesRes.data.data
  } finally {
    loading.value = false
  }
})

function formatCurrency(val) {
  return '₱' + parseFloat(val || 0).toFixed(2)
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Overview of your laundry business</p>
    </div>

    <div v-if="loading" class="text-center text-sm text-gray-400 py-12">Loading...</div>
    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-3xl font-bold text-gray-900">{{ todayOrders.length }}</div>
          <div class="text-sm text-gray-500 mt-1">Today's Orders</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-3xl font-bold text-green-600">{{ formatCurrency(todayRevenue) }}</div>
          <div class="text-sm text-gray-500 mt-1">Today's Revenue</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-3xl font-bold text-blue-600">{{ customers.length }}</div>
          <div class="text-sm text-gray-500 mt-1">Total Customers</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="text-3xl font-bold text-red-500">{{ formatCurrency(monthExpenses) }}</div>
          <div class="text-sm text-gray-500 mt-1">Month's Expenses</div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Recent Orders</h2>
          <RouterLink to="/orders" class="text-sm font-medium text-blue-600 hover:text-blue-700">View all</RouterLink>
        </div>
        <div v-if="!recentOrders.length" class="px-6 py-8 text-center text-sm text-gray-400">No orders yet.</div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 cursor-pointer" @click="$router.push(`/orders/${order.id}`)">
              <td class="px-6 py-3 font-medium">{{ order.orderNumber }}</td>
              <td class="px-6 py-3 text-gray-600">{{ order.customer?.nickname || '—' }}</td>
              <td class="px-6 py-3">{{ formatCurrency(order.total) }}</td>
              <td class="px-6 py-3">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="{
                  'bg-yellow-100 text-yellow-700': order.orderStatus === 'in_progress',
                  'bg-blue-100 text-blue-700': order.orderStatus === 'ready',
                  'bg-green-100 text-green-700': order.orderStatus === 'completed',
                }">{{ order.orderStatus }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
