/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./Resources/assets/js/progress.js ***!
  \*****************************************/
(function ($) {
  'use strict';

  var $toggleBtn = $('#btnToggleAnimatedProgress'),
    $progressBar = $('#animatedBar');
  $toggleBtn.on("click", function (e) {
    if ($progressBar.hasClass("progress-bar-animated")) {
      $progressBar.removeClass('progress-bar-animated');
    } else {
      $progressBar.addClass('progress-bar-animated');
    }
  });
})(jQuery);
/******/ })()
;