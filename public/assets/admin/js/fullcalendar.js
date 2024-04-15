/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************!*\
  !*** ./Resources/assets/js/fullcalendar.js ***!
  \*********************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
//Full Calendar
document.addEventListener('DOMContentLoaded', function () {
  var containerEl = document.getElementById('external-events');
  new FullCalendar.Draggable(containerEl, {
    itemSelector: '.fc-event',
    eventData: function eventData(eventEl) {
      return _defineProperty(_defineProperty({
        title: eventEl.innerText.trim()
      }, "title", eventEl.innerText), "className", eventEl.className + ' overflow-hidden ');
    }
  });
  var calendarEl = document.getElementById('calendar2');
  var calendar = new FullCalendar.Calendar(calendarEl, _defineProperty(_defineProperty(_defineProperty({
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    },
    defaultView: 'month',
    navLinks: true,
    // can click day/week names to navigate views
    businessHours: true,
    // display business hours
    editable: true,
    selectable: true,
    selectMirror: true,
    droppable: true,
    // this allows things to be dropped onto the calendar
    select: function select(arg) {
      var title = prompt('Event Title:');
      if (title) {
        calendar.addEvent({
          title: title,
          start: arg.start,
          end: arg.end,
          allDay: arg.allDay
        });
      }
      calendar.unselect();
    },
    eventClick: function eventClick(arg) {
      if (confirm('Are you sure you want to delete this event?')) {
        arg.event.remove();
      }
    }
  }, "editable", true), "dayMaxEvents", true), "events", [{
    title: 'Business Lunch',
    start: '2021-10-03T13:00:00',
    constraint: 'businessHours'
  }, {
    title: 'Meeting',
    start: '2021-10-13T11:00:00',
    constraint: 'availableForMeeting',
    // defined below
    color: '#38cab3'
  }, {
    title: 'Conference',
    start: '2021-10-18',
    end: '2021-10-20',
    color: '#f74f75'
  }, {
    title: 'Party',
    start: '2021-11-29T20:00:00',
    color: '#ffbd5a'
  },
  // areas where "Meeting" must be dropped
  {
    id: 'availableForMeeting',
    start: '2021-10-11T10:00:00',
    end: '2021-10-11T16:00:00',
    rendering: 'background',
    color: '#f34343'
  }, {
    id: 'availableForMeeting',
    start: '2021-10-13T10:00:00',
    end: '2021-10-13T16:00:00',
    rendering: '#4ec2f0'
  }, {
    title: 'Jyo birthday',
    id: 'Jyo birthday',
    start: '2021-12-19T10:00:00',
    end: '2021-12-19T16:00:00',
    rendering: '#4ec2f0'
  }, {
    title: 'Chandu birthday',
    id: 'Jyo birthday',
    start: '2021-11-30T10:00:00',
    end: '2021-11-30T16:00:00',
    rendering: '#4ec2f0'
  }]));
  calendar.render();
});

//List FullCalendar
document.addEventListener('DOMContentLoaded', function () {
  var calendarEl = document.getElementById('calendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'listDay,listWeek'
    },
    // customize the button names,
    // otherwise they'd all just say "list"
    views: {
      listDay: {
        buttonText: 'list day'
      },
      listWeek: {
        buttonText: 'list week'
      }
    },
    initialView: 'listWeek',
    initialDate: '2021-07-12',
    navLinks: true,
    // can click day/week names to navigate views
    editable: true,
    eventLimit: true,
    // allow "more" link when too many events
    dayMaxEvents: true,
    // allow "more" link when too many events
    events: [{
      title: 'All Day Event',
      start: '2021-11-01'
    }, {
      title: 'Long Event',
      start: '2019-11-07',
      end: '2021-10-10'
    }, {
      id: 999,
      title: 'Repeating Event',
      start: '2021-11-09T16:00:00'
    }, {
      id: 999,
      title: 'Repeating Event',
      start: '2021-11-16T16:00:00'
    }, {
      title: 'Conference',
      start: '2019-11-11',
      end: '2021-11-13'
    }, {
      title: 'Meeting',
      start: '2019-11-12T10:30:00',
      end: '2021-11-12T12:30:00'
    }, {
      title: 'Lunch',
      start: '2021-11-12T12:00:00'
    }, {
      title: 'Meeting',
      start: '2021-11-12T14:30:00'
    }, {
      title: 'Happy Hour',
      start: '2021-11-12T17:30:00'
    }, {
      title: 'Dinner',
      start: '2021-11-12T20:00:00'
    }, {
      title: 'Birthday Party',
      start: '2021-11-13T07:00:00'
    }, {
      title: 'Click for Google',
      url: 'http://google.com/',
      start: '2021-11-28'
    }]
  });
  //select2 dropdown
  $('.select2').select2({
    minimumResultsForSearch: Infinity,
    width: '100%'
  });

  // Select2 by showing the search
  $('.select2-show-search').select2({
    minimumResultsForSearch: '',
    width: '100%'
  });
});
/******/ })()
;