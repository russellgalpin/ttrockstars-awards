<script setup>
import { computed } from 'vue';

const props = defineProps({
    seconds: {
        type: Number,
        required: true,
    },
    totalSeconds: {
        type: Number,
        default: 360,
    },
});

const formattedTime = computed(() => {
    const mins = Math.floor(props.seconds / 60);
    const secs = props.seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
});

const percentage = computed(() => {
    return (props.seconds / props.totalSeconds) * 100;
});

const colorClass = computed(() => {
    if (props.seconds <= 30) return 'text-red-600';
    if (props.seconds <= 60) return 'text-orange-500';
    return 'text-green-600';
});

const progressColorClass = computed(() => {
    if (props.seconds <= 30) return 'bg-red-500';
    if (props.seconds <= 60) return 'bg-orange-500';
    return 'bg-green-500';
});
</script>

<template>
    <div class="flex flex-col items-center gap-2">
        <div :class="['text-4xl font-bold tabular-nums', colorClass]">
            {{ formattedTime }}
        </div>
        <div class="w-48 h-2 bg-gray-200 rounded-full overflow-hidden">
            <div
                :class="['h-full transition-all duration-1000 ease-linear', progressColorClass]"
                :style="{ width: `${percentage}%` }"
            />
        </div>
    </div>
</template>
