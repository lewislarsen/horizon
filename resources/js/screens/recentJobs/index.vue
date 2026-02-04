<script type="text/ecmascript-6">
import JobRow from './job-row.vue';

export default {
  components: {
    JobRow,
  },

  data() {
    return {
      ready: false,
      loadingNewEntries: false,
      hasNewEntries: false,
      page: 1,
      perPage: 50,
      totalPages: 1,
      jobs: []
    };
  },

  mounted() {
    this.updatePageTitle();
    this.loadJobs();
  },

  watch: {
    '$route'() {
      this.updatePageTitle();
      this.page = 1;
      this.loadJobs();
    },

    '$root.autoLoadsNewEntries'(autoLoadsNewEntries) {
      if (autoLoadsNewEntries && this.hasNewEntries) {
        this.hasNewEntries = false;
      }
    }
  },

  methods: {
    updatePageTitle() {
      document.title = "Horizon - " + this.capitalize(this.$route.params.type) + " Jobs";
    },

    loadJobs(starting = -1, refreshing = false) {
      if (!refreshing) { this.ready = false; }

      this.$http.get(Horizon.basePath + '/api/jobs/' + this.$route.params.type + '?starting_at=' + starting + '&limit=' + this.perPage)
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
      this.loadJobs(-1, false);
      this.hasNewEntries = false;
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

    capitalize(s) {
      return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
    }
  }
}
</script>

<template>
  <div>
    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4">
        <h3 class="font-medium text-strong">{{ capitalize($route.params.type) }} Jobs</h3>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
        <div v-if="hasNewEntries && !this.$root.autoLoadsNewEntries" class="bg-brand-weak border-b border-brand text-center py-2 px-4">
          <small class="text-default font-medium">
            <a href="#" @click.prevent="loadNewEntries" class="text-link">Load New Entries</a>
          </small>
        </div>

        <div v-if="!ready" class="flex items-center justify-center p-10">
          <svg class="size-5 animate-spin mr-2 text-icon-alpha" viewBox="0 0 20 20" fill="currentColor"><path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"/></svg>
          <span class="text-default font-medium">Loading jobs...</span>
        </div>

        <div v-if="ready && jobs.length == 0" class="p-10 text-center text-weak">
          There aren't any {{ $route.params.type }} jobs in the system.
        </div>

        <div v-if="ready && jobs.length > 0" class="divide-default divide-y">
          <job-row v-for="job in jobs" :key="job.id" :job="job" />
        </div>

        <div v-if="ready && jobs.length" class="flex items-center justify-between border-t border-default px-5 py-3 bg-weak">
          <button @click="previous" :disabled="page === 1" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50">Previous</button>
          <span class="text-sm text-default font-medium">Page {{ page }} of {{ totalPages }}</span>
          <button @click="next" :disabled="page >= totalPages" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>