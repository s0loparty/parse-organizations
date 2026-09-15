<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});
</script>

<template>
  <div
    data-slot="card-footer"
    :class="
      cn(
        'flex items-center rounded-b-xl border-t bg-muted/50 p-4 group-data-[size=sm]/card:p-3',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
