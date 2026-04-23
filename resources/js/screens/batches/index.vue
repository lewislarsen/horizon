<script type="text/ecmascript-6">
export default {
  data() {
    return {
      ready: false,
      loadingNewEntries: false,
      hasNewEntries: false,
      page: parseInt(this.$route.query.page) || 1,
      previousFirstId: this.$route.query.previous_first_id || null,
      batches: [],
      searchQuery: this.$route.query.query || '',
      searchTimeout: null,
    };
  },
  mounted() {
    document.title = "Horizon - Batches";
    this.loadBatches(this.$route.query.before_id || '');
  },
  watch: {
    '$route'() { this.page = 1; this.loadBatches(); },
    '$root.autoLoadsNewEntries'(autoLoadsNewEntries) {
      if (autoLoadsNewEntries && this.hasNewEntries) { this.hasNewEntries = false; }
    },
    searchQuery(newVal, oldVal) {
      if (!oldVal) return;

      clearTimeout(this.searchTimeout);

      this.searchTimeout = setTimeout(() => {
        this.page = 1;
        this.previousFirstId = null;

        this.loadBatches();
        this.updateQueryParams();
      }, 500);
    },
  },
  methods: {
    loadBatches(beforeId = '', refreshing = false) {
      if (!refreshing) { this.ready = false; }

      var searchQuery = this.searchQuery ? 'query=' + encodeURIComponent(this.searchQuery) + '&' : '';

      this.$http.get(Horizon.basePath + '/api/batches?' + searchQuery + 'before_id=' + beforeId)
          .then(response => {
            if (!this.$root.autoLoadsNewEntries && refreshing && !response.data.batches.length) {
              this.ready = true;
              return;
            }

            if (!this.$root.autoLoadsNewEntries && refreshing && this.batches.length && response.data.batches[0]?.id !== this.batches[0]?.id) {
              this.hasNewEntries = true;
            } else {
              this.batches = response.data.batches;
            }
            this.ready = true;
          });
    },
    loadNewEntries() {
      this.batches = [];
      this.page = 1;
      this.previousFirstId = null;
      this.loadBatches('', false);
      this.hasNewEntries = false;
      this.updateQueryParams();
    },
    refreshBatchesPeriodically() {
      if (this.page != 1) return;
      if (this.searchQuery) return;
      this.loadBatches('', true);
    },
    previous() {
      var beforeId = this.page == 2 ? '' : this.previousFirstId;
      this.loadBatches(beforeId);
      this.page -= 1;
      this.hasNewEntries = false;
      this.updateQueryParams(beforeId);
    },
    next() {
      this.previousFirstId = this.batches[0]?.id + '0';
      var beforeId = this.batches.slice(-1)[0]?.id;
      this.loadBatches(beforeId);
      this.page += 1;
      this.hasNewEntries = false;
      this.updateQueryParams(beforeId);
    },
    updateQueryParams(beforeId) {
      var query = {};

      if (this.searchQuery) query.query = this.searchQuery;
      if (this.page > 1) query.page = this.page;
      if (beforeId) query.before_id = beforeId;
      if (this.previousFirstId && this.page > 1) query.previous_first_id = this.previousFirstId;

      this.$router.replace({ query }).catch(() => {});
    },
  }
}
</script>

<template>
  <div>
    <poll @poll="refreshBatchesPeriodically" />

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-medium text-strong">Batches</h3>

        <div class="relative w-64 flex items-stretch border hover:border-hovered border-base rounded-md focus-within:ring-3 focus-within:ring-brand-weak">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="size-4 text-icon-alpha" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
          </div>
          <input
              v-model="searchQuery"
              type="text"
              class="block w-full bg-transparent pl-9 pr-3 outline-none py-1.5 text-sm text-strong placeholder:text-weak"
              placeholder="Search batches..."
          />
        </div>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
        <div v-if="hasNewEntries && !this.$root.autoLoadsNewEntries" class="bg-brand-weak border-b border-brand text-center py-2 px-4">
          <small class="text-default">
            <a href="#" @click.prevent="loadNewEntries" class="text-link font-medium">Load New Entries</a>
          </small>
        </div>

        <div v-if="!ready" class="flex items-center justify-center p-5">
          <svg aria-hidden="true" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
          </svg>
          <span class="text-default">Loading...</span>
        </div>

        <div v-if="ready && batches.length == 0" class="p-5 text-center text-weak">There aren't any batches.</div>

        <div v-if="ready && batches.length > 0" class="divide-default divide-y">
          <div v-for="batch in batches" :key="batch.id" class="hover:bg-card-hover px-5 py-3 flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
              <router-link :to="{ name: 'batches-preview', params: { batchId: batch.id }}" class="text-sm font-medium text-strong truncate block">
                {{ batch.name || batch.id }}
              </router-link>
              <div class="mt-1 flex items-center gap-2">
                <span v-if="!batch.cancelledAt && batch.failedJobs > 0" class="text-xssm font-medium text-danger">Failures</span>
                <span v-if="!batch.cancelledAt && batch.totalJobs - batch.pendingJobs == batch.totalJobs" class="text-xssm font-medium text-success">Finished</span>
                <span v-if="!batch.cancelledAt && batch.pendingJobs > 0 && !batch.failedJobs" class="text-xssm font-medium text-default">Pending</span>
                <span v-if="batch.cancelledAt" class="text-xssm font-medium text-warning">Cancelled</span>
                <span class="text-xssm text-weak">• {{ batch.totalJobs }} jobs</span>
              </div>
            </div>
            <div class="text-right shrink-0">
              <div class="text-sm font-medium text-strong">{{ batch.progress }}%</div>
              <div class="text-xssm text-weak">{{ formatDateIso(batch.createdAt).format("YYYY-MM-DD HH:mm:ss") }}</div>
            </div>
          </div>
        </div>

        <div v-if="ready && batches.length" class="flex items-center justify-between border-none px-5 py-3 bg-weak">
          <button @click="previous" :disabled="page === 1" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50 disabled:cursor-not-allowed hover:enabled:bg-hovered active:enabled:bg-pressed cursor-pointer transition-colors">Previous</button>
          <span class="text-sm text-default">Page {{ page }}</span>
          <button @click="next" :disabled="batches.length < 50" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50 disabled:cursor-not-allowed hover:enabled:bg-hovered active:enabled:bg-pressed cursor-pointer transition-colors">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>
