<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

const props = defineProps({
    attempts: {
        type: Array,
        required: true,
    },
    color: {
        type: String,
        default: '#6366f1',
    },
});

const chartData = computed(() => {
    const reversed = [...props.attempts].reverse();

    return {
        labels: reversed.map((_, i) => `#${i + 1}`),
        datasets: [{
            label: 'Questions answered',
            data: reversed.map((a) => a.correct_answers + a.incorrect_answers),
            backgroundColor: props.color + '80',
            borderRadius: 4,
        }],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            min: 0,
            beginAtZero: true,
        },
    },
    plugins: {
        tooltip: {
            callbacks: {
                label: (ctx) => `${ctx.parsed.y} answered`,
            },
        },
    },
};
</script>

<template>
    <div class="h-64">
        <Bar v-if="attempts.length > 0" :data="chartData" :options="chartOptions" />
        <div v-else class="h-full flex items-center justify-center text-gray-400">No data yet</div>
    </div>
</template>
