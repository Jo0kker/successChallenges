<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

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

const nameSearch = ref('');
const failedBySearch = ref('');
const participantSearch = ref('');

const showNameList = ref(false);
const showUserList = ref(false);
const showParticipantList = ref(false);

const filteredNames = computed(() => {
    if (!nameSearch.value) return props.successList;
    return props.successList.filter(name =>
        name.toLowerCase().includes(nameSearch.value.toLowerCase())
    );
});

const filteredUsersForFailedBy = computed(() => {
    if (!failedBySearch.value) return props.groupMembers;
    return props.groupMembers.filter(member =>
        member.name.toLowerCase().includes(failedBySearch.value.toLowerCase())
    );
});

const filteredUsersForParticipants = computed(() => {
    if (!participantSearch.value) return props.groupMembers;
    return props.groupMembers.filter(member =>
        member.name.toLowerCase().includes(participantSearch.value.toLowerCase())
    );
});

const addParticipant = (memberId) => {
    if (!form.participants.includes(memberId)) {
        form.participants.push(memberId);
    }
};

const submit = () => {
    form.post(route('challenges.store', {
        group: props.group.id,
        season: props.season.id
    }));
};

const hideNameList = () => {
    setTimeout(() => {
        showNameList.value = false;
    }, 200);
};

const hideUserList = () => {
    setTimeout(() => {
        showUserList.value = false;
    }, 200);
};

const hideParticipantList = () => {
    setTimeout(() => {
        showParticipantList.value = false;
    }, 200);
};
</script>

<template>
    <Head title="Créer un défi" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Créer un défi
                </h2>
                <Link
                    :href="route('seasons.show', { group: group.id, season: season.id })"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Retour à la saison
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
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
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                            required
                                        />
                                        <div v-if="showNameList" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 rounded-md shadow-lg">
                                            <ul class="max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                                <li v-for="name in filteredNames" :key="name"
                                                    @click="form.name = name; nameSearch = name; showNameList = false"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                                    {{ name }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="bet_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mise (kamas)</label>
                                    <input
                                        type="number"
                                        id="bet_amount"
                                        v-model="form.bet_amount"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        required
                                        min="1"
                                    />
                                    <div v-if="form.errors.bet_amount" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.bet_amount }}
                                    </div>
                                </div>

                                <div>
                                    <label for="failed_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Échec par</label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="failed_by"
                                            v-model="failedBySearch"
                                            @input="form.failed_by = null"
                                            @focus="showUserList = true"
                                            @blur="hideUserList"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                            required
                                        />
                                        <div v-if="showUserList" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 rounded-md shadow-lg">
                                            <ul class="max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                                <li v-for="member in filteredUsersForFailedBy" :key="member.id"
                                                    @click="form.failed_by = member.id; failedBySearch = member.name; showUserList = false"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                                    {{ member.name }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.failed_by" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.failed_by }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Participants</label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            v-model="participantSearch"
                                            @focus="showParticipantList = true"
                                            @blur="hideParticipantList"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                            placeholder="Rechercher des participants..."
                                        />
                                        <div v-if="showParticipantList" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-300 dark:border-gray-600">
                                            <ul class="max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                                <li v-for="member in filteredUsersForParticipants" :key="member.id"
                                                    @click="addParticipant(member.id)"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                                    {{ member.name }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.participants" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.participants }}
                                    </div>
                                    <div v-if="form.participants.length > 0" class="mt-2">
                                        <div v-for="participantId in form.participants" :key="participantId" class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 mr-2 mb-2">
                                            {{ props.groupMembers.find(m => m.id === participantId)?.name }}
                                            <button type="button" @click="form.participants = form.participants.filter(id => id !== participantId)" class="ml-1 text-indigo-600 hover:text-indigo-900">
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
