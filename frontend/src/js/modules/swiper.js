import Swiper, { Pagination, Navigation, Autoplay } from "swiper";

const hero_swiper = new Swiper(".hero__slider", {
  modules: [Pagination, Autoplay],

  // Optional parameters
  direction: "horizontal",
  loop: true,
  slidesPerView: 1,
  spaceBetween: 0,
  cssMode: false,
  speed: 900,
  autoplay: {
    delay: 6000,
    disableOnInteraction: false,
    pauseOnMouseEnter: true
  },

  // If we need pagination
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },

  // And if we need scrollbar
  scrollbar: false,

  breakpoints: {
    480: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 1,
    },
    1024: {
      slidesPerView: 1,
    },
  },
});

const participate_swiper = new Swiper(".participate__teaser__slider", {
  modules: [Pagination, Navigation],

  // Optional parameters
  direction: "horizontal",
  loop: true,
  slidesPerView: 1,
  spaceBetween: 30,
  cssMode: false,
  speed: 900,

  // If we need pagination
  pagination: {
    enabled: true,
    el: ".participate__teaser__list .swiper-controls .swiper-pagination",
    type: "fraction",
  },
  navigation: {
    enabled: true,
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  scrollbar: false,

  breakpoints: {
    768: {
      slidesPerView: 2,
      pagination: {
        enabled: true,
      },
      navigation: {
        enabled: true,
      },
    },
    1024: {
      slidesPerView: 2,
      pagination: {
        enabled: false,
      },
      navigation: {
        enabled: true,
      },
    },
  },
});

const service_swiper = new Swiper(".service__teaser__slider", {
  modules: [Pagination, Navigation],

  // Optional parameters
  direction: "horizontal",
  loop: true,
  slidesPerView: 1,
  spaceBetween: 30,
  cssMode: false,
  speed: 900,

  // If we need pagination
  pagination: {
    enabled: true,
    el: ".service__teaser__list  .swiper-controls .swiper-pagination",
    type: "fraction",
  },
  navigation: {
    enabled: true,
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  scrollbar: false,

  breakpoints: {
    600: {
      slidesPerView: 2,
      pagination: {
        enabled: true,
      },
      navigation: {
        enabled: true,
      },
    },
    1024: {
      slidesPerView: 3,
      pagination: {
        enabled: false,
      },
      navigation: {
        enabled: true,
      },
    },
  },
});

const news_swiper = new Swiper(".news__slider", {
  modules: [Pagination, Navigation],

  // Optional parameters
  direction: "horizontal",
  loop: true,
  slidesPerView: 1,
  spaceBetween: 30,
  cssMode: false,
  speed: 900,

  // If we need pagination
  pagination: {
    enabled: true,
    el: ".news__slider__container  .swiper-controls .swiper-pagination",
    type: "fraction",
  },
  navigation: {
    enabled: true,
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  scrollbar: false,

  breakpoints: {
    768: {
      slidesPerView: 2,
      pagination: {
        enabled: true,
      },
      navigation: {
        enabled: true,
      },
    },
    1024: {
      slidesPerView: 2,
      pagination: {
        enabled: false,
      },
      navigation: {
        enabled: true,
      },
    },
  },
});
