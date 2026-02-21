<script type="text/ecmascript-6">
import phpunserialize from 'phpunserialize'
import StackTrace from '@/components/Stacktrace.vue'

export default {
  components: { 'stack-trace': StackTrace },

  data() {
    return { ready: false, retrying: false, job: {} };
  },

  mounted() {
    this.loadFailedJob(this.$route.params.jobId);
    document.title = "Horizon - Failed Jobs";
  },

  methods: {
    loadFailedJob(id) {
      this.ready = false;
      this.$http.get(Horizon.basePath + '/api/jobs/failed/' + id)
          .then(response => {
            this.job = response.data;
            this.ready = true;
          });
    },

    reloadRetries() {
      this.$http.get(Horizon.basePath + '/api/jobs/failed/' + this.$route.params.jobId)
          .then(response => { this.job.retried_by = response.data.retried_by; });
    },

    retry(id) {
      if (this.retrying) return;
      this.retrying = true;
      this.$http.post(Horizon.basePath + '/api/jobs/retry/' + id)
          .then(() => {
            setTimeout(() => {
              this.reloadRetries();
              this.retrying = false;
            }, 3000);
          });
    },

    prettyPrintJob(data) {
      try {
        return data.command && !data.command.includes('CallQueuedClosure')
            ? phpunserialize(data.command) : data;
      } catch (err) {
        return data;
      }
    }
  }
}
</script>

<template>
  <div class="space-y-6">
    <poll @poll="reloadRetries" :immediate="false" />

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-medium text-strong">{{ ready ? jobBaseName(job.name) : 'Job Details' }}</h3>
        <button v-if="ready" @click.prevent="retry(job.id)" :disabled="retrying" class="flex items-center gap-2 rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-hovered disabled:opacity-50">
          <svg class="size-4" :class="{ 'animate-spin': retrying }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
          </svg>
          Retry Job
        </button>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <div v-if="!ready" class="flex justify-center py-4">
          <svg class="size-6 animate-spin text-icon-alpha" viewBox="0 0 20 20" fill="currentColor">
            <path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"/>
          </svg>
        </div>

        <div v-if="ready" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">ID</label><div class="text-sm font-medium text-strong">{{ job.id }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Connection</label><div class="text-sm text-default">{{ job.connection }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Queue</label><div class="text-sm text-default">{{ job.queue }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Failed At</label><div class="text-sm text-default">{{ readableTimestamp(job.failed_at) }}</div></div>
          </div>
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Attempts</label><div class="text-sm text-default">{{ job.payload.attempts }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Retries</label><div class="text-sm text-default">{{ job.retried_by.length }}</div></div>
            <div v-if="job.payload.retry_of">
              <label class="text-xssm font-medium text-weak uppercase tracking-wider block">Retry of ID</label>
              <router-link :to="{ name: 'failed-jobs-preview', params: { jobId: job.payload.retry_of }}" class="text-sm text-link">{{ job.payload.retry_of }}</router-link>
            </div>
            <div v-if="prettyPrintJob(job.payload.data).batchId">
              <label class="text-xssm font-medium text-weak uppercase tracking-wider block">Batch</label>
              <router-link :to="{ name: 'batches-preview', params: { batchId: prettyPrintJob(job.payload.data).batchId }}" class="text-sm text-link">{{ prettyPrintJob(job.payload.data).batchId }}</router-link>
            </div>
            <div v-if="job.payload.tags && job.payload.tags.length">
              <label class="text-xssm font-medium text-weak uppercase tracking-wider block">Tags</label>
              <div class="flex flex-wrap gap-1 mt-1">
                <span v-for="tag in job.payload.tags" :key="tag" class="px-2 py-0.5 bg-weak border border-base rounded text-xs text-default">{{ tag }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="ready" class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4"><h3 class="font-medium text-strong">Exception</h3></div>
      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <stack-trace :trace="job.exception.split('\n')" />
      </div>
    </div>

    <div v-if="ready" class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4"><h3 class="font-medium text-strong">Exception Context</h3></div>
      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <vue-json-pretty :data="prettyPrintJob(job.context)" />
      </div>
    </div>

    <div v-if="ready" class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4"><h3 class="font-medium text-strong">Data</h3></div>
      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <vue-json-pretty :data="prettyPrintJob(job.payload.data)" />
      </div>
    </div>

    <div v-if="ready && job.retried_by.length" class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4"><h3 class="font-medium text-strong">Recent Retries</h3></div>
      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden divide-y divide-default">
        <div v-for="retry in job.retried_by" :key="retry.id" class="px-5 py-3 flex items-center justify-between">
          <div>
            <a v-if="retry.status == 'failed'" :href="Horizon.basePath + '/failed/' + retry.id" class="text-sm font-medium text-strong">{{ retry.id }}</a>
            <span v-else class="text-sm font-medium text-strong">{{ retry.id }}</span>
            <div class="text-xssm text-weak mt-0.5">Retried at {{ readableTimestamp(retry.retried_at) }}</div>
          </div>
          <div>
            <span v-if="retry.status == 'completed'" class="text-success text-xssm font-medium uppercase tracking-wider flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              Completed
            </span>
            <span v-if="retry.status == 'reserved' || retry.status == 'pending'" class="text-warning text-xssm font-medium uppercase tracking-wider flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path fill-rule="evenodd" d="M2 10a8 8 0 1116 0 8 8 0 01-16 0zm5-2.25A.75.75 0 017.75 7h.5a.75.75 0 01.75.75v4.5a.75.75 0 01-.75.75h-.5a.75.75 0 01-.75-.75v-4.5zm4 0a.75.75 0 01.75-.75h.5a.75.75 0 01.75.75v4.5a.75.75 0 01-.75.75h-.5a.75.75 0 01-.75-.75v-4.5z" clip-rule="evenodd" />
              </svg>
              Pending
            </span>
            <span v-if="retry.status == 'failed'" class="text-danger text-xssm font-medium uppercase tracking-wider flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
              </svg>
              Failed
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>