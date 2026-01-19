<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    showResult: {
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

const inputValue = ref(props.question.userValue || '');

watch(inputValue, (newValue) => {
    emit('update:answer', props.question.id, newValue);
});

watch(
    () => props.question.userValue,
    (newValue) => {
        inputValue.value = newValue || '';
    }
);

const isCorrect = computed(() => {
    const userValue = parseInt(inputValue.value, 10);
    if (isNaN(userValue)) return false;

    switch (props.question.userInput) {
        case 'answer':
            return userValue === props.question.correctAnswer;
        case 'operand1':
            return userValue === props.question.correctOperand1;
        case 'operand2':
            return userValue === props.question.correctOperand2;
        default:
            return false;
    }
});

const resultClass = computed(() => {
    if (!props.showResult) return '';
    return isCorrect.value ? 'bg-green-100' : 'bg-red-100';
});
</script>

<template>
    <div
        :class="[
            'flex items-center gap-0.5 text-sm',
            printMode ? 'py-0' : 'py-0.5',
            showResult ? resultClass : '',
        ]"
    >
        <!-- Operand 1 -->
        <template v-if="question.userInput === 'operand1'">
            <template v-if="printMode">
                <span class="w-8 text-center font-medium">...</span>
            </template>
            <template v-else>
                <input
                    v-model="inputValue"
                    type="number"
                    :class="[
                        'w-8 h-6 text-center text-sm font-medium border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500',
                        showResult && !isCorrect ? 'border-red-500 bg-red-50' : 'border-gray-300',
                    ]"
                    :disabled="showResult"
                    min="1"
                    max="144"
                />
            </template>
        </template>
        <template v-else>
            <span class="w-8 text-center font-medium">{{ question.operand1 }}</span>
        </template>

        <!-- Operator -->
        <span class="font-medium text-gray-600">{{ question.operator }}</span>

        <!-- Operand 2 -->
        <template v-if="question.userInput === 'operand2'">
            <template v-if="printMode">
                <span class="w-6 text-center font-medium">...</span>
            </template>
            <template v-else>
                <input
                    v-model="inputValue"
                    type="number"
                    :class="[
                        'w-8 h-6 text-center text-sm font-medium border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500',
                        showResult && !isCorrect ? 'border-red-500 bg-red-50' : 'border-gray-300',
                    ]"
                    :disabled="showResult"
                    min="1"
                    max="12"
                />
            </template>
        </template>
        <template v-else>
            <span class="w-6 text-center font-medium">{{ question.operand2 }}</span>
        </template>

        <!-- Equals -->
        <span class="font-medium text-gray-600">=</span>

        <!-- Answer -->
        <template v-if="question.userInput === 'answer'">
            <template v-if="printMode">
                <span class="w-8 text-center font-medium">...</span>
            </template>
            <template v-else>
                <input
                    v-model="inputValue"
                    type="number"
                    :class="[
                        'w-10 h-6 text-center text-sm font-medium border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500',
                        showResult && !isCorrect ? 'border-red-500 bg-red-50' : 'border-gray-300',
                    ]"
                    :disabled="showResult"
                    min="1"
                    max="144"
                />
            </template>
        </template>
        <template v-else>
            <span class="w-8 text-center font-medium">{{ question.answer }}</span>
        </template>

        <!-- Result indicator -->
        <span v-if="showResult && !printMode" class="ml-0.5 text-xs">
            {{ isCorrect ? '✓' : '✗' }}
        </span>
    </div>
</template>
