<script setup>
import { ref, computed, onUnmounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Tabs from '@/components/ui/Tabs.vue';
import Timer from '@/components/ui/Timer.vue';
import Button from '@/components/ui/Button.vue';
import TableGrid from '@/components/game/TableGrid.vue';
import QuickFireQuestion from '@/components/game/QuickFireQuestion.vue';
import ResultsScreen from '@/components/game/ResultsScreen.vue';
import {
    generateBronzeQuestions,
    generateSilverQuestions,
    generateGoldQuestions,
    calculateScore,
} from '@/lib/questions.js';

const page = usePage();

const logout = () => {
    router.post('/logout');
};

const awardConfig = {
    bronze: { totalTime: 360, totalQuestions: 144 },
    silver: { totalTime: 360, totalQuestions: 144 },
    gold: { totalTime: 90, totalQuestions: 50 },
};

const awardLevel = ref('bronze');
const gameState = ref('idle'); // idle, playing, finished

const totalTime = computed(() => awardConfig[awardLevel.value].totalTime);
const totalQuestions = computed(() => awardConfig[awardLevel.value].totalQuestions);

const totalTimeFormatted = computed(() => {
    const minutes = Math.floor(totalTime.value / 60);
    const seconds = totalTime.value % 60;

    if (minutes > 0 && seconds > 0) {
        return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ${seconds} seconds`;
    }

    if (minutes > 0) {
        return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'}`;
    }

    return `${seconds} seconds`;
});

const timeRemaining = ref(awardConfig.bronze.totalTime);
const tables = ref([]);
const showResults = ref(false);
const score = ref({ correct: 0, total: 0, percentage: 0 });
const isPrinting = ref(false);
const printBatchSets = ref([]);
const printBatchCode = ref('');

let timerInterval = null;

const awardTabs = [
    { value: 'bronze', label: 'Bronze' },
    { value: 'silver', label: 'Silver' },
    { value: 'gold', label: 'Gold' },
];

const flatQuestions = computed(() => tables.value.flatMap((t) => t.questions));

const bgGradient = computed(() => {
    const gradients = {
        bronze: 'from-amber-50 to-amber-100',
        silver: 'from-gray-50 to-gray-100',
        gold: 'from-yellow-50 to-yellow-100',
    };
    return gradients[awardLevel.value];
});

const awardLabel = computed(() => {
    const labels = {
        bronze: 'Bronze',
        silver: 'Silver',
        gold: 'Gold',
    };
    return labels[awardLevel.value];
});

const generateQuestions = () => {
    switch (awardLevel.value) {
        case 'bronze':
            return generateBronzeQuestions();
        case 'silver':
            return generateSilverQuestions();
        case 'gold':
            return generateGoldQuestions();
        default:
            return generateBronzeQuestions();
    }
};

const startGame = () => {
    tables.value = generateQuestions();
    timeRemaining.value = totalTime.value;
    gameState.value = 'playing';
    showResults.value = false;

    timerInterval = setInterval(() => {
        timeRemaining.value--;
        if (timeRemaining.value <= 0) {
            endGame();
        }
    }, 1000);
};

const endGame = () => {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    score.value = calculateScore(tables.value);
    gameState.value = 'finished';
    showResults.value = true;

    router.post('/attempts', {
        award_level: awardLevel.value,
        total_questions: score.value.total,
        correct_answers: score.value.correct,
        incorrect_answers: score.value.total - score.value.correct,
        time_remaining: timeRemaining.value,
    }, { preserveState: true, preserveScroll: true });
};

const handleAllAnswered = () => {
    endGame();
};

const playAgain = () => {
    showResults.value = false;
    startGame();
};

const closeResults = () => {
    showResults.value = false;
};

const handleAnswerUpdate = (questionId, value) => {
    for (const table of tables.value) {
        const question = table.questions.find((q) => q.id === questionId);
        if (question) {
            question.userValue = value;
            break;
        }
    }
};

const printTables = () => {
    if (tables.value.length === 0) {
        tables.value = generateQuestions();
    }
    printBatchSets.value = [];
    isPrinting.value = true;
    setTimeout(() => {
        window.print();
        isPrinting.value = false;
    }, 100);
};

const generateBatchCode = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 3; i++) {
        code += chars[Math.floor(Math.random() * chars.length)];
    }
    return code;
};

