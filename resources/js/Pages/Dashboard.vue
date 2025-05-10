<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    groups: {
        type: Array,
        required: true
    }
});

const isOwner = (group) => {
    return group.owner_id === usePage().props.auth.user.id;
};

const isModerator = (group) => {
    return group.pivot?.role === 'moderator';
};

const isMember = (group) => {
    return group.pivot?.role === 'member';
};

const getRole = (group) => {
    if (isOwner(group)) return 'owner';
    return group.pivot?.role || 'member';
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col space-y-2">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Tableau de bord
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Gérez vos groupes et suivez vos défis en cours
                </p>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Vos groupes
                            </h3>
                            <Link
                                :href="route('groups.create')"
                                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                Créer un groupe
                            </Link>
                        </div>

                        <div v-if="groups.length === 0" class="py-12 text-center">
                            <p class="text-gray-500 dark:text-gray-400">Vous n'avez pas encore de groupes. Créez-en un pour commencer !</p>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div v-for="group in groups" :key="group.id" class="flex flex-col overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                                <div class="flex-1 p-6">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ group.name }}
                                        </h4>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': isOwner(group),
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': isModerator(group),
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200': isMember(group)
                                        }">
                                            {{ getRole(group) }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ group.members.length }} membres
                                    </p>

                                    <div class="mt-4">
                                        <div v-for="season in group.seasons" :key="season.id" class="mb-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ season.name }}
                                                </span>
                                                <span v-if="season.is_active" class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                                    Active
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ season.challenges.length }} défis
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                    <div class="flex justify-end space-x-2">
                                        <Link
                                            v-if="group.can_manage"
                                            :href="route('groups.edit', group.id)"
                                            class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            Modifier
                                        </Link>
                                        <Link
                                            :href="route('groups.show', group.id)"
                                            class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-600 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                        >
                                            Voir
                                        </Link>
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
