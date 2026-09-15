<script setup>
import { Plus } from '@lucide/vue';

import OrganizationCreateForm from '@/components/organizations/OrganizationCreateForm.vue';
import OrganizationsEmptyState from '@/components/organizations/OrganizationsEmptyState.vue';
import OrganizationsErrorState from '@/components/organizations/OrganizationsErrorState.vue';
import OrganizationsList from '@/components/organizations/OrganizationsList.vue';
import OrganizationsSkeleton from '@/components/organizations/OrganizationsSkeleton.vue';
import { Button } from '@/components/ui/button';
import { useOrganizations } from '@/composables/useOrganizations';

const {
  closeCreateForm,
  createErrorMessage,
  createOrganization,
  isCreateError,
  isCreateFormOpen,
  isCreatePending,
  isListError,
  isListFetching,
  isListPending,
  listErrorMessage,
  openCreateForm,
  organizations,
  organizationUrl,
  retryOrganizations,
} = useOrganizations();
</script>

<template>
  <main class="min-h-dvh bg-gray-100 text-foreground antialiased">
    <div
      class="mx-auto flex max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 sm:py-10"
    >
      <header class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-3xl font-semibold tracking-tight text-balance">
            Организации
          </h1>
          <p class="mt-1 text-sm text-pretty text-muted-foreground">
            Организации, информация и отзывы из внешних сервисов
          </p>
        </div>

        <Button
          class="h-10 shrink-0 transition-transform active:scale-[0.96]"
          @click="openCreateForm"
        >
          <Plus data-icon="inline-start" />
          <span class="hidden sm:inline">Добавить</span>
        </Button>
      </header>

      <OrganizationCreateForm
        v-model:url="organizationUrl"
        :open="isCreateFormOpen"
        :is-pending="isCreatePending"
        :is-error="isCreateError"
        :error-message="createErrorMessage"
        @close="closeCreateForm"
        @submit="createOrganization"
      />

      <OrganizationsSkeleton v-if="isListPending" />

      <OrganizationsErrorState
        v-else-if="isListError"
        :message="listErrorMessage"
        @retry="retryOrganizations"
      />

      <OrganizationsEmptyState
        v-else-if="organizations.length === 0"
        @add="openCreateForm"
      />

      <OrganizationsList
        v-else
        :organizations="organizations"
        :is-fetching="isListFetching"
      />
    </div>
  </main>
</template>
