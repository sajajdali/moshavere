/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**************************************!*\
  !*** ./Resources/assets/js/reply.js ***!
  \**************************************/
//reply to a comment/review
function replay() {
  'use strict';

  var replayButtom = document.querySelectorAll('.reply a');

  // Creating Div
  var Div = document.createElement('div');
  Div.setAttribute('class', "comment mt-5 d-grid w-60");

  // creating textarea
  var textArea = document.createElement('textarea');
  textArea.setAttribute('class', "form-control");
  textArea.setAttribute('rows', "5");
  textArea.innerText = "Your Comment";

  // creating Cancel buttons
  var cancelButton = document.createElement('button');
  cancelButton.setAttribute('class', "btn btn-danger");
  cancelButton.innerText = "Cancel";
  var buttonDiv = document.createElement('div');
  buttonDiv.setAttribute('class', "btn-list ms-auto mt-2");

  // Creating submit button
  var submitButton = document.createElement('button');
  submitButton.setAttribute('class', "btn btn-success ms-3");
  submitButton.innerText = "Submit";

  // appending text are to div
  Div.append(textArea);
  Div.append(buttonDiv);
  buttonDiv.append(cancelButton);
  buttonDiv.append(submitButton);
  replayButtom.forEach(function (element, index) {
    element.addEventListener('click', function () {
      var replay = $(element).parent();
      replay.append(Div);
      cancelButton.addEventListener('click', function () {
        Div.remove();
      });
    });
  });
}
replay();
/******/ })()
;