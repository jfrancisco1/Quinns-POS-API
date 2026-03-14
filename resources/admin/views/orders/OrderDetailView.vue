<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getOrder } from '../../api'

const route = useRoute()
const router = useRouter()

const order = ref(null)
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const { data } = await getOrder(route.params.id)
    order.value = data.data
  } catch {
    error.value = 'Failed to load order.'
  } finally {
    loading.value = false
  }
})

function formatCurrency(val) {
  return '₱' + parseFloat(val || 0).toFixed(2)
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

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
    <div class="mb-6 flex items-center gap-4">
      <button @click="router.push('/orders')" class="text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <h1 class="text-2xl font-bold text-gray-900">Order Detail</h1>
    </div>

    <div v-if="loading" class="text-center text-sm text-gray-400 py-12">Loading...</div>
    <div v-else-if="error" class="text-center text-sm text-red-500 py-12">{{ error }}</div>
    <template v-else-if="order">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Header card -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h2 class="text-lg font-semibold text-gray-900">Order #{{ order.orderNumber }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ formatDate(order.createdAt) }}</p>
              </div>
              <div class="flex gap-2">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium" :class="paymentBadge[order.paymentStatus]">{{ order.paymentStatus }}</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium" :class="statusBadge[order.orderStatus]">{{ order.orderStatus }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-500">Customer</span>
                <p class="font-medium text-gray-900 mt-0.5">{{ order.customer?.nickname || '—' }}</p>
              </div>
              <div>
                <span class="text-gray-500">Mobile</span>
                <p class="font-medium text-gray-900 mt-0.5">{{ order.customer?.mobile || '—' }}</p>
              </div>
              <div>
                <span class="text-gray-500">Fulfillment Type</span>
                <p class="font-medium text-gray-900 capitalize mt-0.5">{{ order.fulfillmentType?.replace('-', ' ') || '—' }}</p>
              </div>
              <div>
                <span class="text-gray-500">Branch</span>
                <p class="font-medium text-gray-900 mt-0.5">{{ order.branch?.name || '—' }}</p>
              </div>
            </div>

            <div v-if="order.notes" class="mt-4 pt-4 border-t border-gray-100">
              <span class="text-sm text-gray-500">Notes</span>
              <p class="text-sm text-gray-900 mt-0.5">{{ order.notes }}</p>
            </div>
          </div>

          <!-- Items table -->
          <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-900">Order Items</h3>
            </div>
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in (order.items || order.orderItems || [])" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-6 py-3 font-medium text-gray-900">{{ item.label || item.item?.name || '—' }}</td>
                  <td class="px-6 py-3 text-gray-600">{{ item.unit || '—' }}</td>
                  <td class="px-6 py-3 text-gray-600">{{ item.quantity || item.qty || 1 }}</td>
                  <td class="px-6 py-3 text-gray-600">{{ formatCurrency(item.price) }}</td>
                  <td class="px-6 py-3 text-right font-medium text-gray-900">{{ formatCurrency(item.subtotal || (parseFloat(item.price || 0) * parseInt(item.quantity || item.qty || 1))) }}</td>
                </tr>
                <tr v-if="!(order.items || order.orderItems)?.length">
                  <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-400">No items.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Summary sidebar -->
        <div class="space-y-4">
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Order Summary</h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Subtotal</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(order.subtotal) }}</span>
              </div>
              <div v-if="parseFloat(order.discount || 0) > 0" class="flex justify-between">
                <span class="text-gray-500">Discount</span>
                <span class="font-medium text-red-600">-{{ formatCurrency(order.discount) }}</span>
              </div>
              <div v-if="parseFloat(order.deliveryFee || order.delivery_fee || 0) > 0" class="flex justify-between">
                <span class="text-gray-500">Delivery Fee</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(order.deliveryFee || order.delivery_fee) }}</span>
              </div>
              <div class="flex justify-between pt-3 border-t border-gray-100">
                <span class="font-semibold text-gray-900">Total</span>
                <span class="font-bold text-gray-900 text-base">{{ formatCurrency(order.total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
