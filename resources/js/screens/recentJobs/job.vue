<script type="text/ecmascript-6">
import phpunserialize from 'phpunserialize'
import moment from 'moment-timezone';

export default {
  data() {
    return {
      ready: false,
      job: {}
    };
  },

  computed: {
    delayedAt() {
      try {
        const unserialized = phpunserialize(this.job.payload.data.command);
        if (unserialized && unserialized.delay && unserialized.delay.date) {
          return moment.tz(unserialized.delay.date, unserialized.delay.timezone).local().format('YYYY-MM-DD HH:mm:ss');
        } else if (unserialized && unserialized.delay) {
          return this.formatDate(this.job.payload.pushedAt).add(unserialized.delay, 'seconds').local().format('YYYY-MM-DD HH:mm:ss');
        }
      } catch (e) {}
      return null;
    },
  },

  mounted() {
    this.loadJob(this.$route.params.jobId);
    document.title = "Horizon - Job Detail";
  },

  methods: {
    loadJob(id) {
      this.ready = false;
      this.$http.get(Horizon.basePath + '/api/jobs/' + id)
          .then(response => {
            this.job = response.data;
            this.ready = true;
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
    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-medium text-strong">{{ ready ? jobBaseName(job.name) : 'Job Preview' }}</h3>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <div v-if="!ready" class="flex justify-center py-4">
          <svg class="size-6 animate-spin text-icon-alpha" viewBox="0 0 20 20" fill="currentColor"><path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"/></svg>
        </div>

        <div v-if="ready" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Job ID</label><div class="text-sm font-medium text-strong">{{ job.id }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Queue</label><div class="text-sm text-default">{{ job.queue }}</div></div>
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Pushed At</label><div class="text-sm text-default">{{ readableTimestamp(job.payload.pushedAt) }}</div></div>
            <div v-if="delayedAt"><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Delayed Until</label><div class="text-sm text-default">{{ delayedAt }}</div></div>
          </div>
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Status</label><div class="text-sm font-medium capitalize" :class="job.status === 'completed' ? 'text-success' : 'text-default'">{{ job.status }}</div></div>
            <div v-if="job.completed_at"><label class="text-xssm font-medium text-weak uppercase tracking-wider block">Completed At</label><div class="text-sm text-default">{{ readableTimestamp(job.completed_at) }}</div></div>
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
      <div class="px-5 py-4"><h3 class="font-medium text-strong">Payload</h3></div>
      <div class="bg-default shadow-xs-with-border rounded-lg p-5 overflow-auto">
        <pre class="text-sm text-default leading-relaxed">{{ prettyPrintJob(job.payload.data) }}</pre>
      </div>
    </div>
  </div>
</template>