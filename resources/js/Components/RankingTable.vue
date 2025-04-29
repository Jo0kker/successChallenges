<script setup>
defineProps({
    ranking: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Classement</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Position</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Membre</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Points</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Défis participés</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Défis échoués</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        <tr v-for="(member, index) in ranking" :key="member.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                {{ index + 1 }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                {{ member.name }}
                                <span v-if="member.type === 'guest'" class="text-xs text-gray-500">(invité)</span>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap"
                                :class="member.points >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                {{ member.points }} kamas
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                {{ member.participated_challenges }}
                            </td>
                            <td class="px-6 py-4 text-sm text-red-600 whitespace-nowrap dark:text-red-400">
                                {{ member.failed_challenges }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
