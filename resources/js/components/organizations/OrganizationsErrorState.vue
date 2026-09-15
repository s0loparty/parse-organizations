<script setup>
import { CircleAlert, RefreshCw } from '@lucide/vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

defineProps({
  message: { type: String, required: true },
});

defineEmits(['retry']);
</script>

<template>
  <Card class="shadow-sm">
    <CardContent class="flex flex-col items-center gap-4 py-12 text-center">
      <div
        class="flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600"
      >
        <CircleAlert class="size-6" />
      </div>
      <div class="grid gap-1">
        <h2 class="font-semibold">Не удалось загрузить список</h2>
        <p class="text-sm text-muted-foreground">{{ message }}</p>
      </div>
      <Button variant="outline" class="h-10" @click="$emit('retry')">
        <RefreshCw data-icon="inline-start" />
        Попробовать снова
      </Button>
    </CardContent>
  </Card>
</template>