const printBatch = () => {
    const sets = [];
    for (let i = 0; i < 10; i++) {
        sets.push(generateQuestions());
    }
    printBatchSets.value = sets;
    printBatchCode.value = generateBatchCode();
    isPrinting.value = true;
    setTimeout(() => {
        window.print();
        isPrinting.value = false;
        printBatchSets.value = [];
    }, 100);
};

// Reset game when award level changes
watch(awardLevel, () => {
    if (gameState.value === 'playing') {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        gameState.value = 'idle';
        tables.value = [];
        showResults.value = false;
    }
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});
</script>

<template>
    <Head title="Times Table Challenge">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <!-- Print-only version (single) -->
    <div v-if="isPrinting && printBatchSets.length === 0" class="print-only">
        <div class="print-header">
            <h1>{{ awardLabel }} Award - Times Table Challenge</h1>
            <p>Name: _________________________ Date: _____________</p>
        </div>
        <div :class="['print-grid', awardLevel === 'gold' ? 'print-grid-gold' : 'print-grid-default']">
            <div v-for="table in tables" :key="table.tableNumber" class="print-table">
                <TableGrid
                    :table="table"
                    :show-results="false"
                    :award-level="awardLevel"
                    :print-mode="true"
                />
            </div>
        </div>
    </div>

    <!-- Print-only version (batch of 10) -->
    <div v-if="isPrinting && printBatchSets.length > 0" class="print-only">
        <div v-for="(set, index) in printBatchSets" :key="index" class="print-page">
            <div class="print-header">
                <h1>{{ awardLabel }} Award - Times Table Challenge</h1>
                <p>Name: _________________________ Date: _____________</p>
                <span class="print-batch-code">Code: {{ printBatchCode }}</span>
                <span class="print-page-number">Page: {{ index + 1 }}</span>
            </div>
            <div :class="['print-grid', awardLevel === 'gold' ? 'print-grid-gold' : 'print-grid-default']">
                <div v-for="table in set" :key="`${index}-${table.tableNumber}`" class="print-table">
                    <TableGrid
                        :table="table"
                        :show-results="false"
                        :award-level="awardLevel"
                        :print-mode="true"
                    />
                </div>
            </div>
        </div>

        <!-- Answer Key (11th page) -->
        <div class="print-page print-answer-key">
            <div class="print-header">
                <h1>{{ awardLabel }} Award - Answer Key</h1>
                <span class="print-batch-code">Code: {{ printBatchCode }}</span>
            </div>
            <div class="answer-key-pages">
                <div v-for="(set, setIndex) in printBatchSets" :key="`answer-${setIndex}`" class="answer-key-page-section">
                    <div class="answer-key-page-title">Page {{ setIndex + 1 }}</div>
                    <div class="answer-key-grid">
                        <div v-for="table in set" :key="`ak-${setIndex}-${table.tableNumber}`" class="answer-key-column">
                            <div v-for="q in table.questions" :key="`akq-${q.id}`">
                                {{ q.correctAnswer }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Screen version -->
    <div :class="[
        'bg-gradient-to-br p-4',
        bgGradient,
        isPrinting ? 'screen-only' : '',
        gameState === 'playing' ? 'h-dvh flex flex-col overflow-hidden' : 'min-h-screen',
    ]">
        <div :class="[
            'max-w-7xl mx-auto',
            gameState === 'playing' ? 'flex flex-col flex-1 min-h-0' : '',
        ]">
            <!-- Header -->
            <header v-if="gameState !== 'playing'" class="text-center mb-4 relative">
                <button
                    @click="logout"
                    class="absolute right-0 top-0 text-sm text-gray-500 hover:text-gray-700 transition-colors"
                >
                    Sign out ({{ page.props.auth?.user?.name }})
                </button>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-1">Times Table Challenge</h1>
                <p class="text-gray-600">Earn your award by completing all questions correctly!</p>
            </header>

            <!-- Award Level Tabs -->
            <div v-if="gameState !== 'playing'" class="max-w-md mx-auto mb-4">
                <Tabs v-model="awardLevel" :tabs="awardTabs" />
            </div>

            <!-- Game Controls -->
            <div :class="[
                'flex flex-col items-center',
                gameState === 'playing' ? 'gap-2 mb-2 shrink-0' : 'gap-4 mb-4',
            ]">
                <template v-if="gameState === 'idle'">
                    <div class="text-center">
                        <p class="text-gray-600 mb-2 text-sm">
                            <template v-if="awardLevel === 'bronze'">
                                Complete all 12 times tables. Questions are in order.
                            </template>
                            <template v-else-if="awardLevel === 'silver'">
                                Random tables, random order. Mix of times and divides (not mixed in same table).
                            </template>
                            <template v-else>
                                The ultimate challenge! Mixed tables with random operations.
                            </template>
                        </p>
                        <p class="text-gray-500 text-xs mb-4">You have {{ totalTimeFormatted }} to complete all {{ totalQuestions }} questions.</p>
                        <div class="flex gap-3 justify-center">
                            <Button :variant="awardLevel" size="md" @click="startGame">Start Challenge</Button>
                            <Button variant="secondary" size="md" @click="printTables">Print Tables</Button>
                            <Button variant="secondary" size="md" @click="printBatch">Print 10</Button>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="flex items-center gap-6">
                        <Timer :seconds="timeRemaining" :total-seconds="totalTime" />
                        <div class="flex gap-2">
                            <Button
                                v-if="gameState === 'finished'"
                                :variant="awardLevel"
                                size="md"
                                @click="playAgain"
                            >
                                Play Again
                            </Button>
                            <Button variant="secondary" size="md" @click="printTables">Print</Button>
                            <Button variant="secondary" size="md" @click="printBatch">Print 10</Button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Quick-fire question display -->
            <QuickFireQuestion
                v-if="gameState === 'playing' && flatQuestions.length > 0"
                :questions="flatQuestions"
                :award-level="awardLevel"
                @answer-submitted="handleAnswerUpdate"
                @all-answered="handleAllAnswered"
            />

            <!-- Results Screen -->
            <ResultsScreen
                v-if="gameState === 'finished' && showResults"
                :score="score"
                :award-level="awardLevel"
                :time-remaining="timeRemaining"
                @play-again="playAgain"
                @close="closeResults"
            />
        </div>
    </div>
