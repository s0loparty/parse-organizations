<script setup>
import {
  ChevronLeft,
  ChevronRight,
  CircleAlert,
  MessageSquareText,
} from '@lucide/vue';

import OrganizationReviewCard from '@/components/organizations/OrganizationReviewCard.vue';
import { Button } from '@/components/ui/button';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';

defineProps({
  reviews: { type: Array, default: () => [] },
  meta: { type: Object, default: null },
  page: { type: Number, required: true },
  isPending: { type: Boolean, default: false },
  isFetching: { type: Boolean, default: false },
  isError: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
});

defineEmits(['update:page', 'retry']);

const reviewsPerPage = 5;

function formatNumber(value) {
  return new Intl.NumberFormat('ru-RU').format(value ?? 0);
}
</script>

<template>
  <section class="grid gap-4" aria-labelledby="reviews-title">
    <div class="flex items-center justify-between gap-3">
      <h2 id="reviews-title" class="text-xl font-semibold">Отзывы</h2>
      <span v-if="meta" class="text-sm text-muted-foreground tabular-nums">
        {{ formatNumber(meta.total) }} всего
      </span>
    </div>

    <div v-if="isPending" class="grid gap-3">
      <article
        v-for="item in 3"
        :key="item"
        class="animate-pulse rounded-2xl bg-white p-5 shadow-sm"
      >
        <div class="flex gap-4">
          <div class="size-11 shrink-0 rounded-full bg-muted"></div>
          <div class="grid flex-1 gap-3">
            <div class="h-4 w-1/3 rounded bg-muted"></div>
            <div class="h-16 rounded bg-muted"></div>
          </div>
        </div>
      </article>
    </div>

    <div
      v-else-if="isError"
      class="flex flex-col items-center gap-4 rounded-2xl bg-white px-6 py-12 text-center shadow-sm"
    >
      <CircleAlert class="size-8 text-red-600" />
      <p class="text-sm text-muted-foreground">
        {{ errorMessage }}
      </p>
      <Button variant="outline" class="h-10" @click="$emit('retry')">
        Попробовать снова
      </Button>
    </div>

    <div
      v-else-if="reviews.length === 0"
      class="flex flex-col items-center gap-3 rounded-2xl bg-white px-6 py-14 text-center shadow-sm"
    >
      <div
        class="flex size-12 items-center justify-center rounded-2xl bg-muted text-muted-foreground"
      >
        <MessageSquareText class="size-6" />
      </div>
      <div class="grid gap-1">
        <h3 class="font-semibold">Отзывов пока нет</h3>
        <p class="text-sm text-muted-foreground">
          Организация не имеет отзывов, или синхронизация отзывов ещё не
          завершилась.
        </p>
      </div>
    </div>

    <div
      v-else
      class="grid gap-3 transition-opacity duration-150"
      :class="{ 'opacity-60': isFetching }"
    >
      <OrganizationReviewCard
        v-for="review in reviews"
        :key="review.id"
        :review="review"
      />
    </div>

    <Pagination
      v-if="meta?.last_page > 1"
      :page="page"
      :items-per-page="meta.per_page ?? reviewsPerPage"
      :total="meta.total"
      :sibling-count="1"
      show-edges
      class="pt-4"
      @update:page="$emit('update:page', $event)"
    >
      <PaginationContent v-slot="{ items }">
        <PaginationPrevious>
          <ChevronLeft data-icon="inline-start" />
          <span class="hidden sm:inline">Назад</span>
        </PaginationPrevious>

        <template v-for="(item, index) in items" :key="index">
          <PaginationItem
            v-if="item.type === 'page'"
            :value="item.value"
            :is-active="item.value === page"
          >
            {{ item.value }}
          </PaginationItem>
          <PaginationEllipsis v-else :index="index" />
        </template>

        <PaginationNext>
          <span class="hidden sm:inline">Вперёд</span>
          <ChevronRight data-icon="inline-end" />
        </PaginationNext>
      </PaginationContent>
    </Pagination>
  </section>
</template>
