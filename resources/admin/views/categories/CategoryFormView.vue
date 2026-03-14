<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCategory, createCategory, updateCategory } from '../../api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  name: '',
  color: '#3b82f6',
  is_active: true,
})

const loading = ref(false)
const fetchLoading = ref(isEdit.value)
const errors = ref({})
const serverError = ref('')

onMounted(async () => {
  if (isEdit.value) {
    try {
      const { data } = await getCategory(route.params.id)
      const cat = data.data
      form.value = {
        name: cat.name || '',
        color: cat.color || '#3b82f6',
        is_active: cat.is_active ?? true,
      }
    } catch {
      serverError.value = 'Failed to load category.'
    } finally {
      fetchLoading.value = false
    }
  }
})

async function handleSubmit() {
  errors.value = {}
  serverError.value = ''
  loading.value = true
  try {
    if (isEdit.value) {
      await updateCategory(route.params.id, form.value)
    } else {
      await createCategory(form.value)
    }
    router.push('/categories')
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
      <button @click="router.push('/categories')" class="text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Category' : 'New Category' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update category details' : 'Add a new category' }}</p>
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
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Color</label>
          <div class="flex items-center gap-3">
            <input v-model="form.color" type="color" class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" />
            <span class="text-sm text-gray-600">{{ form.color }}</span>
          </div>
          <p v-if="errors.color" class="mt-1 text-xs text-red-600">{{ errors.color[0] }}</p>
        </div>

        <div v-if="isEdit">
          <label class="flex items-center gap-3 cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <span class="text-sm font-medium text-gray-700">Active</span>
          </label>
          <p class="text-xs text-gray-500 mt-1 ml-7">Inactive categories will not appear in the POS.</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="loading" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
            {{ loading ? 'Saving...' : (isEdit ? 'Update Category' : 'Create Category') }}
          </button>
          <button type="button" @click="router.push('/categories')" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
