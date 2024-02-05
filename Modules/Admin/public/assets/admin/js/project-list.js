/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************!*\
  !*** ./Resources/assets/js/project-list.js ***!
  \*********************************************/
(function ($) {
  "use strict";

  $('#project-table').DataTable({
    language: {
      searchPlaceholder: 'Search...',
      sSearch: ''
    }
  });

  // Select2 
  $('.select2').select2({
    minimumResultsForSearch: Infinity
  });
})(jQuery);
/******/ })()
;