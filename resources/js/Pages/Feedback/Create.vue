<template>
    <Head>
        <title>Feedback - Dofus Success Challenge</title>
        <meta name="description" content="Envoyez-nous votre feedback pour améliorer Dofus Success Challenge" />
    </Head>

    <div class="relative flex justify-center min-h-screen bg-gray-100 items-top dark:bg-gray-900 sm:items-center sm:pt-0">
        <!-- Navigation -->
        <div v-if="canLogin" class="z-10 p-6 text-right sm:fixed sm:top-0 sm:right-0">
            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Tableau de bord</Link>

            <template v-else>
                <Link :href="route('login')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Se connecter</Link>

                <Link v-if="canRegister" :href="route('register')" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">S'inscrire</Link>
            </template>
        </div>

        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
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
                        Feedback
                    </h1>
                </div>
                <p class="max-w-2xl mt-4 text-xl text-center text-gray-600 dark:text-gray-400">
                    Aidez-nous à améliorer Dofus Success Challenge en partageant vos idées et retours d'expérience !
                </p>
            </div>

            <!-- Form Section -->
            <div class="mt-16">
                <div class="max-w-3xl mx-auto">
                    <div v-if="page.props.flash && page.props.flash.success" class="mb-6 flash-message">
                        <div class="rounded bg-green-100 text-green-800 px-4 py-3">
                            {{ page.props.flash.success }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <form @submit.prevent="submit" class="space-y-6">
                                <div>
                                    <InputLabel for="type" value="Type de feedback" />
                                    <SelectInput
                                        id="type"
                                        v-model="form.type"
                                        class="mt-1 block w-full"
                                        required
                                    >
                                        <option value="bug">Bug</option>
                                        <option value="feature">Nouvelle fonctionnalité</option>
                                        <option value="feedback">Retour d'expérience</option>
                                        <option value="other">Autre</option>
                                    </SelectInput>
                                    <InputError :message="form.errors.type" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="title" value="Titre" />
                                    <TextInput
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.title" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="content" value="Description" />
                                    <TextArea
                                        id="content"
                                        v-model="form.content"
                                        class="mt-1 block w-full"
                                        rows="5"
                                        required
                                    />
                                    <InputError :message="form.errors.content" class="mt-2" />
                                </div>

                                <div v-if="!page.props.auth?.user">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="name" value="Nom (optionnel)" />
                                            <TextInput
                                                id="name"
                                                v-model="form.name"
                                                type="text"
                                                class="mt-1 block w-full"
                                            />
                                            <InputError :message="form.errors.name" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="email" value="Email (optionnel)" />
                                            <TextInput
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                class="mt-1 block w-full"
                                            />
                                            <InputError :message="form.errors.email" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end">
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Envoyer
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-center mt-16">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    © {{ new Date().getFullYear() }} Dofus Success Challenge - Tous droits réservés
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';

const page = usePage();
const canLogin = page.props.canLogin;
const canRegister = page.props.canRegister;

const form = useForm({
    type: '',
    title: '',
    content: '',
    email: '',
    name: '',
});

const submit = () => {
    form.post(route('feedback.store'), {
        onSuccess: () => {
            form.reset();
            setTimeout(() => {
                const flashMessage = document.querySelector('.flash-message');
                if (flashMessage) {
                    flashMessage.scrollIntoView({ behavior: 'smooth' });
                }
            }, 100);
        },
    });
};
</script>
