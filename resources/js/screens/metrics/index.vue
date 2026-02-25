<script type="text/ecmascript-6">
export default {
  /**
   * Prepare the component.
   */
  created() {
    document.title = "Horizon - Metrics";
  }
}
</script>

<template>
  <div>
    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex flex-col items-start justify-between gap-x-6 gap-y-3 px-5 py-4 sm:flex-row sm:items-center">
        <div class="space-y-1">
          <h3 class="font-medium text-strong">Metrics</h3>
          <p class="text-xssm text-default">Monitor throughput and runtime performance metrics.</p>
        </div>
      </div>

      <div class="border-weaker relative z-10 border-b sm:border-none">
        <div class="relative overflow-x-auto px-5">
          <nav aria-label="Tabs" class="relative flex gap-6">
            <span>
              <router-link
                  :to="{ name: 'metrics-jobs'}"
                  class="group relative cursor-pointer font-medium whitespace-nowrap py-3 inline-block font-sans focus:outline-none"
                  :class="$route.name === 'metrics-jobs' ? 'text-strong' : 'text-weak hover:text-default'"
              >
                <span class="-mx-2.5 flex items-center rounded-lg px-2.5 h-8 group-hover:bg-hovered">
                  Jobs
                </span>
              </router-link>
            </span>
            <span>
              <router-link
                  :to="{ name: 'metrics-queues'}"
                  class="group relative cursor-pointer font-medium whitespace-nowrap py-3 inline-block font-sans focus:outline-none"
                  :class="$route.name === 'metrics-queues' ? 'text-strong' : 'text-weak hover:text-default'"
              >
                <span class="-mx-2.5 flex items-center rounded-lg px-2.5 h-8 group-hover:bg-hovered">
                  Queues
                </span>
              </router-link>
            </span>
            <span
                :style="{
                    '--current-tab-width': $route.name === 'metrics-jobs' ? '45px' : '65px',
                    '--current-tab-left': $route.name === 'metrics-jobs' ? '0px' : '71px'
                }"
                class="bg-stronger absolute bottom-0 h-[2px] transition-all left-[var(--current-tab-left)] w-[var(--current-tab-width)]"
                aria-hidden="true"
            ></span>
          </nav>
        </div>
      </div>

      <router-view />
    </div>
  </div>
</template>