<template>
  <div class="animate-pulse rounded-3xl bg-white p-6 shadow-sm sm:p-8">
    <div class="h-8 w-2/3 rounded-lg bg-muted"></div>
    <div class="mt-4 h-5 w-1/3 rounded-lg bg-muted"></div>
    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div
        v-for="item in 3"
        :key="item"
        class="h-28 rounded-2xl bg-muted"
      ></div>
    </div>
  </div>
</template>
