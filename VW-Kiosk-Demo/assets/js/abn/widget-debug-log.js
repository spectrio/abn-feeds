// check the console for output and note that the line numbers in c
// odepen are not correct. It will be correct in your code!
var isDebug = false;
var Debugger = function (gState, klass) {
    this.debug = {}
    if (!window.console) return function () {}
    if (gState && klass.isDebug) {
        for (var m in console) {
            if (typeof console[m] == 'function') {
                this.debug[m] = console[m].bind(window.console, klass.toString() + ": ");
            }
        }
    } else {
        for (var m in console) {
            if (typeof console[m] == 'function') {
                this.debug[m] = function () {};
            }
        }
    }
    return this.debug;
}

// we instantiate with the global switch and a ref to this for the local 
// this must have it's own isDebug defined for local controll
var debug = Debugger(isDebug, this);

if (isDebug) {
    if (sentryKey) {
        Sentry.init({
            dsn: sentryKey
        });
    }
}

/*
 *
 * All functions of console:
 * debug.log, debug.info, debug.debug, debug.error,
 * and debug.trace for stack trace
 * 
 */