import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { getBranches } from '../api'

export const useBranchStore = defineStore('branch', () => {
    const branches = ref([])
    const selectedBranchId = ref(localStorage.getItem('admin_branch_id') || null)

    const selectedBranch = computed(() =>
        branches.value.find(b => String(b.id) === String(selectedBranchId.value)) || null
    )

    async function fetchBranches() {
        const { data } = await getBranches()
        branches.value = data.data

        // Auto-select first branch if none selected
        if (!selectedBranchId.value && branches.value.length) {
            selectBranch(branches.value[0].id)
        }
    }

    function selectBranch(id) {
        selectedBranchId.value = String(id)
        localStorage.setItem('admin_branch_id', String(id))
    }

    return { branches, selectedBranchId, selectedBranch, fetchBranches, selectBranch }
})
