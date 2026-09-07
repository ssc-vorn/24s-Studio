<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { FilePlus2, FolderOpen, Image, Pencil, Plus, Settings2, UsersRound } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

type Organization = { id: string; name: string }
type Page = {
  id: string
  title: string
  slug: string
  status: string
  updated_at: string | null
  latest_version: { id: string; number: number; status: string } | null
}

const props = defineProps<{
  organizations: Organization[]
  selectedOrganizationId: string | null
  pages: Page[]
  permissions: { viewPages: boolean; createPages: boolean; manageMembers: boolean }
}>()

const showCreatePage = ref(false)
const title = ref('')
const slug = ref('')
const isSubmitting = ref(false)
const errors = ref<Record<string, string>>({})

const selectedOrganization = computed(() => props.organizations.find((item) => item.id === props.selectedOrganizationId) ?? null)
const pageUrl = (page: Page) => page.latest_version && selectedOrganization.value
  ? `/admin/cms/organizations/${selectedOrganization.value.id}/pages/${page.id}/builder/${page.latest_version.id}`
  : null
const mediaUrl = computed(() => selectedOrganization.value ? `/admin/cms/organizations/${selectedOrganization.value.id}/media` : null)
const teamUrl = computed(() => selectedOrganization.value ? `/admin/cms/organizations/${selectedOrganization.value.id}/team` : null)
const formattedDate = (date: string | null) => date
  ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(date))
  : 'Not edited yet'
const statusClass = (status: string) => ({
  draft: 'bg-amber-50 text-amber-800 ring-amber-600/20',
  review: 'bg-blue-50 text-blue-800 ring-blue-700/20',
  approved: 'bg-violet-50 text-violet-800 ring-violet-700/20',
  published: 'bg-emerald-50 text-emerald-800 ring-emerald-700/20',
}[status] ?? 'bg-slate-100 text-slate-700 ring-slate-600/20')

const resetCreateForm = () => {
  showCreatePage.value = false
  title.value = ''
  slug.value = ''
  errors.value = {}
}
const updateSlug = () => {
  slug.value = title.value.toLowerCase().trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
}
const selectOrganization = (event: Event) => {
  const organizationId = (event.target as HTMLSelectElement).value
  router.get('/dashboard', { organization: organizationId }, { preserveState: false, preserveScroll: true })
}
const createPage = () => {
  if (!selectedOrganization.value || isSubmitting.value) return
  isSubmitting.value = true
  errors.value = {}
  router.post(`/admin/cms/organizations/${selectedOrganization.value.id}/pages`, {
    title: title.value,
    slug: slug.value,
    template: 'default',
  }, {
    preserveScroll: true,
    onError: (formErrors) => { errors.value = formErrors },
    onFinish: () => { isSubmitting.value = false },
  })
}
</script>

