(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["vue-jdd-flow"] = factory();
	else
		root["vue-jdd-flow"] = factory();
})((typeof self !== 'undefined' ? self : this), function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "f6fd":
/***/ (function(module, exports) {

// document.currentScript polyfill by Adam Miller

// MIT license

(function(document){
  var currentScript = "currentScript",
      scripts = document.getElementsByTagName('script'); // Live NodeList collection

  // If browser needs currentScript polyfill, add get currentScript() to the document object
  if (!(currentScript in document)) {
    Object.defineProperty(document, currentScript, {
      get: function(){

        // IE 6-10 supports script readyState
        // IE 10+ support stack trace
        try { throw new Error(); }
        catch (err) {

          // Find the second match for the "at" string to get file src url from stack.
          // Specifically works with the format of stack traces in IE.
          var i, res = ((/.*at [^\(]*\((.*):.+:.+\)$/ig).exec(err.stack) || [false])[1];

          // For all scripts on the page, if src matches or if ready state is interactive, return the script tag
          for(i in scripts){
            if(scripts[i].src == res || scripts[i].readyState == "interactive"){
              return scripts[i];
            }
          }

          // If no match, return null
          return null;
        }
      }
    });
  }
})(document);


/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  if (true) {
    __webpack_require__("f6fd")
  }

  var i
  if ((i = window.document.currentScript) && (i = i.src.match(/(.+\/)[^/]+\.js(\?.*)?$/))) {
    __webpack_require__.p = i[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// CONCATENATED MODULE: ./src/components/mixins/workflow.js
/* harmony default export */ var workflow = ({
  data: function data() {
    return {
      dashboardPath: '/'
    };
  },
  computed: {
    workflowToken: function workflowToken() {
      return {
        instance: this.$route.query.instance,
        token: this.$route.query.token
      };
    }
  },
  methods: {
    onProcessInstance: function onProcessInstance() {},
    onProcessCanceled: function onProcessCanceled() {},
    onTaskCompleted: function onTaskCompleted() {},
    callProcess: function callProcess(processUrl) {
      var _this = this;

      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      return window.axios.post('process', {
        call: {
          method: 'call',
          parameters: {
            processUrl: processUrl,
            data: data
          }
        }
      }).then(function (response) {
        var instance = response.data.response;

        _this.onProcessInstance(instance);

        _this.gotoNextStep({
          instance: instance.id,
          token: null
        });

        return response;
      });
    },
    startProcess: function startProcess(processUrl, start) {
      var _this2 = this;

      var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return window.axios.post('process', {
        call: {
          method: 'start',
          parameters: {
            processUrl: processUrl,
            start: start,
            data: data
          }
        }
      }).then(function (response) {
        var instance = response.data.response;

        _this2.onProcessInstance(instance);

        _this2.gotoNextStep({
          instance: instance.id,
          token: null
        });

        return response;
      });
    },
    completeTask: function completeTask(data) {
      var _this3 = this;

      var token = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.workflowToken;
      this.validateToken(token);
      return window.axios.post('process/' + token.instance, {
        call: {
          method: 'completeTask',
          parameters: {
            token: token.token,
            data: data
          }
        }
      }).then(function (response) {
        _this3.onTaskCompleted(token);

        _this3.gotoNextStep(token);

        return response;
      });
    },
    cancelProcess: function cancelProcess() {
      var _this4 = this;

      var token = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.workflowToken;
      this.validateToken(token);
      return window.axios.post('process/' + token.instance, {
        call: {
          method: 'cancel',
          parameters: {}
        }
      }).then(function (response) {
        _this4.onProcessCanceled(token);

        _this4.gotoDashboard();

        return response;
      });
    },
    processTasks: function processTasks(token) {
      return window.axios.post('process/' + token.instance, {
        call: {
          method: 'tasks',
          parameters: {}
        }
      });
    },
    openTask: function openTask(task) {
      this.$router.push({
        path: task.path,
        query: task.token
      });
    },
    gotoDashboard: function gotoDashboard() {
      this.$router.push({
        path: this.dashboardPath
      });
    },
    gotoNextStep: function gotoNextStep(token) {
      var _this5 = this;

      return this.processTasks(token).then(function (response) {
        var tasks = response.data.response;

        if (tasks.length === 1) {
          _this5.openTask(tasks[0]);
        } else {
          _this5.gotoDashboard();
        }
      });
    },
    validateToken: function validateToken(token) {
      var valid = token && token instanceof Object && token.instance && token.token;

      if (!valid) {
        throw "Invalid token: " + JSON.stringify(token);
      }
    }
  }
});
// CONCATENATED MODULE: ./src/components/index.js

window.workflowMixin = workflow;
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=vue-jdd-flow.umd.js.map