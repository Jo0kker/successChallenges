<template>
    <div class="space-y-4">
        <div v-for="challenge in challenges" :key="challenge.id" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100" autocomplete="off">{{ challenge.name }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400" autocomplete="off">
                        Mise : {{ challenge.bet_amount }} k
                    </p>
                </div>
                <div v-if="challenge.failed_by" class="text-sm text-red-600 dark:text-red-400" autocomplete="off">
                    Échoué par {{ challenge.failed_by.name }}
                </div>
            </div>

            <div class="mt-2">
                <div class="text-sm text-gray-500 dark:text-gray-400" autocomplete="off">
                    Participants :
                    <span v-for="(participant, index) in challenge.participants" :key="participant.id" autocomplete="off">
                        {{ participant.name }}{{ index < challenge.participants.length - 1 ? ', ' : '' }}
                    </span>
                </div>
            </div>

            <div v-if="canManage && !challenge.failed_by" class="mt-4">
                <button
                    @click="$emit('mark-failed', challenge)"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    Marquer comme échoué
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    challenges: {
        type: Array,
        required: true
    },
    canManage: {
        type: Boolean,
        default: false
    }
});

defineEmits(['mark-failed']);
</script>
