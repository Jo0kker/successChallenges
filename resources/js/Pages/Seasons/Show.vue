<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ChallengeList from '@/Components/ChallengeList.vue';
import { useChallenges } from '@/Composables/useChallenges';
import { parse } from 'vue/compiler-sfc';

const props = defineProps({
    group: Object,
    season: Object,
    canManage: Boolean,
    canAddChallenges: Boolean,
    ranking: Array
});

const { loading, deleteChallenge, markAsFailed } = useChallenges();

console.log('Group data:', {
    members: props.group.members,
    guestMembers: props.group.guestMembers
});

console.log('Season data:', {
    challenges: props.season.challenges.map(challenge => ({
        id: challenge.id,
        name: challenge.name,
        participants: challenge.participants,
        guestParticipants: challenge.guestParticipants,
        failed_by: challenge.failed_by
    }))
});

console.log('Ranking data:', props.ranking);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="season.name" />

    <AppLayout title="Détails de la saison">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ season.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link
                        v-if="canManage"
                        :href="route('seasons.edit', { group: group.id, season: season.id })"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md dark:bg-gray-200 dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        Modifier
                    </Link>
                    <Link
                        v-if="canManage && season.status === 'pending'"
                        :href="route('seasons.start', { group: group.id, season: season.id })"
                        method="post"
                        as="button"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        Démarrer la saison
                    </Link>
                    <Link
                        v-if="canManage && season.status === 'active'"
                        :href="route('seasons.complete', { group: group.id, season: season.id })"
                        method="post"
                        as="button"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        Terminer la saison
                    </Link>
                    <Link
                        v-if="season.status === 'active'"
                        :href="route('challenges.create', { group: group.id, season: season.id })"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        Ajouter un défi
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Description</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ season.description }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Statut</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ season.status === 'pending' ? 'En attente' : season.status === 'active' ? 'Active' : 'Terminée' }}
                                </p>
                            </div>

                            <div v-if="season.start_date">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Date de début</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(season.start_date) }}</p>
                            </div>

                            <div v-if="season.end_date">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Date de fin</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(season.end_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Classement</h3>
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Position</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Membre</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Total</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Gains</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Pertes</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Victoires</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Défis échoués</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <tr v-for="(member, index) in ranking" :key="member.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-gray-100">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                                {{ member.name }}
                                                <span v-if="member.type === 'guest'" class="text-xs text-gray-500">(invité)</span>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap" :class="{
                                                'text-green-600 dark:text-green-400': parseInt(member.total) > 0,
                                                'text-red-600 dark:text-red-400': parseInt(member.total) < 0,
                                                'text-gray-900 dark:text-gray-100': parseInt(member.total) === 0
                                            }">
                                                {{ member.total }} k
                                            </td>
                                            <td class="px-6 py-4 text-sm text-green-600 whitespace-nowrap dark:text-green-400">
                                                {{ member.gains }} k
                                            </td>
                                            <td class="px-6 py-4 text-sm text-red-600 whitespace-nowrap dark:text-red-400">
                                                {{ member.pertes }} k
                                            </td>
                                            <td class="px-6 py-4 text-sm text-green-600 whitespace-nowrap dark:text-green-400">
                                                {{ member.victoires }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-red-600 whitespace-nowrap dark:text-red-400">
                                                {{ member.defis_echoues }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Défis</h3>
                    <ChallengeList
                        :challenges="season.challenges"
                        :can-manage="canManage"
                        :group="group"
                        :season="season"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
