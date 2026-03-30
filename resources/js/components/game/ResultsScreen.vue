<script setup>
import { computed } from 'vue';
import Button from '../ui/Button.vue';

const props = defineProps({
    score: {
        type: Object,
        required: true,
    },
    awardLevel: {
        type: String,
        required: true,
    },
    timeRemaining: {
        type: Number,
        required: true,
    },
});

defineEmits(['play-again', 'close']);

const isAwardEarned = computed(() => {
    return props.score.percentage === 100 && props.timeRemaining > 0;
});

const awardEmoji = computed(() => {
    if (!isAwardEarned.value) return '';
    const emojis = {
        bronze: '🥉',
        silver: '🥈',
        gold: '🥇',
    };
    return emojis[props.awardLevel];
});

const awardName = computed(() => {
    const names = {
        bronze: 'Bronze',
        silver: 'Silver',
        gold: 'Gold',
    };
    return names[props.awardLevel];
});

const bgGradient = computed(() => {
    const gradients = {
        bronze: 'from-amber-400 to-amber-600',
        silver: 'from-gray-300 to-gray-500',
        gold: 'from-yellow-300 to-yellow-500',
    };
    return gradients[props.awardLevel];
});
</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div
            :class="[
                'bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all',
            ]"
        >
            <!-- Header -->
            <div :class="['bg-gradient-to-r p-6 text-white text-center', bgGradient]">
                <div v-if="isAwardEarned" class="text-6xl mb-2 animate-bounce">
                    {{ awardEmoji }}
                </div>
                <h2 class="text-2xl font-bold">
                    {{ isAwardEarned ? `${awardName} Award Earned!` : 'Game Complete!' }}
                </h2>
            </div>

            <!-- Content -->
            <div class="p-6 text-center">
                <div class="mb-4">
                    <div class="text-5xl font-bold text-gray-800 mb-2">
                        {{ score.correct }}/{{ score.total }}
                    </div>
                    <div class="text-xl text-gray-600">
                        {{ score.percentage }}% Correct
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-6 max-w-xs mx-auto text-sm">
                    <div class="bg-red-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-red-600">{{ score.incorrect }}</div>
                        <div class="text-red-500">Incorrect</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-gray-600">{{ score.unanswered }}</div>
                        <div class="text-gray-500">Not Answered</div>
                    </div>
                </div>

                <div v-if="!isAwardEarned && timeRemaining > 0" class="mb-6 text-gray-600">
                    <p>You need 100% to earn the {{ awardName }} award.</p>
                    <p class="text-sm mt-1">Keep practicing!</p>
                </div>

                <div v-if="timeRemaining <= 0" class="mb-6 text-red-600">
                    <p>Time ran out!</p>
                    <p class="text-sm mt-1">Try to answer all questions faster next time.</p>
                </div>

                <div class="flex gap-3 justify-center">
                    <Button variant="secondary" @click="$emit('close')"> Review Answers </Button>
                    <Button :variant="awardLevel" @click="$emit('play-again')"> Play Again </Button>
                </div>
            </div>
        </div>
    </div>
</template>
