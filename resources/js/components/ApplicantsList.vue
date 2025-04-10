<template>
    <div>
        <table class="min-w-full bg-white shadow rounded overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
            <tr>
                <th class="py-3 px-4 text-left">Name</th>
                <th class="py-3 px-4 text-left">Email</th>
                <th class="py-3 px-4 text-left">Status</th>
                <th class="py-3 px-4">Actions</th>
            </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
            <tr v-for="applicant in applicants" :key="applicant.id" class="border-b border-gray-200 hover:bg-gray-50">
                <td class="py-3 px-4">
                    {{ applicant.title ? (applicant.title + ' ') : '' }}{{ applicant.first_name }} {{ applicant.last_name }}
                </td>
                <td class="py-3 px-4">{{ applicant.email }}</td>
                <td class="py-3 px-4">
            <span :class="statusLabelClass(applicant.status)">
              {{ applicant.status }}
            </span>
                </td>
                <td class="py-3 px-4 text-center">
                    <a :href="`/applicants/${applicant.id}`"
                       class="text-blue-600 hover:text-blue-800 font-semibold">
                        View Details
                    </a>
                </td>
            </tr>
            <tr v-if="applicants.length === 0">
                <td colspan="4" class="py-4 px-4 text-center text-gray-500">No applicants found.</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: 'ApplicantsList',
    props: {
        initialApplicants: { type: Array, required: true }
    },
    data() {
        return {
            applicants: this.initialApplicants
        }
    },
    mounted() {
        console.log('Applicants:', this.applicants);
    },
    methods: {
        // Returns TailwindCSS classes to style the status text (optional styling)
        statusLabelClass(status) {
            switch(status) {
                case 'hired':
                case 'background check passed':
                    return 'text-green-600 font-medium';
                case 'background check failed':
                    return 'text-red-600 font-medium';
                case 'background check':
                case 'background check review':
                    return 'text-yellow-600 font-medium';
                case 'background check in progress':
                    return 'text-blue-600 font-medium';
                default:
                    return 'text-gray-800';
            }
        }
    }
}
</script>
