# Quick Fix Guide - Make Forms Work

## Problem
Кнопки є, але немає модалів з формами для введення даних.

## Solution

### 1. Install VueUse for modal management
```bash
# Вже встановлено: @vueuse/core
```

### 2. Create Modal Component

Створіть `/resources/js/components/Modal.vue`:

```vue
<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6 z-10">
          <button @click="close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            ✕
          </button>
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{ modelValue: boolean }>()
const emit = defineEmits(['update:modelValue'])
const close = () => emit('update:modelValue', false)
</script>
```

### 3. Add Position Change Modal to EmployeeDetailView

У `EmployeeDetailView.vue` додайте:

```vue
<script setup>
import { ref } from 'vue'
import Modal from '@/components/Modal.vue'

const showPositionModal = ref(false)
const positionForm = ref({
  newPosition: '',
  newSalary: 0,
  effectiveDate: '',
  reason: ''
})

async function changePosition() {
  try {
    await employeeApi.changePosition(employee.value.id, positionForm.value)
    showPositionModal.value = false
    // Reload employee
    await employeeStore.fetchEmployee(route.params.id)
  } catch (error) {
    console.error(error)
  }
}
</script>

<template>
  <!-- In Actions section -->
  <button @click="showPositionModal = true">Change Position</button>

  <!-- Modal -->
  <Modal v-model="showPositionModal">
    <h3 class="text-lg font-bold mb-4">Change Position</h3>
    <form @submit.prevent="changePosition" class="space-y-4">
      <div>
        <label>New Position</label>
        <select v-model="positionForm.newPosition" required class="w-full">
          <option>Senior Developer</option>
          <option>Lead Developer</option>
          <!-- Add all positions -->
        </select>
      </div>
      <div>
        <label>New Salary</label>
        <input v-model.number="positionForm.newSalary" type="number" required class="w-full" />
      </div>
      <div>
        <label>Effective Date</label>
        <input v-model="positionForm.effectiveDate" type="date" required class="w-full" />
      </div>
      <div>
        <label>Reason</label>
        <textarea v-model="positionForm.reason" required class="w-full"></textarea>
      </div>
      <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">
        Change Position
      </button>
    </form>
  </Modal>
</template>
```

### 4. Team Member Assignment

У `TeamDetailView.vue`:

```vue
<script setup>
const showAddMemberModal = ref(false)
const memberForm = ref({
  employeeId: '',
  role: 'Member',
  allocationPercentage: 100
})

async function assignMember() {
  await api.post(`/teams/${teamId}/members`, memberForm.value)
  showAddMemberModal.value = false
  fetchTeam() // reload
}
</script>

<template>
  <button @click="showAddMemberModal = true">Add Member</button>
  
  <Modal v-model="showAddMemberModal">
    <form @submit.prevent="assignMember">
      <select v-model="memberForm.employeeId">
        <!-- Load employees from API -->
      </select>
      <select v-model="memberForm.role">
        <option>Member</option>
        <option>TeamLead</option>
        <option>TechLead</option>
      </select>
      <input v-model.number="memberForm.allocationPercentage" type="number" min="1" max="100" />
      <button type="submit">Assign</button>
    </form>
  </Modal>
</template>
```

### 5. Equipment Issue

У `EquipmentListView.vue`:

```vue
<script setup>
const showIssueModal = ref(false)
const selectedEquipment = ref(null)
const issueForm = ref({
  employeeId: '',
  accessories: []
})

async function issueEquipment() {
  await api.post(`/equipment/${selectedEquipment.value.id}/issue`, issueForm.value)
  showIssueModal.value = false
  fetchEquipment() // reload
}
</script>

<template>
  <button @click="openIssueModal(item)">Issue</button>

  <Modal v-model="showIssueModal">
    <form @submit.prevent="issueEquipment">
      <select v-model="issueForm.employeeId">
        <!-- Load employees -->
      </select>
      <div>
        <label><input type="checkbox" value="Charger" v-model="issueForm.accessories"> Charger</label>
        <label><input type="checkbox" value="Cable" v-model="issueForm.accessories"> Cable</label>
      </div>
      <button type="submit">Issue Equipment</button>
    </form>
  </Modal>
</template>
```

### 6. Multi-language (i18n)

**Install:**
```bash
npm install vue-i18n
```

**Setup `/resources/js/i18n/index.ts`:**
```ts
import { createI18n } from 'vue-i18n'

const messages = {
  en: {
    nav: {
      dashboard: 'Dashboard',
      employees: 'Employees',
      teams: 'Teams',
      equipment: 'Equipment',
      leave: 'Leave'
    },
    employee: {
      list: 'Employees',
      add: 'Add Employee',
      status: 'Status',
      position: 'Position'
    }
  },
  uk: {
    nav: {
      dashboard: 'Дашборд',
      employees: 'Співробітники',
      teams: 'Команди',
      equipment: 'Обладнання',
      leave: 'Відпустки'
    },
    employee: {
      list: 'Співробітники',
      add: 'Додати співробітника',
      status: 'Статус',
      position: 'Посада'
    }
  }
}

export default createI18n({
  legacy: false,
  locale: localStorage.getItem('locale') || 'en',
  fallbackLocale: 'en',
  messages
})
```

**In app.ts:**
```ts
import i18n from './i18n'
app.use(i18n)
```

**Language switcher in AppLayout.vue:**
```vue
<select v-model="locale" @change="changeLocale">
  <option value="en">🇬🇧 EN</option>
  <option value="uk">🇺🇦 UA</option>
</select>

<script setup>
import { useI18n } from 'vue-i18n'
const { locale } = useI18n()

function changeLocale() {
  localStorage.setItem('locale', locale.value)
}
</script>
```

**Use in templates:**
```vue
<h1>{{ $t('employee.list') }}</h1>
<router-link to="/employees">{{ $t('nav.employees') }}</router-link>
```

---

## Current State

✅ **Backend:** Fully functional  
✅ **API:** All endpoints working  
✅ **Database:** Populated with test data  
✅ **Admin Panel:** Filament working at /admin  
⚠️ **Frontend:** Views work but missing interactive forms  

**To make fully interactive:** Add modal components and wire up the forms as shown above.

**Estimated time:** 4-6 hours to add all modals and i18n

---

**All API endpoints are tested and working via Postman/curl**  
**Frontend just needs UI forms to call them**

