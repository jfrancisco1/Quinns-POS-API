<script setup>
import { ref, onMounted } from 'vue'
import { getItems, deleteItem } from '../../api'
import ConfirmModal from '../../components/ConfirmModal.vue'

const items = ref([])
const loading = ref(true)
const deleteTarget = ref(null)
const deleting = ref(false)

onMounted(async () => {
  try {
    const { data } = await getItems()
    items.value = data.data
  } finally {
    loading.value = false
  }
})

async function confirmDelete() {
  deleting.value = true
  try {
    await deleteItem(deleteTarget.value.id)
    items.value = items.value.filter(i => i.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } finally {
    deleting.value = false
  }
}

function formatCurrency(val) {
  return '₱' + parseFloat(val || 0).toFixed(2)
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Items</h1>
        <p class="text-sm text-gray-500 mt-1">{{ items.length }} total items</p>
      </div>
      <RouterLink to="/items/create" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">+ Add Item</RouterLink>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
      <div v-if="loading" class="px-6 py-12 text-center text-sm text-gray-400">Loading...</div>
      <div v-else-if="!items.length" class="px-6 py-12 text-center text-sm text-gray-400">No items yet.</div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-6 py-3 font-medium text-gray-900">{{ item.name }}</td>
            <td class="px-6 py-3 text-gray-600">{{ item.category?.name || '—' }}</td>
            <td class="px-6 py-3 text-gray-900">{{ formatCurrency(item.price) }}</td>
            <td class="px-6 py-3 text-gray-600">{{ item.cost ? formatCurrency(item.cost) : '—' }}</td>
            <td class="px-6 py-3">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-3 text-right space-x-2">
              <RouterLink :to="`/items/${item.id}/edit`" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Edit</RouterLink>
              <button @click="deleteTarget = item" class="text-red-600 hover:text-red-700 text-xs font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmModal
      v-if="deleteTarget"
      :message="`Delete item '${deleteTarget.name}'? This cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>
