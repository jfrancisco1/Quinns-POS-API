<script setup>
import { ref, onMounted } from 'vue'
import { getExpenses, deleteExpense } from '../../api'
import ConfirmModal from '../../components/ConfirmModal.vue'

const expenses = ref([])
const loading = ref(true)
const deleteTarget = ref(null)
const deleting = ref(false)

onMounted(async () => {
  try {
    const { data } = await getExpenses()
    expenses.value = data.data
  } finally {
    loading.value = false
  }
})

async function confirmDelete() {
  deleting.value = true
  try {
    await deleteExpense(deleteTarget.value.id)
    expenses.value = expenses.value.filter(e => e.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } finally {
    deleting.value = false
  }
}

function formatCurrency(val) {
  return '₱' + parseFloat(val || 0).toFixed(2)
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
        <p class="text-sm text-gray-500 mt-1">{{ expenses.length }} total expenses</p>
      </div>
      <RouterLink to="/expenses/create" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">+ Add Expense</RouterLink>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
      <div v-if="loading" class="px-6 py-12 text-center text-sm text-gray-400">Loading...</div>
      <div v-else-if="!expenses.length" class="px-6 py-12 text-center text-sm text-gray-400">No expenses yet.</div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50">
            <td class="px-6 py-3 font-medium text-gray-900">{{ expense.description }}</td>
            <td class="px-6 py-3 font-medium text-red-600">{{ formatCurrency(expense.amount) }}</td>
            <td class="px-6 py-3 text-gray-600">{{ formatDate(expense.expense_date) }}</td>
            <td class="px-6 py-3 text-gray-500 max-w-xs truncate">{{ expense.note || '—' }}</td>
            <td class="px-6 py-3 text-right space-x-2">
              <RouterLink :to="`/expenses/${expense.id}/edit`" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Edit</RouterLink>
              <button @click="deleteTarget = expense" class="text-red-600 hover:text-red-700 text-xs font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmModal
      v-if="deleteTarget"
      :message="`Delete expense '${deleteTarget.description}'? This cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>
