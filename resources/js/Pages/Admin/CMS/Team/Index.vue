<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ArrowLeft, UserPlus, UsersRound } from 'lucide-vue-next'

type Member = { id: number; name: string; email: string; role: string; is_owner: boolean }

const props = defineProps<{
  organization: { id: string; name: string }
  members: Member[]
  roles: string[]
}>()

const inviteEmail = ref('')
const inviteRole = ref('editor')
const inviting = ref(false)
const error = ref('')
const formatRole = (role: string) => role.replace(/-/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())
const sortedMembers = computed(() => [...props.members].sort((a, b) => Number(b.is_owner) - Number(a.is_owner) || a.name.localeCompare(b.name)))
const updateRole = (member: Member, event: Event) => {
  const role = (event.target as HTMLSelectElement).value
  router.patch(`/admin/cms/organizations/${props.organization.id}/team/${member.id}`, { role }, { preserveScroll: true })
}
const removeMember = (member: Member) => {
  if (window.confirm(`Remove ${member.name} from ${props.organization.name}?`)) {
    router.delete(`/admin/cms/organizations/${props.organization.id}/team/${member.id}`, { preserveScroll: true })
  }
}
const addMember = () => {
  inviting.value = true
  error.value = ''
  router.post(`/admin/cms/organizations/${props.organization.id}/team`, { email: inviteEmail.value, role: inviteRole.value }, {
    preserveScroll: true,
    onError: (errors) => { error.value = errors.email ?? errors.role ?? 'Unable to add this member.' },
    onSuccess: () => { inviteEmail.value = '' },
    onFinish: () => { inviting.value = false },
  })
}
</script>

<template>
  <Head :title="`Team access · ${organization.name}`" />
  <main class="min-h-screen bg-slate-50 px-4 py-8 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
      <Link href="/dashboard" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900"><ArrowLeft class="h-4 w-4" aria-hidden="true" /> Back to CMS</Link>
      <div class="mt-6 flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm font-medium text-slate-500">{{ organization.name }}</p><h1 class="text-2xl font-semibold text-slate-950">Team access</h1><p class="mt-1 text-sm text-slate-600">Control who can create, review, approve, and publish content for this organization.</p></div><UsersRound class="h-8 w-8 text-indigo-600" aria-hidden="true" /></div>

      <section class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Add an existing user</h2><p class="mt-1 text-sm text-slate-600">The user must already have an account. Email invitations are the next step.</p>
        <form class="mt-5 flex flex-col gap-3 sm:flex-row" @submit.prevent="addMember"><label class="sr-only" for="member-email">Email address</label><input id="member-email" v-model="inviteEmail" required type="email" placeholder="name@example.com" class="min-w-0 flex-1 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><label class="sr-only" for="member-role">Role</label><select id="member-role" v-model="inviteRole" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option v-for="role in roles" :key="role" :value="role">{{ formatRole(role) }}</option></select><button type="submit" :disabled="inviting" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600 disabled:opacity-60"><UserPlus class="h-4 w-4" aria-hidden="true" /> {{ inviting ? 'Adding…' : 'Add member' }}</button></form>
        <p v-if="error" class="mt-3 text-sm text-red-700" role="alert">{{ error }}</p>
      </section>

      <section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-6 py-4"><h2 class="font-semibold text-slate-900">Members</h2></div><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left"><caption class="sr-only">Members with access to {{ organization.name }}</caption><thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600"><tr><th scope="col" class="px-6 py-3">Member</th><th scope="col" class="px-6 py-3">Role</th><th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th></tr></thead><tbody class="divide-y divide-slate-100"><tr v-for="member in sortedMembers" :key="member.id"><td class="px-6 py-4"><div class="font-medium text-slate-900">{{ member.name }}</div><div class="text-sm text-slate-500">{{ member.email }}</div></td><td class="px-6 py-4"><span v-if="member.is_owner" class="inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-800 ring-1 ring-inset ring-violet-600/20">Owner</span><select v-else :value="member.role" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :aria-label="`Role for ${member.name}`" @change="updateRole(member, $event)"><option v-if="member.role === 'legacy'" value="legacy" disabled>Legacy access — assign a role</option><option v-for="role in roles" :key="role" :value="role">{{ formatRole(role) }}</option></select></td><td class="px-6 py-4 text-right"><button v-if="!member.is_owner" type="button" class="text-sm font-semibold text-red-700 hover:text-red-900" @click="removeMember(member)">Remove<span class="sr-only"> {{ member.name }}</span></button></td></tr></tbody></table></div></section>
    </div>
  </main>
</template>
