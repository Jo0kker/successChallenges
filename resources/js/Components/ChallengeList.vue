<template>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="challenge in challenges" :key="challenge.id"
            class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <div class="p-3">
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ challenge.name }}
                            </h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                ({{ challenge.bet_amount }} kamas)
                            </span>
                        </div>
                        <div v-if="canManage" class="flex items-center gap-2">
                            <button
                                @click="deleteChallenge(challenge, group, season)"
                                class="inline-flex items-center px-2.5 py-1 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                :disabled="loading"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div v-if="challenge.failed_by" class="mb-2">
                        <span class="text-sm text-red-600 dark:text-red-400">
                            Échoué par {{ challenge.failed_by.name }}
                        </span>
                    </div>

                    <div class="mt-auto">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Participants</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ challenge.participants.length + challenge.guest_participants.length }} participant{{ (challenge.participants.length + challenge.guest_participants.length) > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <!-- Participants users -->
                            <div v-for="participant in challenge.participants" :key="participant.user_id"
                                class="flex items-center px-3 py-1 text-sm bg-gray-100 rounded-full dark:bg-gray-700">
                                <span class="text-gray-700 dark:text-gray-300">{{ participant.user_name }}</span>
                                <span v-if="challenge.failed_by && challenge.failed_by.id === participant.user_id"
                                    class="ml-2 text-xs text-red-500 dark:text-red-400">
                                    (Échoué)
                                </span>
                            </div>
                            <!-- Participants guests -->
                            <div v-for="participant in challenge.guest_participants" :key="participant.guest_id"
                                class="flex items-center px-3 py-1 text-sm bg-gray-100 rounded-full dark:bg-gray-700">
                                <span class="text-gray-700 dark:text-gray-300">{{ participant.guest_name }}</span>
                                <span v-if="challenge.failed_by && challenge.failed_by.id === participant.guest_id"
                                    class="ml-2 text-xs text-red-500 dark:text-red-400">
                                    (Échoué)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useChallenges } from '@/Composables/useChallenges';

const props = defineProps({
    challenges: {
        type: Array,
        required: true
    },
    canManage: {
        type: Boolean,
        default: false
    },
    group: {
        type: Object,
        required: true
    },
    season: {
        type: Object,
        required: true
    }
});

const { loading, deleteChallenge, markAsFailed } = useChallenges();
</script>
