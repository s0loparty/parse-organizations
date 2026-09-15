<script setup>
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { ArrowLeft, CircleAlert } from '@lucide/vue';

import OrganizationReviews from '@/components/organizations/OrganizationReviews.vue';
import OrganizationSummary from '@/components/organizations/OrganizationSummary.vue';
import OrganizationSummarySkeleton from '@/components/organizations/OrganizationSummarySkeleton.vue';
import { Button } from '@/components/ui/button';
import { useOrganization } from '@/composables/useOrganization';

const route = useRoute();
const organizationId = computed(() => route.params.id);

const {
  organization,
  organizationErrorMessage,
  isOrganizationError,
  isOrganizationFetching,
  isOrganizationPending,
  isReviewsError,
  isReviewsFetching,
  isReviewsPending,
  reviews,
  reviewsErrorMessage,
  reviewsMeta,
  reviewsPage,
  retryOrganization,
  retryReviews,
} = useOrganization(organizationId);
</script>

<template>
  <main class="min-h-dvh bg-gray-100 text-foreground antialiased">
    <div
      class="mx-auto flex max-w-4xl flex-col gap-6 px-4 py-8 sm:px-6 sm:py-10"
    >
      <RouterLink
        :to="{ name: 'organizations.index' }"
        class="inline-flex min-h-10 w-fit items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
      >
        <ArrowLeft class="size-4" />
        Все организации
      </RouterLink>

      <OrganizationSummarySkeleton v-if="isOrganizationPending" />

      <div
        v-else-if="isOrganizationError"
        class="flex flex-col items-center gap-4 rounded-3xl bg-white px-6 py-16 text-center shadow-sm"
      >
        <div
          class="flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600"
        >
          <CircleAlert class="size-6" />
        </div>
        <div class="grid gap-1">
          <h1 class="text-lg font-semibold">Не удалось открыть организацию</h1>
          <p class="text-sm text-muted-foreground">
            {{ organizationErrorMessage }}
          </p>
        </div>
        <Button variant="outline" class="h-10" @click="retryOrganization">
          Попробовать снова
        </Button>
      </div>

      <template v-else>
        <OrganizationSummary
          :organization="organization"
          :is-fetching="isOrganizationFetching"
        />

        <OrganizationReviews
          v-model:page="reviewsPage"
          :reviews="reviews"
          :meta="reviewsMeta"
          :is-pending="isReviewsPending"
          :is-fetching="isReviewsFetching"
          :is-error="isReviewsError"
          :error-message="reviewsErrorMessage"
          @retry="retryReviews"
        />
      </template>
    </div>
  </main>
</template>
