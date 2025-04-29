import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useChallenges() {
    const loading = ref(false);

    const deleteChallenge = (challenge, group, season) => {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce défi ?')) {
            loading.value = true;
            router.delete(route('challenges.destroy', [group, season, challenge]), {
                preserveScroll: true,
                onFinish: () => {
                    loading.value = false;
                },
            });
        }
    };

    const markAsFailed = (challenge, group, season) => {
        loading.value = true;
        router.post(route('challenges.fail', [group, season, challenge]), {
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        });
    };

    return {
        loading,
        deleteChallenge,
        markAsFailed
    };
}
