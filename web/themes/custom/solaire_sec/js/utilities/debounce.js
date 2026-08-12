(function (Drupal) {
  'use strict';

  Drupal.debounce = function (callback, delay = 250) {
    let timeout;

    return function (...args) {
      const context = this;

      clearTimeout(timeout);

      timeout = setTimeout(() => {
        callback.apply(context, args);
      }, delay);
    };
  };

})(Drupal);
