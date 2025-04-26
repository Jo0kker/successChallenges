<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";

defineProps({
    groups: Array,
});
</script>

<template>
    <Head title="Mes Groupes" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mes Groupes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">
                                Liste des groupes
                            </h3>
                            <Link
                                :href="route('groups.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Créer un groupe
                            </Link>
                        </div>

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                            <div
                                v-for="group in groups"
                                :key="group.id"
                                class="border rounded-lg p-4 hover:shadow-lg transition-shadow"
                            >
                                <h4 class="font-semibold text-lg mb-2">
                                    {{ group.name }}
                                </h4>
                                <p class="text-gray-600 mb-4">
                                    {{ group.description }}
                                </p>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">
                                        Propriétaire: {{ group.owner.name }}
                                    </span>
                                    <Link
                                        :href="route('groups.show', group.id)"
                                        class="text-blue-500 hover:text-blue-700"
                                    >
                                        Voir détails
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
