<script type="text/ecmascript-6">
import moment from 'moment';

export default {
  /**
   * The component's data.
   */
  data() {
    return {
      stats: {},
      workers: [],
      workload: [],
      ready: false,
      clearingQueues: {},
    };
  },

  /**
   * Prepare the component.
   */
  mounted() {
    document.title = "Horizon - Dashboard";
  },

  computed: {
    /**
     * Determine the recent job period label.
     */
    recentJobsPeriod() {
      return !this.ready
          ? 'Jobs Past Hour'
          : `Jobs Past ${this.determinePeriod(this.stats.periods.recentJobs)}`;
    },

    /**
     * Determine the recently failed job period label.
     */
    failedJobsPeriod() {
      return !this.ready
          ? 'Failed Jobs Past 7 Days'
          : `Failed Jobs Past ${this.determinePeriod(this.stats.periods.failedJobs)}`;
    },
  },

  methods: {
    /**
     * Load the general stats.
     */
    loadStats() {
      return this.$http.get(Horizon.basePath + '/api/stats')
          .then(response => {
            this.stats = response.data;

            if (Object.values(response.data.wait)[0]) {
              this.stats.max_wait_time = Object.values(response.data.wait)[0];
              this.stats.max_wait_queue = Object.keys(response.data.wait)[0].split(':')[1];
            }
          });
    },

    /**
     * Load the workers.
     */
    loadWorkers() {
      return this.$http.get(Horizon.basePath + '/api/masters')
          .then(response => {
            this.workers = response.data;
          });
    },

    /**
     * Load the workload.
     */
    loadWorkload() {
      return this.$http.get(Horizon.basePath + '/api/workload')
          .then(response => {
            this.workload = response.data;
          });
    },

    /**
     * Refresh the stats periodically.
     */
    refreshStatsPeriodically() {
      Promise.all([
        this.loadStats(),
        this.loadWorkers(),
        this.loadWorkload(),
      ]).then(() => {
        this.ready = true;
      });
    },

    /**
     * Count the total processes.
     */
    countProcesses(processes) {
      return Object.values(processes).reduce((total, value) => total + value, 0).toLocaleString();
    },

    /**
     * Get the display name for the supervisor.
     */
    superVisorDisplayName(supervisor, worker) {
      return supervisor.replace(worker + ':', '');
    },

    /**
     * Format the given time in seconds to a human-readable string.
     */
    humanTime(time) {
      return moment.duration(time, "seconds").humanize().replace(/^(.)/g, function ($1) {
        return $1.toUpperCase();
      });
    },

    /**
     * Determine the period label for the given minutes.
     */
    determinePeriod(minutes) {
      return moment.duration(moment().diff(moment().subtract(minutes, "minutes"))).humanize().replace(/^An?\s/i, '').replace(/^(.)|\s(.)/g, function ($1) {
        return $1.toUpperCase();
      });
    },

    /**
     * Capitalize the first letter of a string.
     */
    upperFirst(string) {
      return string ? string.charAt(0).toUpperCase() + string.slice(1) : '';
    },

    /**
     * Clear a specific queue.
     */
    clearQueue(queueName) {
      if (this.isClearing(queueName)) {
        return;
      }

      this.$root.alert = {
        type: 'confirmation',
        message: 'Are you sure you want to clear all jobs from the "' + queueName + '" queue?',
        confirmationProceed: () => {
          this.clearingQueues[queueName] = true;

          const payload = {
            connection: 'redis',
            queue: queueName,
          };

          this.$http.post(Horizon.basePath + '/api/queues/clear', payload)
            .then(response => {
              const cleared = response.data.cleared;
              this.$root.alert = {
                type: 'success',
                message: 'Successfully cleared ' + cleared + ' jobs from the "' + queueName + '" queue.',
                autoClose: 5000,
              };
              this.loadWorkload();
            })
            .catch(error => {
              this.$root.alert = {
                type: 'error',
                message: 'Failed to clear queue: ' + (error.response?.data?.error || error.message),
                autoClose: 5000,
              };
            })
            .finally(() => {
              delete this.clearingQueues[queueName];
            });
        },
        confirmationCancel: () => {},
      };
    },

    /**
     * Check if a queue is currently being cleared.
     */
    isClearing(queueName) {
      return this.clearingQueues[queueName] === true;
    },
  }
}
</script>

