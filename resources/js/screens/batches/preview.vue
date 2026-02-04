<script type="text/ecmascript-6">
export default {
  data() {
    return { ready: false, retrying: false, batch: {}, failedJobs : [] };
  },
  mounted() {
    document.title = "Horizon - Batches";
    this.loadBatch();
  },
  methods: {
    loadBatch(reload = true) {
      if (reload) this.ready = false;
      this.$http.get(Horizon.basePath + '/api/batches/' + this.$route.params.batchId)
          .then(response => {
            this.batch = response.data.batch;
            this.failedJobs = response.data.failedJobs;
            this.ready = true;
          });
    },
    retry(id) {
      if (this.retrying) return;
      this.retrying = true;
      this.$http.post(Horizon.basePath + '/api/batches/retry/' + id)
          .then(() => {
            setTimeout(() => { this.loadBatch(false); this.retrying = false; }, 3000);
          });
    },
  }
}
</script>

<template>
  <div class="space-y-6">
    <poll @poll="loadBatch(false)" />

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-medium text-strong">{{ ready ? (batch.name || batch.id) : 'Batch Preview' }}</h3>
        <button v-if="ready && failedJobs.length > 0" @click.prevent="retry(batch.id)" class="flex items-center gap-2 rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-hovered disabled:opacity-50">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" :class="{ 'animate-spin': retrying }">
            <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
          </svg>
          Retry Failed Jobs
        </button>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <div v-if="!ready" class="flex justify-center"><svg class="size-6 animate-spin text-icon-alpha" viewBox="0 0 20 20" fill="currentColor"><path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"/></svg></div>

        <div v-if="ready" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Batch ID</label><div class="text-sm font-medium text-strong">{{ batch.id }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Created</label><div class="text-sm text-default">{{ formatDateIso(batch.createdAt).format('YYYY-MM-DD HH:mm:ss') }}</div></div>
            <div v-if="batch.finishedAt"><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Finished</label><div class="text-sm text-default">{{ formatDateIso(batch.finishedAt).format('YYYY-MM-DD HH:mm:ss') }}</div></div>
          </div>
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Progress</label><div class="text-sm font-medium text-strong">{{ batch.progress }}% ({{ batch.processedJobs }} / {{ batch.totalJobs }} jobs)</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Queue</label><div class="text-sm text-default">{{ batch.options.queue || '-' }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Failed Jobs</label><div class="text-sm" :class="batch.failedJobs > 0 ? 'text-danger font-medium' : 'text-default'">{{ batch.failedJobs }}</div></div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="ready && failedJobs.length" class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4"><h3 class="font-medium text-strong">Failed Jobs</h3></div>
      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden divide-y divide-default">
        <div v-for="failedJob in failedJobs" class="px-5 py-3 hover:bg-card-hover flex items-center justify-between">
          <div>
            <router-link :to="{ name: 'failed-jobs-preview', params: { jobId: failedJob.id }}" class="text-sm font-medium text-strong">{{ jobBaseName(failedJob.name) }}</router-link>
            <div class="text-xssm text-weak mt-1">Failed {{ readableTimestamp(failedJob.failed_at) }}</div>
          </div>
          <div class="text-right text-xssm text-weak">Runtime: {{ failedJob.failed_at && failedJob.reserved_at ? (failedJob.failed_at - failedJob.reserved_at).toFixed(2) + 's' : '-' }}</div>
        </div>
      </div>
    </div>
  </div>
</template>