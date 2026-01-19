<script setup>
import QuestionCard from './QuestionCard.vue';

defineProps({
    table: {
        type: Object,
        required: true,
    },
    showResults: {
        type: Boolean,
        default: false,
    },
    awardLevel: {
        type: String,
        default: 'bronze',
    },
    printMode: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:answer']);

const handleAnswerUpdate = (questionId, value) => {
    emit('update:answer', questionId, value);
};

const headerColors = {
    bronze: 'bg-amber-600',
    silver: 'bg-gray-500',
    gold: 'bg-yellow-500',
};
</script>

<template>
    <div :class="[printMode ? 'border border-gray-400' : 'bg-white rounded-lg shadow-md overflow-hidden']">
        <!-- Table header -->
        <div
            :class="[
                'text-center font-bold',
                printMode ? 'py-1 text-sm border-b border-gray-400' : 'px-2 py-1.5 text-white text-sm',
                printMode ? '' : headerColors[awardLevel],
            ]"
        >
            {{ table.tableNumber }}× Table
        </div>

        <!-- Questions list -->
        <div :class="[printMode ? 'px-1 py-0.5' : 'p-1.5']">
            <QuestionCard
                v-for="question in table.questions"
                :key="question.id"
                :question="question"
                :show-result="showResults"
                :award-level="awardLevel"
                :print-mode="printMode"
                @update:answer="handleAnswerUpdate"
            />
        </div>
    </div>
</template>
