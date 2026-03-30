<script setup>
import { computed, watch } from 'vue';
import { Head, Link, Deferred, router, usePage } from '@inertiajs/vue3';
import Tabs from '@/components/ui/Tabs.vue';
import RecentResultsTable from '@/components/stats/RecentResultsTable.vue';
import AccuracyOverTimeChart from '@/components/stats/AccuracyOverTimeChart.vue';
import TableStrengthChart from '@/components/stats/TableStrengthChart.vue';
import OperatorComparisonChart from '@/components/stats/OperatorComparisonChart.vue';
import HeatmapGrid from '@/components/stats/HeatmapGrid.vue';
import SpeedProgressChart from '@/components/stats/SpeedProgressChart.vue';
import QuestionsAnsweredChart from '@/components/stats/QuestionsAnsweredChart.vue';

const props = defineProps({
    awardLevel: {
        type: String,
        required: true,
    },
    recentAttempts: {
        type: Array,
        default: () => [],
    },
    perTableAccuracy: {
        type: Array,
        default: () => [],
    },
    perOperatorAccuracy: {
        type: Array,
        default: () => [],
    },
    heatmapData: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const awardTabs = [
    { value: 'gold', label: 'Gold' },
    { value: 'silver', label: 'Silver' },
    { value: 'bronze', label: 'Bronze' },
];

const selectedLevel = computed({
    get: () => props.awardLevel,
    set: (value) => {
        router.get('/stats', { award_level: value }, { preserveState: false });
    },
});

const awardColor = computed(() => {
    const colors = {
        bronze: '#d97706',
        silver: '#6b7280',
        gold: '#eab308',
    };

    return colors[props.awardLevel] ?? colors.gold;
});

const bgGradient = computed(() => {
    const gradients = {
        bronze: 'from-amber-50 to-amber-100',
        silver: 'from-gray-50 to-gray-100',
        gold: 'from-yellow-50 to-yellow-100',
    };

    return gradients[props.awardLevel] ?? gradients.gold;
});

const awardLabel = computed(() => {
    const labels = { bronze: 'Bronze', silver: 'Silver', gold: 'Gold' };

    return labels[props.awardLevel] ?? 'Gold';
});

const totalAttempts = computed(() => props.recentAttempts.length);

const bestScore = computed(() => {
    if (props.recentAttempts.length === 0) {
        return null;
    }

    return Math.max(...props.recentAttempts.map((a) => Math.round((a.correct_answers / a.total_questions) * 100)));
});

const averageScore = computed(() => {
    if (props.recentAttempts.length === 0) {
        return null;
    }

    const sum = props.recentAttempts.reduce((acc, a) => acc + Math.round((a.correct_answers / a.total_questions) * 100), 0);

    return Math.round(sum / props.recentAttempts.length);
});

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head :title="`${awardLabel} Stats`" />

    <div :class="['min-h-screen bg-gradient-to-br p-4', bgGradient]">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <header class="text-center mb-4 relative">
                <Link
                    href="/"
                    class="absolute left-0 top-0 text-sm text-indigo-600 hover:text-indigo-500 font-medium transition-colors"
                >
                    &larr; Back to Game
                </Link>
                <button
                    @click="logout"
                    class="absolute right-0 top-0 text-sm text-gray-500 hover:text-gray-700 transition-colors"
                >
                    Sign out ({{ page.props.auth?.user?.name }})
                </button>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-1">My Stats</h1>
                <p class="text-gray-600">Track your progress and improvement</p>
            </header>

            <!-- Award Level Tabs -->
            <div class="max-w-md mx-auto mb-6">
                <Tabs v-model="selectedLevel" :tabs="awardTabs" />
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-bold text-gray-800">{{ totalAttempts }}</div>
                    <div class="text-sm text-gray-500">Attempts</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-bold text-gray-800">{{ bestScore !== null ? `${bestScore}%` : '-' }}</div>
                    <div class="text-sm text-gray-500">Best Score</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-bold text-gray-800">{{ averageScore !== null ? `${averageScore}%` : '-' }}</div>
                    <div class="text-sm text-gray-500">Average</div>
                </div>
            </div>

            <!-- Recent Results -->
            <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Recent Results</h2>
                <RecentResultsTable :attempts="recentAttempts" />
            </div>

            <!-- Accuracy + Operator -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-5 md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Accuracy Over Time</h2>
                    <AccuracyOverTimeChart :attempts="recentAttempts" :color="awardColor" />
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Multiply vs Divide</h2>
                    <Deferred :data="['perOperatorAccuracy']">
                        <template #fallback>
                            <div class="h-64 animate-pulse bg-gray-100 rounded" />
                        </template>
                        <OperatorComparisonChart :data="perOperatorAccuracy" />
                    </Deferred>
                </div>
            </div>

            <!-- Table Strength -->
            <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Accuracy by Times Table</h2>
                <Deferred :data="['perTableAccuracy']">
                    <template #fallback>
                        <div class="h-64 animate-pulse bg-gray-100 rounded" />
                    </template>
                    <TableStrengthChart :data="perTableAccuracy" />
                </Deferred>
            </div>

            <!-- Heatmap -->
            <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Accuracy Heatmap</h2>
                <p class="text-sm text-gray-500 mb-3">Accuracy percentage for each times table combination</p>
                <Deferred :data="['heatmapData']">
                    <template #fallback>
                        <div class="h-80 animate-pulse bg-gray-100 rounded" />
                    </template>
                    <HeatmapGrid :data="heatmapData" />
                </Deferred>
            </div>

            <!-- Speed + Questions Answered -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Speed Progress</h2>
                    <p class="text-sm text-gray-500 mb-3">Time remaining when finished</p>
                    <SpeedProgressChart :attempts="recentAttempts" :color="awardColor" />
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Questions Answered</h2>
                    <p class="text-sm text-gray-500 mb-3">How many questions completed per attempt</p>
                    <QuestionsAnsweredChart :attempts="recentAttempts" :color="awardColor" />
                </div>
            </div>
        </div>
    </div>
</template>
