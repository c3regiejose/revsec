(function (Drupal) {

  Drupal.breakpoint = {

    /**
     * Get the current viewport width.
     *
     * @returns {number}
     *   The viewport width in pixels.
     */
    getWidth() {
      return window.innerWidth;
    },

    /**
     * Check if the viewport is at least the given breakpoint.
     *
     * @param {number} breakpoint
     *   Breakpoint width in pixels.
     *
     * @returns {boolean}
     *   TRUE if the viewport is at or above the breakpoint.
     */
    isMin(breakpoint) {
      return this.getWidth() >= breakpoint;
    },

    /**
     * Check if the viewport is below the given breakpoint.
     *
     * @param {number} breakpoint
     *   Breakpoint width in pixels.
     *
     * @returns {boolean}
     *   TRUE if the viewport is below the breakpoint.
     */
    isMax(breakpoint) {
      return this.getWidth() < breakpoint;
    },

    /**
     * Get a named breakpoint.
     *
     * @returns {string}
     *   The current breakpoint name.
     */
    get() {
      const width = this.getWidth();

      if (width < 500) {
        return 'xs';
      }

      if (width < 768) {
        return 'sm';
      }

      if (width < 1024) {
        return 'md';
      }

      if (width < 1200) {
        return 'lg';
      }

      if (width < 1400) {
        return 'xl';
      }

      return 'xxl';
    },

    /**
     * Check if the current breakpoint matches the given breakpoint.
     *
     * @param {string} breakpoint
     *   Breakpoint name.
     *
     * @returns {boolean}
     *   TRUE if the current breakpoint matches.
     */
    is(breakpoint) {
      return this.get() === breakpoint;
    }

  };

})(Drupal);
