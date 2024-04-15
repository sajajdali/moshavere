/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./Resources/assets/js/projects.js ***!
  \*****************************************/
var stars = document.querySelectorAll('.star');
for (var i = 0; i < stars.length; i++) {
  stars[i].addEventListener('click', activeStar);
}
function activeStar($e) {
  'use strict';

  var currentStar = $e.target;
  if (currentStar.classList.contains('active')) {
    currentStar.classList.remove('active');
  } else {
    currentStar.classList.add('active');
  }
}
var typeofProject = document.querySelector('#typeTitle');
var allBtn = document.querySelector('#all');
var holdBtn = document.querySelector('#onHold');
var progressBtn = document.querySelector('#inProgress');
var completedBtn = document.querySelector('#completed');
if (allBtn) {
  allBtn.addEventListener('click', writeAll);
}
function writeAll() {
  'use strict';

  typeofProject.innerHTML = "All Projects";
}
if (holdBtn) {
  holdBtn.addEventListener('click', writeHold);
}
function writeHold() {
  'use strict';

  typeofProject.innerHTML = "On Hold";
}
if (progressBtn) {
  progressBtn.addEventListener('click', writeProgress);
}
function writeProgress() {
  'use strict';

  typeofProject.innerHTML = "In Progress";
}
if (completedBtn) {
  completedBtn.addEventListener('click', writeCompleted);
}
function writeCompleted() {
  'use strict';

  typeofProject.innerHTML = "Completed";
}
/******/ })()
;