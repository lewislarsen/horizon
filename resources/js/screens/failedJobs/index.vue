<script type="text/ecmascript-6">
export default {
  data() {
    return {
      tagSearchPhrase: '',
      searchTimeout: null,
      ready: false,
      loadingNewEntries: false,
      hasNewEntries: false,
      page: 1,
      perPage: 50,
      totalPages: 1,
      jobs: [],
      retryingJobs: [],
    };
  },

  mounted() {
    document.title = "Horizon - Failed Jobs";
    this.loadJobs();
  },

  watch: {
    '$route'() {
      this.page = 1;
      this.loadJobs();
    },

    tagSearchPhrase() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.loadJobs();
        this.refreshJobsPeriodically();
      }, 500);
    },

    '$root.autoLoadsNewEntries'(autoLoadsNewEntries) {
      if (autoLoadsNewEntries && this.hasNewEntries) {
        this.hasNewEntries = false;
      }
    }
  },

  methods: {
    loadJobs(starting = 0, refreshing = false) {
      if (!refreshing) { this.ready = false; }

      this.$http.get(Horizon.basePath + '/api/jobs/failed' + (this.tagSearchPhrase ? '?tag=' + encodeURIComponent(this.tagSearchPhrase) : '') + (this.tagSearchPhrase ? '&starting_at=' + starting : '?starting_at=' + starting) + '&limit=' + this.perPage)
          .then(response => {
            if (!this.$root.autoLoadsNewEntries && refreshing && this.jobs.length && response.data.jobs[0]?.id !== this.jobs[0]?.id) {
              this.hasNewEntries = true;
            } else {
              this.jobs = response.data.jobs;
              this.totalPages = Math.ceil(response.data.total / this.perPage);
            }
            this.ready = true;
          });
    },

    loadNewEntries() {
      this.jobs = [];
      this.loadJobs(0, false);
      this.hasNewEntries = false;
    },

    refreshJobsPeriodically() {
      if (this.page != 1) return;
      this.loadJobs(0, true);
    },

    previous() {
      this.loadJobs((this.page - 2) * this.perPage);
      this.page -= 1;
      this.hasNewEntries = false;
    },

    next() {
      this.loadJobs(this.page * this.perPage);
      this.page += 1;
      this.hasNewEntries = false;
    },

    retry(id) {
      if (this.isRetrying(id)) return;
      this.retryingJobs.push(id);
      this.$http.post(Horizon.basePath + '/api/jobs/failed/retry/' + id)
          .then(() => {
            setTimeout(() => { this.retryingJobs = _.without(this.retryingJobs, id); }, 5000);
          });
    },

    isRetrying(id) { return _.includes(this.retryingJobs, id); },
    hasCompleted(job) { return _.find(job.retried_by, retry => retry.status === 'completed'); }
  }
}
</script>

<template>
  <div>
    <poll @poll="refreshJobsPeriodically" />

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-medium text-strong">Failed Jobs</h3>

        <div class="relative w-64 flex items-stretch border hover:border-hovered border-base rounded-md focus-within:ring-3 focus-within:ring-brand-weak">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="size-4 text-icon-alpha" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
          </div>
          <input
              type="text"
              v-model="tagSearchPhrase"
              placeholder="Search by tag..."
              class="block w-full bg-transparent pl-9 pr-3 outline-none py-1.5 text-sm text-strong placeholder:text-weak"
          />
        </div>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
        <div v-if="hasNewEntries && !this.$root.autoLoadsNewEntries" class="bg-brand-weak border-b border-brand text-center py-2 px-4">
          <small class="text-default">
            <a href="#" @click.prevent="loadNewEntries" class="text-link font-medium">Load New Entries</a>
          </small>
        </div>

        <div v-if="!ready" class="flex items-center justify-center p-10">
          <svg aria-hidden="true" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
          </svg>
          <span class="text-default">Loading jobs...</span>
        </div>

        <div v-if="ready && jobs.length == 0" class="p-10 text-center text-weak">
          There aren't any failed jobs matching your criteria.
        </div>

        <div v-if="ready && jobs.length > 0" class="divide-default divide-y">
          <div v-for="job in jobs" :key="job.id" class="hover:bg-card-hover px-5 py-3 flex items-center justify-between gap-4 transition-colors">
            <div class="flex-1 min-w-0">
              <router-link :to="{ name: 'failed-jobs-preview', params: { jobId: job.id }}" class="text-sm font-medium text-strong truncate block hover:text-brand">
                {{ jobBaseName(job.name) }}
              </router-link>
              <div class="mt-1 flex items-center gap-2 text-xssm text-weak">
                <span>Queue: {{ job.queue }}</span>
                <span>• Failed {{ readableTimestamp(job.failed_at) }}</span>
              </div>
            </div>

            <div class="flex items-center gap-6 shrink-0">
              <div class="text-right hidden md:block">
                <div class="text-xssm font-medium text-weak uppercase tracking-wider">Runtime</div>
                <div class="text-sm text-default">{{ job.failed_at && job.reserved_at ? (job.failed_at - job.reserved_at).toFixed(2) + 's' : '-' }}</div>
              </div>

              <button @click.prevent="retry(job.id)" v-if="!hasCompleted(job)" class="group p-2 text-icon-alpha hover:bg-weak rounded-lg transition-all focus:outline-none" title="Retry Job">
                <svg class="size-5 group-hover:text-brand" :class="{'animate-spin': isRetrying(job.id)}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                </svg>
              </button>
              <div v-else class="p-2">
                <svg class="size-5 text-success" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div v-if="ready && jobs.length" class="flex items-center justify-between border-t border-default px-5 py-3 bg-weak">
          <button @click="previous" :disabled="page === 1" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs hover:bg-hovered transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
          <span class="text-sm text-default font-medium">Page {{ page }} of {{ totalPages }}</span>
          <button @click="next" :disabled="page >= totalPages" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs hover:bg-hovered transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>