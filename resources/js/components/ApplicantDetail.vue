<template>
    <div class="bg-white shadow rounded p-6">
        <!-- Applicant basic info -->
        <div class="mb-4">
            <h2 class="text-xl font-semibold">
                {{ applicant.title ? (applicant.title + ' ') : '' }}{{ applicant.first_name }} {{ applicant.last_name }}
            </h2>
            <p class="text-gray-600">{{ applicant.email }}</p>
            <p class="mt-2">
                <strong>Status:</strong>
                <select v-model="currentStatus" @change="onStatusChange"
                        class="border border-gray-300 rounded px-2 py-1">
                    <option v-for="status in statuses" :key="status" :value="status">
                        {{ status }}
                    </option>
                </select>
                <!-- Note: currentStatus will reflect changes, but applicant.status stays as original until confirmed -->
            </p>
        </div>

        <!-- Background check action (if eligible) -->
        <div v-if="applicant.status !== 'background check' && currentStatus === 'background check'" class="mb-4">
            <!-- This section appears when user selects 'background check' -->
            <button @click="confirmAndStartCheck"
                    class="bg-blue-600 text-white font-semibold px-4 py-2 rounded shadow">
                Confirm & Start Background Check
            </button>
            <button @click="cancelBackgroundCheckSelection" class="ml-2 text-gray-600 underline">
                Cancel
            </button>
        </div>

        <!-- Background checks history -->
        <div class="mt-6">
            <h3 class="text-lg font-medium mb-2">Background Checks</h3>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase">
                <tr>
                    <th class="py-2 px-3 text-left">Record ID</th>
                    <th class="py-2 px-3 text-left">Perform URL</th>
                    <th class="py-2 px-3 text-left">Status</th>
                    <th class="py-2 px-3 text-left">Created At</th>
                    <th class="py-2 px-3 text-left">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="check in applicant.background_checks" :key="check.id" class="border-b">
                    <td class="py-2 px-3">{{ check.amiqus_record_id }}</td>
                    <td class="py-2 px-3">
                        <div class="flex items-center space-x-2">
                            <input
                                :value="check.perform_url"
                                readonly
                                :class="[
        'w-full px-2 py-1 text-sm border rounded bg-gray-50 text-gray-600 transition',
        copiedRowId === check.id ? 'border-green-500 ring-2 ring-green-300' : 'border-gray-300'
      ]"
                            />
                            <button
                                @click="copyToClipboard(check)"
                                class="px-2 py-1 text-sm rounded text-white transition duration-200"
                                :class="copiedRowId === check.id ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                            >
                                {{ copiedRowId === check.id ? 'Copied!' : 'Copy' }}
                            </button>
                        </div>
                    </td>
                    <td class="py-2 px-3">{{ check.status }}</td>
                    <td class="py-2 px-3">{{ formatDate(check.created_at) }}</td>
                    <td class="py-2 px-3">
                        <button
                            @click="refreshStatus(check)"
                            :disabled="refreshingCheckId === check.id"
                            class="px-2 py-1 text-sm rounded transition flex items-center space-x-1"
                            :class="refreshingCheckId === check.id
      ? 'bg-gray-400 text-white cursor-wait'
      : 'bg-gray-200 hover:bg-gray-300 text-gray-800'"
                        >
    <span v-if="refreshingCheckId === check.id">
      <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
        <circle
            class="opacity-25"
            cx="12" cy="12" r="10"
            stroke="currentColor" stroke-width="4"
        ></circle>
        <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8z"
        ></path>
      </svg>
    </span>
                            <span>
      {{ refreshingCheckId === check.id ? 'Updating…' : 'Update' }}
    </span>
                        </button>
                    </td>
                </tr>
                <tr v-if="applicant.background_checks.length === 0">
                    <td colspan="3" class="py-2 px-3 text-gray-500">No background checks on record.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Success/Error messages -->
        <div v-if="message" class="mt-4 p-3 rounded" :class="message.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ message.text }}
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    name: 'ApplicantDetail',
    props: {
        applicantData: { type: Object, required: true }
    },
    data() {
        return {
            applicant: this.applicantData,
            currentStatus: this.applicantData.status,
            statuses: [
                'applied', 'stage 1', 'stage 2', 'stage 3',
                'background check', 'background check in progress', 'background check review',
                'background check passed', 'background check failed',
                'hired'
            ],
            message: null,
            copiedRowId: null,
            refreshingCheckId: null
        }
    },
    methods: {
        formatDate(datetime) {
            // Simple date formatter (could use library)
            if (!datetime) return '';
            const date = new Date(datetime);
            return date.toLocaleString();
        },
        copyToClipboard(check) {
            navigator.clipboard.writeText(check.perform_url).then(() => {
                this.copiedRowId = check.id;
                setTimeout(() => {
                    this.copiedRowId = null;
                }, 2000);
            }).catch(err => {
                console.error(err);
                this.message = { type: 'error', text: 'Failed to copy link.' };
            });
        },
        onStatusChange() {
            // If user selected "background check", we don't immediately update via API.
            if (this.currentStatus === 'background check') {
                // UI will show the confirm button; do nothing here yet.
                return;
            }
            // Otherwise, immediately send an update to the server
            this.updateStatus(this.currentStatus);
        },
        updateStatus(newStatus) {
            axios.patch(`/applicants/${this.applicant.id}`, { status: newStatus })
                .then(response => {
                    this.applicant.status = newStatus;
                    this.message = { type: 'success', text: 'Status updated successfully.' };
                })
                .catch(error => {
                    console.error(error);
                    this.message = { type: 'error', text: 'Failed to update status.' };
                    // Revert select to actual status in case of error
                    this.currentStatus = this.applicant.status;
                });
        },
        confirmAndStartCheck() {
            // Ask for confirmation one more time
            if (!confirm(`Are you sure you want to start a background check for ${this.applicant.first_name}?`)) {
                return;
            }
            // Redirect to the start-check route to initiate OAuth flow (which will handle further steps)
            window.location = `/applicants/${this.applicant.id}/start-check`;
            // Note: after redirection, the page will reload (either immediately if token exists, or after OAuth flow)
        },
        cancelBackgroundCheckSelection() {
            // User canceled starting background check, revert the selection
            this.currentStatus = this.applicant.status;
        },
        async refreshStatus(check) {
            this.refreshingCheckId = check.id;
            try {
                const response = await axios.post(`/amiqus/${check.id}/refresh-record`);
                check.status = response.data.status;

                this.message = { type: 'success', text: 'Status updated.' };
            } catch (error) {
                console.error(error);
                this.message = { type: 'error', text: 'Failed to update background check.' };
            } finally {
                setTimeout(() => {
                    this.message = null;
                    this.refreshingCheckId = null;
                }, 1000);
            }
        }
    }
}
</script>
