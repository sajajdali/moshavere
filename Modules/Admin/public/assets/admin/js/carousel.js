/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./Resources/assets/js/carousel.js ***!
  \*****************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
(function ($) {
  'use strict';

  /*---Owl-carousel----*/

  // Owl-carousel-icons
  var owl = $('.owl-carousel-icons');
  owl.owlCarousel({
    margin: 25,
    loop: true,
    nav: true,
    autoplay: true,
    dots: false,
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 1
      },
      1300: {
        items: 3
      }
    }
  });

  // Owl-carousel-icons2
  var owl = $('.owl-carousel-icons2');
  owl.owlCarousel(_defineProperty(_defineProperty(_defineProperty({
    loop: true,
    rewind: false,
    margin: 25,
    animateIn: 'fadeInDowm',
    animateOut: 'fadeOutDown',
    autoplay: false,
    autoplayTimeout: 5000,
    // set value to change speed
    autoplayHoverPause: true,
    dots: false,
    nav: true
  }, "autoplay", true), "responsiveClass", true), "responsive", {
    0: {
      items: 1,
      nav: true
    },
    600: {
      items: 2,
      nav: true
    },
    1300: {
      items: 4,
      nav: true
    }
  }));

  // Owl-carousel-icons3
  var owl = $('.owl-carousel-icons3');
  owl.owlCarousel({
    margin: 25,
    loop: true,
    nav: false,
    dots: false,
    autoplay: true,
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 2
      },
      1000: {
        items: 2
      }
    }
  });

  // Owl-carousel-icons4
  var owl = $('.owl-carousel-icons4');
  owl.owlCarousel({
    margin: 25,
    loop: true,
    nav: false,
    dots: false,
    autoplay: true,
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 3
      },
      1000: {
        items: 6
      }
    }
  });

  // Owl-carousel-icons5
  var owl = $('.owl-carousel-icons5');
  owl.owlCarousel(_defineProperty(_defineProperty(_defineProperty({
    loop: true,
    rewind: false,
    margin: 25,
    animateIn: 'fadeInDowm',
    animateOut: 'fadeOutDown',
    autoplay: false,
    autoplayTimeout: 5000,
    // set value to change speed
    autoplayHoverPause: true,
    dots: true,
    nav: false
  }, "autoplay", true), "responsiveClass", true), "responsive", {
    0: {
      items: 1,
      nav: true
    },
    600: {
      items: 2,
      nav: true
    },
    1300: {
      items: 4,
      nav: true
    }
  }));

  // Owl-carousel-icons6
  var owl = $('.owl-carousel-icons6');
  owl.owlCarousel({
    margin: 25,
    loop: true,
    nav: false,
    dots: false,
    autoplay: true,
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 2
      },
      1000: {
        items: 3
      }
    }
  });

  // Owl-carousel-icons2
  var owl = $('.owl-carousel-icons2');
  owl.owlCarousel(_defineProperty(_defineProperty(_defineProperty({
    loop: true,
    rewind: false,
    margin: 25,
    animateIn: 'fadeInDowm',
    animateOut: 'fadeOutDown',
    autoplay: false,
    autoplayTimeout: 5000,
    // set value to change speed
    autoplayHoverPause: true,
    dots: false,
    nav: true
  }, "autoplay", true), "responsiveClass", true), "responsive", {
    0: {
      items: 1,
      nav: true
    },
    600: {
      items: 2,
      nav: true
    },
    1300: {
      items: 4,
      nav: true
    }
  }));
})(jQuery);
/******/ })()
;