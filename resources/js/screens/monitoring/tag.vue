<script type="text/ecmascript-6">
export default {
  computed: {
    /**
     * The tag being monitored.
     */
    tag() {
      return this.$route.params.tag;
    }
  }
}
</script>

<template>
  <div>
    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex flex-col items-start justify-between gap-x-6 gap-y-3 px-5 py-4 sm:flex-row sm:items-center">
        <div class="space-y-1">
          <h3 class="font-medium text-strong">Recent Jobs for "{{ tag }}"</h3>
          <p class="text-xssm text-default">
            View all jobs tagged with {{ tag }}
          </p>
        </div>
      </div>

      <div class="border-weaker relative z-10 border-b sm:border-none">
        <div class="relative overflow-x-auto px-5">
          <nav aria-label="Tabs" class="relative flex gap-6">
            <span>
                <router-link
                    :to="{ name: 'monitoring-jobs', params: { tag: tag }}"
                    class="group relative cursor-pointer font-medium whitespace-nowrap py-3 inline-block font-sans focus:outline-none"
                    :class="$route.name === 'monitoring-jobs' ? 'text-strong hover:text-strong' : 'text-weak hover:text-default'"
                >
                    <span class="-mx-2.5 flex items-center rounded-lg px-2.5 h-8 group-hover:bg-hovered group-focus-visible:shadow-xs-selected group-focus-visible:rounded">
                        Recent Jobs
                    </span>
                    <span aria-hidden="true" class="pointer-events-auto absolute top-1/2 left-1/2 size-[max(100%,2.75rem)] -translate-x-1/2 -translate-y-1/2 pointer-fine:hidden"></span>
                </router-link>
            </span>
            <span>
                <router-link
                    :to="{ name: 'monitoring-failed', params: { tag: tag }}"
                    class="group relative cursor-pointer font-medium whitespace-nowrap py-3 inline-block font-sans focus:outline-none"
                    :class="$route.name === 'monitoring-failed' ? 'text-strong hover:text-strong' : 'text-weak hover:text-default'"
                >
                    <span class="-mx-2.5 flex items-center rounded-lg px-2.5 h-8 group-hover:bg-hovered group-focus-visible:shadow-xs-selected group-focus-visible:rounded">
                        Failed Jobs
                    </span>
                    <span aria-hidden="true" class="pointer-events-auto absolute top-1/2 left-1/2 size-[max(100%,2.75rem)] -translate-x-1/2 -translate-y-1/2 pointer-fine:hidden"></span>
                </router-link>
            </span>
            <span
                :style="{
                    '--current-tab-width': $route.name === 'monitoring-jobs' ? '85px' : '75px',
                    '--current-tab-left': $route.name === 'monitoring-jobs' ? '0px' : '109px'
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