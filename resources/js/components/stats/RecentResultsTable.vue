<script setup>
defineProps({
    attempts: {
        type: Array,
        required: true,
    },
});

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const percentageColor = (pct) => {
    if (pct >= 100) {
        return 'text-green-600 bg-green-50';
    }
    if (pct >= 70) {
        return 'text-amber-600 bg-amber-50';
    }

    return 'text-red-600 bg-red-50';
};

const formatTime = (seconds) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;

    return `${m}:${String(s).padStart(2, '0')}`;
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2 px-3 font-medium text-gray-500">Date</th>
                    <th class="text-center py-2 px-3 font-medium text-gray-500">Score</th>
                    <th class="text-center py-2 px-3 font-medium text-gray-500">Accuracy</th>
                    <th class="text-center py-2 px-3 font-medium text-gray-500">Time Left</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="attempt in attempts.slice(0, 10)"
                    :key="attempt.id"
                    class="border-b border-gray-100 hover:bg-gray-50"
                >
                    <td class="py-2 px-3 text-gray-600">{{ formatDate(attempt.created_at) }}</td>
                    <td class="py-2 px-3 text-center font-medium">{{ attempt.correct_answers }}/{{ attempt.total_questions }}</td>
                    <td class="py-2 px-3 text-center">
                        <span
                            :class="['inline-block rounded-full px-2 py-0.5 text-xs font-semibold', percentageColor(Math.round(attempt.correct_answers / attempt.total_questions * 100))]"
                        >
                            {{ Math.round(attempt.correct_answers / attempt.total_questions * 100) }}%
                        </span>
                    </td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ formatTime(attempt.time_remaining) }}</td>
                </tr>
                <tr v-if="attempts.length === 0">
                    <td colspan="4" class="py-8 text-center text-gray-400">No attempts yet</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
