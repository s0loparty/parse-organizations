<script setup>
import { computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useMutation, useQueryClient } from '@tanstack/vue-query';

import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { setAccessToken } from '@/lib/auth-token';
import { currentUserQueryKey, login } from '@/queries/auth';

const router = useRouter();
const queryClient = useQueryClient();

const credentials = reactive({
  email: '',
  password: '',
});

const { error, isError, isPending, mutate } = useMutation({
  mutationFn: login,
  onSuccess: async (data) => {
    setAccessToken(data.access_token);
    queryClient.setQueryData(currentUserQueryKey, data.user);

    await router.replace({ name: 'organizations.index' });
  },
});

const errorMessage = computed(() => {
  const responseData = error.value?.response?.data;

  return (
    responseData?.errors?.email?.[0] ??
    responseData?.message ??
    'Не удалось войти. Попробуйте ещё раз.'
  );
});

function handleLogin() {
  mutate({ ...credentials });
}
</script>

<template>
  <main class="min-h-dvh w-full bg-gray-100">
    <div class="mx-auto flex w-full max-w-sm flex-col gap-6 pt-[20dvh]">
      <form @submit.prevent="handleLogin">
        <Card>
          <CardHeader>
            <CardTitle>Войти</CardTitle>
            <CardDescription> Войдите в свой аккаунт </CardDescription>
          </CardHeader>
          <CardContent class="grid gap-6">
            <div class="grid gap-3">
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="credentials.email"
                name="email"
                type="email"
                autocomplete="email"
                :aria-invalid="isError"
                required
              />
            </div>
            <div class="grid gap-3">
              <Label for="password">Пароль</Label>
              <Input
                id="password"
                v-model="credentials.password"
                name="password"
                type="password"
                autocomplete="current-password"
                :aria-invalid="isError"
                required
              />
            </div>
            <p v-if="isError" role="alert" class="text-sm text-destructive">
              {{ errorMessage }}
            </p>
          </CardContent>
          <CardFooter>
            <Button type="submit" :disabled="isPending">
              {{ isPending ? 'Входим…' : 'Войти' }}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </main>
</template>
