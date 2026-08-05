(function (Drupal, once) {
  Drupal.behaviors.mainBannerSwiper = {
    attach(context) {
      once('main-banner-swiper-item', '.swiper-main-banner-slider .paragraph--type-banner', context).forEach((element) => {
        element.classList = 'swiper-slide';
      });

      once('main-banner-swiper', '.swiper-main-banner-slider', context).forEach((element) => {
        new Swiper(element, {
          slidesPerView: 1,
          loop: true,
          speed: 1000,
          navigation: {
            nextEl: ".banner-btn-next",
            prevEl: ".banner-btn-prev",
          },
        });
      });
    }
  };
})(Drupal, once);
