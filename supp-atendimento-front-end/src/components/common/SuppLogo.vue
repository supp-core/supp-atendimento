<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 120 120"
    xmlns="http://www.w3.org/2000/svg"
    role="img"
    aria-label="SUPP Atendimentos"
  >
    <defs>
      <linearGradient id="suppLogoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#7ec8e3" />
        <stop offset="60%" stop-color="#3aa9e0" />
        <stop offset="100%" stop-color="#1565c0" />
      </linearGradient>
    </defs>
    <path
      d="M 30 28 Q 60 18, 92 32"
      fill="none"
      stroke="url(#suppLogoGradient)"
      stroke-width="9"
      stroke-linecap="round"
    />
    <path
      d="M 28 58 Q 62 48, 92 68"
      fill="none"
      stroke="url(#suppLogoGradient)"
      stroke-width="9"
      stroke-linecap="round"
    />
    <path
      d="M 28 92 Q 60 102, 92 88"
      fill="none"
      stroke="url(#suppLogoGradient)"
      stroke-width="9"
      stroke-linecap="round"
    />
  </svg>
</template>

<script setup>
defineProps({
  width: { type: [Number, String], default: 96 },
  height: { type: [Number, String], default: 96 }
})
</script>
