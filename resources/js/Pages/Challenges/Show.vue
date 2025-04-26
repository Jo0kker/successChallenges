<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    challenge: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <AppLayout title="Détails du défi">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Détails du défi
                </h2>
                <Link
                    :href="route('seasons.show', challenge.season_id)"
                    class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    Retour à la saison
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Informations du défi
                                </h3>
                                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Nom du succès
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ challenge.success_name }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Mise
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ challenge.bet_amount }} kamas
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Échoué par
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ challenge.failed_by }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Statut
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': challenge.is_completed,
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': !challenge.is_completed
                                                }"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                {{ challenge.is_completed ? 'Terminé' : 'En cours' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Participants
                                </h3>
                                <div class="mt-4">
                                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <li
                                            v-for="participant in challenge.participants"
                                            :key="participant.id"
                                            class="py-4"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ participant.name }}
                                                        </p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ participant.role }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center">
                                                    <span
                                                        :class="{
                                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': participant.has_won,
                                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': !participant.has_won
                                                        }"
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    >
                                                        {{ participant.has_won ? 'Gagnant' : 'Perdant' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
