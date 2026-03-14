<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCustomer, createCustomer, updateCustomer } from '../../api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  nickname: '',
  mobile: '',
  address: '',
  notes: '',
  delivery_fee: '',
})

const loading = ref(false)
const fetchLoading = ref(isEdit.value)
const errors = ref({})
const serverError = ref('')

onMounted(async () => {
  if (isEdit.value) {
    try {
      const { data } = await getCustomer(route.params.id)
      const c = data.data
      form.value = {
        nickname: c.nickname || '',
        mobile: c.mobile || '',
        address: c.address || '',
        notes: c.notes || '',
        delivery_fee: c.deliveryFee || '',
      }
    } catch {
      serverError.value = 'Failed to load customer.'
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
    const payload = { ...form.value }
    if (payload.delivery_fee === '') delete payload.delivery_fee
    if (isEdit.value) {
      await updateCustomer(route.params.id, payload)
    } else {
      await createCustomer(payload)
    }
    router.push('/customers')
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
      <button @click="router.push('/customers')" class="text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Customer' : 'New Customer' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update customer details' : 'Add a new customer' }}</p>
      </div>
    </div>

    <div v-if="fetchLoading" class="text-center text-sm text-gray-400 py-12">Loading...</div>
    <div v-else class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
      <div v-if="serverError" class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3.5 py-2.5">
        {{ serverError }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nickname <span class="text-red-500">*</span></label>
          <input v-model="form.nickname" type="text" required class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.nickname ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.nickname" class="mt-1 text-xs text-red-600">{{ errors.nickname[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobile</label>
          <input v-model="form.mobile" type="text" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.mobile ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.mobile" class="mt-1 text-xs text-red-600">{{ errors.mobile[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
          <input v-model="form.address" type="text" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.address ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.address" class="mt-1 text-xs text-red-600">{{ errors.address[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Fee</label>
          <input v-model="form.delivery_fee" type="number" step="0.01" min="0" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.delivery_fee ? 'border-red-400' : 'border-gray-300'" placeholder="0.00" />
          <p v-if="errors.delivery_fee" class="mt-1 text-xs text-red-600">{{ errors.delivery_fee[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
          <textarea v-model="form.notes" rows="3" class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :class="errors.notes ? 'border-red-400' : 'border-gray-300'"></textarea>
          <p v-if="errors.notes" class="mt-1 text-xs text-red-600">{{ errors.notes[0] }}</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="loading" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
            {{ loading ? 'Saving...' : (isEdit ? 'Update Customer' : 'Create Customer') }}
          </button>
          <button type="button" @click="router.push('/customers')" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
