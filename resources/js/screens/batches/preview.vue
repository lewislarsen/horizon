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
        <div v-if="!ready" class="flex justify-center">
          <svg aria-hidden="true" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
          </svg>
        </div>

        <div v-if="ready" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Batch ID</label><div class="text-sm font-medium text-strong">{{ batch.id }}</div></div>
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Created</label><div class="text-sm text-default">{{ formatDateIso(batch.createdAt).format('YYYY-MM-DD HH:mm:ss') }}</div></div>
            <div v-if="batch.finishedAt"><label class="text-xssm font-medium text-weak tracking-wider block">Finished</label><div class="text-sm text-default">{{ formatDateIso(batch.finishedAt).format('YYYY-MM-DD HH:mm:ss') }}</div></div>
          </div>
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Progress</label><div class="text-sm font-medium text-strong">{{ batch.progress }}% ({{ batch.processedJobs }} / {{ batch.totalJobs }} jobs)</div></div>
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Queue</label><div class="text-sm text-default">{{ batch.options.queue || '-' }}</div></div>
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Failed Jobs</label><div class="text-sm" :class="batch.failedJobs > 0 ? 'text-danger font-medium' : 'text-default'">{{ batch.failedJobs }}</div></div>
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