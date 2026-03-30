<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
});

const grid = computed(() => {
    const matrix = {};

    for (const row of props.data) {
        const key = `${row.table_number}-${row.multiplier}`;
        matrix[key] = row;
    }

    return matrix;
});

const getCell = (table, multiplier) => {
    return grid.value[`${table}-${multiplier}`] ?? null;
};

const cellColor = (cell) => {
    if (!cell || cell.total === 0) {
        return 'bg-gray-100 text-gray-400';
    }

    const pct = cell.percentage;

    if (pct === 100) {
        return 'bg-green-500 text-white';
    }
    if (pct >= 80) {
        return 'bg-green-300 text-green-900';
    }
    if (pct >= 60) {
        return 'bg-amber-300 text-amber-900';
    }
    if (pct >= 40) {
        return 'bg-orange-400 text-white';
    }

    return 'bg-red-500 text-white';
};

const tables = Array.from({ length: 12 }, (_, i) => i + 1);
</script>

<template>
    <div class="overflow-x-auto">
        <div class="min-w-[600px]">
            <!-- Header row -->
            <div class="grid grid-cols-13 gap-0.5 mb-0.5">
                <div class="text-center text-xs font-medium text-gray-400 p-1">×</div>
                <div
                    v-for="m in tables"
                    :key="`h-${m}`"
                    class="text-center text-xs font-medium text-gray-500 p-1"
                >
                    {{ m }}
                </div>
            </div>

            <!-- Data rows -->
            <div v-for="t in tables" :key="`r-${t}`" class="grid grid-cols-13 gap-0.5 mb-0.5">
                <div class="text-center text-xs font-medium text-gray-500 p-1 flex items-center justify-center">
                    {{ t }}
                </div>
                <div
                    v-for="m in tables"
                    :key="`c-${t}-${m}`"
                    :class="['text-center text-xs font-semibold rounded p-1 aspect-square flex items-center justify-center', cellColor(getCell(t, m))]"
                    :title="`${t} × ${m}: ${getCell(t, m)?.percentage ?? 0}% (${getCell(t, m)?.correct ?? 0}/${getCell(t, m)?.total ?? 0})`"
                >
                    {{ getCell(t, m)?.percentage ?? '' }}
                </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center gap-3 mt-3 text-xs text-gray-500 justify-center">
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-500" /> 0-39%</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-orange-400" /> 40-59%</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-300" /> 60-79%</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-300" /> 80-99%</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-500" /> 100%</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.grid-cols-13 {
    grid-template-columns: 2rem repeat(12, 1fr);
}
</style>
