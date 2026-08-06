(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.solaireAccordion = {
    attach: function (context, settings) {
      // Attach click handlers to accordion headers (once per element).
      (once('solaire-accordion', '.accordion-header', context) || []).forEach(function (header) {
        header.addEventListener('click', function () {
          var item = header.parentElement;
          var panel = header.nextElementSibling;
          var isActive = item.classList.contains('active');

          // close all
          (context.querySelectorAll ? context.querySelectorAll('.accordion-item') : document.querySelectorAll('.accordion-item')).forEach(function (el) {
            el.classList.remove('active');
            var h = el.querySelector('.accordion-header');
            if (h) {
              h.setAttribute('aria-expanded', 'false');
            }
            var p = el.querySelector('.accordion-panel');
            if (p) {
              p.style.maxHeight = null;
            }
          });

          // open clicked one if it wasn't already active
          if (!isActive) {
            item.classList.add('active');
            header.setAttribute('aria-expanded', 'true');
            if (panel) {
              panel.style.maxHeight = panel.scrollHeight + 40 + 'px';
            }
          }
        });
      });

      // set initial state for the item marked active (once per item)
      (once('solaire-accordion-init', '.accordion-item.active', context) || []).forEach(function (item) {
        var panel = item.querySelector('.accordion-panel');
        if (panel) {
          panel.style.maxHeight = panel.scrollHeight + 40 + 'px';
        }
      });
    }
  };

})(Drupal, once);
