<script setup>
import { Card, CardContent, CardHeader } from '@/components/ui/card';
</script>

<template>
  <section>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <Card v-for="index in 4" :key="index" class="animate-pulse shadow-sm">
        <CardHeader class="gap-3">
          <div class="h-5 w-2/3 rounded-md bg-muted"></div>
          <div class="h-4 w-1/3 rounded-md bg-muted"></div>
        </CardHeader>
        <CardContent class="grid grid-cols-3 gap-4">
          <div v-for="item in 3" :key="item" class="grid gap-2">
            <div class="h-7 rounded-md bg-muted"></div>
            <div class="h-3 rounded-md bg-muted"></div>
          </div>
        </CardContent>
      </Card>
    </div>
  </section>
</template>
