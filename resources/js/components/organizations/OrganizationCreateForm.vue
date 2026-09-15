<script setup>
import { LoaderCircle, X } from '@lucide/vue';

import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps({
  open: { type: Boolean, default: false },
  url: { type: String, required: true },
  isPending: { type: Boolean, default: false },
  isError: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
});

defineEmits(['update:url', 'close', 'submit']);
</script>

<template>
  <Transition
    enter-active-class="transition-[transform,opacity] duration-200 ease-out"
    enter-from-class="translate-y-2 opacity-0"
    leave-active-class="transition-[transform,opacity] duration-150 ease-in"
    leave-to-class="translate-y-1 opacity-0"
  >
    <Card v-if="open" class="shadow-sm">
      <CardHeader class="relative pr-14">
        <CardTitle>Добавить организацию</CardTitle>
        <CardDescription>
          Вставьте полную или короткую ссылку на организацию.
        </CardDescription>

        <Button
          variant="ghost"
          size="icon"
          class="absolute top-3 right-3 size-10"
          :disabled="isPending"
          aria-label="Закрыть форму"
          @click="$emit('close')"
        >
          <X />
        </Button>
      </CardHeader>

      <CardContent>
        <form
          class="flex flex-col gap-3 sm:flex-row sm:items-start"
          @submit.prevent="$emit('submit')"
        >
          <div class="grid min-w-0 flex-1 gap-2">
            <Label for="organization-url">Ссылка на организацию</Label>
            <Input
              id="organization-url"
              :model-value="url"
              name="url"
              type="url"
              placeholder="https://yandex.ru/maps/..."
              autocomplete="url"
              :aria-invalid="isError"
              required
              class="h-10"
              @update:model-value="$emit('update:url', $event)"
            />
            <p v-if="isError" role="alert" class="text-sm text-destructive">
              {{ errorMessage }}
            </p>
          </div>

          <Button type="submit" class="h-10 sm:mt-6" :disabled="isPending">
            <LoaderCircle
              v-if="isPending"
              class="animate-spin"
              data-icon="inline-start"
            />
            {{ isPending ? 'Добавляем…' : 'Добавить' }}
          </Button>
        </form>
      </CardContent>
    </Card>
  </Transition>
</template>
