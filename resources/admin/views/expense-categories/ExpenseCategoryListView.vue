<script setup>
import { ref, onMounted } from 'vue'
import { getExpenseCategories, deleteExpenseCategory } from '../../api'
import ConfirmModal from '../../components/ConfirmModal.vue'

const categories = ref([])
const loading = ref(true)
const deleteTarget = ref(null)
const deleting = ref(false)
const deleteError = ref('')

onMounted(async () => {
  try {
    const { data } = await getExpenseCategories()
    categories.value = data.data
  } finally {
    loading.value = false
  }
})

async function confirmDelete() {
  deleting.value = true
  deleteError.value = ''
  try {
    await deleteExpenseCategory(deleteTarget.value.id)
    categories.value = categories.value.filter(c => c.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch (e) {
    deleteError.value = e.response?.data?.message || 'Failed to delete category.'
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Expense Categories</h1>
        <p class="text-sm text-gray-500 mt-1">{{ categories.length }} total categories</p>
      </div>
      <RouterLink to="/expense-categories/create" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">+ Add Category</RouterLink>
    </div>

    <div v-if="deleteError" class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3.5 py-2.5">
      {{ deleteError }}
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
      <div v-if="loading" class="px-6 py-12 text-center text-sm text-gray-400">Loading...</div>
      <div v-else-if="!categories.length" class="px-6 py-12 text-center text-sm text-gray-400">No expense categories yet.</div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50">
            <td class="px-6 py-3 font-medium text-gray-900">{{ cat.name }}</td>
            <td class="px-6 py-3">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="cat.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                {{ cat.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-3 text-right space-x-2">
              <RouterLink :to="`/expense-categories/${cat.id}/edit`" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Edit</RouterLink>
              <button @click="deleteTarget = cat" class="text-red-600 hover:text-red-700 text-xs font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmModal
      v-if="deleteTarget"
      :message="`Delete category '${deleteTarget.name}'? This fails if any expenses still use it.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>
