import Swiper, { Navigation, Pagination } from 'swiper';
import Swal from 'sweetalert2';
import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox.css";
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Swal = Swal ;
// sidebar
const nav_button = document.querySelector('.navbar__menu--button')
const sidebar_container = document.querySelector('.sidebar__container')

// accordion
const accordion__containers = document.querySelectorAll(
  ".accordion__container"
);
const accordion__containers_2 = document.querySelectorAll(
  ".accordion__container--2"
);
// tabbar
const tabbar__container_header = document.querySelectorAll('.tabbar__container-header-item')
const tabbar__container_content = document.querySelectorAll('.tabbar__container-content')

// ++++++ EVENT HANDLERS ++++++++
// sidebar events
if(nav_button && sidebar_container) {
  nav_button.addEventListener('click', () => {
    sidebar_container.classList.toggle('opened')
  })
}

// accordion events
if (accordion__containers.length) {
  accordion__containers.forEach((item, index) => {
    const button = item.querySelector(".accordion_select__button");

    button.addEventListener("click", () => {
      document
      .querySelectorAll(".accordion_select__container")
        .forEach((itemContent) => {
          if (itemContent.classList.contains("open")) {
            itemContent.classList.remove("open");
          }
        });
      item.classList.add("open");
    });
  });
}
if (accordion__containers_2.length) {
  accordion__containers_2.forEach((item, index) => {
    const button = item.querySelector(".accordion__button");
    button.addEventListener("click", () => {
      item.classList.toggle("open");
    });
    // Close all nested accordions if this one is closed
    if (!item.classList.contains("open")) {
      item.querySelectorAll(".accordion__container--2.open").forEach(nestedItem => {
        nestedItem.classList.remove("open");
      });
    }
  });
}

// tabbar
if(tabbar__container_header.length && tabbar__container_content.length) {
  tabbar__container_header.forEach((headerItem, headerIndex) => {
    headerItem.addEventListener('click', () => {
      tabbar__container_header.forEach((item) => item.classList.contains('active') && item.classList.remove('active'))
      tabbar__container_content.forEach((item) => item.classList.contains('active') && item.classList.remove('active'))

      headerItem.classList.add('active');
      tabbar__container_content[headerIndex].classList.add('active');
    })
  })
}

// ++++++ Swiper cards ++++++
const swiperCards = new Swiper('.swiper-cards', {
  modules: [Pagination, Navigation],

  slidesPerView: 1.25,
  spaceBetween: 20,
  breakpoints: {
    675: {
      slidesPerView: 2.25,
    },
    881: {
      slidesPerView: 3,
    },
    1024: {
      slidesPerView: 4,
    },
  },

  pagination: {
    el: '.swiper-pagination',
  },

  // Navigation arrows
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
});
const swiperCards6 = new Swiper('.swiper-cards-6', {
  modules: [Pagination, Navigation],

  slidesPerView: 2,
  spaceBetween: 20,
  breakpoints: {
    675: {
      slidesPerView: 3,
    },
    881: {
      slidesPerView: 4,
    },
    1024: {
      slidesPerView: 6,
    },
  },

  pagination: {
    el: '.swiper-pagination',
  },

  // Navigation arrows
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
});
