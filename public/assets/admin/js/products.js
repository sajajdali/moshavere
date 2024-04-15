/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./Resources/assets/js/products.js ***!
  \*****************************************/


var colBtn = document.querySelectorAll('#viewBtn');
var products = document.querySelectorAll('.product-each');
var itemContainer = document.querySelectorAll('.product-grid6');
var displayBtn = document.querySelectorAll('.layoutBtn');
var fourCol = document.getElementsByClassName("fourCol");
fourCol[0].addEventListener("click", fourColfunction);
function fourColfunction(e) {
  for (var i = 0; i < products.length; i++) {
    products[i].className = " ";
    products[i].className = "col-12 col-md-6 col-sm-6 col-lg-6 col-xl-3";
  }
  for (var i = 0; i < colBtn.length; i++) {
    colBtn[i].classList.remove('active');
  }
  var element = e.target;
  element.classList.add('active');
}
var threeCol = document.getElementsByClassName("threeCol");
threeCol[0].addEventListener("click", threeColfunction);
function threeColfunction(e) {
  for (var i = 0; i < products.length; i++) {
    products[i].className = " ";
    products[i].className = "col-12 col-md-6 col-sm-6 col-lg-6 col-xl-4";
  }
  for (var i = 0; i < colBtn.length; i++) {
    colBtn[i].classList.remove('active');
  }
  var element = e.target;
  element.classList.add('active');
}
var twoCol = document.getElementsByClassName("twoCol");
twoCol[0].addEventListener("click", twoColfunction);
function twoColfunction(e) {
  for (var i = 0; i < products.length; i++) {
    products[i].className = " ";
    products[i].className = "col-12 col-md-6 col-sm-6 col-lg-6 col-xl-6";
  }
  for (var i = 0; i < colBtn.length; i++) {
    colBtn[i].classList.remove('active');
  }
  var element = e.target;
  element.classList.add('active');
}
/******/ })()
;