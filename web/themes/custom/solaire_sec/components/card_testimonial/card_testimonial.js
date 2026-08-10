(function (Drupal, once) {
  'use strict';

  /**
   * Renders a decimal-capable star rating into an element.
   * Works by layering a full row of filled stars on top of a full
   * row of empty stars, then clipping the filled layer's width to
   * match the rating percentage (e.g. 4.5 / 5 = 90%).
   *
   * @param {HTMLElement} el
   *   Container with the star rating.
   * @param {number} rating
   *   Decimal rating, e.g. 4.5.
   * @param {number} [max=5]
   *   Number of stars total.
   */
  function renderStarRating(el, rating, max = 5) {
    const clamped = Math.max(0, Math.min(rating, max));
    const percent = (clamped / max) * 100;
    const starsMarkup = '★'.repeat(max);

    el.innerHTML = `
      <span class="stars-base">${starsMarkup}</span>
      <span class="stars-fill" style="width: ${percent}%">${starsMarkup}</span>
    `;

    el.setAttribute('aria-label', `Rated ${clamped} out of ${max} stars`);
    el.setAttribute('role', 'img');
  }

  Drupal.behaviors.solaireTestimonialStars = {
    attach: function (context, settings) {
      const stars = once('solaire-testimonial-stars', '.stars[data-rating]', context) || [];
      stars.forEach(function (el) {
        const rating = parseFloat(el.getAttribute('data-rating')) || 1;
        const max = parseFloat(el.getAttribute('data-max')) || 5;
        renderStarRating(el, rating, max);
      });
    }
  };

})(Drupal, once);
