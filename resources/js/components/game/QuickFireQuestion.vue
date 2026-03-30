<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    questions: {
        type: Array,
        required: true,
    },
    awardLevel: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['answer-submitted', 'all-answered']);

const currentIndex = ref(0);
const inputBuffer = ref('');

const currentQuestion = computed(() => props.questions[currentIndex.value]);

const progressPercent = computed(() => ((currentIndex.value) / props.questions.length) * 100);

const displayParts = computed(() => {
    const q = currentQuestion.value;
    if (!q) {
        return [];
    }

    const op1 = q.userInput === 'operand1' ? null : q.operand1 ?? q.correctOperand1;
    const op2 = q.userInput === 'operand2' ? null : q.operand2 ?? q.correctOperand2;
    const ans = q.userInput === 'answer' ? null : q.answer ?? q.correctAnswer;

    return [
        { value: op1, isInput: q.userInput === 'operand1' },
        { value: q.operator, isInput: false, isOperator: true },
        { value: op2, isInput: q.userInput === 'operand2' },
        { value: '=', isInput: false, isOperator: true },
        { value: ans, isInput: q.userInput === 'answer' },
    ];
});

const enterButtonColor = computed(() => {
    const colors = {
        bronze: 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800',
        silver: 'bg-gray-500 hover:bg-gray-600 active:bg-gray-700',
        gold: 'bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700',
    };

    return colors[props.awardLevel] ?? colors.bronze;
});

const pressDigit = (digit) => {
    if (inputBuffer.value.length < 3) {
        inputBuffer.value += digit;
    }
};

const pressBackspace = () => {
    inputBuffer.value = inputBuffer.value.slice(0, -1);
};

const pressEnter = () => {
    if (inputBuffer.value === '') {
        return;
    }

    const value = parseInt(inputBuffer.value, 10);
    emit('answer-submitted', currentQuestion.value.id, value);

    inputBuffer.value = '';

    if (currentIndex.value >= props.questions.length - 1) {
        emit('all-answered');
        return;
    }

    currentIndex.value++;
};

const handleKeydown = (event) => {
    if (event.key >= '0' && event.key <= '9') {
        event.preventDefault();
        pressDigit(event.key);
    } else if (event.key === 'Backspace') {
        event.preventDefault();
        pressBackspace();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        pressEnter();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="flex flex-col items-center gap-6">
        <!-- Progress -->
        <div class="w-full max-w-md text-center">
            <p class="text-sm text-gray-500 mb-2">
                Question {{ currentIndex + 1 }} of {{ questions.length }}
            </p>
            <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                <div
                    class="h-full bg-indigo-500 rounded-full transition-all duration-300"
                    :style="{ width: `${progressPercent}%` }"
                />
            </div>
        </div>

        <!-- Question Display -->
        <div class="flex items-center justify-center gap-3 md:gap-5 select-none" v-if="currentQuestion">
            <template v-for="(part, i) in displayParts" :key="i">
                <span
                    v-if="part.isOperator"
                    class="text-4xl md:text-6xl font-bold text-gray-400"
                >
                    {{ part.value }}
                </span>
                <span
                    v-else-if="!part.isInput"
                    class="text-5xl md:text-7xl font-bold text-gray-800"
                >
                    {{ part.value }}
                </span>
                <span
                    v-else
                    class="text-5xl md:text-7xl font-bold min-w-[3ch] text-center border-b-4 border-indigo-400 pb-1"
                    :class="inputBuffer ? 'text-indigo-600' : 'text-gray-300'"
                >
                    {{ inputBuffer || '?' }}
                </span>
            </template>
        </div>

        <!-- Keypad -->
        <div class="grid grid-cols-3 gap-3 max-w-xs mx-auto mt-4">
            <button
                v-for="digit in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                :key="digit"
                @click="pressDigit(String(digit))"
                class="w-22 h-22 md:w-24 md:h-24 text-2xl md:text-3xl font-bold rounded-xl bg-white border-2 border-gray-200 text-gray-800 hover:bg-gray-50 active:bg-gray-100 transition-colors select-none shadow-sm"
            >
                {{ digit }}
            </button>

            <!-- Backspace -->
            <button
                @click="pressBackspace"
                class="w-22 h-22 md:w-24 md:h-24 text-2xl md:text-3xl font-bold rounded-xl bg-gray-200 text-gray-600 hover:bg-gray-300 active:bg-gray-400 transition-colors select-none shadow-sm"
            >
                &#x232B;
            </button>

            <!-- Zero -->
            <button
                @click="pressDigit('0')"
                class="w-22 h-22 md:w-24 md:h-24 text-2xl md:text-3xl font-bold rounded-xl bg-white border-2 border-gray-200 text-gray-800 hover:bg-gray-50 active:bg-gray-100 transition-colors select-none shadow-sm"
            >
                0
            </button>

            <!-- Enter -->
            <button
                @click="pressEnter"
                :class="[
                    'w-22 h-22 md:w-24 md:h-24 text-xl md:text-2xl font-bold rounded-xl text-white transition-colors select-none shadow-sm',
                    enterButtonColor,
                ]"
            >
                Enter
            </button>
        </div>
    </div>
</template>
