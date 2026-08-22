<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useBranchStore } from '../stores/branch'
import { ref, onMounted } from 'vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const branchStore = useBranchStore()
const reportsOpen = ref(route.path.startsWith('/reports'))

onMounted(() => branchStore.fetchBranches())

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-60 bg-gray-900 text-white flex flex-col shrink-0">
      <div class="px-6 py-5 border-b border-gray-700">
        <span class="text-lg font-bold tracking-wide">Quinns - Laundry POS</span>
        <span class="block text-xs text-gray-400 mt-0.5">Admin Panel</span>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <RouterLink to="/" :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.name === 'Dashboard' ? 'bg-gray-700 text-white' : 'text-gray-300']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
          Dashboard
        </RouterLink>

        <!-- Customers -->
        <RouterLink to="/customers" :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.path.startsWith('/customers') ? 'bg-gray-700 text-white' : 'text-gray-300']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          Customers
        </RouterLink>

        <!-- Orders -->
        <RouterLink to="/orders" :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.path.startsWith('/orders') ? 'bg-gray-700 text-white' : 'text-gray-300']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
          Orders
        </RouterLink>

        <!-- Items -->
        <RouterLink to="/items" :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.path.startsWith('/items') ? 'bg-gray-700 text-white' : 'text-gray-300']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
          Items
        </RouterLink>

        <!-- Categories -->
        <RouterLink to="/categories" :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.path.startsWith('/categories') ? 'bg-gray-700 text-white' : 'text-gray-300']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
          Categories
        </RouterLink>

        <!-- Expenses -->
        <RouterLink to="/expenses" :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.path.startsWith('/expenses') ? 'bg-gray-700 text-white' : 'text-gray-300']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
          Expenses
        </RouterLink>

        <!-- Expense Categories -->
        <RouterLink to="/expense-categories" :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.path.startsWith('/expense-categories') ? 'bg-gray-700 text-white' : 'text-gray-300']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
          Expense Categories
        </RouterLink>

        <!-- Reports -->
        <div>
          <button @click="reportsOpen = !reportsOpen" :class="['w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700', route.path.startsWith('/reports') ? 'bg-gray-700 text-white' : 'text-gray-300']">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <span class="flex-1 text-left">Reports</span>
            <svg class="w-3 h-3 transition-transform" :class="reportsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
          </button>
          <div v-if="reportsOpen" class="ml-4 mt-1 space-y-1">
            <RouterLink to="/reports/sales" :class="['flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors hover:bg-gray-700', route.path === '/reports/sales' ? 'bg-gray-700 text-white' : 'text-gray-400']">Sales Summary</RouterLink>
            <RouterLink to="/reports/by-item" :class="['flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors hover:bg-gray-700', route.path === '/reports/by-item' ? 'bg-gray-700 text-white' : 'text-gray-400']">By Item</RouterLink>
            <RouterLink to="/reports/by-category" :class="['flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors hover:bg-gray-700', route.path === '/reports/by-category' ? 'bg-gray-700 text-white' : 'text-gray-400']">By Category</RouterLink>
            <RouterLink to="/reports/by-payment" :class="['flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors hover:bg-gray-700', route.path === '/reports/by-payment' ? 'bg-gray-700 text-white' : 'text-gray-400']">By Payment</RouterLink>
            <RouterLink to="/reports/expenses" :class="['flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors hover:bg-gray-700', route.path === '/reports/expenses' ? 'bg-gray-700 text-white' : 'text-gray-400']">Expenses</RouterLink>
          </div>
        </div>
      </nav>

      <div class="px-4 py-4 border-t border-gray-700">
        <div class="text-xs text-gray-400 mb-2 px-2">{{ auth.user?.name }}</div>
        <button @click="handleLogout" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-700 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0 bg-gray-50">
      <!-- Top bar with branch selector -->
      <div class="bg-white border-b border-gray-200 px-8 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="text-sm text-gray-500">Branch:</span>
          <select
            v-if="branchStore.branches.length > 1"
            :value="branchStore.selectedBranchId"
            @change="branchStore.selectBranch($event.target.value)"
            class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="b in branchStore.branches" :key="b.id" :value="b.id">
              {{ b.name }}
            </option>
          </select>
          <span v-else class="text-sm font-medium text-gray-800">
            {{ branchStore.selectedBranch?.name || '—' }}
          </span>
        </div>
        <span class="text-xs text-gray-400">{{ auth.user?.name }}</span>
      </div>
      <main class="flex-1 p-8 overflow-y-auto">
        <RouterView />
      </main>
    </div>
  </div>
</template>
