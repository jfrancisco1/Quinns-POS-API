<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getExpense, createExpense, updateExpense, getExpenseCategories } from '../../api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  description: '',
  amount: '',
  expense_date: '',
  note: '',
  expense_category_id: '',
})

const categories = ref([])
const selectableCategories = computed(() =>
  categories.value.filter(c => c.is_active || c.id === form.value.expense_category_id)
)
const loading = ref(false)
const fetchLoading = ref(true)
const errors = ref({})
const serverError = ref('')

onMounted(async () => {
  try {
    const requests = [getExpenseCategories()]
    if (isEdit.value) requests.push(getExpense(route.params.id))

    const [categoriesRes, expenseRes] = await Promise.all(requests)
    categories.value = categoriesRes.data.data

    if (expenseRes) {
      const e = expenseRes.data.data
      form.value = {
        description: e.description || '',
        amount: e.amount || '',
        expense_date: e.expense_date || '',
        note: e.note || '',
        expense_category_id: e.expense_category_id || '',
      }
    }
  } catch {
    serverError.value = 'Failed to load expense.'
  } finally {
    fetchLoading.value = false
  }
})

async function handleSubmit() {
  errors.value = {}
  serverError.value = ''
  loading.value = true
  try {
    const payload = { ...form.value }
    if (payload.note === '') delete payload.note

    if (isEdit.value) {
      await updateExpense(route.params.id, payload)
    } else {
      await createExpense(payload)
    }
    router.push('/expenses')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      serverError.value = e.response?.data?.message || 'An error occurred.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center gap-4">
      <button @click="router.push('/expenses')" class="text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Expense' : 'New Expense' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update expense record' : 'Record a new expense' }}</p>
      </div>
    </div>

    <div v-if="fetchLoading" class="text-center text-sm text-gray-400 py-12">Loading...</div>
    <div v-else class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
      <div v-if="serverError" class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3.5 py-2.5">
        {{ serverError }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Description <span class="text-red-500">*</span></label>
          <input v-model="form.description" type="text" required class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.description ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.description" class="mt-1 text-xs text-red-600">{{ errors.description[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Amount <span class="text-red-500">*</span></label>
          <input v-model="form.amount" type="number" step="0.01" min="0" required class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.amount ? 'border-red-400' : 'border-gray-300'" placeholder="0.00" />
          <p v-if="errors.amount" class="mt-1 text-xs text-red-600">{{ errors.amount[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Date <span class="text-red-500">*</span></label>
          <input v-model="form.expense_date" type="date" required class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.expense_date ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.expense_date" class="mt-1 text-xs text-red-600">{{ errors.expense_date[0] }}</p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
            <RouterLink to="/expense-categories" class="text-xs text-blue-600 hover:text-blue-700">Manage categories</RouterLink>
          </div>
          <select v-model="form.expense_category_id" required class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.expense_category_id ? 'border-red-400' : 'border-gray-300'">
            <option value="" disabled>Select a category</option>
            <option v-for="cat in selectableCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
          <p v-if="errors.expense_category_id" class="mt-1 text-xs text-red-600">{{ errors.expense_category_id[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Note</label>
          <textarea v-model="form.note" rows="3" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.note ? 'border-red-400' : 'border-gray-300'" placeholder="Optional note..."></textarea>
          <p v-if="errors.note" class="mt-1 text-xs text-red-600">{{ errors.note[0] }}</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="loading" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
            {{ loading ? 'Saving...' : (isEdit ? 'Update Expense' : 'Create Expense') }}
          </button>
          <button type="button" @click="router.push('/expenses')" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
