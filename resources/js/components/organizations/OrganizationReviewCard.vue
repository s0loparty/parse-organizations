<script setup>
import { Star } from '@lucide/vue';

const props = defineProps({
  review: { type: Object, required: true },
});

function formatNumber(value) {
  return new Intl.NumberFormat('ru-RU').format(value ?? 0);
}

function formatDate(value) {
  if (!value) {
    return 'Дата не указана';
  }

  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(new Date(value));
}

function getAuthorName() {
  return props.review.author_name || 'Аноним';
}

function getAuthorInitial() {
  return getAuthorName().trim().charAt(0).toUpperCase() || '?';
}

function getAvatarUrl(url) {
  return url?.replace('{size}', 'islands-68');
}

function hideBrokenImage(event) {
  event.currentTarget.hidden = true;
}
</script>

<template>
  <article class="rounded-2xl bg-white p-5 shadow-sm">
    <div class="flex items-start gap-4">
      <div
        class="relative flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted text-sm font-medium text-muted-foreground"
      >
        {{ getAuthorInitial() }}
        <img
          v-if="review.author_avatar_url"
          :src="getAvatarUrl(review.author_avatar_url)"
          :alt="getAuthorName()"
          class="absolute inset-0 size-full object-cover outline -outline-offset-1 outline-black/10"
          loading="lazy"
          @error="hideBrokenImage"
        />
      </div>

      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div>
            <h3 class="text-sm font-semibold">{{ getAuthorName() }}</h3>
            <time
              :datetime="review.source_updated_at"
              class="text-xs text-muted-foreground"
            >
              {{ formatDate(review.source_updated_at) }}
            </time>
          </div>

          <div
            class="flex items-center gap-0.5"
            :aria-label="`Оценка ${review.rating} из 5`"
          >
            <Star
              v-for="star in 5"
              :key="star"
              class="size-4 text-amber-500"
              :class="{ 'fill-current': star <= review.rating }"
            />
          </div>
        </div>

        <p
          class="mt-3 text-sm leading-6 text-pretty whitespace-pre-line text-neutral-700"
        >
          {{ review.text || 'Пользователь оставил оценку без текста.' }}
        </p>

        <div
          class="mt-4 flex flex-wrap items-center gap-3 text-xs text-muted-foreground tabular-nums"
        >
          <span class="rounded-full bg-muted px-3 py-1.5">
            👍 {{ formatNumber(review.likes_count) }}
          </span>
          <span class="rounded-full bg-muted px-3 py-1.5">
            👎 {{ formatNumber(review.dislikes_count) }}
          </span>
        </div>
      </div>
    </div>
  </article>
</template>
