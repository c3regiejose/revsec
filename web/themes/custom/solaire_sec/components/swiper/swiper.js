(function (Drupal, once) {
  Drupal.behaviors.mainSwiper = {
    attach(context) {

      once('swiper-item', '.main-swiper article', context).forEach((element) =>{
        element.classList = 'swiper-slide';
      });

      once('slider', '.main-swiper', context).forEach((element) => {

        slPerView = 1;
        if (element.getAttribute("data-slidePerView") !== null) {
          slPerView = element.getAttribute("data-slidePerView");
        }

        new Swiper(element, {
          slidesPerView: 1,
          loop: true,
          spaceBetween: 20,
          breakpoints: {
            768: {
              slidesPerView: slPerView,
            },
          },
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          }
        });
      });
    }
  };
})(Drupal, once);
