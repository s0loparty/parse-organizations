<script setup>
import { Building2, Plus } from '@lucide/vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

defineEmits(['add']);
</script>

<template>
  <Card
    class="border-0 shadow-[0_1px_2px_rgb(0_0_0/0.04),0_8px_24px_rgb(0_0_0/0.06)]"
  >
    <CardContent
      class="flex flex-col items-center gap-5 px-6 py-16 text-center sm:py-20"
    >
      <div
        class="flex size-14 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-sm"
      >
        <Building2 class="size-7" />
      </div>
      <div class="grid max-w-md gap-2">
        <h2 class="text-xl font-semibold tracking-tight text-balance">
          Организаций пока нет
        </h2>
        <p class="text-sm leading-6 text-pretty text-muted-foreground">
          Добавьте ссылку на организацию. Мы загрузим основные данные и отзывы в
          фоне.
        </p>
      </div>
      <Button
        class="h-10 transition-transform active:scale-[0.96]"
        @click="$emit('add')"
      >
        <Plus data-icon="inline-start" />
        Добавить организацию
      </Button>
    </CardContent>
  </Card>
</template>
