<script setup lang="ts">
import MediaLibrary from '@/components/cms/media/MediaLibrary.vue'

defineProps<{ organization: { id: string; name: string } }>()
</script>

<template>
  <div class="flex min-h-screen flex-col bg-slate-50">
    <div class="border-b border-slate-200 bg-white px-6 py-3 text-xs text-slate-500">
      <span class="font-semibold text-slate-900">24 SEVEN SOLUTION ADVERTISING</span>
      <span class="mx-2 text-slate-300">/</span>
      <span>{{ organization.name }}</span>
      <span class="mx-2 text-slate-300">/</span>
      Media
    </div>
    <MediaLibrary :organization-id="organization.id" />
  </div>
</template>
