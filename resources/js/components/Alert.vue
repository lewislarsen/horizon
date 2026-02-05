<script type="text/ecmascript-6">
export default {
  props: ['type', 'message', 'autoClose', 'confirmationProceed', 'confirmationCancel'],

  data(){
    return {
      timeout: null,
      showModal: false,
      anotherModalOpened: document.body.classList.contains('modal-open')
    }
  },

  mounted() {
    // Show modal on mount
    this.showModal = true;

    if (this.autoClose) {
      this.timeout = setTimeout(() => {
        this.close();
      }, this.autoClose);
    }
  },

  methods: {
    /**
     * Close the modal.
     */
    close(){
      clearTimeout(this.timeout);
      this.showModal = false;

      // Wait for transition to complete before cleaning up
      setTimeout(() => {
        this.$root.alert.type = null;
        this.$root.alert.autoClose = false;
        this.$root.alert.message = '';
        this.$root.alert.confirmationProceed = null;
        this.$root.alert.confirmationCancel = null;

        if (this.anotherModalOpened) {
          document.body.classList.add('modal-open');
        }
      }, 200);
    },

    /**
     * Confirm and close the modal.
     */
    confirm(){
      this.confirmationProceed();
      this.close();
    },

    /**
     * Cancel and close the modal.
     */
    cancel(){
      if (this.confirmationCancel) {
        this.confirmationCancel();
      }
      this.close();
    }
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition
        enter-active-class="transition-opacity duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed inset-0 z-[99999] overflow-hidden flex items-center justify-center p-4">
        <div @click="type !== 'confirmation' ? close() : null" class="absolute inset-0 bg-modal backdrop-blur-[1px]"></div>

        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
          <div v-if="showModal" class="relative z-50 w-full max-w-md bg-default/60 p-2 shadow-xs rounded-3xl backdrop-blur-[1px]">
            <div class="bg-default relative overflow-hidden rounded-2xl border-weaker flex flex-col shadow-dialog">
              <div class="px-5 py-6">
                <p class="text-default text-sm font-normal">{{ message }}</p>
              </div>

              <div class="flex flex-col-reverse items-center justify-end gap-3 sm:flex-row p-5 pt-0">
                <!-- Error type: single Close button -->
                <button
                    v-if="type === 'error'"
                    type="button"
                    @click="close"
                    class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-stronger border-stronger text-inverted hover:bg-stronger-btn-hover w-full sm:w-auto"
                >
                  Close
                </button>

                <!-- Success type: single Okay button -->
                <button
                    v-if="type === 'success'"
                    type="button"
                    @click="close"
                    class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-stronger border-stronger text-inverted hover:bg-stronger-btn-hover w-full sm:w-auto"
                >
                  Okay
                </button>

                <!-- Confirmation type: Cancel and Yes buttons -->
                <template v-if="type === 'confirmation'">
                  <button
                      type="button"
                      @click="cancel"
                      class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-default border-base text-strong hover:bg-weak w-full sm:w-auto"
                  >
                    Cancel
                  </button>
                  <button
                      type="button"
                      @click="confirm"
                      class="cursor-pointer rounded-md border py-2 px-4 text-sm font-medium bg-red-600 border-red-600 text-white hover:bg-red-700 w-full sm:w-auto"
                  >
                    Yes
                  </button>
                </template>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>