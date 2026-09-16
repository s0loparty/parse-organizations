<script setup>
import { RouterLink } from 'vue-router';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Badge } from '../ui/badge';

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
      class="h-full shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
      <CardHeader class="block min-w-0">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <CardTitle class="sm:truncate">
              {{ organization.name || 'Название загружается…' }}
            </CardTitle>
          </div>

          <div class="flex gap-2">
            <Badge :class="getStatusDetails(organization.status).badgeClass">
              {{ getStatusDetails(organization.status).label }}
            </Badge>
            <Badge class="bg-red-50 text-red-700">
              {{
                organization.source === 'yandex'
                  ? 'Яндекс'
                  : organization.source
              }}
            </Badge>
          </div>
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
