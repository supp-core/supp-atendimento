<template>
  <img
    :src="logoUrl"
    :width="width"
    :height="height"
    alt="SUPP"
    style="object-fit: contain; display: block;"
  />
</template>

<script setup>
import logoUrl from '@/assets/logo-supp.png'

defineProps({
  width:  { type: [Number, String], default: 96 },
  height: { type: [Number, String], default: 96 }
})
</script>
