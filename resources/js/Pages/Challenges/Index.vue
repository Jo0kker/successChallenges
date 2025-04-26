<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    group: Object,
    season: Object,
    challenges: Array,
    member: Object,
    canManage: Boolean
});

const title = computed(() => {
    if (props.member) {
        return `Défis de ${props.member.name}`;
    }
    return `Défis de la saison ${props.season.name}`;
});
</script>

<template>
    <AppLayout :title="title">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ title }}
                </h2>
                <div class="flex space-x-4">
                    <Link
                        v-if="canManage"
                        :href="route('challenges.create', { group: group.id, season: season.id })"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Créer un défi
                    </Link>
                    <Link
                        :href="route('seasons.show', { group: group.id, season: season.id })"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Retour à la saison
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="challenges.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                            Aucun défi trouvé.
                        </div>
                        <div v-else class="space-y-6">
                            <div v-for="challenge in challenges" :key="challenge.id"
                                class="border dark:border-gray-700 rounded-lg p-6
                                hover:bg-gray-50 dark:hover:bg-gray-800/80
                                transition-colors duration-200">
                                <div class="flex flex-col space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ challenge.success_name }}</h3>
                                        <p class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ challenge.bet_amount }} kamas
                                        </p>
                                    </div>

                                    <div v-if="challenge.failed_by" class="flex items-center space-x-2 text-sm text-red-500 dark:text-red-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Échoué par {{ challenge.failed_by.name }}</span>
                                    </div>

                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Participants :</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="participant in challenge.participants" :key="participant.id"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                {{ participant.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
