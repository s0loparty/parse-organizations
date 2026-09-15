<script setup>
import { LoaderCircle } from '@lucide/vue';

import OrganizationCard from '@/components/organizations/OrganizationCard.vue';

defineProps({
  organizations: { type: Array, default: () => [] },
  isFetching: { type: Boolean, default: false },
});
</script>

<template>
  <section>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <OrganizationCard
        v-for="organization in organizations"
        :key="organization.id"
        :organization="organization"
      />
    </div>

    <p
      v-if="isFetching"
      class="mt-4 flex items-center justify-center gap-2 text-xs text-muted-foreground"
    >
      <LoaderCircle class="size-3.5 animate-spin" />
      Обновляем данные
    </p>
  </section>
</template>
