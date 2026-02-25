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
          <svg aria-hidden="true" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
          </svg>
        </div>

        <div v-if="ready" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Job ID</label><div class="text-sm font-medium text-strong">{{ job.id }}</div></div>
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Queue</label><div class="text-sm text-default">{{ job.queue }}</div></div>
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Pushed At</label><div class="text-sm text-default">{{ readableTimestamp(job.payload.pushedAt) }}</div></div>
            <div v-if="delayedAt"><label class="text-xssm font-medium text-weak tracking-wider block">Delayed Until</label><div class="text-sm text-default">{{ delayedAt }}</div></div>
          </div>
          <div class="space-y-4">
            <div><label class="text-xssm font-medium text-weak tracking-wider block">Status</label><div class="text-sm font-medium capitalize" :class="job.status === 'completed' ? 'text-success' : 'text-default'">{{ job.status }}</div></div>
            <div v-if="job.completed_at"><label class="text-xssm font-medium text-weak tracking-wider block">Completed At</label><div class="text-sm text-default">{{ readableTimestamp(job.completed_at) }}</div></div>
            <div v-if="job.payload.tags && job.payload.tags.length">
              <label class="text-xssm font-medium text-weak tracking-wider block">Tags</label>
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