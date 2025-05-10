<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('welcome')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <Link
                                    :href="route('login')"
                                    class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none transition duration-150 ease-in-out"
                                >
                                    Se connecter
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none transition duration-150 ease-in-out"
                                >
                                    S'inscrire
                                </Link>
                                <Link
                                    :href="route('feedback.create')"
                                    class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none transition duration-150 ease-in-out"
                                >
                                    Feedback
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white dark:bg-gray-800 shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>

            <footer class="bg-white dark:bg-gray-800 shadow mt-8">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            &copy; {{ new Date().getFullYear() }} Dofus Success Challenge
                        </div>
                        <div class="flex space-x-4">
                            <Link :href="route('feedback.create')" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Feedback
                            </Link>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canRegister: {
        type: Boolean,
        default: false
    }
});
</script>
