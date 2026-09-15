<script setup>
import { RouterLink } from 'vue-router';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

defineProps({
  organization: { type: Object, required: true },
});

const statusDetails = {
  pending: {
    label: 'Обновляется',
    badgeClass: 'bg-amber-50 text-amber-700',
    dotClass: 'bg-amber-500',
  },
  valid: {
    label: 'Готово',
    badgeClass: 'bg-emerald-50 text-emerald-700',
    dotClass: 'bg-emerald-500',
  },
  invalid: {
    label: 'Ошибка',
    badgeClass: 'bg-red-50 text-red-700',
    dotClass: 'bg-red-500',
  },
};

function getStatusDetails(status) {
  return statusDetails[status] ?? statusDetails.pending;
}

function formatNumber(value) {
  if (value === null || value === undefined) {
    return '—';
  }

  return new Intl.NumberFormat('ru-RU').format(value);
}

function formatRating(value) {
  if (value === null || value === undefined) {
    return '—';
  }

  return Number(value).toFixed(1);
}
</script>

<template>
  <RouterLink
    :to="{
      name: 'organizations.show',
      params: { id: organization.id },
    }"
  >
    <Card
      class="shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
      <CardHeader class="block min-w-0">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <CardTitle class="truncate">
              {{ organization.name || 'Название загружается…' }}
            </CardTitle>
            <CardDescription class="mt-1 flex min-w-0 items-center gap-2">
              <span
                class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700"
              >
                {{
                  organization.source === 'yandex'
                    ? 'Яндекс'
                    : organization.source
                }}
              </span>
              <span class="truncate font-mono text-xs">
                {{ organization.external_id }}
              </span>
            </CardDescription>
          </div>

          <span
            class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2 py-1 text-xs font-medium"
            :class="getStatusDetails(organization.status).badgeClass"
          >
            <span
              class="size-1.5 rounded-full"
              :class="getStatusDetails(organization.status).dotClass"
            ></span>
            {{ getStatusDetails(organization.status).label }}
          </span>
        </div>
      </CardHeader>

      <CardContent class="grid grid-cols-3 divide-x divide-border">
        <div class="grid gap-1 pr-4">
          <span class="text-xl font-semibold tabular-nums">
            {{ formatRating(organization.rating) }}
          </span>
          <span class="text-xs text-muted-foreground">рейтинг</span>
        </div>
        <div class="grid gap-1 px-4">
          <span class="text-xl font-semibold tabular-nums">
            {{ formatNumber(organization.ratings_count) }}
          </span>
          <span class="text-xs text-muted-foreground">оценок</span>
        </div>
        <div class="grid gap-1 pl-4">
          <span class="text-xl font-semibold tabular-nums">
            {{ formatNumber(organization.reviews_count) }}
          </span>
          <span class="text-xs text-muted-foreground">отзывов</span>
        </div>
      </CardContent>
    </Card>
  </RouterLink>
</template>
