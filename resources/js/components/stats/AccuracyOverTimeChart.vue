<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Filler);

const props = defineProps({
    attempts: {
        type: Array,
        required: true,
    },
    color: {
        type: String,
        default: '#eab308',
    },
});

const chartData = computed(() => {
    const reversed = [...props.attempts].reverse();

    return {
        labels: reversed.map((_, i) => `#${i + 1}`),
        datasets: [{
            label: 'Accuracy %',
            data: reversed.map((a) => Math.round((a.correct_answers / a.total_questions) * 100)),
            borderColor: props.color,
            backgroundColor: props.color + '20',
            fill: true,
            tension: 0.3,
            pointRadius: 4,
            pointHoverRadius: 6,
        }],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            min: 0,
            max: 100,
            ticks: { callback: (v) => `${v}%` },
        },
    },
    plugins: {
        tooltip: {
            callbacks: {
                label: (ctx) => `${ctx.parsed.y}% correct`,
            },
        },
    },
};
</script>

<template>
    <div class="h-64">
        <Line v-if="attempts.length > 0" :data="chartData" :options="chartOptions" />
        <div v-else class="h-full flex items-center justify-center text-gray-400">No data yet</div>
    </div>
</template>
