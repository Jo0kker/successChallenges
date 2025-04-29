<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    group: Object,
    season: Object,
    challenge: Object,
    canManage: Boolean,
});

const showMarkFailedModal = ref(false);
const failedByType = ref('user');
const failedById = ref(null);
const guestPseudo = ref('');

const form = useForm({
    failed_by_type: 'user',
    failed_by_id: null,
});

const markAsFailed = () => {
    form.failed_by_type = failedByType.value;
    form.failed_by_id = failedByType.value === 'user' ? failedById.value : guestPseudo.value;

    form.post(route('challenges.mark-failed', {
        group: props.group.id,
        season: props.season.id,
        challenge: props.challenge.id
    }), {
        onSuccess: () => {
            showMarkFailedModal.value = false;
            failedByType.value = 'user';
            failedById.value = null;
            guestPseudo.value = '';
        }
    });
};
</script>

<template>
    <AppLayout title="Détails du défi">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Détails du défi
                </h2>
                <Link
                    :href="route('seasons.show', { group: group.id, season: season.id })"
                    class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    Retour à la saison
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ challenge.name }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Mise : {{ challenge.bet_amount }} k
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Participants</h4>
                                <div class="mt-2">
                                    <div v-for="participant in challenge.participants" :key="participant.id" class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ participant.name }}
                                    </div>
                                    <div v-for="guest in challenge.guest_participants" :key="guest.id" class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ guest.name }} (invité)
                                    </div>
                                </div>
                            </div>

                            <div v-if="challenge.failed_by">
                                <h4 class="text-sm font-medium text-red-600 dark:text-red-400">Échoué par</h4>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ challenge.failed_by_type === 'App\\Models\\User' ? challenge.failed_by.name : challenge.failed_by.name }}
                                </p>
                            </div>

                            <div v-if="canManage && !challenge.failed_by" class="mt-4">
                                <button
                                    @click="showMarkFailedModal = true"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Marquer comme échoué
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour marquer comme échoué -->
        <div v-if="showMarkFailedModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <div class="absolute right-0 top-0 pr-4 pt-4">
                            <button
                                type="button"
                                class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                @click="showMarkFailedModal = false"
                            >
                                <span class="sr-only">Fermer</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                                    Marquer le défi comme échoué
                                </h3>

                                <div class="mt-4">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Type de participant
                                        </label>
                                        <select
                                            v-model="failedByType"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        >
                                            <option value="user">Membre du groupe</option>
                                            <option value="guest">Invité</option>
                                        </select>
                                    </div>

                                    <div v-if="failedByType === 'user'" class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Membre qui a échoué
                                        </label>
                                        <select
                                            v-model="failedById"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        >
                                            <option v-for="participant in challenge.participants" :key="participant.id" :value="participant.id">
                                                {{ participant.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div v-else class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Pseudo de l'invité
                                        </label>
                                        <input
                                            type="text"
                                            v-model="guestPseudo"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                            placeholder="Entrez le pseudo de l'invité"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                type="button"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                                @click="markAsFailed"
                                :disabled="form.processing || (failedByType === 'user' && !failedById) || (failedByType === 'guest' && !guestPseudo)"
                            >
                                Confirmer
                            </button>
                            <button
                                type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto"
                                @click="showMarkFailedModal = false"
                            >
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
