<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import axios from 'axios';
import ChallengeList from '@/Components/ChallengeList.vue';

const props = defineProps({
    group: Object,
    season: Object,
    canManage: Boolean,
    canAddChallenges: Boolean,
});

console.log('canAddChallenges:', props.canAddChallenges);
console.log('season status:', props.season.status);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const calculateMemberStats = (member) => {
    // Gains : somme des montants des défis où le membre participe et n'est pas le perdant
    const gains = props.season.challenges
        .filter(challenge =>
            challenge.participants.some(p => p.id === member.id) && // Le membre participe
            challenge.failed_by?.id !== member.id // Et n'est pas le perdant
        )
        .reduce((sum, challenge) => {
            // Chaque participant gagnant reçoit la mise complète
            return sum + Number(challenge.bet_amount);
        }, 0);

    // Pertes : somme des montants des défis où le membre a échoué
    const pertes = props.season.challenges
        .filter(challenge => challenge.failed_by?.id === member.id)
        .reduce((sum, challenge) => {
            // Le perdant paie la mise multipliée par le nombre de participants
            return sum + (Number(challenge.bet_amount) * challenge.participants.length);
        }, 0);

    const total = gains - pertes;

    return {
        gains: Math.round(gains),
        pertes: Math.round(pertes),
        total: Math.round(total),
        wins: props.season.challenges.filter(challenge =>
            challenge.participants.some(p => p.id === member.id) &&
            challenge.failed_by?.id !== member.id
        ).length,
        losses: props.season.challenges.filter(challenge =>
            challenge.failed_by?.id === member.id
        ).length
    };
};

const sortedMembers = computed(() => {
    return props.group.members
        .map(member => ({
            ...member,
            stats: calculateMemberStats(member)
        }))
        .filter(member => member.stats.wins > 0 || member.stats.losses > 0)
        .sort((a, b) => b.stats.total - a.stats.total);
});

const markAsFailed = async (challenge) => {
    if (!confirm('Êtes-vous sûr de vouloir marquer ce défi comme échoué ?')) {
        return;
    }

    try {
        await axios.post(route('challenges.mark-failed', {
            group: props.group.id,
            season: props.season.id,
            challenge: challenge.id
        }));
        window.location.reload();
    } catch (error) {
        console.error('Erreur lors du marquage du défi comme échoué:', error);
    }
};
</script>

<template>
    <Head :title="season.name" />

    <AppLayout title="Détails de la saison">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ season.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link
                        v-if="canManage"
                        :href="route('seasons.edit', [group.id, season.id])"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Modifier
                    </Link>
                    <Link
                        v-if="canManage && season.status === 'pending'"
                        :href="route('seasons.start', [group.id, season.id])"
                        method="post"
                        as="button"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Démarrer la saison
                    </Link>
                    <Link
                        v-if="canManage && season.status === 'active'"
                        :href="route('seasons.complete', [group.id, season.id])"
                        method="post"
                        as="button"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Terminer la saison
                    </Link>
                    <Link
                        v-if="season.status === 'active'"
                        :href="route('challenges.create', [group.id, season.id])"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Ajouter un défi
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
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
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Classement</h3>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Membre</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gains</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pertes</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Victoires</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Défis échoués</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="(member, index) in sortedMembers" :key="member.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                <Link :href="route('challenges.member', [group.id, season.id, member.id])" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                    {{ member.name }}
                                                </Link>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">
                                                +{{ member.stats.gains }} k
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400">
                                                -{{ member.stats.pertes }} k
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="{
                                                'text-green-600 dark:text-green-400': member.stats.total > 0,
                                                'text-red-600 dark:text-red-400': member.stats.total < 0,
                                                'text-gray-900 dark:text-gray-100': member.stats.total === 0
                                            }">
                                                {{ member.stats.total > 0 ? '+' : '' }}{{ member.stats.total }} k
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">
                                                {{ member.stats.wins }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400">
                                                {{ member.stats.losses }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Défis</h3>
                    <ChallengeList
                        :challenges="season.challenges"
                        :can-manage="canManage"
                        @mark-failed="markAsFailed"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
