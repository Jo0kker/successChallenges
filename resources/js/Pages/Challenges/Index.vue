<script setup>
import { computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    group: Object,
    season: Object,
    challenges: Array,
    member: Object,
    canManage: Boolean,
    ranking: Array
});

onMounted(() => {
    console.log('Challenges:', props.challenges);
    console.log('Ranking:', props.ranking);
    console.log('Ranking details:', props.ranking.map(member => ({
        id: member.id,
        name: member.name,
        type: member.type,
        points: member.points,
        participated: member.participated_challenges,
        failed: member.failed_challenges,
        raw: member
    })));
});

const title = computed(() => {
    if (props.member) {
        return `Défis de ${props.member.name}`;
    }
    return `Défis de la saison ${props.season.name}`;
});

const deleteChallenge = (challenge) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce défi ?')) {
        router.delete(route('challenges.destroy', {
            group: props.group.id,
            season: props.season.id,
            challenge: challenge.id
        }));
    }
};
</script>

<template>
    <AppLayout :title="title">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ title }}
                </h2>
                <div class="flex space-x-4">
                    <Link
                        v-if="canManage"
                        :href="route('challenges.create', { group: group.id, season: season.id })"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Créer un défi
                    </Link>
                    <Link
                        :href="route('seasons.show', { group: group.id, season: season.id })"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        Retour à la saison
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Classement -->
                <div class="mb-8 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Classement</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Position</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Membre</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Points</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Défis participés</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Défis échoués</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="(member, index) in ranking" :key="member.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                            {{ member.name }}
                                            <span v-if="member.type === 'guest'" class="text-xs text-gray-500">(invité)</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap"
                                            :class="member.points >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ member.points }} kamas
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                            {{ member.participated_challenges }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-red-600 whitespace-nowrap dark:text-red-400">
                                            {{ member.failed_challenges }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Liste des défis -->
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="challenges.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                            Aucun défi trouvé.
                        </div>
                        <div v-else class="space-y-6">
                            <div v-for="challenge in challenges" :key="challenge.id"
                                class="p-6 transition-colors duration-200 border rounded-lg dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/80">
                                <div class="flex flex-col space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ challenge.name }}</h3>
                                        <div class="flex items-center space-x-4">
                                            <p class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                                                {{ challenge.bet_amount }} kamas
                                            </p>
                                            <button v-if="canManage"
                                                @click="deleteChallenge(challenge)"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="challenge.failed_by" class="flex items-center space-x-2 text-sm text-red-500 dark:text-red-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Échoué par {{ challenge.failed_by.pseudo || challenge.failed_by.name }}</span>
                                        <span v-if="challenge.failed_by_type === 'App\\Models\\GuestParticipant'" class="text-xs text-gray-500">(invité)</span>
                                    </div>

                                    <div class="mt-4">
                                        <h4 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Participants :</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="participant in challenge.participants" :key="participant.id"
                                                class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-200">
                                                {{ participant.name }}
                                            </span>
                                            <span v-for="guest in challenge.guest_participants" :key="guest.id"
                                                class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-200">
                                                {{ guest.name }}
                                                <span class="ml-1 text-xs text-gray-500">(invité)</span>
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
