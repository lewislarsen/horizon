<script type="text/ecmascript-6">
export default {
  data() {
    return {
      ready: false,
      loadingNewEntries: false,
      hasNewEntries: false,
      page: 1,
      previousFirstId: null,
      batches: [],
    };
  },
  mounted() {
    document.title = "Horizon - Batches";
    this.loadBatches();
  },
  watch: {
    '$route'() { this.page = 1; this.loadBatches(); },
    '$root.autoLoadsNewEntries'(autoLoadsNewEntries) {
      if (autoLoadsNewEntries && this.hasNewEntries) { this.hasNewEntries = false; }
    }
  },
  methods: {
    loadBatches(beforeId = '', refreshing = false) {
      if (!refreshing) { this.ready = false; }
      this.$http.get(Horizon.basePath + '/api/batches?before_id=' + beforeId)
          .then(response => {
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
      this.loadBatches('', false);
      this.hasNewEntries = false;
    },
    refreshBatchesPeriodically() {
      if (this.page != 1) return;
      this.loadBatches('', true);
    },
    previous() {
      this.loadBatches(this.page == 2 ? '' : this.previousFirstId);
      this.page -= 1;
      this.hasNewEntries = false;
    },
    next() {
      this.previousFirstId = this.batches[0]?.id + '0';
      this.loadBatches(this.batches.slice(-1)[0]?.id);
      this.page += 1;
      this.hasNewEntries = false;
    }
  }
}
</script>

<template>
  <div>
    <poll @poll="refreshBatchesPeriodically" />

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-medium text-strong">Batches</h3>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
        <div v-if="hasNewEntries && !this.$root.autoLoadsNewEntries" class="bg-brand-weak border-b border-brand text-center py-2 px-4">
          <small class="text-default">
            <a href="#" @click.prevent="loadNewEntries" class="text-link font-medium">Load New Entries</a>
          </small>
        </div>

        <div v-if="!ready" class="flex items-center justify-center p-5">
          <svg class="size-5 animate-spin mr-2 text-icon-alpha" viewBox="0 0 20 20" fill="currentColor"><path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"/></svg>
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

        <div v-if="ready && batches.length" class="flex items-center justify-between border-t border-default px-5 py-3 bg-weak">
          <button @click="previous" :disabled="page === 1" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50">Previous</button>
          <span class="text-sm text-default">Page {{ page }}</span>
          <button @click="next" :disabled="batches.length < 50" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>