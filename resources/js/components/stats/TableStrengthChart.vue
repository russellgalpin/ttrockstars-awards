<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
});

const barColor = (pct) => {
    if (pct >= 90) {
        return 'rgba(34, 197, 94, 0.8)';
    }
    if (pct >= 70) {
        return 'rgba(245, 158, 11, 0.8)';
    }

    return 'rgba(239, 68, 68, 0.8)';
};

const chartData = computed(() => {
    const allTables = Array.from({ length: 12 }, (_, i) => {
        const found = props.data.find((d) => d.table_number === i + 1);

        return found ?? { table_number: i + 1, percentage: 0, total: 0 };
    });

    return {
        labels: allTables.map((d) => `${d.table_number}×`),
        datasets: [{
            label: 'Accuracy %',
            data: allTables.map((d) => d.percentage),
            backgroundColor: allTables.map((d) => barColor(d.percentage)),
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
            max: 100,
            ticks: { callback: (v) => `${v}%` },
        },
    },
    plugins: {
        tooltip: {
            callbacks: {
                label: (ctx) => `${ctx.parsed.y}% accuracy`,
            },
        },
    },
};
</script>

<template>
    <div class="h-64">
        <Bar v-if="data.length > 0" :data="chartData" :options="chartOptions" />
        <div v-else class="h-full flex items-center justify-center text-gray-400">No data yet</div>
    </div>
</template>
