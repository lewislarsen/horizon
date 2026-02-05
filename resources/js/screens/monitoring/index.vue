<script type="text/ecmascript-6">
export default {
  /**
   * The component's data.
   */
  data() {
    return {
      ready: false,
      newTag: '',
      showAddTagModal: false, // Replaces Bootstrap JS state
      tags: []
    };
  },

  /**
   * Prepare the component.
   */
  mounted() {
    document.title = "Horizon - Monitoring";
    this.loadTags();
  },

  methods: {
    /**
     * Load the monitored tags.
     */
    loadTags() {
      this.$http.get(Horizon.basePath + '/api/monitoring')
          .then(response => {
            this.tags = response.data;
            this.ready = true;
          });
    },

    /**
     * Poll handler to refresh the tags at regular intervals.
     */
    refreshTagsPeriodically() {
      this.loadTags();
    },

    /**
     * Open the modal for adding a new tag.
     */
    openNewTagModal() {
      this.showAddTagModal = true;
      this.newTag = '';

      // Auto-focus the input after the DOM updates
      this.$nextTick(() => {
        const newTagInput = document.getElementById('newTagInput');
        if (newTagInput) {
          newTagInput.focus();
        }
      });
    },

    /**
     * Monitor the given tag.
     */
    monitorNewTag() {
      if (!this.newTag) {
        const newTagInput = document.getElementById('newTagInput');
        if (newTagInput) {
          newTagInput.focus();
        }
        return;
      }

      this.$http.post(Horizon.basePath + '/api/monitoring', {'tag': this.newTag})
          .then(response => {
            this.showAddTagModal = false;
            this.tags.push({tag: this.newTag, count: 0});
            this.newTag = '';
          })
    },

    /**
     * Cancel adding a new tag.
     */
    cancelNewTag() {
      this.showAddTagModal = false;
      this.newTag = '';
    },

    /**
     * Stop monitoring the given tag.
     */
    stopMonitoring(tag) {
      this.$http.delete(Horizon.basePath + '/api/monitoring/' + encodeURIComponent(tag))
          .then(() => {
            this.tags = this.tags.filter(existing => existing.tag !== tag)
          })
    }
  }
}
</script>

<template>
  <div>
    <poll @poll="refreshTagsPeriodically" />

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="flex flex-col items-start justify-between gap-x-6 gap-y-3 px-5 py-4 sm:flex-row sm:items-center">
        <div class="space-y-1">
          <h3 class="font-medium text-strong">Monitoring</h3>
          <p class="text-xssm text-default">
            Monitor specific tags to track job processing.
          </p>
        </div>
        <button
            type="button"
            @click="openNewTagModal"
            class="cursor-pointer flex relative group/button justify-center items-center rounded-md border px-4 py-2 text-sm font-medium text-nowrap h-8 pl-2.5 pr-4 bg-default border-base text-strong shadow-xs hover:bg-weak dark:hover:bg-hovered focus:outline-none shrink-0"
        >
          <span class="size-5 shrink-0 mr-1.5 text-icon-alpha group-hover/button:text-icon-strong">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M10 5.625V10M10 10V14.375M10 10H5.625M10 10H14.375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
              </svg>
          </span>
          <span>Monitor Tag</span>
        </button>
      </div>

      <div v-if="!ready" class="flex items-center justify-center bg-default shadow-xs-with-border rounded-lg p-5">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-5 shrink-0 mr-2 text-icon-alpha animate-spin">
          <path fill="currentColor" d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
        </svg>
        <span class="text-default">Loading...</span>
      </div>

      <div v-if="ready && tags.length === 0" class="bg-default shadow-xs-with-border rounded-lg">
        <div class="flex flex-col items-center justify-center p-5 text-center">
          <p class="text-default font-medium">You're not monitoring any tags.</p>
        </div>
      </div>

      <div v-if="ready && tags.length > 0" class="bg-default shadow-xs-with-border rounded-lg">
        <div class="divide-default divide-y [&>:first-child]:rounded-t-lg [&>:last-child]:rounded-b-lg">
          <div
              v-for="tag in tags"
              :key="tag.tag"
              class="flex w-full justify-between py-3 hover:bg-card-hover px-5 items-center gap-8"
          >
            <div class="flex items-center gap-3">
              <div class="text-sm font-medium text-strong shrink leading-5 w-full">
                <router-link
                    :to="{ name: 'monitoring-jobs', params: { tag: tag.tag }}"
                    class="text-link hover:text-link-hovered"
                >
                  {{ tag.tag }}
                </router-link>
                <p class="mt-1 font-normal text-weak text-xssm">
                  {{ tag.count }} {{ tag.count === 1 ? 'job' : 'jobs' }}
                </p>
              </div>
            </div>
            <div class="text-weak flex shrink-0 justify-end items-center">
              <button
                  type="button"
                  @click="stopMonitoring(tag.tag)"
                  class="cursor-pointer flex relative group/button items-center rounded-md border py-2 text-sm font-medium h-8 hover:text-strong text-default border-transparent focus:outline-none w-8 justify-center px-0"
                  title="Stop Monitoring"
              >
                <span class="cursor-pointer size-5 shrink-0 text-icon-alpha group-hover/button:text-icon-strong">
                    <svg viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <Transition
          enter-active-class="transition-opacity duration-200 ease-out"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition-opacity duration-150 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
      >
        <div v-if="showAddTagModal" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-4">
          <div @click="cancelNewTag" class="absolute inset-0 bg-modal backdrop-blur-[1px]"></div>

          <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition-all duration-150 ease-in"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
          >
            <div v-if="showAddTagModal" class="relative z-50 w-full max-w-xl bg-default/60 p-2 shadow-xs rounded-3xl backdrop-blur-[1px]">
              <div class="bg-default relative overflow-hidden rounded-2xl border-weaker flex flex-col shadow-dialog">
                <div class="p-5 border-b border-transparent">
                  <h2 class="font-medium text-strong text-base/5">Monitor New Tag</h2>
                  <p class="mt-2 text-default text-sm font-normal">
                    Enter the tag you want to monitor.
                  </p>
                </div>

                <div class="px-5 pt-3 pb-5">
                  <label for="newTagInput" class="text-default text-sm font-medium leading-6">Tag</label>
                  <div class="mt-1.5 relative flex items-stretch border hover:border-hovered border-base rounded-md focus-within:ring-3 focus-within:ring-brand-weak">
                    <input
                        v-model="newTag"
                        @keyup.enter="monitorNewTag"
                        class="block w-full bg-transparent px-3 outline-none h-10 text-sm text-strong placeholder:text-weak"
                        id="newTagInput"
                        type="text"
                        placeholder="App\Models\User:6352"
                    >
                  </div>
                </div>

                <div class="flex flex-col-reverse items-center justify-end gap-3 sm:flex-row p-5">
                  <button
                      type="button"
                      @click="cancelNewTag"
                      class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-default border-base text-strong hover:bg-weak w-full sm:w-auto"
                  >
                    Cancel
                  </button>
                  <button
                      type="button"
                      @click="monitorNewTag"
                      class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-stronger border-stronger text-inverted hover:bg-stronger-btn-hover w-full sm:w-auto"
                  >
                    Monitor
                  </button>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>