<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import axios from "axios";
import { debounce } from "lodash";

const props = defineProps({
    group: Object,
    canManage: Boolean,
    canManageMembers: Boolean,
    tab: {
        type: String,
        default: 'seasons'
    },
    seasons: {
        type: Array,
        default: () => []
    },
    members: {
        type: Array,
        default: () => []
    },
    activeTab: String,
});

const activeTab = computed(() => props.activeTab || 'seasons');

// Mettre à jour l'URL quand l'onglet change
watch(activeTab, (newTab) => {
    router.get(route('groups.show', props.group.id), { tab: newTab }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
});

const memberForm = useForm({
    user_id: "",
    user_type: "",
    role: "member",
});

const searchQuery = ref("");
const searchResults = ref([]);
const isLoading = ref(false);
const selectedUser = ref(null);
const searchInputRef = ref(null);

const searchUsers = debounce(async (query) => {
    if (query.length < 2) {
        searchResults.value = [];
        return;
    }

    isLoading.value = true;
    try {
        console.log('Recherche pour:', query);
        const response = await axios.get(route("users.search"), {
            params: { query },
        });
        console.log('Résultats:', response.data);
        searchResults.value = response.data;
    } catch (error) {
        console.error("Erreur lors de la recherche:", error);
        searchResults.value = [];
    } finally {
        isLoading.value = false;
    }
}, 300);

watch(searchQuery, (newQuery) => {
    searchUsers(newQuery);
});

const selectUser = (user) => {
    console.log('Sélection:', user);
    selectedUser.value = user;
    memberForm.user_id = user.id;
    memberForm.user_type = user.type;
    searchQuery.value = user.name;
    searchResults.value = [];
};

const addMember = () => {
    if (!selectedUser.value && !searchQuery.value) {
        return;
    }

    const formData = {
        user_id: selectedUser.value?.id || null,
        user_type: memberForm.user_type || (selectedUser.value ? 'user' : 'guest'),
        role: memberForm.role,
    };

    // Ajouter le champ name uniquement pour les invités
    if (formData.user_type === 'guest') {
        formData.name = searchQuery.value;
    }

    console.log('Données du formulaire:', formData);

    const form = useForm(formData);

    form.post(route("groups.members.add", props.group.id), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Membre ajouté avec succès');
            memberForm.reset();
            searchQuery.value = "";
            selectedUser.value = null;
            searchResults.value = [];
        },
        onError: (errors) => {
            console.error('Erreur lors de l\'ajout du membre:', errors);
        }
    });
};

const removeMember = (userId, type) => {
    if (confirm("Êtes-vous sûr de vouloir retirer ce membre ?")) {
        useForm({ user_id: userId, user_type: type }).delete(
            route("groups.members.remove", props.group.id)
        );
    }
};

const updateMemberRole = (userId, newRole) => {
    const form = useForm({
        role: newRole
    });

    form.put(route('groups.members.update-role', [props.group.id, userId]), {
        preserveScroll: true,
        onSuccess: () => {
            // Mettre à jour le rôle dans la liste des membres
            const member = props.members.find(m => m.id === userId);
            if (member) {
                member.role = newRole;
            }
        },
    });
};

const showNewSeasonForm = ref(false);

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
});

const submit = () => {
    form.post(route('seasons.store', props.group.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showNewSeasonForm.value = false;
        },
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const deleteGroup = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe ? Cette action est irréversible.')) {
        router.delete(route('groups.destroy', props.group.id), {
            onSuccess: () => {
                router.visit(route('groups.index'));
            }
        });
    }
};
</script>

