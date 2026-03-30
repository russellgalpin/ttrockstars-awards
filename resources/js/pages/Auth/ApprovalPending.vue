<script setup>
import { Head, router } from '@inertiajs/vue3';

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Approval Pending" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-amber-50 to-amber-100 p-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="mb-6">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                        <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-gray-800 mb-2">Approval Pending</h1>
                <p class="text-gray-500 mb-8">
                    Your account is awaiting approval from an administrator. You'll be able to access the site once your account has been approved.
                </p>

                <button
                    @click="logout"
                    class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                >
                    Sign out
                </button>
            </div>
        </div>
    </div>
</template>
