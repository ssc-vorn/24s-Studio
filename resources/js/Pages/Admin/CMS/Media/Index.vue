<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import MediaLibrary from '@/components/cms/media/MediaLibrary.vue'

defineProps<{ organization: { id: string; name: string } }>()
</script>

<template>
  <Head title="Media Library" />
  <div class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
    <div class="border-b border-slate-200 bg-white px-6 py-3 text-xs text-slate-500">{{ organization.name }} · CMS</div>
    <MediaLibrary :organization-id="organization.id" />
  </div>
</template>
