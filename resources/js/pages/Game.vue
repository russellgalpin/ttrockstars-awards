<script setup>
import { ref, computed, onUnmounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import Tabs from '@/components/ui/Tabs.vue';
import Timer from '@/components/ui/Timer.vue';
import Button from '@/components/ui/Button.vue';
import TableGrid from '@/components/game/TableGrid.vue';
import ResultsScreen from '@/components/game/ResultsScreen.vue';
import {
    generateBronzeQuestions,
    generateSilverQuestions,
    generateGoldQuestions,
    calculateScore,
} from '@/lib/questions.js';

const TOTAL_TIME = 360; // 6 minutes in seconds

const awardLevel = ref('bronze');
const gameState = ref('idle'); // idle, playing, finished
const timeRemaining = ref(TOTAL_TIME);
const tables = ref([]);
const showResults = ref(false);
const score = ref({ correct: 0, total: 0, percentage: 0 });
const isPrinting = ref(false);

let timerInterval = null;

const awardTabs = [
    { value: 'bronze', label: 'Bronze' },
    { value: 'silver', label: 'Silver' },
    { value: 'gold', label: 'Gold' },
];

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
    timeRemaining.value = TOTAL_TIME;
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
};

const submitAnswers = () => {
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
    // Generate fresh questions if none exist
    if (tables.value.length === 0) {
        tables.value = generateQuestions();
    }
    isPrinting.value = true;
    // Wait for Vue to update the DOM, then print
    setTimeout(() => {
        window.print();
        isPrinting.value = false;
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

    <!-- Print-only version -->
    <div v-if="isPrinting" class="print-only">
        <div class="print-header">
            <h1>{{ awardLabel }} Award - Times Table Challenge</h1>
            <p>Name: _________________________ Date: _____________</p>
        </div>
        <div class="print-grid">
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

    <!-- Screen version -->
    <div :class="['min-h-screen bg-gradient-to-br p-4', bgGradient, isPrinting ? 'screen-only' : '']">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <header class="text-center mb-4">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-1">Times Table Challenge</h1>
                <p class="text-gray-600">Earn your award by completing all questions correctly!</p>
            </header>

            <!-- Award Level Tabs -->
            <div class="max-w-md mx-auto mb-4">
                <Tabs v-model="awardLevel" :tabs="awardTabs" />
            </div>

            <!-- Game Controls -->
            <div class="flex flex-col items-center gap-4 mb-4">
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
                                The ultimate challenge! Random everything with mixed operations.
                            </template>
                        </p>
                        <p class="text-gray-500 text-xs mb-4">You have 6 minutes to complete all 144 questions.</p>
                        <div class="flex gap-3 justify-center">
                            <Button :variant="awardLevel" size="md" @click="startGame">Start Challenge</Button>
                            <Button variant="secondary" size="md" @click="printTables">Print Tables</Button>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="flex items-center gap-6">
                        <Timer :seconds="timeRemaining" :total-seconds="TOTAL_TIME" />
                        <div class="flex gap-2">
                            <Button
                                v-if="gameState === 'playing'"
                                variant="primary"
                                size="md"
                                @click="submitAnswers"
                            >
                                Submit
                            </Button>
                            <Button
                                v-else
                                :variant="awardLevel"
                                size="md"
                                @click="playAgain"
                            >
                                Play Again
                            </Button>
                            <Button variant="secondary" size="md" @click="printTables">Print</Button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Tables Grid -->
            <div
                v-if="tables.length > 0"
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2"
            >
                <TableGrid
                    v-for="table in tables"
                    :key="table.tableNumber"
                    :table="table"
                    :show-results="showResults && gameState === 'finished'"
                    :award-level="awardLevel"
                    @update:answer="handleAnswerUpdate"
                />
            </div>

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
        grid-template-columns: repeat(6, 1fr);
        gap: 4px;
        font-size: 10px;
    }

    .print-table {
        break-inside: avoid;
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
