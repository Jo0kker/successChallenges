<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    challenge: {
        type: Object,
        required: true
    },
    groupMembers: {
        type: Array,
        required: true
    }
});

const form = useForm({
    success_name: props.challenge.success_name,
    bet_amount: props.challenge.bet_amount,
    failed_by: props.challenge.failed_by,
    participants: props.challenge.participants.map(p => p.user_id),
    is_completed: props.challenge.is_completed
});

const submit = () => {
    form.put(route('challenges.update', props.challenge.id), {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <AppLayout title="Modifier le défi">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Modifier le défi
                </h2>
                <Link
                    :href="route('challenges.show', challenge.id)"
                    class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    Retour au défi
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label
                                        for="success_name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Nom du succès
                                    </label>
                                    <input
                                        id="success_name"
                                        v-model="form.success_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 sm:text-sm"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.success_name"
                                        class="mt-2 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.success_name }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="bet_amount"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Mise (en kamas)
                                    </label>
                                    <input
                                        id="bet_amount"
                                        v-model="form.bet_amount"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 sm:text-sm"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.bet_amount"
                                        class="mt-2 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.bet_amount }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="failed_by"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Joueur qui a échoué
                                    </label>
                                    <select
                                        id="failed_by"
                                        v-model="form.failed_by"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 sm:text-sm"
                                        required
                                    >
                                        <option value="">Sélectionnez un joueur</option>
                                        <option
                                            v-for="member in groupMembers"
                                            :key="member.id"
                                            :value="member.id"
                                        >
                                            {{ member.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.failed_by"
                                        class="mt-2 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.failed_by }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Participants
                                    </label>
                                    <div class="mt-2 space-y-2">
                                        <div
                                            v-for="member in groupMembers"
                                            :key="member.id"
                                            class="flex items-center"
                                        >
                                            <input
                                                :id="`participant-${member.id}`"
                                                v-model="form.participants"
                                                type="checkbox"
                                                :value="member.id"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                            />
                                            <label
                                                :for="`participant-${member.id}`"
                                                class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                            >
                                                {{ member.name }}
                                            </label>
                                        </div>
                                    </div>
                                    <p
                                        v-if="form.errors.participants"
                                        class="mt-2 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.participants }}
                                    </p>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_completed"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Défi terminé
                                        </span>
                                    </label>
                                </div>

                                <div class="flex justify-end space-x-4">
                                    <Link
                                        :href="route('challenges.show', challenge.id)"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                                    >
                                        Annuler
                                    </Link>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        :disabled="form.processing"
                                    >
                                        Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
