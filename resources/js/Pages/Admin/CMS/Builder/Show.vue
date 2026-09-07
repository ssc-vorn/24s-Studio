<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import BuilderShell from '@/components/cms/builder/BuilderShell.vue'
import type { PageSection } from '@/types/cms'

defineProps<{
  organization: { id: string; name: string }
  page: { id: string; title: string; slug: string }
  version: { id: string; version: number; revision: number; status: string; sections: PageSection[] }
}>()
</script>

<template>
  <Head :title="`Builder · ${page.title}`" />
  <BuilderShell :organization="organization" :page="page" :version="version" />
</template>