<template>
  <div class="space-y-6 mt-0">
    <poll @poll="refreshStatsPeriodically" :interval="5" />

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex flex-col items-start justify-between gap-x-6 gap-y-3 px-5 py-4 sm:flex-row sm:items-center">
        <div class="space-y-1">
          <h3 class="font-medium text-strong leading-none">Overview</h3>
        </div>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg">
        <div class="divide-none">
          <div class="grid grid-cols-4 sm:grid-cols-2 lg:grid-cols-4 divide-none border-b border-default">
            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm">Jobs Per Minute</small>
              <p class="text-strong text-2xl font-medium mt-2 mb-0">
                {{ stats.jobsPerMinute ? stats.jobsPerMinute.toLocaleString() : 0 }}
              </p>
            </div>

            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm" v-text="recentJobsPeriod"></small>
              <p class="text-strong text-2xl font-medium mt-2 mb-0">
                {{ stats.recentJobs ? stats.recentJobs.toLocaleString() : 0 }}
              </p>
            </div>

            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm" v-text="failedJobsPeriod"></small>
              <p class="text-strong text-2xl font-medium mt-2 mb-0">
                {{ stats.failedJobs ? stats.failedJobs.toLocaleString() : 0 }}
              </p>
            </div>

            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm">Status</small>
              <div class="flex items-center mt-2">
                <svg v-if="stats.status == 'running'" xmlns="http://www.w3.org/2000/svg" class="text-success size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <svg v-if="stats.status == 'paused'" xmlns="http://www.w3.org/2000/svg" class="text-warning size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <svg v-if="stats.status == 'inactive'" xmlns="http://www.w3.org/2000/svg" class="text-danger size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>

                <p class="text-strong text-2xl font-medium mb-0 ml-2 leading-none">
                  {{ {running: 'Active', paused: 'Paused', inactive: 'Inactive'}[stats.status] }}
                </p>
                <small v-if="stats.status == 'running' && stats.pausedMasters > 0" class="text-default text-xssm mb-0 ml-2">({{ stats.pausedMasters }} paused)</small>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-4 sm:grid-cols-2 lg:grid-cols-4 divide-none">
            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm">Total Processes</small>
              <p class="text-strong text-2xl font-medium mt-2 mb-0">
                {{ stats.processes ? stats.processes.toLocaleString() : 0 }}
              </p>
            </div>

            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm">Max Wait Time</small>
              <p class="text-strong text-base font-medium mt-2 mb-0">
                {{ stats.max_wait_time ? humanTime(stats.max_wait_time) : '-' }}
              </p>
              <small class="text-default text-xssm mt-1 block" v-if="stats.max_wait_queue">({{ stats.max_wait_queue }})</small>
            </div>

            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm">Max Runtime</small>
              <p class="text-strong text-2xl font-medium mt-2 mb-0">
                {{ stats.queueWithMaxRuntime ? stats.queueWithMaxRuntime : '-' }}
              </p>
            </div>

            <div class="px-5 py-4">
              <small class="text-weak font-medium text-xssm">Max Throughput</small>
              <p class="text-strong text-2xl font-medium mt-2 mb-0">
                {{ stats.queueWithMaxThroughput ? stats.queueWithMaxThroughput : '-' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl" v-if="workload.length">
      <div class="px-5 py-4">
        <h3 class="font-medium text-strong">Current Workload</h3>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
        <div class="divide-default divide-y">
          <template v-for="queue in workload">
            <div class="flex w-full justify-between py-3 hover:bg-card-hover px-5 items-center gap-8">
              <div class="flex items-center gap-3 flex-1">
                <div class="text-sm font-medium shrink leading-5 w-full" :class="{ 'text-strong': queue.split_queues, 'text-default': !queue.split_queues }">
                  {{ queue.name.replace(/,/g, ', ') }}
                  <p class="mt-1 font-normal text-weak text-xssm">
                    {{ queue.length ? queue.length.toLocaleString() : 0 }} jobs • {{ queue.processes ? queue.processes.toLocaleString() : 0 }} processes
                  </p>
                </div>
              </div>
              <div class="text-weak flex shrink-0 justify-end items-center gap-4">
                <span class="text-sm text-default" :class="{ 'font-medium': queue.split_queues }">
                  {{ humanTime(queue.wait) }} wait
                </span>
                <button 
                  v-if="queue.length > 0"
                  @click.prevent="clearQueue(queue.name)"
                  :disabled="isClearing(queue.name)"
                  class="p-1.5 text-icon-alpha hover:text-danger rounded-md hover:bg-weak transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  title="Clear Queue"
                >
                  <svg v-if="!isClearing(queue.name)" class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.519.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </button>
              </div>
            </div>

            <div v-for="split_queue in queue.split_queues" :key="split_queue.name" class="flex w-full justify-between py-3 hover:bg-card-hover px-5 items-center gap-8 pl-9 border-t border-default/50 bg-weak/30">
              <div class="flex items-center gap-3 flex-1">
                <svg class="inline-block size-4 text-icon-weak mr-2 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm font-medium text-default shrink leading-5 w-full">
                  {{ split_queue.name.replace(/,/g, ', ') }}
                  <p class="mt-1 font-normal text-weak text-xssm">
                    {{ split_queue.length ? split_queue.length.toLocaleString() : 0 }} jobs
                  </p>
                </div>
              </div>
              <div class="text-weak flex shrink-0 justify-end items-center gap-4">
                <span class="text-sm text-default">
                  {{ humanTime(split_queue.wait) }}
                </span>
                <button 
                  v-if="split_queue.length > 0"
                  @click.prevent="clearQueue(split_queue.name)"
                  :disabled="isClearing(split_queue.name)"
                  class="p-1.5 text-icon-alpha hover:text-danger rounded-md hover:bg-weak transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  title="Clear Queue"
                >
                  <svg v-if="!isClearing(split_queue.name)" class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.519.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl" v-for="worker in workers" :key="worker.name">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-medium text-strong">{{ worker.name }}</h3>

        <svg v-if="worker.status == 'running'" xmlns="http://www.w3.org/2000/svg" class="text-success size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <svg v-if="worker.status == 'paused'" xmlns="http://www.w3.org/2000/svg" class="text-warning size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>

      <div class="bg-default shadow-xs-with-border rounded-lg overflow-hidden">
        <div class="divide-default divide-y">
          <div v-for="supervisor in worker.supervisors" :key="supervisor.name" class="flex w-full justify-between py-3 hover:bg-card-hover px-5 items-center gap-8">
            <div class="flex items-center gap-3 flex-1">
              <div class="text-sm font-medium text-strong shrink leading-5 w-full">
                <div class="flex items-center">
                  <svg v-if="supervisor.status == 'paused'" class="inline-block size-4 text-warning mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z" />
                  </svg>
                  <svg v-if="supervisor.status == 'inactive'" class="inline-block size-4 text-danger mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z" />
                  </svg>
                  {{ superVisorDisplayName(supervisor.name, worker.name) }}
                </div>
                <p class="mt-1 font-normal text-weak text-xssm">
                  {{ supervisor.options.connection }} • {{ supervisor.options.queue.replace(/,/g, ', ') }} • {{ countProcesses(supervisor.processes) }} processes
                </p>
              </div>
            </div>
            <div class="text-weak flex shrink-0 justify-end items-center">
              <span class="text-sm text-default" v-if="supervisor.options.balance">
                {{ upperFirst(supervisor.options.balance) }}
              </span>
              <span class="text-sm text-weak" v-else>
                Disabled
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>