<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
import axios from 'axios';

const props = defineProps({
    group: Object,
    season: Object,
    groupMembers: Array,
    successList: Array,
});

const form = useForm({
    name: '',
    bet_amount: '',
    failed_by: null,
    participants: [],
});

// Variables de recherche séparées pour chaque champ
const failedBySearchQuery = ref("");
const participantsSearchQuery = ref("");
const searchResults = ref([]);
const failedBySearchResults = ref([]);
const isLoading = ref(false);
const selectedUser = ref(null);
const selectedFailedBy = ref(null);

const nameSearch = ref('');
const showNameList = ref(false);

const filteredNames = computed(() => {
    if (!nameSearch.value) return props.successList;
    return props.successList.filter(name =>
        name.toLowerCase().includes(nameSearch.value.toLowerCase())
    );
});

// Fonction de recherche pour les participants
const searchParticipants = debounce((query) => {
    if (query.length < 2) {
        searchResults.value = [];
        return;
    }

    const searchTerm = query.toLowerCase();
    searchResults.value = props.groupMembers.filter(member => {
        if (!member) return false;
        const memberName = member.type === 'guest' ? member.name : member.name;
        return memberName && memberName.toLowerCase().includes(searchTerm);
    });
}, 300);

// Fonction de recherche pour "Qui a échoué"
const searchFailedBy = debounce((query) => {
    if (query.length < 2) {
        failedBySearchResults.value = [];
        return;
    }

    const searchTerm = query.toLowerCase();
    failedBySearchResults.value = props.groupMembers.filter(member => {
        if (!member) return false;
        const memberName = member.type === 'guest' ? member.name : member.name;
        return memberName && memberName.toLowerCase().includes(searchTerm);
    });
}, 300);

// Observateurs pour les deux champs de recherche
watch(participantsSearchQuery, (newQuery) => {
    searchParticipants(newQuery);
});

watch(failedBySearchQuery, (newQuery) => {
    searchFailedBy(newQuery);
});

const selectUser = (user) => {
    selectedUser.value = user;
    participantsSearchQuery.value = user.name;
    searchResults.value = [];
};

const selectFailedBy = (user) => {
    form.participants = form.participants.filter(p => p.id !== user.id);
    form.failed_by = {
        id: user.id,
        name: user.name,
        type: user.type
    };
    failedBySearchQuery.value = "";
    failedBySearchResults.value = [];
};

const removeFailedBy = () => {
    form.failed_by = null;
    failedBySearchQuery.value = "";
};

const addParticipant = (user) => {
    if (form.failed_by && form.failed_by.id === user.id) {
        return;
    }

    if (!form.participants.find(p => p.id === user.id)) {
        form.participants.push({
            id: user.id,
            name: user.name,
            type: user.type
        });
    }
    participantsSearchQuery.value = "";
    searchResults.value = [];
};

const removeParticipant = (userId) => {
    form.participants = form.participants.filter(p => p.id !== userId);
};