</template>

<style>
/* Print styles */
@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    .screen-only {
        display: none !important;
    }

    .print-only {
        display: block !important;
    }

    .print-header {
        text-align: center;
        margin-bottom: 8px;
    }

    .print-header h1 {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .print-header p {
        font-size: 12px;
    }

    .print-grid {
        display: grid;
    }

    .print-grid-default {
        grid-template-columns: repeat(6, 1fr);
        gap: 4px;
        font-size: 10px;
    }

    .print-grid-gold {
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        font-size: 24px;
        height: calc(100vh - 60px);
    }

    .print-grid-gold .print-table {
        height: 100%;
    }

    .print-grid-gold .print-table > * {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .print-grid-gold .print-table > * > :first-child {
        display: none;
    }

    .print-grid-gold .print-table > * > :last-child {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .print-grid-gold * {
        font-size: inherit !important;
        width: auto !important;
        gap: 8px !important;
    }

    .print-table {
        break-inside: avoid;
    }

    .print-page {
        page-break-after: always;
        height: 100vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .print-page:last-child {
        page-break-after: auto;
    }

    .print-page .print-grid-gold {
        height: auto;
        flex: 1;
    }

    .print-header {
        position: relative;
    }

    .print-batch-code {
        position: absolute;
        top: 0;
        right: 0;
        font-size: 22px;
        font-weight: bold;
    }

    .print-page-number {
        position: absolute;
        right: 0;
        font-size: 22px;
        font-weight: bold;
        top: 28px;
    }

    .print-answer-key {
        page-break-before: always;
    }

    .answer-key-pages {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        grid-template-rows: 1fr 1fr;
        flex: 1;
    }

    .answer-key-page-section {
        border-right: 1px solid #000;
    }

    .answer-key-page-section:nth-child(5n) {
        border-right: none;
    }

    .answer-key-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        font-size: 18px;
        line-height: 1.7;
        font-weight: bold;
    }

    .answer-key-column {
        text-align: center;
        border-right: 1px solid #ccc;
    }

    .answer-key-column:last-child {
        border-right: none;
    }

    .answer-key-page-title {
        font-weight: bold;
        font-size: 16px;
        border-bottom: 1px solid #000;
        margin-bottom: 1px;
    }
}

@media screen {
    .print-only {
        display: none !important;
    }
}

/* Hide number input spinners */
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type='number'] {
    -moz-appearance: textfield;
}
</style>
