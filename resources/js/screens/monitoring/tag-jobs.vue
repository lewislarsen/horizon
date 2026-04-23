<script type="text/ecmascript-6">
import JobRow from './job-row.vue';

export default {
  props: ['type'],

  /**
   * The component's data.
   */
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

  /**
   * Components
   */
  components: {
    JobRow,
  },

  /**
   * Prepare the component.
   */
  mounted() {
    document.title = "Horizon - Monitoring";

    this.loadJobs(this.$route.params.tag);
  },

  /**
   * Watch these properties for changes.
   */
  watch: {
    '$route'() {
      this.page = 1;

      this.loadJobs(this.$route.params.tag);
    },

    '$root.autoLoadsNewEntries'(autoLoadsNewEntries) {
      if (autoLoadsNewEntries && this.hasNewEntries) {
        this.hasNewEntries = false;
      }
    }
  },

  methods: {
    /**
     * Load the jobs of the given tag.
     */
    loadJobs(tag, starting = 0, refreshing = false) {
      if (!refreshing) {
        this.ready = false;
      }

      const tagParam = this.type == 'failed' ? 'failed:' + tag : tag;

      this.$http.get(Horizon.basePath + '/api/monitoring/' +
          encodeURIComponent(tagParam) + '?starting_at=' + starting +
          '&limit=' + this.perPage + '&tag=' + encodeURIComponent(tagParam))
          .then(response => {
            if (!this.$root.autoLoadsNewEntries &&
                refreshing && this.jobs.length &&
                response.data.jobs[0]?.id !== this.jobs[0]?.id) {
              this.hasNewEntries = true;
            } else {
              this.jobs = response.data.jobs;

              this.totalPages = Math.ceil(response.data.total / this.perPage);
            }

            this.ready = true;
          });
    },

    /**
     * Load new entries.
     */
    loadNewEntries() {
      this.jobs = [];

      this.loadJobs(this.$route.params.tag, 0, false);

      this.hasNewEntries = false;
    },

    /**
     * Poll handler to refresh the jobs at regular intervals.
     */
    refreshJobsPeriodically() {
      if (this.page != 1) {
        return;
      }

      this.loadJobs(this.$route.params.tag, 0, true);
    },

    /**
     * Load the jobs for the previous page.
     */
    previous() {
      this.loadJobs(this.$route.params.tag,
          (this.page - 2) * this.perPage
      );

      this.page -= 1;

      this.hasNewEntries = false;
    },

    /**
     * Load the jobs for the next page.
     */
    next() {
      this.loadJobs(this.$route.params.tag,
          this.page * this.perPage
      );

      this.page += 1;

      this.hasNewEntries = false;
    }
  }
}
</script>

<template>
  <div>
    <poll @poll="refreshJobsPeriodically" />

    <div v-if="!ready" class="flex items-center justify-center bg-default shadow-xs-with-border rounded-lg p-5">
      <svg aria-hidden="true" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
      </svg>
      <span class="text-default">Loading...</span>
    </div>

    <div v-if="ready && jobs.length === 0" class="bg-default shadow-xs-with-border rounded-lg">
      <div class="flex flex-col items-center justify-center p-5 text-center">
        <p class="text-default font-medium">There aren't any jobs for this tag.</p>
      </div>
    </div>

    <div v-if="ready && jobs.length > 0" class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
      <div v-if="hasNewEntries && !this.$root.autoLoadsNewEntries" class="border-b border-default bg-weak/70 px-5 py-3">
        <div class="flex items-center justify-center">
          <button
              v-if="!loadingNewEntries"
              type="button"
              @click.prevent="loadNewEntries"
              class="cursor-pointer rounded-md border border-base bg-default px-3 py-1.5 text-sm font-medium text-strong shadow-xs transition-colors hover:bg-hovered active:bg-pressed"
          >
            Load New Entries
          </button>
          <span v-if="loadingNewEntries" class="text-sm text-default">Loading...</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-default">
          <thead class="bg-weak">
          <tr>
            <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-weak uppercase tracking-wider">Job</th>
            <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-weak uppercase tracking-wider">Queued</th>
            <th v-if="type === 'jobs'" scope="col" class="px-5 py-3 text-left text-xs font-medium text-weak uppercase tracking-wider">Completed</th>
            <th v-if="type === 'jobs'" scope="col" class="px-5 py-3 text-right text-xs font-medium text-weak uppercase tracking-wider">Runtime</th>
            <th v-if="type === 'failed'" scope="col" class="px-5 py-3 text-right text-xs font-medium text-weak uppercase tracking-wider">Failed</th>
          </tr>
          </thead>
          <tbody class="bg-default divide-y divide-default">
          <job-row
              v-for="job in jobs"
              :key="job.id"
              :job="job"
              :type="type"
          />
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-default px-5 py-3 bg-weak">
        <button
            @click="previous"
            :disabled="page === 1"
            class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-default border-base text-strong shadow-xs hover:bg-weak disabled:opacity-50"
        >
          Previous
        </button>
        <span class="text-sm text-default">
            Page {{ page }} of {{ totalPages }}
        </span>
        <button
            @click="next"
            :disabled="page >= totalPages"
            class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-default border-base text-strong shadow-xs hover:bg-weak disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>