const submit = () => {
    form.post(route("challenges.store", { group: props.group.id, season: props.season.id }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const hideNameList = () => {
    setTimeout(() => {
        showNameList.value = false;
    }, 200);
};

const handleKeyDown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
};
</script>

<template>
    <Head title="Créer un défi" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Créer un défi
                </h2>
                <Link
                    :href="route('seasons.show', { group: group.id, season: season.id })"
                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-600 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                >
                    Retour à la saison
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du défi</label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="name"
                                            v-model="form.name"
                                            @input="nameSearch = form.name"
                                            @focus="showNameList = true"
                                            @blur="hideNameList"
                                            autocomplete="off"
                                            data-form-type="other"
                                            data-lpignore="true"
                                            data-kwimpalast="0"
                                            data-kwimpalaid="0"
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                            required
                                        />
                                        <div v-if="showNameList" class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-gray-800">
                                            <ul class="py-1 overflow-auto text-base rounded-md max-h-60 focus:outline-none sm:text-sm">
                                                <li v-for="name in filteredNames" :key="name"
                                                    @click="form.name = name; nameSearch = name; showNameList = false"
                                                    class="relative py-2 pl-3 cursor-pointer select-none pr-9 hover:bg-indigo-600 hover:text-white">
                                                    {{ name }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="bet_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mise (kamas)</label>
                                    <input
                                        type="number"
                                        id="bet_amount"
                                        v-model="form.bet_amount"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        required
                                        min="1"
                                    />
                                    <div v-if="form.errors.bet_amount" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.bet_amount }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Qui a échoué ?</label>
                                    <div class="mt-2">
                                        <div class="relative">
                                            <div class="flex flex-wrap gap-2 p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700">
                                                <!-- Tag de la personne qui a échoué -->
                                                <div v-if="form.failed_by"
                                                     class="flex items-center gap-1 px-2 py-1 text-sm text-red-800 bg-red-100 rounded-md dark:bg-red-900 dark:text-red-200">
                                                    <span>{{ form.failed_by.name }}</span>
                                                    <button @click="removeFailedBy"
                                                            class="text-red-600 hover:text-red-800 dark:text-red-300 dark:hover:text-red-100">
                                                        ×
                                                    </button>
                                                </div>
                                                <!-- Input de recherche -->
                                                <input
                                                    v-if="!form.failed_by"
                                                    type="text"
                                                    v-model="failedBySearchQuery"
                                                    class="flex-1 min-w-[200px] bg-transparent border-0 focus:ring-0 p-0 text-sm dark:text-gray-100"
                                                    placeholder="Rechercher un membre..."
                                                    @input="searchFailedBy"
                                                    @keydown="handleKeyDown"
                                                />
                                            </div>
                                            <!-- Résultats de recherche -->
                                            <div v-if="failedBySearchResults.length > 0 && failedBySearchQuery.length >= 2 && !form.failed_by"
                                                class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-gray-800"
                                            >
                                                <ul class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
                                                    <li
                                                        v-for="member in failedBySearchResults"
                                                        :key="member.id"
                                                        @click="selectFailedBy(member)"
                                                        class="relative py-2 pl-3 cursor-pointer select-none pr-9 hover:bg-indigo-600 hover:text-white dark:text-gray-100"
                                                    >
                                                        <div class="flex items-center">
                                                            <span class="block ml-3 font-normal truncate">
                                                                {{ member.name }}
                                                                <span v-if="member.type === 'guest'" class="text-xs text-gray-500">(invité)</span>
                                                            </span>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Participants</label>
                                    <div class="mt-2">
                                        <div class="relative">
                                            <div class="flex flex-wrap gap-2 p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700">
                                                <!-- Tags des participants sélectionnés -->
                                                <div v-for="participant in form.participants"
                                                     :key="participant.id"
                                                     class="flex items-center gap-1 px-2 py-1 text-sm text-indigo-800 bg-indigo-100 rounded-md dark:bg-indigo-900 dark:text-indigo-200">
                                                    <span>{{ participant.name }}</span>
                                                    <button @click="removeParticipant(participant.id)"
                                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-300 dark:hover:text-indigo-100">
                                                        ×
                                                    </button>
                                                </div>
                                                <!-- Input de recherche -->
                                                <input
                                                    type="text"
                                                    v-model="participantsSearchQuery"
                                                    class="flex-1 min-w-[200px] bg-transparent border-0 focus:ring-0 p-0 text-sm dark:text-gray-100"
                                                    placeholder="Rechercher un membre..."
                                                    @input="searchParticipants"
                                                    @keydown="handleKeyDown"
                                                />
                                            </div>
                                            <!-- Résultats de recherche -->
                                            <div v-if="searchResults.length > 0 && participantsSearchQuery.length >= 2"
                                                class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-gray-800"
                                            >
                                                <ul class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
                                                    <li
                                                        v-for="member in searchResults"
                                                        :key="member.id"
                                                        @click="addParticipant(member)"
                                                        class="relative py-2 pl-3 cursor-pointer select-none pr-9 hover:bg-indigo-600 hover:text-white dark:text-gray-100"
                                                    >
                                                        <div class="flex items-center">
                                                            <span class="block ml-3 font-normal truncate">
                                                                {{ member.name }}
                                                                <span v-if="member.type === 'guest'" class="text-xs text-gray-500">(invité)</span>
                                                            </span>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Créer le défi
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
