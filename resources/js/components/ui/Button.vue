<script setup>
defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'bronze', 'silver', 'gold'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :disabled="disabled"
        :class="[
            'inline-flex items-center justify-center font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
            {
                'px-3 py-1.5 text-sm': size === 'sm',
                'px-4 py-2 text-base': size === 'md',
                'px-6 py-3 text-lg': size === 'lg',
            },
            {
                'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500': variant === 'primary',
                'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-400': variant === 'secondary',
                'bg-amber-600 text-white hover:bg-amber-700 focus:ring-amber-500': variant === 'bronze',
                'bg-gray-400 text-white hover:bg-gray-500 focus:ring-gray-400': variant === 'silver',
                'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-400': variant === 'gold',
            },
            {
                'opacity-50 cursor-not-allowed': disabled,
            },
        ]"
    >
        <slot />
    </button>
</template>