<template>
    <Head :title="group.name" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ group.name }}
                </h2>
                <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-4">
                    <Link
                        v-if="group.owner.id === $page.props.auth.user.id"
                        :href="route('groups.edit', group.id)"
                        class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-600 border border-transparent rounded-md sm:w-auto hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        Modifier le groupe
                    </Link>
                    <button
                        v-if="group.owner.id === $page.props.auth.user.id"
                        @click="deleteGroup"
                        class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md sm:w-auto hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        Supprimer le groupe
                    </button>
                    <Link
                        v-if="canManage"
                        :href="route('seasons.create', group.id)"
                        class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md sm:w-auto hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Créer une saison
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-visible bg-gray-800 shadow-sm dark:bg-gray-900 sm:rounded-lg">
                    <div class="p-6 text-gray-100">
                        <div class="mb-6">
                            <div class="border-b border-gray-200 dark:border-gray-700">
                                <nav class="flex -mb-px space-x-8">
                                    <Link
                                        :href="route('groups.show', { group: group.id, tab: 'seasons' })"
                                        class="px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap"
                                        :class="[
                                            activeTab === 'seasons'
                                                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                        ]"
                                    >
                                        Saisons
                                    </Link>
                                    <Link
                                        :href="route('groups.show', { group: group.id, tab: 'members' })"
                                        class="px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap"
                                        :class="[
                                            activeTab === 'members'
                                                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                        ]"
                                    >
                                        Membres
                                    </Link>
                                </nav>
                            </div>
                        </div>

                        <div v-if="activeTab === 'seasons'">
                            <div v-if="seasons && seasons.length > 0" class="space-y-4">
                                <div v-for="season in seasons" :key="season.id"
                                    class="p-4 rounded-lg"
                                    :class="{
                                        'bg-indigo-900/50 dark:bg-indigo-900/50': season.status === 'active',
                                        'bg-gray-700 dark:bg-gray-700': season.status !== 'active'
                                    }"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold">{{ season.name }}</h3>
                                            <div class="flex items-center mt-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': season.status === 'active',
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': season.status === 'pending',
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': season.status === 'completed'
                                                    }"
                                                >
                                                    {{ season.status === 'active' ? 'Active' : season.status === 'pending' ? 'En attente' : 'Terminée' }}
                                                </span>
                                                <span v-if="season.status === 'active'" class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                                    ({{ season.challenges.length }} défi{{ season.challenges.length > 1 ? 's' : '' }})
                                                </span>
                                            </div>
                                        </div>
                                        <Link
                                            :href="route('seasons.show', { group: group.id, season: season.id })"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            Voir les détails
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-12 text-center">
                                <p class="text-gray-400">Aucune saison n'a été créée pour ce groupe.</p>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'members'">
                            <div v-if="canManageMembers || group.members.find(m => m.id === $page.props.auth.user.id && m.pivot.role === 'moderator')" class="mb-6">
                                <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                                    <div class="relative flex-1" style="position: relative; z-index: 50;">
                                        <input
                                            type="text"
                                            v-model="searchQuery"
                                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                            placeholder="Rechercher un utilisateur..."
                                        />
                                        <div v-if="searchResults.length > 0 && searchQuery.length >= 2"
                                            class="absolute z-[9999] w-full mt-1 bg-white rounded-md shadow-lg dark:bg-gray-800"
                                            style="position: absolute; top: 100%; left: 0; right: 0;"
                                        >
                                            <ul class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
                                                <li
                                                    v-for="user in searchResults"
                                                    :key="user.id || user.name"
                                                    @click="selectUser(user)"
                                                    class="relative py-2 pl-3 cursor-pointer select-none pr-9 hover:bg-indigo-600 hover:text-white dark:text-gray-100"
                                                >
                                                    <div class="flex items-center">
                                                        <span class="block ml-3 font-normal truncate">
                                                            {{ user.name }}
                                                            <span v-if="user.type === 'guest'" class="text-xs text-gray-500">(invité)</span>
                                                        </span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="w-full sm:w-auto">
                                        <select
                                            v-model="memberForm.role"
                                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                        >
                                            <option value="member">Membre</option>
                                            <option value="moderator">Modérateur</option>
                                        </select>
                                    </div>
                                    <button
                                        @click.prevent="addMember"
                                        :disabled="!searchQuery && !selectedUser"
                                        class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md sm:w-auto hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                    >
                                        {{ selectedUser ? 'Ajouter l\'utilisateur' : 'Ajouter l\'invité' }}
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div v-for="member in members" :key="member.id" class="flex flex-col justify-between p-4 space-y-2 bg-gray-700 rounded-lg sm:flex-row sm:items-center sm:space-y-0">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-100">{{ member.name }}</p>
                                    </div>
                                    <div class="flex flex-col items-start space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': member.role === 'owner',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': member.role === 'moderator',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': !member.role || member.role === 'member'
                                            }"
                                        >
                                            {{ member.role === 'owner' ? 'Propriétaire' : member.role === 'moderator' ? 'Modérateur' : 'Membre' }}
                                        </span>
                                        <div v-if="canManageMembers && member.role !== 'owner'" class="flex flex-col items-start space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                                            <select
                                                :value="member.role || 'member'"
                                                v-if="member.type === 'user'"
                                                @change="updateMemberRole(member.id, $event.target.value)"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm sm:w-auto focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                            >
                                                <option value="member">Membre</option>
                                                <option value="moderator">Modérateur</option>
                                            </select>
                                            <button
                                                @click="removeMember(member.id, member.type)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Retirer
                                            </button>
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
