(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.primaryNavigation = {
    attach: function (context, settings) {

      // Mobile Burger Menu
      (once('mobile-hamburger', '.sn-hamburger', context) || []).forEach(function (element) {
        $(element).on("click", function () {
          $('.header').toggleClass('show-menu');
        });
      });

      // Mobile Accordion.
      (once('mobile-accordion', 'li[data-drupal-selector="primary-nav-menu-item-has-children"]', context) || []).forEach(function (element) {
        const handleResize = Drupal.debounce(() => {
          if (Drupal.breakpoint.isMax(1199)) {
            $(element).on("click", function () {
              $(this).toggleClass('showed');
            });
          }
        }, 250);

        handleResize();
        window.addEventListener('resize', handleResize);
      });

      // Desktop Hover.
      // (once('desktop-hover', '.primary-nav__menu-item--level-1', context) || []).forEach(function (element) {
      //   const handleResize = Drupal.debounce(() => {
      //     if (Drupal.breakpoint.isMin(1200)) {
      //       $(element).on("mouseenter", function () {
      //         $(element).addClass('showed');
      //       });

      //       $(element).on("mouseleave", function () {
      //         $(element).removeClass('showed');
      //       });
      //     }
      //   }, 250);
      //   handleResize();
      //   window.addEventListener('resize', handleResize);
      // });
    }
  };

})(jQuery, Drupal, once);
