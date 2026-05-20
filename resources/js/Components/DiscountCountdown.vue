<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps<{
  expiration: string | null
}>()

const emit = defineEmits(['expired'])

const timeLeft = ref<{
  hours: number
  minutes: number
  seconds: number
} | null>(null)

let timer: number | null = null

function calculateTimeLeft() {
  if (!props.expiration) return
  
  const expirationDate = new Date(props.expiration).getTime()
  const now = new Date().getTime()
  const distance = expirationDate - now

  if (distance < 0) {
    timeLeft.value = null
    if (timer) clearInterval(timer)
    emit('expired')
    return
  }

  timeLeft.value = {
    hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
    minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
    seconds: Math.floor((distance % (1000 * 60)) / 1000)
  }
}

onMounted(() => {
  calculateTimeLeft()
  timer = window.setInterval(calculateTimeLeft, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

const formattedTime = computed(() => {
  if (!timeLeft.value) return ''
  const h = String(timeLeft.value.hours).padStart(2, '0')
  const m = String(timeLeft.value.minutes).padStart(2, '0')
  const s = String(timeLeft.value.seconds).padStart(2, '0')
  return `${h}:${m}:${s}`
})
</script>

<template>
  <div v-if="timeLeft" class="flex items-center gap-1.5 text-red-600 font-bold animate-pulse">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span class="text-[10px] uppercase tracking-wider">Ends in: {{ formattedTime }}</span>
  </div>
</template>
