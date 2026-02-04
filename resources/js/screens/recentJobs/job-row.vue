<script type="text/ecmascript-6">
import phpunserialize from 'phpunserialize'
import moment from 'moment-timezone';

export default {
  props: {
    job: {
      type: Object,
      required: true
    }
  },

  computed: {
    unserialized() {
      try {
        return phpunserialize(this.job.payload.data.command);
      } catch(err) {
        //
      }
    },

    delayed() {
      if (this.unserialized && this.unserialized.delay && this.unserialized.delay.date) {
        return moment.tz(this.unserialized.delay.date, this.unserialized.delay.timezone)
            .fromNow(true);
      } else if (this.unserialized && this.unserialized.delay) {
        return this.formatDate(this.job.payload.pushedAt).add(this.unserialized.delay, 'seconds')
            .fromNow(true);
      }

      return null;
    },
  },
}
</script>

<template>
  <div class="hover:bg-card-hover px-5 py-3 transition-colors group">
    <div class="flex items-center justify-between gap-4">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <router-link :to="{ name: 'job-preview', params: { jobId: job.id, type: $route.params.type }}" class="text-sm font-medium text-strong truncate hover:text-brand transition-colors">
            {{ jobBaseName(job.name) }}
          </router-link>

          <span v-if="delayed && (job.status == 'reserved' || job.status == 'pending')"
                class="px-1.5 py-0.5 rounded bg-weak text-xssm text-default border border-base font-medium"
                :title="`Delayed for ${delayed}`">
            Delayed
          </span>
        </div>

        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xssm text-weak">
          <span>Queue: {{ job.queue }}</span>
          <template v-if="job.payload.tags && job.payload.tags.length">
            <span>•</span>
            <span class="truncate">Tags: {{ job.payload.tags.slice(0, 3).join(', ') }}<span v-if="job.payload.tags.length > 3"> +{{ job.payload.tags.length - 3 }} more</span></span>
          </template>
        </div>
      </div>

      <div class="text-right shrink-0">
        <div v-if="$route.params.type == 'pending' || $route.params.type == 'reserved'">
          <div class="text-xssm font-medium text-weak uppercase tracking-wider">Queued</div>
          <div class="text-sm text-default">{{ readableTimestamp(job.payload.pushedAt) }}</div>
        </div>

        <div v-else class="flex gap-6">
          <div class="hidden sm:block">
            <div class="text-xssm font-medium text-weak uppercase tracking-wider">Runtime</div>
            <div class="text-sm text-default">{{ job.completed_at ? (job.completed_at - job.reserved_at).toFixed(2) + 's' : '-' }}</div>
          </div>
          <div>
            <div class="text-xssm font-medium text-weak uppercase tracking-wider">Completed</div>
            <div class="text-sm text-default">{{ readableTimestamp(job.completed_at) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>