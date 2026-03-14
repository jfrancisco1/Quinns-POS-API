<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getItem, createItem, updateItem, getCategories } from '../../api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  name: '',
  description: '',
  price: '',
  cost: '',
  category_id: '',
  is_active: true,
})

const categories = ref([])
const loading = ref(false)
const fetchLoading = ref(true)
const errors = ref({})
const serverError = ref('')

onMounted(async () => {
  try {
    const [categoriesRes] = await Promise.all([getCategories()])
    categories.value = categoriesRes.data.data

    if (isEdit.value) {
      const { data } = await getItem(route.params.id)
      const item = data.data
      form.value = {
        name: item.name || '',
        description: item.description || '',
        price: item.price || '',
        cost: item.cost || '',
        category_id: item.category?.id || item.category_id || '',
        is_active: item.is_active ?? true,
      }
    }
  } catch {
    serverError.value = 'Failed to load data.'
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
    if (payload.cost === '') delete payload.cost
    if (payload.description === '') delete payload.description
    if (payload.category_id === '') delete payload.category_id

    if (isEdit.value) {
      await updateItem(route.params.id, payload)
    } else {
      await createItem(payload)
    }
    router.push('/items')
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
      <button @click="router.push('/items')" class="text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Item' : 'New Item' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update item details' : 'Add a new service item' }}</p>
      </div>
    </div>

    <div v-if="fetchLoading" class="text-center text-sm text-gray-400 py-12">Loading...</div>
    <div v-else class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
      <div v-if="serverError" class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3.5 py-2.5">
        {{ serverError }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
          <input v-model="form.name" type="text" required class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.name ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
          <textarea v-model="form.description" rows="2" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.description ? 'border-red-400' : 'border-gray-300'"></textarea>
          <p v-if="errors.description" class="mt-1 text-xs text-red-600">{{ errors.description[0] }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Price <span class="text-red-500">*</span></label>
            <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.price ? 'border-red-400' : 'border-gray-300'" placeholder="0.00" />
            <p v-if="errors.price" class="mt-1 text-xs text-red-600">{{ errors.price[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cost</label>
            <input v-model="form.cost" type="number" step="0.01" min="0" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.cost ? 'border-red-400' : 'border-gray-300'" placeholder="0.00" />
            <p v-if="errors.cost" class="mt-1 text-xs text-red-600">{{ errors.cost[0] }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
          <select v-model="form.category_id" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.category_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">— No Category —</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
          <p v-if="errors.category_id" class="mt-1 text-xs text-red-600">{{ errors.category_id[0] }}</p>
        </div>

        <div v-if="isEdit">
          <label class="flex items-center gap-3 cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <span class="text-sm font-medium text-gray-700">Active</span>
          </label>
          <p class="text-xs text-gray-500 mt-1 ml-7">Inactive items will not appear in the POS.</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="loading" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
            {{ loading ? 'Saving...' : (isEdit ? 'Update Item' : 'Create Item') }}
          </button>
          <button type="button" @click="router.push('/items')" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
