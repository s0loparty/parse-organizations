<script setup>
import { LoaderCircle } from '@lucide/vue';
import { Badge } from '../ui/badge';

defineProps({
  organization: { type: Object, required: true },
  isFetching: { type: Boolean, default: false },
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
  <section class="rounded-3xl bg-white p-6 shadow-sm sm:p-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="w-full min-w-0">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <h1
            class="text-2xl font-semibold tracking-tight text-balance sm:text-3xl"
          >
            {{ organization.name || 'Название загружается…' }}
          </h1>

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

              <LoaderCircle v-if="isFetching" class="size-3.5 animate-spin" />
            </Badge>
          </div>
          <!-- <span
            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
            :class="getStatusDetails(organization.status).badgeClass"
          >
            <span
              class="size-1.5 rounded-full"
              :class="getStatusDetails(organization.status).dotClass"
            ></span>
            {{ getStatusDetails(organization.status).label }}
          </span> -->

          <!-- <div
            class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground"
          >
            <span
              class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700"
            >
              {{
                organization.source === 'yandex'
                  ? 'Яндекс'
                  : organization.source
              }}
            </span>
            <span v-if="isFetching" class="inline-flex items-center gap-1.5">
              <LoaderCircle class="size-3.5 animate-spin" />
              Обновляем данные
            </span>
          </div> -->
        </div>
      </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
      <div class="rounded-2xl bg-emerald-50 p-5">
        <div
          class="text-xs font-medium tracking-wide text-emerald-700 uppercase"
        >
          Рейтинг
        </div>
        <div class="mt-1 text-3xl font-semibold text-emerald-700 tabular-nums">
          {{ formatRating(organization.rating) }}
        </div>
      </div>
      <div class="rounded-2xl bg-muted p-5">
        <div
          class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
        >
          Оценок
        </div>
        <div class="mt-1 text-3xl font-semibold tabular-nums">
          {{ formatNumber(organization.ratings_count) }}
        </div>
      </div>
      <div class="rounded-2xl bg-muted p-5">
        <div
          class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
        >
          Отзывов
        </div>
        <div class="mt-1 text-3xl font-semibold tabular-nums">
          {{ formatNumber(organization.reviews_count) }}
        </div>
      </div>
    </div>
  </section>
</template>
