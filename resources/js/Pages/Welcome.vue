<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ApplicationMark from '@/Components/ApplicationMark.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head>
        <title>Dofus Success Challenge - Défis de succès entre amis</title>
        <meta name="description" content="Créez et partagez des défis de succès Dofus avec vos amis. Suivez vos progrès, célébrez vos victoires et relevez de nouveaux challenges !" />
        <meta name="keywords" content="Dofus, succès, défis, challenge, jeu, MMORPG, Ankama" />
        <meta property="og:title" content="Dofus Success Challenge - Défis de succès entre amis" />
        <meta property="og:description" content="Créez et partagez des défis de succès Dofus avec vos amis. Suivez vos progrès, célébrez vos victoires et relevez de nouveaux challenges !" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://dofus-success-challenge.com" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Dofus Success Challenge" />
        <meta name="twitter:description" content="Créez et partagez des défis de succès Dofus avec vos amis" />
    </Head>

    <div class="relative flex justify-center min-h-screen bg-gray-100 items-top dark:bg-gray-900 sm:items-center sm:pt-0">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="flex flex-col items-center justify-center pt-8 sm:pt-0">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-lg opacity-75 bg-gradient-to-r from-primary-500 to-secondary-500 blur"></div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-white rounded-lg dark:bg-gray-800">
                            <svg class="w-10 h-10 text-primary-600 dark:text-primary-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                        Dofus Success Challenge
                    </h1>
                </div>
                <p class="max-w-2xl mt-4 text-xl text-center text-gray-600 dark:text-gray-400">
                    Relevez des défis de succès avec vos amis et transformez vos objectifs en compétition amicale !
                </p>
            </div>

            <!-- Features Section -->
            <div class="mt-16">
                <div class="relative max-w-3xl mx-auto">
                    <!-- Ligne de progression verticale -->
                    <div class="absolute top-0 bottom-0 w-2 rounded-full left-[1.75rem] bg-gradient-to-b from-primary-500 via-secondary-500 to-green-500 sm:left-[1.75rem] left-[1.25rem]"></div>

                    <!-- Étapes -->
                    <div class="relative space-y-8 sm:space-y-16">
                        <!-- Étape 1 -->
                        <div class="flex items-center">
                            <div class="z-10 flex items-center justify-center w-12 h-12 bg-white border-4 border-white rounded-full shadow-lg sm:w-16 sm:h-16 aspect-square dark:bg-gray-800 dark:border-gray-800">
                                <span class="text-xl font-bold sm:text-2xl text-primary-600 dark:text-primary-400">1</span>
                            </div>
                            <div class="ml-4 sm:ml-8">
                                <h2 class="text-lg font-semibold text-gray-900 sm:text-xl dark:text-gray-100">Créez votre groupe</h2>
                                <p class="mt-1 text-sm text-gray-600 sm:mt-2 sm:text-base dark:text-gray-400">
                                    Commencez par créer votre groupe et invitez vos amis
                                </p>
                            </div>
                        </div>

                        <!-- Étape 2 -->
                        <div class="flex items-center">
                            <div class="z-10 flex items-center justify-center w-12 h-12 bg-white border-4 border-white rounded-full shadow-lg sm:w-16 sm:h-16 aspect-square dark:bg-gray-800 dark:border-gray-800">
                                <span class="text-xl font-bold sm:text-2xl text-secondary-600 dark:text-secondary-400">2</span>
                            </div>
                            <div class="ml-4 sm:ml-8">
                                <h2 class="text-lg font-semibold text-gray-900 sm:text-xl dark:text-gray-100">Lancez une saison</h2>
                                <p class="mt-1 text-sm text-gray-600 sm:mt-2 sm:text-base dark:text-gray-400">
                                    Créez une nouvelle saison pour organiser vos défis
                                </p>
                            </div>
                        </div>

                        <!-- Étape 3 -->
                        <div class="flex items-center">
                            <div class="z-10 flex items-center justify-center w-12 h-12 bg-white border-4 border-white rounded-full shadow-lg sm:w-16 sm:h-16 aspect-square dark:bg-gray-800 dark:border-gray-800">
                                <span class="text-xl font-bold text-green-600 sm:text-2xl dark:text-green-400">3</span>
                            </div>
                            <div class="ml-4 sm:ml-8">
                                <h2 class="text-lg font-semibold text-gray-900 sm:text-xl dark:text-gray-100">Proposez des défis</h2>
                                <p class="mt-1 text-sm text-gray-600 sm:mt-2 sm:text-base dark:text-gray-400">
                                    Ajoutez des défis et définissez les enjeux
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-center mt-16">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    © 2025 Dofus Success Challenge - Tous droits réservés
                </p>
            </div>

            <div v-if="canLogin" class="z-10 p-6 text-right sm:fixed sm:top-0 sm:right-0">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Tableau de bord</Link>

                <template v-else>
                    <Link :href="route('login')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Se connecter</Link>

                    <Link v-if="canRegister" :href="route('register')" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">S'inscrire</Link>
                    <Link :href="route('feedback.create')" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Feedback</Link>
                </template>
            </div>
        </div>
    </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
