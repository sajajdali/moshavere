/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./Resources/assets/js/userlist.js ***!
  \*****************************************/
(function ($) {
  "use strict";

  //userlist datatable
  $('#user-datatable').DataTable({
    language: {
      searchPlaceholder: 'Search...',
      sSearch: ''
    },
    pageLength: 25
  });

  // Select2 
  $('.select2').select2({
    minimumResultsForSearch: Infinity
  });
})(jQuery);
/******/ })()
;