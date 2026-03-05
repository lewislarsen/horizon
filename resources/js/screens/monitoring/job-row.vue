<script type="text/ecmascript-6">
import phpunserialize from 'phpunserialize';
import moment from 'moment-timezone';

export default {
  props: {
    job: {
      type: Object,
      required: true
    },
    type: {
      type: String,
      default: 'jobs'
    }
  },

  computed: {
    /**
     * Unserialize the job command payload.
     */
    unserialized() {
      try {
        return phpunserialize(this.job.payload.data.command);
      } catch (err) {
        return null;
      }
    },

    /**
     * Calculate the delay time from now.
     */
    delayed() {
      if (this.unserialized && this.unserialized.delay) {
        return moment.tz(
            this.unserialized.delay.date,
            this.unserialized.delay.timezone
        ).fromNow(true);
      }

      if (this.job.delay > 0) {
        return moment.duration(this.job.delay, 'seconds').humanize();
      }

      return null;
    },
  },

  methods: {
    /**
     * Get the base name of the job class.
     */
    jobBaseName(name) {
      return name ? name.split('\\').pop() : 'Unknown Job';
    },

    /**
     * Format the given timestamp into a readable date.
     */
    readableTimestamp(timestamp) {
      return moment.unix(timestamp).format('YYYY-MM-DD HH:mm:ss');
    }
  }
}
</script>

<template>
  <tr class="hover:bg-card-hover transition-colors">
    <td class="px-5 py-4 whitespace-nowrap">
      <div class="flex flex-col">
        <div class="flex items-center gap-2">
          <router-link
              :title="job.name"
              :to="{ name: 'job-preview', params: { jobId: job.id, type: type || $parent.type }}"
              class="text-link hover:text-link-hovered focus-visible:text-link-pressed font-medium text-sm"
          >
            {{ jobBaseName(job.name) }}
          </router-link>

          <span
              v-if="delayed && (job.status === 'reserved' || job.status === 'pending')"
              :title="`Delayed for ${delayed}`"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-weak text-default border border-base"
          >
            Delayed
          </span>
        </div>

        <div class="mt-1 text-xs text-weak">
          <span>Queue: {{ job.queue }}</span>
          <span v-if="job.payload.tags && job.payload.tags.length">
            <span class="mx-1">|</span>
            <span>Tags: {{ job.payload.tags.slice(0, 3).join(', ') }}</span>
            <span v-if="job.payload.tags.length > 3" class="text-weaker">
                +{{ job.payload.tags.length - 3 }} more
            </span>
          </span>
        </div>
      </div>
    </td>

    <td class="px-5 py-4 whitespace-nowrap text-sm text-weak">
      {{ readableTimestamp(job.payload.pushedAt) }}
    </td>

    <td v-if="(type || $parent.type) === 'jobs'" class="px-5 py-4 whitespace-nowrap text-sm text-weak">
      {{ job.completed_at ? readableTimestamp(job.completed_at) : '—' }}
    </td>

    <td v-if="(type || $parent.type) === 'jobs'" class="px-5 py-4 whitespace-nowrap text-sm text-weak text-right">
      <span v-if="job.completed_at" class="font-mono">
          {{ (job.completed_at - job.reserved_at).toFixed(2) }}s
      </span>
      <span v-else>—</span>
    </td>

    <td v-if="(type || $parent.type) === 'failed'" class="px-5 py-4 whitespace-nowrap text-sm text-weak text-right">
      {{ readableTimestamp(job.failed_at) }}
    </td>
  </tr>
</template>
