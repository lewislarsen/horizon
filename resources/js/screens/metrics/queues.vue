<script type="text/ecmascript-6">
export default {
  /**
   * The component's data.
   */
  data() {
    return {
      ready: false,
      queues: []
    };
  },

  /**
   * Prepare the component.
   */
  mounted() {
    this.loadQueues();
  },

  methods: {
    /**
     * Load the queues.
     */
    loadQueues() {
      this.ready = false;

      this.$http.get(Horizon.basePath + '/api/metrics/queues')
          .then(response => {
            this.queues = response.data;

            this.ready = true;
          });
    }
  }
}
</script>

<template>
  <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
    <div v-if="!ready" class="flex items-center justify-center p-5">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin">
        <path fill="currentColor" d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
      </svg>
      <span class="text-default">Loading...</span>
    </div>

    <div v-if="ready && queues.length == 0" class="p-5 text-center">
      <span class="text-weak">There aren't any queues.</span>
    </div>

    <div v-if="ready && queues.length > 0" class="divide-default divide-y">
      <div v-for="queue in queues" :key="queue" class="hover:bg-card-hover group">
        <router-link
            :to="{ name: 'metrics-preview', params: { type: 'queues', slug: queue }}"
            class="flex w-full justify-between py-3 px-5 items-center gap-8 text-sm font-medium text-strong"
        >
          <span>{{ queue }}</span>
          <svg class="size-4 text-icon-alpha group-hover:text-icon-strong transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
          </svg>
        </router-link>
      </div>
    </div>
  </div>
</template>