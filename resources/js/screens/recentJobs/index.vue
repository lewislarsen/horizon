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
        <div v-if="hasNewEntries && !this.$root.autoLoadsNewEntries" class="border-b border-default bg-weak/70 px-5 py-3">
          <div class="flex items-center justify-center">
            <button
                type="button"
                @click.prevent="loadNewEntries"
                class="cursor-pointer rounded-md border border-base bg-default px-3 py-1.5 text-sm font-medium text-strong shadow-xs transition-colors hover:bg-hovered active:bg-pressed"
            >
              Load New Entries
            </button>
          </div>
        </div>

        <div v-if="!ready" class="flex items-center justify-center p-10">
          <svg aria-hidden="true" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
          </svg>
          <span class="text-default font-medium">Loading jobs...</span>
        </div>

        <div v-if="ready && jobs.length == 0" class="p-10 text-center text-weak">
          There aren't any {{ $route.params.type }} jobs in the system.
        </div>

        <div v-if="ready && jobs.length > 0" class="divide-default divide-y">
          <job-row v-for="job in jobs" :key="job.id" :job="job" />
        </div>

        <div v-if="ready && jobs.length" class="flex items-center justify-between border-none px-5 py-3 bg-weak">
          <button @click="previous" :disabled="page === 1" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50 disabled:cursor-not-allowed hover:enabled:bg-hovered active:enabled:bg-pressed cursor-pointer transition-colors">Previous</button>
          <span class="text-sm text-default font-medium">Page {{ page }} of {{ totalPages }}</span>
          <button @click="next" :disabled="page >= totalPages" class="rounded-md border py-1.5 px-3 text-sm font-medium bg-default border-base text-strong shadow-xs disabled:opacity-50 disabled:cursor-not-allowed hover:enabled:bg-hovered active:enabled:bg-pressed cursor-pointer transition-colors">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>