<template>
  <Head title="CMS" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-sm font-medium text-slate-500">Content management</p>
          <h1 class="text-2xl font-semibold text-slate-950">Pages</h1>
        </div>
        <div v-if="organizations.length" class="flex items-center gap-3">
          <label for="organization" class="text-sm font-medium text-slate-700">Organization</label>
          <select id="organization" :value="selectedOrganizationId ?? ''" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="selectOrganization">
            <option v-for="organization in organizations" :key="organization.id" :value="organization.id">{{ organization.name }}</option>
          </select>
        </div>
      </div>
    </template>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <section v-if="!organizations.length" class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm">
        <Settings2 class="mx-auto h-10 w-10 text-slate-400" aria-hidden="true" />
        <h2 class="mt-4 text-lg font-semibold text-slate-900">No organization is available</h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Ask an administrator to add you to an organization before you start managing content.</p>
      </section>

      <template v-else>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <p class="text-sm text-slate-600">Manage drafts, reviews, and published pages for {{ selectedOrganization?.name }}.</p>
          <div class="flex gap-3">
            <Link v-if="permissions.manageMembers && teamUrl" :href="teamUrl" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
              <UsersRound class="h-4 w-4" aria-hidden="true" /> Team access
            </Link>
            <Link v-if="mediaUrl" :href="mediaUrl" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
              <Image class="h-4 w-4" aria-hidden="true" /> Media library
            </Link>
            <button v-if="permissions.createPages" type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" @click="showCreatePage = true">
              <Plus class="h-4 w-4" aria-hidden="true" /> New page
            </button>
          </div>
        </div>

        <section v-if="!permissions.viewPages" class="rounded-xl border border-amber-200 bg-amber-50 px-6 py-5 text-amber-900">
          <h2 class="font-semibold">You do not have permission to view pages</h2>
          <p class="mt-1 text-sm">Ask an administrator for the <code>pages.view</code> permission.</p>
        </section>

        <section v-else-if="!pages.length" class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
          <FilePlus2 class="mx-auto h-10 w-10 text-indigo-500" aria-hidden="true" />
          <h2 class="mt-4 text-lg font-semibold text-slate-900">Create your first page</h2>
          <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Start with a draft, add sections in the builder, then submit it for review when it is ready.</p>
          <PrimaryButton v-if="permissions.createPages" class="mt-6" @click="showCreatePage = true">Create page</PrimaryButton>
        </section>

        <section v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left">
              <caption class="sr-only">Pages in {{ selectedOrganization?.name }}</caption>
              <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                <tr><th scope="col" class="px-6 py-3">Page</th><th scope="col" class="px-6 py-3">Status</th><th scope="col" class="px-6 py-3">Last edited</th><th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th></tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="page in pages" :key="page.id" class="hover:bg-slate-50">
                  <td class="px-6 py-4"><div class="font-medium text-slate-900">{{ page.title }}</div><div class="mt-0.5 text-sm text-slate-500">/{{ page.slug }}</div></td>
                  <td class="px-6 py-4"><span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', statusClass(page.latest_version?.status ?? page.status)]">{{ page.latest_version?.status ?? page.status }}</span></td>
                  <td class="px-6 py-4 text-sm text-slate-600">{{ formattedDate(page.updated_at) }}</td>
                  <td class="px-6 py-4 text-right"><Link v-if="pageUrl(page)" :href="pageUrl(page)!" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"><Pencil class="h-4 w-4" aria-hidden="true" /> Edit<span class="sr-only"> {{ page.title }}</span></Link><span v-else class="inline-flex items-center gap-2 text-sm text-slate-500"><FolderOpen class="h-4 w-4" aria-hidden="true" /> No draft</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </main>
  </AuthenticatedLayout>

  <Modal :show="showCreatePage" max-width="md" @close="resetCreateForm">
    <form class="p-6" @submit.prevent="createPage">
      <h2 class="text-lg font-semibold text-slate-900">Create a new page</h2>
      <p class="mt-1 text-sm text-slate-600">A draft version will be created and opened in the page builder.</p>
      <div class="mt-5"><label for="page-title" class="block text-sm font-medium text-slate-700">Page title</label><input id="page-title" v-model="title" type="text" required autofocus class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @input="updateSlug"><InputError class="mt-2" :message="errors.title" /></div>
      <div class="mt-4"><label for="page-slug" class="block text-sm font-medium text-slate-700">URL slug</label><input id="page-slug" v-model="slug" type="text" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><p class="mt-1 text-xs text-slate-500">Lowercase letters, numbers, and hyphens only.</p><InputError class="mt-2" :message="errors.slug" /></div>
      <InputError class="mt-4" :message="errors.organization" />
      <div class="mt-6 flex justify-end gap-3"><SecondaryButton type="button" @click="resetCreateForm">Cancel</SecondaryButton><PrimaryButton :disabled="isSubmitting">{{ isSubmitting ? 'Creating…' : 'Create and edit' }}</PrimaryButton></div>
    </form>
  </Modal>
</template>
