<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
});

const multiplication = computed(() => props.data.find((d) => d.operator === '×'));
const division = computed(() => props.data.find((d) => d.operator === '÷'));

const chartData = computed(() => ({
    labels: ['Multiply (×)', 'Divide (÷)'],
    datasets: [{
        data: [
            multiplication.value?.percentage ?? 0,
            division.value?.percentage ?? 0,
        ],
        backgroundColor: ['rgba(99, 102, 241, 0.8)', 'rgba(168, 85, 247, 0.8)'],
        borderWidth: 0,
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '60%',
    plugins: {
        legend: { position: 'bottom' },
        tooltip: {
            callbacks: {
                label: (ctx) => `${ctx.label}: ${ctx.parsed}% accuracy`,
            },
        },
    },
};
</script>

<template>
    <div class="h-64 flex flex-col items-center">
        <Doughnut v-if="data.length > 0" :data="chartData" :options="chartOptions" />
        <div v-else class="h-full flex items-center justify-center text-gray-400">No data yet</div>
    </div>
</template>
