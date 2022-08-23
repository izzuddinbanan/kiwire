/**
 *     __  ___ ____ ______ __  __ ________  __ _____  __     ____ ____   ______ ____ 
 *    /  |/  //  _// ____// / / //_  __/\ \/ // ___/ / /    /  _// __ \ / ____// __ \
 *   / /|_/ / / / / / __ / /_/ /  / /    \  / \__ \ / /     / / / / / // __/  / /_/ /
 *  / /  / /_/ / / /_/ // __  /  / /     / / ___/ // /___ _/ / / /_/ // /___ / _, _/
 * /_/  /_//___/ \____//_/ /_/  /_/     /_/ /____//_____//___//_____//_____//_/ |_|
 *
 * mightySlider - Mighty Responsive Slider
 * http://mightyslider.com
 *
 * @version:  2.1.3
 * @released: June 24, 2015
 *
 * @author:   Hemn Chawroka
 *            http://iprodev.com/
 *
 */

/*!
 * elementSpy 1.0.0 - 22nd May 2015
 *
 * Licensed under the MIT license.
 * http://opensource.org/licenses/MIT
 */

;(function ($, w, undefined) {
	'use strict';

	// Plugin names
	var pluginName = 'elementspy',
		pluginClass = 'elementSpy',
		namespace  = pluginName;

	$[pluginClass] = function(context, callback, options) {
		// Optional arguments delay
		if (typeof callback !== 'function') {
			options = callback;
			callback = 0;
		}

		// Private variables
		var self     = this;
		var $context = $(context);
		var defaults = $.extend({}, $.fn[pluginName].defaults, options);
		var spies    = {};
		var lastId   = 0;
		var pos      = {
			top: $context.scrollTop(),
			left: $context.scrollLeft(),
			width: $context.innerWidth(),
			height: $context.innerHeight(),
			offset: $context.offset() || {
				top: 0,
				left: 0
			}
		};

		/**
		 * Add new element(s) to spies list.
		 *
		 * @param {Node}     element
		 * @param {Function} callback
		 * @param {Object}   options
		 *
		 * @return {Object} Spy object with basic control methods.
		 */
		self.add = function (element, callback, options) {
			// Optional arguments logic
			if ($.isPlainObject(callback)) {
				options = callback;
				callback = 0;
			}

			$(element).each(function (i, el) {
				var spyId = getId(el) || 's' + lastId++;

				// Add new element to spying list
				spies[spyId] = $.extend({
					id: spyId,
					el:  el,
					$el: $(el),
					callback: callback
				}, defaults, options);

				// Load the element data
				load(spyId);
			});
		};

		/**
		 * (Re)Load spy object's dimensions.
		 *
		 * @param  {Mixed} element
		 *
		 * @return {Void}
		 */
		function load(element) {
			var spy = getSpy(element);
			if (!spy) {
				return;
			}

			var start = spy.$el.offset()[spy.horizontal ? 'left' : 'top'] - pos.offset[spy.horizontal ? 'left' : 'top'];
			var size  = spy.$el[spy.horizontal ? 'outerWidth' : 'outerHeight']();

			// Add new element to spying list
			$.extend(spy, {
				start: start,
				elSize: size,
				end: start + size
			});

			// Check the element for its state
			check(spy);
		}

		/**
		 * Reload element's dimensions.
		 *
		 * @param  {Node} element
		 *
		 * @return {Void}
		 */
		self.reload = function (element) {
			$(element).each(function (i, el) {
				var spyId = getId(el);
				if (spyId) {
					load(spyId);
				}
			});
		};

		/**
		 * Remove element(s) from spying list.
		 *
		 * @param  {Node} element
		 *
		 * @return {Void}
		 */
		self.remove = function (element) {
			$(element).each(function (i, el) {
				var spyId = getId(el);
				if (spyId) {
					delete spies[spyId];
				}
			});
		};

		/**
		 * Check element state, and trigger callback on change.
		 *
		 * @param  {Mixed} element Can be element node, spy ID, or spy object. Omit to check all elements.
		 *
		 * @return {Void}
		 */
		function check(element) {
			if (element === undefined) {
				$.each(spies, check);
				return;
			}

			// Check whether the element/ID exist in spying list.
			var spy = getSpy(element);
			if (!spy) {
				return;
			}

			// Variables necessary for determination.
			var viewSize     = pos[spy.horizontal ? 'width' : 'height'];
			var triggerSize  = parseRatio(spy.size, viewSize);
			var triggerStart = pos[spy.horizontal ? 'left' : 'top'] + parseRatio(spy.offset, viewSize, -triggerSize);
			var triggerEnd   = triggerStart + triggerSize;
			var newState;

			// Calculate element state in relation to trigger area.
			if (spy.contain) {
				if (triggerStart <= spy.start && triggerEnd >= spy.end) {
					newState = 'inside';
				} else if (triggerStart + triggerSize / 2 > spy.start + spy.elSize / 2) {
					newState = spy.horizontal ? 'left' : 'up';
				} else {
					newState = spy.horizontal ? 'right' : 'down';
				}
			} else {
				if (
					triggerStart > spy.start && triggerStart < spy.end ||
					triggerEnd > spy.start && triggerEnd < spy.end ||
					triggerStart <= spy.start && triggerEnd >= spy.start ||
					triggerStart <= spy.end && triggerEnd >= spy.end
				) {
					newState = 'inside';
				} else if (triggerStart > spy.end) {
					newState = spy.horizontal ? 'left' : 'up';
				} else {
					newState = spy.horizontal ? 'right' : 'down';
				}
			}

			// Trigger callbacks on change.
			if (spy.state !== newState) {
				spy.state = newState;
				if (typeof callback === 'function') {
					callback.call(spy.el, newState === 'inside', newState);
				}
				if (typeof spy.callback === 'function') {
					spy.callback.call(spy.el, newState === 'inside', newState);
				}
			}
		}

		/**
		 * Check whether the element is already spied on, and return the spy ID.
		 *
		 * @param  {Node}  element
		 *
		 * @return {Mixed} Spy ID string, or false.
		 */
		function getId(element) {
			// Return when ID has been passed.
			if (spies.hasOwnProperty(element)) {
				return element;
			}

			// Return ID when spy object has been passed.
			if ($.isPlainObject(element) && spies.hasOwnProperty(element.id)) {
				return element.id;
			}

			// Ensure the element is a single Node
			element = $(element)[0];

			// Check for existence and return the ID.
			var is = false;
			$.each(spies, function (id, spy) {
				if (spy.el === element) {
					is = id;
				}
			});
			return is;
		}

		/**
		 * Return spy object of an element.
		 *
		 * @param  {Node}  element
		 *
		 * @return {Object} Spy ID string, or false.
		 */
		function getSpy(element) {
			var spyId = getId(element);
			return spyId ? spies[spyId] : false;
		}

		/**
		 * Destroy Espy instance.
		 *
		 * @return {Void}
		 */
		self.destroy = function () {
			$context.off('.' + namespace);
			spies = {};
			self = undefined;
		};

		// Register scroll handler
		$context.on('scroll.' + namespace, throttle(defaults.delay, function () {
			pos.top  = $context.scrollTop();
			pos.left = $context.scrollLeft();
			check();
		}));

		// Register resize handler
		$context.on('resize.' + namespace, throttle(defaults.delay, function () {
			pos.width  = $context.innerWidth();
			pos.height = $context.innerHeight();
			check();
		}));
	};

	/**
	 * Parse string like -200% and return the final dimension.
	 *
	 * @param  {Mixed}   value  Integer, or percent string.
	 * @param  {Integer} total  Total value representing 100%.
	 * @param  {Integer} offset Optional offset for negative numbers.
	 *
	 * @return {Integer}
	 */
	function parseRatio(value, total, offset) {
		var matches = (value+'').match(/^(-?[0-9]+)(%)?$/);
		if (!matches) {
			return false;
		}
		var num = parseInt(matches[1], 10);
		if (matches[2]) {
			num = total / 100 * num;
		}
		return num < 0 ? total + num + (offset || 0) : num;
	}

	/**
	 * Create a throttled version of a callback function.
	 *
	 * Copied & pasted with slight adjustments from
	 * https://github.com/cowboy/jquery-throttle-debounce/
	 *
	 * @param  {Integer}  delay
	 * @param  {Function} callback
	 *
	 * @return {Function}
	 */
	function throttle(delay, callback) {
		var timeoutId;
		var lastExec = 0;

		// The `wrapper` function encapsulates all of the throttling functionality
		// and when executed will limit the rate at which `callback` is executed.
		function wrapper() {
			/*jshint validthis:true */
			var that = this;
			var elapsed = +new Date() - lastExec;
			var args = arguments;

			function clear() {
				if (timeoutId) {
					timeoutId = clearTimeout(timeoutId);
				}
			}

			function exec() {
				lastExec = +new Date();
				callback.apply(that, args);
				clear();
			}

			clear();

			if (elapsed > delay) {
				exec();
			} else {
				timeoutId = setTimeout(exec, delay - elapsed);
			}
		}

		// Set the guid of `wrapper` function to the same of original callback, so it can be
		// removed in jQuery 1.4+ .unbind or .off by using the original callback as a reference.
		if ($.guid) {
			wrapper.guid = callback.guid = callback.guid || $.guid++;
		}

		// Return the wrapper function.
		return wrapper;
	}

	// Extend jQuery
	$.fn[pluginName] = function (callback, options) {
		var method, methodArgs;
		var context = options && options.context || w;
		var espy = $.data(context, namespace) || $.data(context, namespace, new $[pluginClass](context));

		// Attributes logic
		if (typeof callback === 'string') {
			method = options === false || options === 'destroy' ? 'remove' : options;
			methodArgs = Array.prototype.slice.call(arguments, 1);
			options = {};
		}

		// Apply to all elements
		return this.each(function (i, element) {
			if (!method) {
				// Adding element to spy on
				espy.add(element, callback, options);
			} else {
				// Call plugin method
				if (typeof espy[method] === 'function') {
					espy[method].apply(espy, methodArgs);
				}
			}
		});
	};

	// Default options
	$.fn[pluginName].defaults = {
		delay:      100,    // Events throttling delay in milliseconds.
		context:    window, // Scrolling context.
		horizontal: 0,      // Enable for horizontal scrolling.
		offset:     0,      // Target area offset from start (top in vert., left in hor.).
		size:       '100%', // Target area size (height in vert., width in hor.).
		contain:    0       // Trigger as entered only when element is completely within the target area.
	};
}(jQuery, window));


/**
 * mightySlider - Mighty Responsive Slider
 * http://mightyslider.com
 */
(function ($, window, undefined) {
	'use strict';

	/**
	 * References.
	 */
	var ArrayProto = Array.prototype, ObjProto = Object.prototype, StringProto = String.prototype;

	 /*!
	 * mightySlider Components 1.0.0 - 22nd May 2015
	 * http://iprodev.com/
	 *
	 * Licensed under the MIT license.
	 * http://opensource.org/licenses/MIT
	 */
	var _ = {};

	/**
	 * Return the type of `val`. Type assertions aka less-broken `typeof`.
	 *
	 * @param {Mixed} val
	 * @return {String}
	 * @api public
	 */

	function type(val) {
		switch (toString.call(val)) {
			case '[object Date]':
				return 'date';
			case '[object RegExp]':
				return 'regexp';
			case '[object Arguments]':
				return 'arguments';
			case '[object Array]':
				return 'array';
			case '[object Error]':
				return 'error';
		}

		if (val === null) return 'null';
		if (val === undefined) return 'undefined';
		if (val !== val) return 'nan';
		if (val && val.nodeType === 1) return 'element';

		val = val.valueOf ? val.valueOf() : Object.prototype.valueOf.apply(val)

		return typeof val;
	};

	// Check to see if an object is a plain object (created using "{}" or "new Object").
	function isObject(object) {
		return type(object) === 'object';
	};

	// Determine whether the argument is an array.
	var isArray = Array.isArray || function (array) {
		return type(array) === 'array';
	};

	var indexOfPolyfill = function(searchElement, fromIndex) {
		var k;

		// 1. Let O be the result of calling ToObject passing
		//    the this value as the argument.
		if (this == null) {
			throw new TypeError('"this" is null or not defined');
		}

		var O = Object(this);

		// 2. Let lenValue be the result of calling the Get
		//    internal method of O with the argument "length".
		// 3. Let len be ToUint32(lenValue).
		var len = O.length >>> 0;

		// 4. If len is 0, return -1.
		if (len === 0) {
			return -1;
		}

		// 5. If argument fromIndex was passed let n be
		//    ToInteger(fromIndex); else let n be 0.
		var n = +fromIndex || 0;

		if (Math.abs(n) === Infinity) {
			n = 0;
		}

		// 6. If n >= len, return -1.
		if (n >= len) {
			return -1;
		}

		// 7. If n >= 0, then Let k be n.
		// 8. Else, n<0, Let k be len - abs(n).
		//    If k is less than 0, then let k be 0.
		k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

		// 9. Repeat, while k < len
		while (k < len) {
			// a. Let Pk be ToString(k).
			//   This is implicit for LHS operands of the in operator
			// b. Let kPresent be the result of calling the
			//    HasProperty internal method of O with argument Pk.
			//   This step can be combined with c
			// c. If kPresent is true, then
			//    i.  Let elementK be the result of calling the Get
			//        internal method of O with the argument ToString(k).
			//   ii.  Let same be the result of applying the
			//        Strict Equality Comparison Algorithm to
			//        searchElement and elementK.
			//  iii.  If same is true, return k.
			if (k in O && O[k] === searchElement) {
				return k;
			}
			k++;
		}
		return -1;
	};

	// Create quick reference variables for speed access to core prototypes.
	var push = ArrayProto.push,
		indexOf = function(searchElement, fromIndex) {
			if (this == null) {
				throw new TypeError('"this" is null or not defined');
			}

			if (isArray(this)) {
				return (ArrayProto.indexOf || indexOfPolyfill).call(this, searchElement, fromIndex);
			}
			else {
				return StringProto.indexOf.call(this, searchElement, fromIndex);
			}
		},
		slice = ArrayProto.slice,
		splice = ArrayProto.splice,
		filter = ArrayProto.filter,
		toString = ObjProto.toString,
		hasOwnProperty = ObjProto.hasOwnProperty;

	(function () {
		/**
		 * Check whether value is a window object.
		 *
		 * Uses duck typing to determine window. Without IE8 all we need is:
		 *
		 *   var type = Object.prototype.toString.call(val);
		 *   return type === '[object global]' || type === '[object Window]' || type === '[object DOMWindow]';
		 *
		 * @param  {Mixed} val
		 * @return {Boolean}
		 */
		function isWindow(val) {
			/* jshint eqeqeq:false */
			var doc, docWin;
			return !!(
				val
				&& typeof val === 'object'
				&& typeof val.window === 'object'
				&& val.window == val
				&& val.setTimeout
				&& val.alert
				&& (doc = val.document)
				&& typeof doc === 'object'
				&& (docWin = doc.defaultView || doc.parentWindow)
				&& typeof docWin === 'object'
				&& docWin == val
			);
		};
		_.isWindow = isWindow;

		/**
		 * Determines whether an array includes a certain element, returning true or false as appropriate.
		 *
		 * @param  {Mixed} searchElement
		 * @param  {Array} array
		 * @return {Boolean}
		 */
		function inArray(elem, arr, i) {
			return arr == null ? -1 : indexOf.call( arr, elem, i );
		};
		_.inArray = inArray;

		// Returns element's position object relative to document, window, or other elements.
		(function () {
			/**
			 * Poor man's shallow object extend;
			 *
			 * @param  {Object} a
			 * @param  {Object} b
			 * @return {Object}
			 */
			function extend(a, b) {
				for (var k in b) a[k] = b[k];
				return a;
			}

			/**
			 * Returns element's position object with `left`, `top`, `bottom`, `right`,
			 * `width`, and `height` properties indicating the position and dimensions
			 * of element on a page, or relative to other element.
			 *
			 * @param {Element} element
			 * @param {Element} [relativeTo] Defaults to `document.documentElement`.
			 *
			 * @return {Object|null}
			 */
			_.position = function(element, relativeTo) {
				var isWin = isWindow(element);
				var doc = isWin ? element.document : element.ownerDocument || element;
				var docEl = doc.documentElement;
				var win = isWindow(relativeTo) ? relativeTo : doc.defaultView || window;

				// normalize arguments
				if (element === doc) element = docEl;
				relativeTo = !relativeTo || relativeTo === doc ? docEl : relativeTo;

				var winTop = (win.pageYOffset || docEl.scrollTop) - docEl.clientTop;
				var winLeft = (win.pageXOffset || docEl.scrollLeft) - docEl.clientLeft;
				var box = { top: 0, left: 0 };

				if (isWin) {
					box.width = box.right = win.innerWidth || docEl.clientWidth;
					box.height = box.bottom = win.innerHeight || docEl.clientHeight;
				} else if (element === docEl) {
					// we need to do  this manually because docEl.getBoundingClientRect
					// is inconsistent in <IE11
					box.top = -winTop;
					box.left = -winLeft;
					box.width = Math.max(docEl.clientWidth, docEl.scrollWidth);
					box.height = Math.max(docEl.clientHeight, docEl.scrollHeight);
					box.right = box.width - winLeft;
					box.bottom = box.height - winTop;
				} else if (docEl.contains(element) && element.getBoundingClientRect) {
					// new object needed because DOMRect properties are read-only
					box = extend({}, element.getBoundingClientRect());
					// width & height don't exist in <IE9
					box.width = box.right - box.left;
					box.height = box.bottom - box.top;
				} else {
					return null;
				}

				// current box is already relative to window
				if (relativeTo === win) return box;

				// add window offsets, making the box relative to documentElement
				box.top += winTop;
				box.left += winLeft;
				box.right += winLeft;
				box.bottom += winTop;

				// current box is already relative to documentElement
				if (relativeTo === docEl) return box;

				// subtract position of other element
				var relBox = position(relativeTo);
				box.left -= relBox.left;
				box.right -= relBox.left;
				box.top -= relBox.top;
				box.bottom -= relBox.top;

				return box;
			}
		}());


		// Array / object / string iteration utility.
		(function () {
			/**
			 * Iterate string chars.
			 *
			 * @param {String} obj
			 * @param {Function} fn
			 * @param {Object} ctx
			 * @api private
			 */

			function string(obj, fn, ctx) {
				for (var i = 0, len = obj.length; i < len; ++i) {
					fn.call(ctx, obj.charAt(i), i);
				}
			}

			/**
			 * Iterate object keys.
			 *
			 * @param {Object} obj
			 * @param {Function} fn
			 * @param {Object} ctx
			 * @api private
			 */

			function object(obj, keys, fn, ctx) {
				for (var i = 0, len = keys.length; i < len; ++i) {
					fn.call(ctx, keys[i], obj[keys[i]]);
				}
			}

			/**
			 * Iterate array-ish.
			 *
			 * @param {Array|Object} obj
			 * @param {Function} fn
			 * @param {Object} ctx
			 * @api private
			 */

			function array(obj, fn, ctx) {
				for (var i = 0, len = obj.length; i < len; ++i) {
					fn.call(ctx, obj[i], i);
				}
			}

			/**
			 * Iterate the given `obj` and invoke `fn(val, i)`
			 * in optional context `ctx`.
			 *
			 * @param {String|Array|Object} obj
			 * @param {Function} fn
			 * @param {Object} [ctx]
			 * @api public
			 */

			_.each = function(obj, fn, ctx) {
				ctx = ctx || this;
				switch (type(obj)) {
					case 'array':
						return array(obj, fn, ctx);
					case 'object':
						if ('number' == type(obj.length)) return array(obj, fn, ctx);
						return object(obj, Object.keys(obj), fn, ctx);
					case 'string':
						return string(obj, fn, ctx);
				}
			};
		}());

		// Event binding component with support for legacy browsers.
		(function () {
			/**
			 * Prevets default event action in IE8-.
			 */
			function preventDefault() {
				this.returnValue = false;
			}

			/**
			 * Stops event propagation in IE8-.
			 */
			function stopPropagation() {
				this.cancelBubble = true;
			}

			/**
			 * Bind `el` event `type` to `fn`.
			 *
			 * @param {Element}  el
			 * @param {String}   type
			 * @param {Function} fn
			 * @param {Boolean}  [capture]
			 *
			 * @return {Function} `fn`
			 */
			_.bind = window.addEventListener ? function (el, type, fn, capture) {
				el.addEventListener(type, fn, capture || false);
				return fn;
			} : function (el, type, fn) {
				var fnid = type + fn;

				el[fnid] = el[fnid] || function () {
					var event = window.event;
					event.target = event.srcElement;
					event.preventDefault = preventDefault;
					event.stopPropagation = stopPropagation;
					fn.call(el, event);
				};

				el.attachEvent('on' + type, el[fnid]);
				return fn;
			};

			/**
			 * Unbind `el` event `type`'s callback `fn`.
			 *
			 * @param {Element}  el
			 * @param {String}   type
			 * @param {Function} fn
			 * @param {Boolean}  [capture]
			 *
			 * @return {Function} `fn`
			 */
			_.unbind = window.removeEventListener ? function (el, type, fn, capture) {
				el.removeEventListener(type, fn, capture || false);
				return fn;
			} : function (el, type, fn) {
				var fnid = type + fn;
				el.detachEvent('on' + type, el[fnid]);

				// clean up reference to handler function, but with a fallback
				// because we can't delete window object properties
				try {
					delete el[fnid];
				} catch (err) {
					el[fnid] = undefined;
				}

				return fn;
			};
		}());

		// Browser detect
		(function () {
			function uaMatch( ua ) {
				ua = ua.toLowerCase();

				var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
					/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
					/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
					/(msie) ([\w.]+)/.exec( ua ) ||
					indexOf.call(ua, "compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
					[];

				return {
					browser: match[ 1 ] || "",
					version: match[ 2 ] || "0"
				};
			}

			var matched = uaMatch( navigator.userAgent );
			var browser = {};

			if ( matched.browser ) {
				browser[ matched.browser ] = true;
				browser.version = matched.version;
			}

			// Chrome is Webkit, but Webkit is also Safari.
			if ( browser.chrome ) {
				browser.webkit = true;
			}
			else if ( browser.webkit ) {
				browser.safari = true;
			}

			_.browser = browser;
		}());

		/**
		 * A JavaScript equivalent of PHP’s basename.
		 *
		 * @param {String} path
		 * @param {String} suffix
		 *
		 * @return {String}
		 */
		function basename(path, suffix) {
			var b = path.replace(/^.*[\/\\]/g, '');

			if (type(suffix) === 'string' && b.substr(b.length - suffix.length) === suffix) {
				b = b.substr(0, b.length - suffix.length);
			}

			return b;
		};
		_.baseName = basename;

		/**
		 * A JavaScript equivalent of PHP’s parse_url.
		 *
		 * @param {String} url           The URL to parse.
		 * @param {String} component     Specify one of URL_SCHEME, URL_HOST, URL_PORT, URL_USER, URL_PASS, URL_PATH, URL_QUERY or URL_FRAGMENT to retrieve just a specific URL component as a string.
		 *
		 * @return {Mixed}
		 */
		function parse_url(url, component) {
			var query, key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port',
				'relative', 'path', 'directory', 'file', 'query', 'fragment'],
				mode = 'php',
				parser = {
					php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
					strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
					loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
				};

			var m = parser[mode].exec(url),
				uri = {},
				i = 14;
				while (i--) {
					if (m[i]) {
						uri[key[i]] = m[i];
					}
				}

			if (component) {
				return uri[component.replace('URL_', '').toLowerCase()];
			}
			if (mode !== 'php') {
				var name = 'queryKey';
				parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
				uri[name] = {};
				query = uri[key[12]] || '';
				query.replace(parser, function ($0, $1, $2) {
					if ($1) {uri[name][$1] = $2;}
				});
			}
			uri.source = null;
			return uri;
		};
		_.parseURL = parse_url;

		/**
		 * Get file extension.
		 *
		 * @param {String} URL
		 *
		 * @return {String}
		 */
		_.getExtension = function(URL){
			var parsedURL = parse_url(URL),
				filename = basename(URL);

			if (parsedURL.query) {
				filename = filename.replace("?" + parsedURL.query, "");
			}

			// Return extension
			return filename.substr((~-filename.lastIndexOf(".") >>> 0) + 2);;
		};

		// Gets the absolute URI.
		(function () {
			var removeDotSegments = function (input) {
				var output = [];
				input.replace(/^(\.\.?(\/|$))+/, '')
					 .replace(/\/(\.(\/|$))+/g, '/')
					 .replace(/\/\.\.$/, '/../')
					 .replace(/\/?[^\/]*/g, function (p) {
					if (p === '/..') {
						output.pop();
					} else {
						push.call(output, p);
					}
				});
				return output.join('').replace(/^\//, input.charAt(0) === '/' ? '/' : '');
			}

			var URIComponents = function (url) {
				var m = String(url).replace(/^\s+|\s+$/g, '').match(/^([^:\/?#]+:)?(\/\/(?:[^:@]*(?::[^:@]*)?@)?(([^:\/?#]*)(?::(\d*))?))?([^?#]*)(\?[^#]*)?(#[\s\S]*)?/);
				// authority = '//' + user + ':' + pass '@' + hostname + ':' port
				return (m ? {
					href     : m[0] || '',
					protocol : m[1] || '',
					authority: m[2] || '',
					host     : m[3] || '',
					hostname : m[4] || '',
					port     : m[5] || '',
					pathname : m[6] || '',
					search   : m[7] || '',
					hash     : m[8] || ''
				} : null);
			}

			/**
			 * Gets the absolute URI.
			 *
			 * @param {String} href     The relative URL.
			 * @param {String} base     The base URL.
			 *
			 * @return {String}         The absolute URL.
			 */
			_.absolutizeURI = function (href, base) {// RFC 3986
				href = URIComponents(href || '');
				base = URIComponents(base || window.location.href);

				return !href || !base ? null : (href.protocol || base.protocol) +
					(href.protocol || href.authority ? href.authority : base.authority) +
					removeDotSegments(href.protocol || href.authority || href.pathname.charAt(0) === '/' ? href.pathname : (href.pathname ? ((base.authority && !base.pathname ? '/' : '') + base.pathname.slice(0, base.pathname.lastIndexOf('/') + 1) + href.pathname) : base.pathname)) +
					(href.protocol || href.authority || href.pathname ? href.search : (href.search || base.search)) +
					href.hash;
			};
		}());

		// A JavaScript equivalent of PHP’s uniqid.
		(function () {
			var formatSeed = function (seed, reqWidth) {
				seed = parseInt(seed, 10).toString(16); // to hex str
				if (reqWidth < seed.length) { // so long we split
					return slice.call(seed, seed.length - reqWidth);
				}
				if (reqWidth > seed.length) { // so short we pad
					return Array(1 + (reqWidth - seed.length)).join('0') + seed;
				}
				return seed;
			};

			/**
			 * A JavaScript equivalent of PHP’s uniqid.
			 *
			 * @param {String}  prefix
			 * @param {Boolean} more_entropy
			 *
			 * @return {String}
			 */
			_.uniqID = function (prefix, more_entropy) {
				if (type(prefix) === 'undefined') {
					prefix = "";
				}

				var retId;

				// BEGIN REDUNDANT
				var php_js = {};

				// END REDUNDANT
				if (!php_js.uniqidSeed) { // init seed with big random int
					php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
				}
				php_js.uniqidSeed++;

				retId = prefix; // start with prefix, add current milliseconds hex string
				retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
				retId += formatSeed(php_js.uniqidSeed, 5); // add seed hex string
				if (more_entropy) {
					// for more entropy we add a float lower to 10
					retId += (Math.random() * 10).toFixed(8).toString();
				}

				return retId;
			}
		}());
	}());

	var namespace = 'mightySlider',
		minnamespace  = 'mS',
		mightySliderInstances = [],
		videoRegularExpressions = [
			{
				reg:    /youtu\.be\//i,
				split:  '/',
				index:  3,
				iframe: 1,
				url:    "https://www.youtube.com/embed/{id}?autoplay=1&fs=1&rel=0&enablejsapi=1&wmode=opaque"
			},
			{
				reg:    /youtube\.com\/watch/i,
				split:  '=',
				index:  1,
				iframe: 1,
				url:    "https://www.youtube.com/embed/{id}?autoplay=1&fs=1&rel=0&enablejsapi=1&wmode=opaque"
			},
			{
				reg:    /vimeo\.com\//i,
				split:  '/',
				index:  3,
				iframe: 1,
				url:    "https://player.vimeo.com/video/{id}?hd=1&autoplay=1&show_title=1&show_byline=1&show_portrait=0&color=&fullscreen=1&api=1"
			},
			{
				reg:   /metacafe\.com\/watch/i,
				split: '/',
				index: 4,
				url:   "http://www.metacafe.com/fplayer/{id}/.swf?playerVars=autoPlay=yes"
			},
			{
				reg:   /dailymotion\.com\/video/i,
				split: '/',
				index: 4,
				url:   "http://www.dailymotion.com/swf/video/{id}?additionalInfos=0&autoStart=1"
			},
			{
				reg:   /gametrailers\.com/i,
				split: '/',
				index: 5,
				url:   "http://www.gametrailers.com/remote_wrap.php?mid={id}"
			},
			{
				reg:   /collegehumor\.com\/video\//i,
				split: 'video/',
				index: 1,
				url:   "http://www.collegehumor.com/moogaloop/moogaloop.jukebox.swf?autostart=true&fullscreen=1&use_node_id=true&clip_id={id}"
			},
			{
				reg:   /collegehumor\.com\/video:/i,
				split: 'video:',
				index: 1,
				url:   "http://www.collegehumor.com/moogaloop/moogaloop.swf?autoplay=true&fullscreen=1&clip_id={id}"
			},
			{
				reg:   /ustream\.tv/i,
				split: '/',
				index: 4,
				url:   "http://www.ustream.tv/flash/video/{id}?loc=%2F&autoplay=true&vid={id}&disabledComment=true&beginPercent=0.5331&endPercent=0.6292&locale=en_US"
			},
			{
				reg:   /twitvid\.com/i,
				split: '/',
				index: 3,
				url:   "http://www.twitvid.com/player/{id}"
			},
			{
				reg:   /vine\.co\/v\//i,
				split: '/',
				index: 4,
				url:   "https://vine.co/v/{id}/embed/simple"
			},
			{
				reg:   /v\.wordpress\.com/i,
				split: '/',
				index: 3,
				url:   "http://s0.videopress.com/player.swf?guid={id}&v=1.01"
			},
			{
				reg:   /google\.com\/videoplay/i,
				split: '=',
				index: 1,
				url:   "http://video.google.com/googleplayer.swf?autoplay=1&hl=en&docId={id}"
			},
			{
				reg:   /vzaar\.com\/videos/i,
				split: '/',
				index: 4,
				url:   "http://view.vzaar.com/{id}.flashplayer?autoplay=true&border=none"
			}
		],
		JSONReader = '//api.mightyslider.com/getjson.php',
		photoRegularExpressions = [
			{
				reg:     /vimeo\.com\//i,
				oembed:  'https://vimeo.com/api/oembed.json?url={URL}',
				inJSON:  'thumbnail_url'
			},
			{
				reg:     /youtube\.com\/watch/i,
				oembed:  'https://www.youtube.com/oembed?url={URL}&format=json',
				inJSON:  'thumbnail_url',
				replace: {
					from: 'hqdefault.jpg',
					to:   'maxresdefault.jpg'
				}
			},
			{
				reg:    /dailymotion\.com\/video/i,
				oembed: 'http://www.dailymotion.com/services/oembed?format=json&url={URL}',
				inJSON: 'url'
			},
			{
				reg:    /vine\.co\/v\//i,
				oembed: 'https://vine.co/oembed.json?url={URL}',
				inJSON: 'thumbnail_url'
			},
			{
				reg:     /500px\.com\/photo\/([0-9]+)/i,
				oembed:  '{URL}/oembed.json',
				inJSON:  'thumbnail_url',
				replace: {
					from: '3.jpg',
					to:   '5.jpg'
				}
			},
			{
				reg:    /flickr\.com\/photos\/([^\/]+)\/([0-9]+)/i,
				oembed: 'https://www.flickr.com/services/oembed?url={URL}&format=json',
				inJSON: 'url'
			},
			{
				reg:    /instagram\.com\/p\//i,
				oembed: 'http://api.instagram.com/oembed?url={URL}',
				inJSON: 'url'
			},
			{
				reg:    /deviantart\.com\/p\//i,
				oembed: 'http://backend.deviantart.com/oembed?url={URL}',
				inJSON: 'url'
			}
		],
		videoTypes = {
			'avi' : 'video/msvideo',
			'mov' : 'video/quicktime',
			'mpg' : 'video/mpeg',
			'mpeg': 'video/mpeg',
			'mp4' : 'video/mp4',
			'webm': 'video/webm',
			'ogv' : 'video/ogg',
			'3gp' : 'video/3gpp',
			'm4v' : 'video/x-m4v'
		},
		extensions = {
			flash: 'swf',
			image: 'bmp gif jpeg jpg png tiff tif jfif jpe',
			video: 'avi mov mpg mpeg mp4 webm ogv 3gp m4v'
		},
		tmpArray = [],
		interactiveElements = ['INPUT', 'SELECT', 'BUTTON', 'TEXTAREA'],
		time,

		// HTML5 video tag default attributes
		videoDefaultAttributes = {
			width: '100%',
			height: '100%',
			preload: 'preload',
			autoplay: 'autoplay',
			controls: 'controls'
		},

		// iframe tag default attributes
		iframeDefaultAttributes = {
			width: '100%',
			height: '100%',
			frameborder: 0,
			webkitAllowFullScreen: true,
			mozallowfullscreen: true,
			allowFullScreen: true
		},

		// embed tag default attributes
		embedDefaultAttributes = {
			width: '100%',
			height: '100%',
			bgcolor: '#000000',
			quality: 'high',
			play: true,
			loop: true,
			menu: true,
			wmode: 'transparent',
			scale: 'showall',
			allowScriptAccess: 'always',
			allowFullScreen: true,
			fullscreen: 'yes'
		},

		captionResponsiveStyles = [
			// captions scale able styles
			'width',
			'height',
			'fontSize',
			'fontSize',
			'top',
			'left',
			'paddingTop',
			'paddingLeft',
			'paddingBottom',
			'paddingRight'
		],

		// Global DOM elements
		$win = $(window),
		$doc = $(document),

		// Events
		clickEvent = 'click.' + namespace,
		mouseDownEvent = 'touchstart.' + namespace + ' mousedown.' + namespace,
		wheelEvent = (document.implementation.hasFeature('Event.wheel', '3.0') ? 'wheel.' : 'mousewheel.') + namespace,
		dragInitEvents = 'touchstart.' + namespace + ' mousedown.' + namespace,
		dragMouseEvents = 'mousemove.' + namespace + ' mouseup.' + namespace,
		dragTouchEvents = 'touchmove.' + namespace + ' touchend.' + namespace,
		hoverEvent = 'mouseenter.' + namespace + ' mouseleave.' + namespace,

		// Local WindowAnimationTiming interface
		requestAnimationFrame = window.requestAnimationFrame,
		cancelAnimationFrame = window.cancelAnimationFrame || window.cancelRequestAnimationFrame,

		// Support indicators
		transform, gpuAcceleration, visibilityEvent, visibilityHidden,
		supportTouch  = !!('ontouchstart' in window) && (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)),
		orientation = typeof window.orientation !== 'undefined' ? window.orientation : ($win.height() > $win.width() ? 0 : 90),
		orientationSupport = !!window.DeviceOrientationEvent,
		_now = Date.now || function () {return new Date().getTime();},

		// Speed access to frequently called functions
		_each = _.each,
		_bind = _.bind,
		_unbind = _.unbind,
		_position = _.position,
		tweenLite = window.GreenSockGlobals && window.GreenSockGlobals.TweenLite;

	// Keep track of last fired global wheel event
	var lastWheel = 0;
	$doc.on(wheelEvent, function (event) {
		var mSEvent = event.originalEvent[namespace];
		var time = _now();
		// Update last wheel time, but only when event didn't originate
		// in mightySlider frame, or the origin was less than scrollHijack time ago
		if (!mSEvent || mSEvent.options.scrolling.hijack < time - lastWheel) lastWheel = time;
	});

	// Spy the user viewport to just show the sliders in the viewport
	var windowSpy = new jQuery.elementSpy(window, function (entered) {
		if (!mightySliderInstances.length) {
			return;
		}

		var frame = this;
		var instance;

		_each(mightySliderInstances, function(ins) {
			if (ins.frame === frame) {
				instance = ins;
			}
		});

		if (!instance) {
			return;
		}

		// Unfreeze entered slider
		if (entered) {
			instance.unFreeze();
		}
		// Freeze leaved slider
		else {
			instance.freeze();
		}
	});

	/**
	 * mightySlider.
	 *
	 * @class
	 *
	 * @param {Element} frame       DOM element of mightySlider container.
	 * @param {Object}  options     Object with options.
	 * @param {Object}  callbackMap Callbacks map.
	 */
	function mightySlider(frame, options, callbackMap) {
		var self = this;

		// Merge options deeply
		var o = $.extend(true, {}, mightySlider.defaults, options),

			// Frame
			$frame = $(frame),
			$parent = $(frame).parent(),
			$slideElement = $frame.children().eq(0),
			frameInlineOptions = getInlineOptions($frame),
			autoScale = o.autoScale && ( frameInlineOptions.height && { width: frameInlineOptions.width, height: frameInlineOptions.height } || { width: $parent.width(), height: $parent.height() }) || null,
			frameSize = 0,
			frameRatio = 1,
			slideElementSize = 0,
			pos = {
				current: 0,
				start: 0,
				center: 0,
				end: 0,
				destination: 0
			},

			// Slides
			$slides = 0,
			items = [],
			rel = {
				activeSlide: -1,
				firstSlide: 0,
				centerSlide: 0,
				lastSlide: 0,
				activePage: 0
			},

			// Navigation
			basicNav = o.navigation.navigationType === 'basic',
			forceCenteredNav = o.navigation.navigationType === 'forceCentered',
			centeredNav = o.navigation.navigationType === 'centered' || forceCenteredNav,
			navigationType = (basicNav || centeredNav || forceCenteredNav),

			// Scrollbar
			$scrollbar = $(o.scrollBar.scrollBarSource).eq(0),
			$handle = $scrollbar.children().eq(0),
			scrollbarSize = 0,
			handleSize = 0,
			hPos = {
				start: 0,
				end: 0,
				current: 0
			},

			// Pagesbar
			$pagesBar = o.pages.pagesBar && $(o.pages.pagesBar) || {},
			$pages = 0,
			pages = [],

			// Thumbnails bar
			$thumbnailsBar = o.thumbnails.thumbnailsBar && $(o.thumbnails.thumbnailsBar) || {},
			$thumbnails = 0,
			thumbnails = [],
			thumbnailNav = null,
			thumbnailNavOptions = {},

			// Scrolling and Dragging
			$scrollSource = o.scrolling.scrollSource && $(o.scrolling.scrollSource) || $frame,
			$dragSource = o.dragging.dragSource && $(o.dragging.dragSource) || $frame,
			dragging = {
				released: 1
			},

			// Buttons
			$forwardButton = $(o.buttons.forward),
			$backwardButton = $(o.buttons.backward),
			$prevButton = $(o.buttons.prev),
			$nextButton = $(o.buttons.next),
			$prevPageButton = $(o.buttons.prevPage),
			$nextPageButton = $(o.buttons.nextPage),
			$fullScreenButton = $(o.buttons.fullScreen),

			// Layers
			parallax = {},
			parallaxTo = {},
			captionParallax = {},
			captionID = 0,
			captionHistory = [],
			captionRendered = -1,
			scrollParallaxCaptions = [],
			scrollParallaxSlide = null,
			parallaxTween,
			scalers = [],

			// Miscellaneous
			inserted = 0,
			hashLock = 0,
			callbacks = {},
			last = {},
			animation = {},
			move = {},
			scrolling = {
				last: 0,
				delta: 0,
				resetTime: 200
			},
			renderID = 0,
			historyID = 0,
			cycleID = 0,
			cycleLastTime = 0,
			continuousID = 0,
			resizeID = 0,
			freezeID = 0,
			mediaEnabled = null,
			uniqId = _.uniqID(namespace),
			fxEasing = $.easing[o.easing] || $.easing['swing'],
			i, l,
			
			// Events
			resizeEvent = 'resize.' + uniqId + ' orientationchange.' + uniqId,
			hashChangeEvent = 'hashchange.' + uniqId,
			keyDownEvent = 'keydown.' + uniqId,
			mouseEnterEvent = 'mouseenter.' + uniqId,
			mouseLeaveEvent = 'mouseleave.' + uniqId,
			mouseMoveEvent = 'mousemove.' + uniqId,
			deviceOrientationEvent = 'deviceorientation.' + uniqId,
			visibilityChangeEvent = visibilityEvent + '.' + uniqId;

		// Expose properties
		self.initialized = 0,
		self.options = o,
		self.frame = $frame[0],
		self.slideElement = $slideElement[0],
		self.slides = items,
		self.position = pos,
		self.relative = rel,
		self.pages = pages,
		self.thumbnails = thumbnails,
		self.handlePosition = hPos,
		self.isFullScreen = 0,
		self.isPaused = 0,
		self.isFreezed = 0,
		self.progressElapsed = 0,
		self.uniqId = uniqId;

		/**
		 * (Re)Loading function.
		 *
		 * Populate arrays, set sizes, bind events, ...
		 * @param {Boolean} [isInit] Whether load is called from within self.init().
		 *
		 * @return {Object}
		 */
		function load(isInit) {
			// Local variables
			var lastSlidesCount = 0,
				lastPagesCount = pages.length,
				matchMedia = 0,
				slidesLength = 0;

			// Auto scale slider if options.autoScale is enabled
			if (o.autoScale) {
				scaleSlider();
			}

			// Save old position
			pos.old = $.extend({}, pos),

			// Reset global variables
			frameSize = $frame[o.navigation.horizontal ? 'width' : 'height'](),
			scrollbarSize = $scrollbar[o.navigation.horizontal ? 'width' : 'height'](),
			slideElementSize = $slideElement[o.navigation.horizontal ? 'outerWidth' : 'outerHeight'](),
			pages.length = 0,

			// Set position limits & relatives
			pos.start = 0,
			pos.end = Math.max(slideElementSize - frameSize, 0);

			// Sizes & offsets for slide based navigations
			if (navigationType) {
				// Save the number of current slides
				lastSlidesCount = items.length;

				// Reset navigationType related variables
				$slides = $slideElement.children(o.navigation.slideSelector);
				items.length = 0;

				// Needed variables
				var paddingStart = getPixel($slideElement, o.navigation.horizontal ? 'paddingLeft' : 'paddingTop'),
					paddingEnd = getPixel($slideElement, o.navigation.horizontal ? 'paddingRight' : 'paddingBottom'),
					borderBox = $($slides).css('boxSizing') === 'border-box',
					areFloated = $slides.css('float') !== 'none',
					ignoredMargin = 0,
					lastSlideIndex = $slides.length - 1,
					lastSlide;

				// Reset slideElementSize
				slideElementSize = 0;

				var $slide, slideOptions, slideType, property, slideMarginStart, slideMarginEnd, slideSizeFull, slideSize, singleSpaced, slide, $caption, captionOptions, captionType, captionAnimation, captionStyles, captionData;

				// Iterate through slides
				_each($slides, function(element, i){
					// Slide
					$slide = $(element),
					slideOptions = getInlineOptions($slide),
					slideType = getSlideType(slideOptions),
					property = slideOptions.size || o.navigation.slideSize,
					slideMarginStart = getPixel($slide, o.navigation.horizontal ? 'marginLeft' : 'marginTop'),
					slideMarginEnd = getPixel($slide, o.navigation.horizontal ? 'marginRight' : 'marginBottom'),
					slideSize = getSlideSize(element, property),
					slideSizeFull = slideSize + slideMarginStart + slideMarginEnd,
					singleSpaced = !slideMarginStart || !slideMarginEnd,
					slide = {};

					slide.element = element,
					slide.options = slideOptions,
					slide.type = slideType,
					slide.captions = [];

					if (o.deeplinking.linkID) {
						slide.ID = slideOptions.ID && rawurlencode(slideOptions.ID) || i;
					}

					slide.size = singleSpaced ? slideSize : slideSizeFull;
					slide.half = slide.size / 2;
					slide.start = slideElementSize + (singleSpaced ? slideMarginStart : 0);
					slide.center = slide.start - Math.round(frameSize / 2 - slide.size / 2);
					slide.end = slide.start - frameSize + slide.size;

					// Add captions to slide object
					_each(element.getElementsByClassName(minnamespace + 'Caption'), function(caption, index){
						var $caption = $(caption),
							captionOptions = getInlineOptions($caption),
							captionType = getSlideType(captionOptions),
							captionAnimation = getCaptionKeyFrames($caption),
							captionStyles = $caption.data(minnamespace + 'styles');

						// Remove the empty keyframes
						if (captionAnimation.length) {
							captionAnimation = filter.call(captionAnimation, function(value){
								return value && (value.style || value.delay);
							});
						}

						var captionData = {
								element: caption,
								type: captionType,
								options: captionOptions,
								animation: captionAnimation
							};

						if (captionStyles && resizeID && o.autoScale) {
							// Set necessary caption styles
							$caption.css(normalizeStyles($.extend({}, captionStyles, captionAnimation[captionAnimation.length - 1] && captionAnimation[captionAnimation.length - 1].style || {}), captionResponsiveStyles, frameRatio));
						}

						// Set isParallax value to slide if any captions has parallaxLevel options
						if (!slide.isParallax && captionOptions.parallaxLevel) {
							slide.isParallax = 1;
						}

						// Set hasCaptionMediaEnabled value to slide if any captions has media options
						if (!slide.hasCaptionMediaEnabled && (captionOptions.cover || captionOptions.video || captionOptions.source)) {
							slide.hasCaptionMediaEnabled = 1;
						}

						push.call(slide.captions, captionData);
					});

					// Normalize slide size for responsive purpose
					if(property) {
						$slide[0].style[o.navigation.horizontal ? 'width' : 'height'] = slideSize + 'px';
					}

					// Account for slideElementSize padding
					if (!i) {
						slideElementSize += paddingStart;
					}

					// Increment slideElement size for size of the active element
					slideElementSize += slideSizeFull;

					// Try to account for vertical margin collapsing in vertical mode
					// It's not bulletproof, but should work in 99% of cases
					if (!o.navigation.horizontal && !areFloated) {
						// Subtract smaller margin, but only when top margin is not 0, and this is not the first element
						if (slideMarginEnd && slideMarginStart && i > 0) {
							slideElementSize -= Math.min(slideMarginStart, slideMarginEnd);
						}
					}

					// Things to be done on last slide
					if (i === lastSlideIndex) {
						slide.end += paddingEnd;
						slideElementSize += paddingEnd;
						ignoredMargin = singleSpaced ? slideMarginEnd : 0;
					}

					// If is necessary to use matchMedia
					if ($.isArray(slideOptions.cover)) {
						matchMedia = 1;
					}

					// Add id to the slide element if deeplinking linkID & scrollTo is available
					if (o.deeplinking.linkID && o.deeplinking.scrollTo) {
						element.id = o.deeplinking.linkID + o.deeplinking.separator + slide.ID;
					}

					// Add slide object to slides array
					push.call(items, slide);
					lastSlide = slide;
				});

				// Resize slideElement to fit all slides
				$slideElement[0].style[o.navigation.horizontal ? 'width' : 'height'] = (borderBox ? slideElementSize : slideElementSize - paddingStart - paddingEnd) + 'px';

				// Adjust internal slideElement size for last margin
				slideElementSize -= ignoredMargin;

				slidesLength = items.length;

				// Set limits
				if (slidesLength) {
					pos.start =  items[0][forceCenteredNav ? 'center' : 'start'];
					pos.end = forceCenteredNav ? lastSlide.center : frameSize < slideElementSize ? lastSlide.end : pos.start;
				} else {
					pos.start = pos.end = 0;
				}
			}

			// Calculate slideElement center position
			pos.center = Math.round(pos.end / 2 + pos.start / 2);

			// Update relative positions
			updateRelatives();

			// Scrollbar
			if ($handle[0] && scrollbarSize > 0) {
				// Stretch scrollbar handle to represent the visible area
				if (o.scrollBar.dynamicHandle) {
					handleSize = pos.start === pos.end ? scrollbarSize : Math.round(scrollbarSize * frameSize / slideElementSize);
					handleSize = within(handleSize, o.scrollBar.minHandleSize, scrollbarSize);
					$handle[0].style[o.navigation.horizontal ? 'width' : 'height'] = handleSize + 'px';
				} else {
					handleSize = $handle[o.navigation.horizontal ? 'outerWidth' : 'outerHeight']();
				}

				hPos.end = scrollbarSize - handleSize;

				if (!renderID) {
					syncScrollbar();
				}
			}

			// Pages
			if (frameSize > 0) {
				var tempPagePos = pos.start,
					pagesHtml = '';

				// Populate pages array
				if (navigationType) {
					for (var i = 0, slide; i < slidesLength; i++) {
						slide = items[i];

						if (forceCenteredNav) {
							push.call(pages, slide.center);
						} else if (slide.start + slide.size > tempPagePos && tempPagePos <= pos.end) {
							tempPagePos = slide.start;
							push.call(pages, tempPagePos);
							tempPagePos += frameSize;
							if (tempPagePos > pos.end && tempPagePos < pos.end + frameSize) {
								push.call(pages, pos.end);
							}
						}
					}
				} else {
					while (tempPagePos - frameSize < pos.end) {
						push.call(pages, tempPagePos);
						tempPagePos += frameSize;
					}
				}

				// Pages bar
				var pagesLength = pages.length;
				if ($pagesBar[0] && lastPagesCount !== pagesLength) {
					for (var i = 0; i < pagesLength; i++) {
						pagesHtml += o.pages.pageBuilder.call(self, i);
					}
					$pages = $pagesBar.html(pagesHtml).children();
					$pages.eq(rel.activePage).addClass(o.classes.activeClass);
				}
			}

			// Thumbnails
			if (slidesLength > 0 && (!self.initialized || (self.initialized && inserted))) {
				syncThumbnailsbar();
			}

			// Extend relative variables object with some useful info
			rel.slideElementSize = slideElementSize;
			rel.frameSize = frameSize;
			rel.scrollbarSize = scrollbarSize;
			rel.handleSize = handleSize;

			// Activate requested position
			if (navigationType) {
				if (isInit && o.startAt != null) {
					activate(o.startAt);
					self[centeredNav ? 'toCenter' : 'toStart'](o.startAt, 1);
				}
				// Fix possible overflowing
				var activeSlide = items[rel.activeSlide];
				slideTo(centeredNav && activeSlide ? activeSlide.center : within(pos.destination, pos.start, pos.end), 1);
			} else {
				if (isInit) {
					if (o.startAt != null) slideTo(o.startAt, 1);
				} else {
					// Fix possible overflowing
					slideTo(within(pos.destination, pos.start, pos.end), 1);
				}
			}

			// Set slides cover & icons
			if(!self.initialized || (resizeID && matchMedia) || (self.initialized && inserted)) {
				setSlidesCovers();
				if(!resizeID) {
					setSlidesIcons();
				}
			}

			if (o.autoResize) {
				resizeFrame(within(rel.activeSlide, rel.firstSlide, rel.lastSlide), 1);
			}

			// Reposition slides contents
			if(self.initialized) {
				repositionCovers();
			}

			// Trigger :load event
			trigger('load');

			return self;
		}
		self.reload = load;

		/**
		 * Animate to a position.
		 *
		 * @param {Int}  newPos    New position.
		 * @param {Bool} immediate Reposition immediately without an animation.
		 * @param {Bool} dontAlign Do not align slides, use the raw position passed in first argument.
		 *
		 * @return {Void}
		 */
		function slideTo(newPos, immediate, dontAlign) {
			// Align slides
			if (navigationType && dragging.released && !dontAlign) {
				var tempRel = getRelatives(newPos),
					isNotBordering = newPos > pos.start && newPos < pos.end;

				if (centeredNav) {
					if (isNotBordering) {
						newPos = items[tempRel.centerSlide].center;
					}
					if (forceCenteredNav && o.navigation.activateMiddle) {
						activate(tempRel.centerSlide);
					}
				} else if (isNotBordering) {
					newPos = items[tempRel.firstSlide].start;
				}
			}

			// Handle overflowing position limits
			if (dragging.init && dragging.slideElement && o.dragging.elasticBounds) {
				if (newPos > pos.end) {
					newPos = pos.end + (newPos - pos.end) / 6;
				} else if (newPos < pos.start) {
					newPos = pos.start + (newPos - pos.start) / 6;
				}
			} else {
				newPos = within(newPos, pos.start, pos.end);
			}

			// Update the animation object
			animation.start = _now();
			animation.time = 0;
			animation.from = pos.current;
			animation.to = newPos;
			animation.delta = newPos - pos.current;
			animation.tweesing = dragging.tweese || dragging.init && !dragging.slideElement;
			animation.immediate = !animation.tweesing && (immediate || dragging.init && dragging.slideElement || !o.speed);

			// Reset dragging tweesing request
			dragging.tweese = 0;

			// Start animation rendering
			if (newPos !== pos.destination) {
				pos.destination = newPos;
				// Trigger :change event
				trigger('change');
				if (!renderID) {
					render();
				}
			}

			// Reset next cycle timeout
			resetCycle();

			// Synchronize states
			updateRelatives();
			updateButtonsState();
			syncPagesbar();
		}

		/**
		 * Render animation frame.
		 *
		 * @return {Void}
		 */
		function render() {
			// If first render call, wait for next animationFrame
			if (!renderID) {
				renderID = requestAnimationFrame(render);
				if (dragging.released) {
					// Trigger :moveStart event
					trigger('moveStart');
				}
				return;
			}

			// If immediate repositioning is requested, don't animate.
			if (animation.immediate) {
				pos.current = animation.to;
			}
			// Use tweesing for animations without known end point
			else if (animation.tweesing) {
				animation.tweeseDelta = animation.to - pos.current;
				// Fuck Zeno's paradox
				if (Math.abs(animation.tweeseDelta) < 0.1) {
					pos.current = animation.to;
				} else {
					pos.current += animation.tweeseDelta * (dragging.released ? o.dragging.swingSpeed : o.dragging.syncSpeed);
				}
			}
			// Use tweening for basic animations with known end point
			else {
				animation.time = Math.min(_now() - animation.start, o.speed);
				pos.current = animation.from + animation.delta * fxEasing(animation.time/o.speed, animation.time, 0, 1, o.speed);
			}

			// If there is nothing more to render break the rendering loop, otherwise request new animation frame.
			if (animation.to === pos.current) {
				pos.current = animation.to;
				dragging.tweese = renderID = 0;
			} else {
				renderID = requestAnimationFrame(render);
			}

			// Trigger :move event
			trigger('move');

			// Update slideElement position
			if (transform) {
				$slideElement[0].style[transform] = gpuAcceleration + (o.navigation.horizontal ? 'translateX' : 'translateY') + '(' + (-pos.current) + 'px)';
			} else {
				$slideElement[0].style[o.navigation.horizontal ? 'left' : 'top'] = -Math.round(pos.current) + 'px';
			}

			// When animation reached the end, and dragging is not active, trigger moveEnd
			if (!renderID && dragging.released) {
				// Set slides covers
				setSlidesCovers();

				// Trigger :moveEnd event
				trigger('moveEnd');
			}

			syncScrollbar();
		}

		/**
		 * Synchronizes scrollbar with the SLIDEELEMENT.
		 *
		 * @return {Void}
		 */
		function syncScrollbar() {
			if ($handle.length) {
				hPos.current = pos.start === pos.end ? 0 : (((dragging.init && !dragging.slideElement) ? pos.destination : pos.current) - pos.start) / (pos.end - pos.start) * hPos.end;
				hPos.current = within(Math.round(hPos.current), hPos.start, hPos.end);
				if (last.hPos !== hPos.current) {
					last.hPos = hPos.current;
					if (transform) {
						$handle[0].style[transform] = gpuAcceleration + (o.navigation.horizontal ? 'translateX' : 'translateY') + '(' + hPos.current + 'px)';
					} else {
						$handle[0].style[o.navigation.horizontal ? 'left' : 'top'] = hPos.current + 'px';
					}
				}
			}
		}

		/**
		 * Synchronizes pagesbar with slideElement.
		 *
		 * @return {Void}
		 */
		function syncPagesbar() {
			if ($pages[0] && last.page !== rel.activePage) {
				last.page = rel.activePage;
				$pages.removeClass(o.classes.activeClass).eq(rel.activePage).addClass(o.classes.activeClass);
				// Trigger :activePage event
				trigger('activePage', last.page);
			}
		}

		/**
		 * Synchronizes thumbnailsbar.
		 *
		 * @return {Void}
		 */
		function syncThumbnailsbar() {
			var thumbnailsHtml = '';

			// Populate thumbnails array
			for (var i = 0, len = items.length, slide, thumbnail; i < len; i++) {
				slide = items[i],
				thumbnail = slide.options.thumbnail || slide.options.cover || 1;

				push.call(thumbnails, thumbnail);
				if ($thumbnailsBar[0] && thumbnail) {
					thumbnailsHtml += o.thumbnails.thumbnailBuilder.call(self, i, thumbnail);
				}
			}

			// Thumbnails bar
			if ($thumbnailsBar[0]) {
				$thumbnails = $thumbnailsBar.html(thumbnailsHtml).children();

				if (o.thumbnails.thumbnailNav) {
					if (thumbnailNav) {
						thumbnailNav.destroy();
					}
					else {
						$.extend(true, thumbnailNavOptions, {
							moveBy: o.moveBy,
							speed: type(o.thumbnails.speed) !== 'undefined' ? o.thumbnails.speed : o.speed,
							easing: o.easing,
							startAt: o.startAt,

							// Navigation options
							navigation: {
								horizontal: o.thumbnails.horizontal,
								navigationType: o.thumbnails.thumbnailNav,
								slideSize: o.thumbnails.thumbnailSize,
								activateOn: o.thumbnails.activateOn
							},

							// Scrolling options
							scrolling: {
								scrollBy: o.thumbnails.scrollBy
							},

							// Dragging options
							dragging: {
								mouseDragging: o.thumbnails.mouseDragging,
								touchDragging: o.thumbnails.touchDragging,
								swingSpeed: o.dragging.swingSpeed,
								elasticBounds: o.dragging.elasticBounds
							}
						});
						
						thumbnailNav = new mightySlider($thumbnailsBar.parent(), thumbnailNavOptions, {
							active: function(name, index) {
								if (index != rel.activeSlide) {
									self.activate(index);
								}
							}
						});
					}

					// Preload thumbnails then initialize thumbnails slider
					if (o.thumbnails.preloadThumbnails) {
						preloadimages(thumbnails).done(function() {
							thumbnailNav.init();
							thumbnailNav.reload();
						});
					}
					else {
						thumbnailNav.init();
						thumbnailNav.reload();
					}
				}
			}
		}

		/**
		 * Scale slider
		 *
		 * @return {Void}
		 */
		function scaleSlider() {
			var parentSize = $parent.width(),
				ratio = parentSize / autoScale.width;

			// Remember frame ratio
			frameRatio = ratio;

			$frame.height(autoScale.height * ratio);
		}

		/**
		 * Returns the position object.
		 *
		 * @param {Mixed} slide
		 *
		 * @return {Object}
		 */
		self.getPosition = function (slide) {
			if (navigationType) {
				var index = getIndex(slide);
				return index !== -1 ? items[index] : false;
			} else {
				var $slide = $slideElement.find(slide).eq(0);

				if ($slide[0]) {
					var offset = o.navigation.horizontal ? $slide.offset().left - $slideElement.offset().left : $slide.offset().top - $slideElement.offset().top;
					var size = $slide[o.navigation.horizontal ? 'outerWidth' : 'outerHeight']();

					return {
						start: offset,
						center: offset - frameSize / 2 + size / 2,
						end: offset - frameSize + size,
						size: size
					};
				} else {
					return false;
				}
			}
		};

		/**
		 * Continuous move in a specified direction.
		 *
		 * @param  {Bool} forward True for forward movement, otherwise it'll go backwards.
		 * @param  {Int}  speed   Movement speed in pixels per frame. Overrides options.moveBy value.
		 *
		 * @return {Void}
		 */
		self.moveBy = function (speed) {
			move.speed = speed;
			// If already initiated, or there is nowhere to move, abort
			if (dragging.init || !move.speed || pos.current === (move.speed > 0 ? pos.end : pos.start)) {
				return;
			}
			// Initiate move object
			move.lastTime = _now();
			move.startPos = pos.current;
			// Set dragging as initiated
			continuousInit('button');
			dragging.init = 1;
			// Start movement
			// Trigger :moveStart event
			trigger('moveStart');
			cancelAnimationFrame(continuousID);
			moveLoop();
		};

		/**
		 * Continuous movement loop.
		 *
		 * @return {Void}
		 */
		function moveLoop() {
			// If there is nowhere to move anymore, stop
			if (!move.speed || pos.current === (move.speed > 0 ? pos.end : pos.start)) {
				self.stop();
			}
			// Request new move loop if it hasn't been stopped
			continuousID = dragging.init ? requestAnimationFrame(moveLoop) : 0;
			// Update move object
			move.now = _now();
			move.pos = pos.current + (move.now - move.lastTime) / 1000 * move.speed;
			// Slide
			slideTo(dragging.init ? move.pos : Math.round(move.pos));
			// Normally, this is triggered in render(), but if there
			// is nothing to render, we have to do it manually here.
			if (!dragging.init && pos.current === pos.destination) {
				// Trigger :moveEnd event
				trigger('moveEnd');
			}
			// Update times for future iteration
			move.lastTime = move.now;
		}

		/**
		 * Stops continuous movement.
		 *
		 * @return {Object}
		 */
		self.stop = function () {
			if (dragging.source === 'button') {
				dragging.init = 0;
				dragging.released = 1;
			}

			return self;
		};

		/**
		 * Activate previous slide.
		 *
		 * @param {Bool}  immediate Whether to reposition immediately in smart navigation.
		 *
		 * @return {Object}
		 */
		self.prev = function (immediate) {
			self.activate(rel.activeSlide - 1, immediate);

			return self;
		};

		/**
		 * Activate next slide.
		 *
		 * @param {Bool}  immediate Whether to reposition immediately in smart navigation.
		 *
		 * @return {Object}
		 */
		self.next = function (immediate) {
			self.activate(rel.activeSlide + 1, immediate);

			return self;
		};

		/**
		 * Activate previous page.
		 *
		 * @param {Bool}  immediate Whether to reposition immediately in smart navigation.
		 *
		 * @return {Object}
		 */
		self.prevPage = function (immediate) {
			self.activatePage(rel.activePage - 1, immediate);

			return self;
		};

		/**
		 * Activate next page.
		 *
		 * @param {Bool}  immediate Whether to reposition immediately in smart navigation.
		 *
		 * @return {Object}
		 */
		self.nextPage = function (immediate) {
			self.activatePage(rel.activePage + 1, immediate);

			return self;
		};

		/**
		 * Slide slideElement by amount of pixels.
		 *
		 * @param {Int}  delta     Difference in position. Positive means forward, negative means backward.
		 * @param {Bool} immediate Reposition immediately without an animation.
		 *
		 * @return {Void}
		 */
		self.slideBy = function (delta, immediate) {
			if (!delta) {
				return;
			}
			if (navigationType) {
				self[centeredNav ? 'toCenter' : 'toStart'](
					within((centeredNav ? rel.centerSlide : rel.firstSlide) + o.scrolling.scrollBy * delta, 0, items.length)
				);
			} else {
				slideTo(pos.destination + delta, immediate);
			}
		};

		/**
		 * Animate slideElement to a specific position.
		 *
		 * @param {Int}  position       New position.
		 * @param {Bool} immediate Reposition immediately without an animation.
		 *
		 * @return {Object}
		 */
		self.slideTo = function (position, immediate) {
			slideTo(position, immediate);

			return self;
		};

		/**
		 * Core method for handling `toLocation` methods.
		 *
		 * @param  {String} location
		 * @param  {Mixed}  slide
		 * @param  {Bool}   immediate
		 *
		 * @return {Void}
		 */
		function to(location, slide, immediate) {
			// Optional arguments logic
			if (type(slide) === 'boolean') {
				immediate = slide;
				slide = undefined;
			}

			if (slide === undefined) {
				slideTo(pos[location], immediate);
			}
			else {
				// You can't align slides to sides of the frame
				// when centered navigation type is enabled
				if (centeredNav && location !== 'center') {
					return;
				}

				var slideObj = self.getPosition(slide);
				if (slideObj) {
					slideTo(slideObj[location], immediate, !centeredNav);
				}
			}
		}

		/**
		 * Animate element or the whole slideElement to the start of the frame.
		 *
		 * @param {Mixed} slide      Slide DOM element, or index starting at 0. Omitting will animate slideElement.
		 * @param {Bool}  immediate Reposition immediately without an animation.
		 *
		 * @return {Object}
		 */
		self.toStart = function (slide, immediate) {
			to('start', slide, immediate);

			return self;
		};

		/**
		 * Animate element or the whole slideElement to the end of the frame.
		 *
		 * @param {Mixed} slide      Slide DOM element, or index starting at 0. Omitting will animate slideElement.
		 * @param {Bool}  immediate Reposition immediately without an animation.
		 *
		 * @return {Object}
		 */
		self.toEnd = function (slide, immediate) {
			to('end', slide, immediate);

			return self;
		};

		/**
		 * Animate element or the whole slideElement to the center of the frame.
		 *
		 * @param {Mixed} slide      Slide DOM element, or index starting at 0. Omitting will animate slideElement.
		 * @param {Bool}  immediate Reposition immediately without an animation.
		 *
		 * @return {Object}
		 */
		self.toCenter = function (slide, immediate) {
			to('center', slide, immediate);

			return self;
		};

		/**
		 * Get the index of an slide in slideElement.
		 *
		 * @param {Mixed} slide     Slide DOM element.
		 *
		 * @return {Int}  Slide     index, or -1 if not found.
		 */
		function getIndex(slide) {
			return type(slide) !== 'undefined' ?
					is_numeric(slide) ?
						slide >= 0 && slide < items.length ? slide : -1 :
						$slides.index(slide) :
					-1;
		}
		// Expose getIndex without lowering the compressibility of it,
		// as it is used quite often throughout mightySlider.
		self.getIndex = getIndex;

		/**
		 * Get index of an slide in slideElement based on a variety of input types.
		 *
		 * @param  {Mixed} slide   DOM element, positive or negative integer.
		 *
		 * @return {Int}   Slide   index, or -1 if not found.
		 */
		function getRelativeIndex(slide) {
			return getIndex(is_numeric(slide) && slide < 0 ? slide + items.length : slide);
		}

		/**
		 * Activates an slide.
		 *
		 * @param  {Mixed} slide       Slide DOM element, or index starting at 0.
		 *
		 * @return {Mixed} Activated   slide index or false on fail.
		 */
		function activate(slide) {
			var index = getIndex(slide),
				lastActive = rel.activeSlide;

			if (!navigationType || index < 0) {
				return false;
			}

			// Update classes, last active index, and trigger active event only when there
			// has been a change. Otherwise just return the current active index.
			if (last.active !== index) {
				var slideData = items[index],
					captions = slideData.captions;

				scrollParallaxCaptions = [];
				scrollParallaxSlide = null;

				// Reset cycling progress time elapsed
				if (!resizeID) {
					self.progressElapsed = 0;
				}

				// Prevent cycling loop
				if (!o.cycling.loop && index >= items.length - 1) {
					self.pause();
				}

				// Update classes
				$slides.eq(rel.activeSlide).removeClass(o.classes.activeClass);
				$slides.eq(index).addClass(o.classes.activeClass);

				// If captions in active slide are parallax
				if (items[lastActive] && items[lastActive].isParallax) {
					$parent.off('.' + uniqId);
					$win.off(deviceOrientationEvent);
				}

				// Find alowed scroll parallax captions
				if (slideData.options.scrollParallax) {
					scrollParallaxCaptions = o.parallax.scroll && captions || filter.call(captions, function(value) {
						return value.options.parallaxAxises && value.options.parallaxAxises.scroll;
					});
					scrollParallaxSlide = slideData.element;
					parallaxScrollHandler();
				}

				if (slideData.isParallax) {
					// Find alowed parallax captions
					parallax.parallaxCaptions = filter.call(captions, function (e, i, arr) {
						return !!e.options.parallaxLevel;
					});

					if (!supportTouch) {
						// Add :mouseenter event to the $parent
						$parent.on(mouseEnterEvent, function (event) {
							parallax.source = 'mouse';

							// Local variables
							var target = $parent[0],
								offset = getOffset(event.originalEvent, target),
								rect = target.getBoundingClientRect(),
								widthHalf = rect.width / 2,
								heightHalf = rect.height / 2;

							// Add :mousemove event to the $parent
							$parent.off(mouseLeaveEvent).on(mouseMoveEvent, function (e) {
								// Calculate the X & Y differences from started axises
								offset = getOffset(e.originalEvent, target);
								parallaxTo.X = offset.x - widthHalf,
								parallaxTo.Y = offset.y - heightHalf;

								// Handle parallax effect for captions
								parallaxCaptions();
							}).one(mouseLeaveEvent, function () {
								$parent.off(mouseMoveEvent);

								// Normalize parallax effect for captions
								if (o.parallax.revert) {
									parallaxTo.X = 0,
									parallaxTo.Y = 0;

									revertParallax();
								}
							});
						}).trigger(mouseEnterEvent);
					}
					else if (orientationSupport) {
						$win.on(deviceOrientationEvent, function(e) {
							var event = e.originalEvent;

							// Validate event properties.
							if (type(event) !== 'undefined' && event.beta !== null && event.gamma !== null) {
								parallax.source = 'orientation';

								// Extract Rotation
								switch (orientation) {
									case 0:
									parallaxTo.X = event.gamma,
									parallaxTo.Y = event.beta;
									break;

									case 180:
									parallaxTo.X = -event.gamma,
									parallaxTo.Y = -event.beta;
									break;

									case -90:
									parallaxTo.X = -event.beta,
									parallaxTo.Y = event.gamma;
									break;

									case 90:
									parallaxTo.X = event.beta,
									parallaxTo.Y = -event.gamma;
									break;
								}

								parallaxTo.X = parallaxTo.X * 8,
								parallaxTo.Y = parallaxTo.Y * 8;

								// Handle parallax effect for captions
								parallaxCaptions();
							}
						});
					}
				}
				else {
					parallax = {};
				}

				// Clear previous slide captions
				if (!resizeID) {
					clearCaptions(last.active);
				}

				last.active = rel.activeSlide = index;

				updateButtonsState();

				// Remove previous media content
				if (!resizeID && mediaEnabled) {
					removeContent();
				}

				// Clear caption timing if available
				if (captionID) {
					clearTimeout(captionID);
				}

				// Render captions in the current active slide
				if (captions.length && !resizeID) {
					self.one('slideLoaded', function(event, slideIndex) {
						if (slideIndex === index) {
							captionID = setTimeout(function () {
								clearTimeout(captionID);
								renderCaptions(index);
							}, lastActive < 0 ? 0 : o.speed + 20);
						}
					});
				}
				else {
					captionRendered = -1;
				}

				// Change Hashtag
				if (o.deeplinking.linkID && !resizeID && !hashLock && self.initialized) {
					changeHashtag(index);
				}

				// Load all slide inner contents
				if (!resizeID) {
					loadSlide(index);
				}

				// Resize the FRAME based on slide size
				if (o.autoResize) {
					resizeFrame(index);
				}

				// Find scaler elements
				scalers = $slides.get(index).getElementsByClassName(minnamespace + 'Scaler');
				scalersHandler($frame, scalers);

				// Trigger :active event
				trigger('active', index);
			}

			return index;
		}

		/**
		 * Activates an slide and helps with further navigation when options.navigation.smart is enabled.
		 *
		 * @param {Mixed} slide      Slide DOM element, or index starting at 0.
		 * @param {Bool}  immediate  Whether to reposition immediately in smart navigation.
		 *
		 * @return {Object}
		 */
		self.activate = function (slide, immediate) {
			var index = activate(slide);

			// Smart navigation
			if (o.navigation.smart && index !== false) {
				// When centeredNav is enabled, center the element.
				// Otherwise, determine where to position the element based on its current position.
				// If the element is currently on the far end side of the frame, assume that user is
				// moving forward and animate it to the start of the visible frame, and vice versa.
				if (centeredNav) {
					self.toCenter(index, immediate);
				}
				else if (index >= rel.lastSlide) {
					self.toStart(index, immediate);
				}
				else if (index <= rel.firstSlide) {
					self.toEnd(index, immediate);
				}
				else {
					resetCycle();
				}
			}

			return self;
		};

		/**
		 * Activates a page.
		 *
		 * @param {Int}  index     Page index, starting from 0.
		 * @param {Bool} immediate Whether to reposition immediately without animation.
		 *
		 * @return {Object}
		 */
		self.activatePage = function (index, immediate) {
			if (is_numeric(index)) {
				// Reset cycling progress time elapsed
				if (!resizeID) {
					self.progressElapsed = 0;
				}

				// Prevent cycling loop
				if (!o.cycling.loop && index >= pages.length - 1) {
					self.pause();
				}

				slideTo(pages[within(index, 0, pages.length - 1)], immediate);
			}

			return self;
		};

		/**
		 * Return relative positions of slides based on their visibility within FRAME.
		 *
		 * @param {Int} slideElementPos Position of slideElement.
		 *
		 * @return {Void}
		 */
		function getRelatives(slideElementPos) {
			slideElementPos = within(is_numeric(slideElementPos) ? slideElementPos : pos.destination, pos.start, pos.end);

			var relatives = {},
				centerOffset = forceCenteredNav ? 0 : frameSize / 2;

			// Determine active page
			for (var p = 0, pl = pages.length; p < pl; p++) {
				if (slideElementPos >= pos.end || p === pages.length - 1) {
					relatives.activePage = pages.length - 1;
					break;
				}

				if (slideElementPos <= pages[p] + centerOffset) {
					relatives.activePage = p;
					break;
				}
			}

			// Relative slide indexes
			if (navigationType) {
				var first = false,
					last = false,
					center = false;

				// From start
				for (var i = 0, il = items.length; i < il; i++) {
					// First slide
					if (first === false && slideElementPos <= items[i].start + items[i].half) {
						first = i;
					}

					// Center slide
					if (center === false && slideElementPos <= items[i].center + items[i].half) {
						center = i;
					}

					// Last slide
					if (i === il - 1 || slideElementPos <= items[i].end + items[i].half) {
						last = i;
						break;
					}
				}

				// Safe assignment, just to be sure the false won't be returned
				relatives.firstSlide = is_numeric(first) ? first : 0;
				relatives.centerSlide = is_numeric(center) ? center : relatives.firstSlide;
				relatives.lastSlide = is_numeric(last) ? last : relatives.centerSlide;
			}

			return relatives;
		}

		/**
		 * Update object with relative positions.
		 *
		 * @param {Int} newPos
		 *
		 * @return {Void}
		 */
		function updateRelatives(newPos) {
			$.extend(rel, getRelatives(newPos));
		}

		/**
		 * Disable navigation buttons when needed.
		 *
		 * Adds disabledClass, and when the button is <button> or <input>, activates :disabled state.
		 *
		 * @return {Void}
		 */
		function updateButtonsState() {
			var isStart = pos.destination <= pos.start,
				isEnd = pos.destination >= pos.end,
				slideElementPosState = isStart ? 1 : isEnd ? 2 : 3;

			// Update paging buttons only if there has been a change in slideElement position
			if (last.slideElementPosState !== slideElementPosState) {
				last.slideElementPosState = slideElementPosState;

				$prevPageButton.prop('disabled', isStart).add($backwardButton)[isStart ? 'addClass' : 'removeClass'](o.classes.disabledClass);
				$nextPageButton.prop('disabled', isEnd).add($forwardButton)[isEnd ? 'addClass' : 'removeClass'](o.classes.disabledClass);
			}

			// Forward & Backward buttons need a separate state caching because we cannot "property disable"
			// them while they are being used, as disabled buttons stop emitting mouse events.
			if (last.fwdbwdState !== slideElementPosState && dragging.released) {
				last.fwdbwdState = slideElementPosState;

				$backwardButton.prop('disabled', isStart);
				$forwardButton.prop('disabled', isEnd);
			}

			// Slide navigation
			if (navigationType) {
				var isFirst = rel.activeSlide === 0,
					isLast = rel.activeSlide >= items.length - 1,
					slidesButtonState = isFirst ? 1 : isLast ? 2 : 3;

				if (last.slidesButtonState !== slidesButtonState) {
					last.slidesButtonState = slidesButtonState;

					$prevButton[isFirst ? 'addClass' : 'removeClass'](o.classes.disabledClass).prop('disabled', isFirst);
					$nextButton[isLast ? 'addClass' : 'removeClass'](o.classes.disabledClass).prop('disabled', isLast);
				}
			}
		}

		/**
		 * Resume cycling.
		 *
		 * @param {Int} priority Resume pause with priority lower or equal than this. Used internally for pauseOnHover.
		 *
		 * @return {Object}
		 */
		self.resume = function (priority) {
			if (!o.cycling.cycleBy || !o.cycling.pauseTime || o.cycling.cycleBy === 'slides' && !items[0] || priority < self.isPaused) {
				return self;
			}

			self.isPaused = 0;

			if (cycleID) {
				cycleID = cancelAnimationFrame(cycleID);
			}
			else {
				// Trigger :resume event
				trigger('resume');
			}

			var timeOut = items[rel.activeSlide] && items[rel.activeSlide].options.pauseTime || o.cycling.pauseTime,
				cyclingActivate = function () {
					switch (o.cycling.cycleBy) {
						case 'slides':
							self.activate(rel.activeSlide >= items.length - 1 ? 0 : rel.activeSlide + 1);
							break;

						case 'pages':
							self.activatePage(rel.activePage >= pages.length - 1 ? 0 : rel.activePage + 1);
							break;
					}
				},
				timestamp,
				requestHandler = function () {
					timestamp = _now();

					// Calculate progress elapsed time
					self.progressElapsed += timestamp - (cycleLastTime || _now()),
					cycleLastTime = timestamp;

					// Trigger :progress event
					trigger('progress', self.progressElapsed / timeOut);

					if (self.progressElapsed >= timeOut) {
						// Activate slides/pages by cycling
						cyclingActivate();

						requestHandler = null;
					}
					else {
						// Call next frame
						cycleID = requestAnimationFrame(requestHandler);
					}
				};

			// Call first frame
			cycleID = requestAnimationFrame(requestHandler);

			return self;
		};

		/**
		 * Pause cycling.
		 *
		 * @param {Int} priority Pause priority. 100 is default. Used internally for pauseOnHover.
		 *
		 * @return {Object}
		 */
		self.pause = function (priority) {
			if (priority < self.isPaused) {
				return self;
			}

			self.isPaused = priority || 100;

			if (cycleID) {
				cycleID = cancelAnimationFrame(cycleID);
				cycleLastTime = 0;
				// Trigger :pause event
				trigger('pause');
			}

			return self;
		};

		/**
		 * Toggle cycling.
		 *
		 * @return {Object}
		 */
		self.toggleCycling = function () {
			self[cycleID ? 'pause' : 'resume']();

			return self;
		};

		/**
		 * Enter fullscreen.
		 *
		 * @return {Object}
		 */
		self.enterFullScreen = function () {
			if (!self.isFullScreen) {
				$parent.addClass(o.classes.isInFullScreen);

				if (screenfull.enabled) {
					screenfull.request($parent[0]);
				}
				else {
					$win.triggerHandler('resize');
				}
				self.isFullScreen = 1;

				// Trigger :enterFullScreen event
				trigger('enterFullScreen');
			}

			return self;
		};

		/**
		 * Exit from fullscreen.
		 *
		 * @return {Object}
		 */
		self.exitFullScreen = function () {
			if (self.isFullScreen) {
				$parent.removeClass(o.classes.isInFullScreen);

				if (screenfull.enabled) {
					screenfull.exit($parent[0]);
				}
				else {
					$win.triggerHandler('resize');
				}
				self.isFullScreen = 0;

				// Trigger :exitFullScreen event
				trigger('exitFullScreen');
			}

			return self;
		};

		/**
		 * Toggle fullscreen.
		 *
		 * @return {Object}
		 */
		self.toggleFullScreen = function () {
			self[self.isFullScreen ? 'exitFullScreen' : 'enterFullScreen']();

			return self;
		};

		/**
		 * Updates a signle or multiple option values.
		 *
		 * @param {Mixed} name  Name of the option that should be updated, or object that will extend the options.
		 * @param {Mixed} value New option value.
		 *
		 * @return {Object}
		 */
		self.set = function (name, value) {
			if (isObject(name)) {
				$.extend(true, o, name);
			}
			else if (hasOwnProperty.call(o, name)) {
				o[name] = value;
			}

			// Set thumbnails options if thumbnail navigation is available
			if (thumbnailNav) {
				$.extend(true, thumbnailNavOptions, {
					moveBy: o.moveBy,
					speed: type(o.thumbnails.speed) !== 'undefined' ? o.thumbnails.speed : o.speed,
					easing: o.easing,
					startAt: o.startAt,

					// Navigation options
					navigation: {
						horizontal: o.thumbnails.horizontal,
						navigationType: o.thumbnails.thumbnailNav,
						slideSize: o.thumbnails.thumbnailSize,
						activateOn: o.thumbnails.activateOn
					},

					// Scrolling options
					scrolling: {
						scrollBy: o.thumbnails.scrollBy
					},

					// Dragging options
					dragging: {
						mouseDragging: o.thumbnails.mouseDragging,
						touchDragging: o.thumbnails.touchDragging,
						swingSpeed: o.dragging.swingSpeed,
						elasticBounds: o.dragging.elasticBounds
					}
				});

				thumbnailNav.set(thumbnailNavOptions);
			}

			// Reload
			load();

			return self;
		};

		/**
		 * Add one or multiple slides to the slideElement end, or a specified position index.
		 *
		 * @param {Mixed} element Node element, or HTML string.
		 * @param {Int}   index   Index of a new slide position. By default slide is appended at the end.
		 *
		 * @return {Object}
		 */
		self.add = function (element, index) {
			var $element = $(element);

			if (navigationType) {
				// Insert the element(s)
				if (type(index) === 'undefined' || !items[0] || index >= items.length) {
					$element.appendTo($slideElement);
				}
				else if (items.length) {
					$element.insertBefore(items[index].element);
				}

				$element.addClass(minnamespace + 'Slide');

				// Adjust the activeSlide index
				if (index <= rel.activeSlide) {
					last.active = rel.activeSlide += $element.length;
				}
			} else {
				$slideElement.append($element);
			}

			// Mark as inserted for load new slide content
			inserted = 1;

			// Reload
			load();

			// Unmark inserted
			inserted = 0;

			return self;
		};

		/**
		 * Remove an slide from slideElement.
		 *
		 * @param {Mixed} element Slide index, or DOM element.
		 * @param {Int}   index   Index of a new slide position. By default slide is appended at the end.
		 *
		 * @return {Object}
		 */
		self.remove = function (element) {
			if (navigationType) {
				var index = getRelativeIndex(element);

				if (index > -1) {
					// Remove the element
					$slides.eq(index).remove();

					// If the current slide is being removed, activate new one after reload
					var reactivate = index === rel.activeSlide && !(forceCenteredNav && o.navigation.activateMiddle);

					// Adjust the activeSlide index
					if (index < rel.activeSlide || rel.activeSlide >= items.length - 1) {
						last.active = --rel.activeSlide;
					}

					// Reload
					load();

					// Activate new slide at the removed position if the current active got removed
					if (reactivate) {
						self.activate(rel.activeSlide);
					}
				}
			} else {
				$(element).remove();
				load();
			}

			return self;
		};

		/**
		 * Helps re-arranging slides.
		 *
		 * @param  {Mixed} slide     Slide DOM element, or index starting at 0. Use negative numbers to select slides from the end.
		 * @param  {Mixed} position Slide insertion anchor. Accepts same input types as slide argument.
		 * @param  {Bool}  after    Insert after instead of before the anchor.
		 *
		 * @return {Void}
		 */
		function move(slide, position, after) {
			slide = getRelativeIndex(slide);
			position = getRelativeIndex(position);

			// Move only if there is an actual change requested
			if (slide > -1 && position > -1 && slide !== position && (!after || position !== slide - 1) && (after || position !== slide + 1)) {
				$slides.eq(slide)[after ? 'insertAfter' : 'insertBefore'](items[position].element);

				var shiftStart = slide < position ? slide : (after ? position : position - 1),
					shiftEnd = slide > position ? slide : (after ? position + 1 : position),
					shiftsUp = slide > position;

				// Update activeSlide index
				if (slide === rel.activeSlide) {
					last.active = rel.activeSlide = after ? (shiftsUp ? position + 1 : position) : (shiftsUp ? position : position - 1);
				}
				else if (rel.activeSlide > shiftStart && rel.activeSlide < shiftEnd) {
					last.active = rel.activeSlide += shiftsUp ? 1 : -1;
				}

				// Reload
				load();
			}
		}

		/**
		 * Move slide after the target anchor.
		 *
		 * @param  {Mixed} slide     Slide to be moved. Can be DOM element or slide index.
		 * @param  {Mixed} position Target position anchor. Can be DOM element or slide index.
		 *
		 * @return {Object}
		 */
		self.moveAfter = function (slide, position) {
			move(slide, position, 1);

			return self;
		};

		/**
		 * Move slide before the target anchor.
		 *
		 * @param  {Mixed} slide     Slide to be moved. Can be DOM element or slide index.
		 * @param  {Mixed} position Target position anchor. Can be DOM element or slide index.
		 *
		 * @return {Object}
		 */
		self.moveBefore = function (slide, position) {
			move(slide, position);

			return self;
		};

		/**
		 * Registers callbacks to be executed only once.
		 *
		 * @param  {Mixed} name  Event name, or callbacks map.
		 * @param  {Mixed} fn    Callback, or an array of callback functions.
		 *
		 * @return {Object}
		 */
		self.one = function (name, fn) {
			function proxy() {
				fn.apply(self, arguments);
				self.off(name, proxy);
			}
			self.on(name, proxy);

			return self;
		};

		/**
		 * Registers callbacks.
		 *
		 * @param  {Mixed} name  Event name, or callbacks map.
		 * @param  {Mixed} fn    Callback, or an array of callback functions.
		 *
		 * @return {Object}
		 */
		self.on = function (name, fn) {
			// Callbacks map
			if (type(name) === 'object') {
				for (var key in name) {
					if (hasOwnProperty.call(name, key)) {
						self.on(key, name[key]);
					}
				}
			// Callback
			}
			else if (type(fn) === 'function') {
				var names = name.split(' ');
				for (var n = 0, nl = names.length; n < nl; n++) {
					callbacks[names[n]] = callbacks[names[n]] || [];
					if (callbackIndex(names[n], fn) === -1) {
						push.call(callbacks[names[n]], fn);
					}
				}
			// Callbacks array
			}
			else if (type(fn) === 'array') {
				for (var f = 0, fl = fn.length; f < fl; f++) {
					self.on(name, fn[f]);
				}
			}

			return self;
		};

		/**
		 * Remove one or all callbacks.
		 *
		 * @param  {String} name Event name.
		 * @param  {Mixed}  fn   Callback, or an array of callback functions. Omit to remove all callbacks.
		 *
		 * @return {Object}
		 */
		self.off = function (name, fn) {
			if (fn instanceof Array) {
				for (var f = 0, fl = fn.length; f < fl; f++) {
					self.off(name, fn[f]);
				}
			}
			else {
				var names = name.split(' ');
				for (var n = 0, nl = names.length; n < nl; n++) {
					callbacks[names[n]] = callbacks[names[n]] || [];
					if (type(fn) === 'undefined') {
						callbacks[names[n]].length = 0;
					}
					else {
						var index = callbackIndex(names[n], fn);
						if (index !== -1) {
							splice.call(callbacks[names[n]], index, 1);
						}
					}
				}
			}

			return self;
		};

		/**
		 * Returns callback array index.
		 *
		 * @param  {String}   name Event name.
		 * @param  {Function} fn   Function
		 *
		 * @return {Int} Callback array index, or -1 if isn't registered.
		 */
		function callbackIndex(name, fn) {
			for (var i = 0, l = callbacks[name].length; i < l; i++) {
				if (callbacks[name][i] === fn) {
					return i;
				}
			}
			return -1;
		}

		/**
		 * Reset next cycle timeout.
		 *
		 * @return {Void}
		 */
		function resetCycle() {
			if (dragging.released && !self.isPaused) {
				self.resume();
			}
		}

		/**
		 * Calculate SLIDEELEMENT representation of handle position.
		 *
		 * @param  {Int} handlePos
		 *
		 * @return {Int}
		 */
		function handleToSlideElement(handlePos) {
			return Math.round(within(handlePos, hPos.start, hPos.end) / hPos.end * (pos.end - pos.start)) + pos.start;
		}

		/**
		 * Keeps track of a dragging delta history.
		 *
		 * @return {Void}
		 */
		function draggingHistoryTick() {
			// Looking at this, I know what you're thinking :) But as we need only 4 history states, doing it this way
			// as opposed to a proper loop is ~25 bytes smaller (when minified with GCC), a lot faster, and doesn't
			// generate garbage. The loop version would create 2 new variables on every tick. Unexaptable!
			dragging.history[0] = dragging.history[1];
			dragging.history[1] = dragging.history[2];
			dragging.history[2] = dragging.history[3];
			dragging.history[3] = dragging.delta;
		}

		/**
		 * Initialize continuous movement.
		 *
		 * @return {Void}
		 */
		function continuousInit(source) {
			dragging.released = 0;
			dragging.source = source;
			dragging.slideElement = source === 'slideElement';
		}

		/**
		 * Dragging initiator.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function dragInit(event) {
			var isTouch = event.type === 'touchstart';

			// Ignore when already in progress, or interactive element in non-touch navivagion
			if (dragging.init || mediaEnabled || !isTouch && isInteractive(event.target)) {
				return;
			}

			var source = event.data.source,
				isSlideElement = source === 'slideElement';

			// Handle dragging conditions
			if (source === 'handle' && (!o.scrollBar.dragHandle || hPos.start === hPos.end)) {
				return;
			}

			// slideElement dragging conditions
			if (isSlideElement && !(isTouch ? o.dragging.touchDragging : o.dragging.mouseDragging && event.which < 2)) {
				return;
			}

			// Reset dragging object
			continuousInit(source);

			// Properties used in dragHandler
			dragging.init = 0;
			dragging.$source = $(event.target);
			dragging.touch = isTouch;
			dragging.pointer = isTouch ? event.originalEvent.touches[0] : event;
			dragging.initX = dragging.pointer.pageX;
			dragging.initY = dragging.pointer.pageY;
			dragging.initPos = isSlideElement ? pos.current : hPos.current;
			dragging.initPage = rel.activePage;
			dragging.start = _now();
			dragging.time = 0;
			dragging.path = 0;
			dragging.delta = 0;
			dragging.locked = 0;
			dragging.history = [0, 0, 0, 0];
			dragging.pathToLock = isSlideElement ? isTouch ? $win.width() / window.outerWidth * 10 : 10 : 0;

			// Bind dragging events
			$doc.on(isTouch ? dragTouchEvents : dragMouseEvents, dragHandler);

			// Pause ongoing cycle
			self.pause(1);

			// Add dragging class
			(isSlideElement ? $slideElement : $handle).addClass(o.classes.draggedClass);

			// Trigger :moveStart event
			trigger('moveStart');

			// Keep track of a dragging path history. This is later used in the
			// dragging release swing calculation when dragging slideElement.
			if (isSlideElement) {
				historyID = setInterval(draggingHistoryTick, 10);
			}
		}

		/**
		 * Handler for dragging scrollbar handle or SLIDEELEMENT.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function dragHandler(event) {
			dragging.released = event.type === 'mouseup' || event.type === 'touchend';
			dragging.pointer = dragging.touch ? event.originalEvent[dragging.released ? 'changedTouches' : 'touches'][0] : event;
			dragging.pathX = dragging.pointer.pageX - dragging.initX;
			dragging.pathY = dragging.pointer.pageY - dragging.initY;
			dragging.path = Math.sqrt(Math.pow(dragging.pathX, 2) + Math.pow(dragging.pathY, 2));
			dragging.delta = o.navigation.horizontal ? dragging.pathX : dragging.pathY;

			if (!dragging.released && dragging.path < 1) return;

			if (!dragging.init) {
				if (o.navigation.horizontal ? Math.abs(dragging.pathX) > Math.abs(dragging.pathY) : Math.abs(dragging.pathX) < Math.abs(dragging.pathY)) {
					dragging.init = 1;
				} else {
					return dragEnd();
				}
			}

			stopDefault(event);

			// Disable click on a source element, as it is unwelcome when dragging
			if (!dragging.locked && dragging.path > dragging.pathToLock && dragging.slideElement) {
				dragging.locked = 1;
				dragging.$source.on(clickEvent, disableOneEvent);
			}

			// Cancel dragging on release
			if (dragging.released) {
				dragEnd();

				// Adjust path with a swing on mouse release
				if (o.dragging.releaseSwing && dragging.slideElement) {
					dragging.swing = (dragging.delta - dragging.history[0]) * o.dragging.swingSync;
					dragging.delta += dragging.swing;
					dragging.tweese = Math.abs(dragging.swing) > 10;
				}
			}

			slideTo(dragging.slideElement ? (dragging.tweese && o.dragging.onePage ? pages[within(dragging.delta < 0 ? dragging.initPage + 1 : dragging.initPage - 1, 0, pages.length - 1)] : Math.round(dragging.initPos - dragging.delta)) : handleToSlideElement(dragging.initPos + dragging.delta));
		}

		/**
		 * Stops dragging and cleans up after it.
		 *
		 * @return {Void}
		 */
		function dragEnd() {
			clearInterval(historyID);
			dragging.released = true;
			$doc.off(dragging.touch ? dragTouchEvents : dragMouseEvents, dragHandler);
			(dragging.slideElement ? $slideElement : $handle).removeClass(o.classes.draggedClass);

			// Make sure that disableOneEvent is not active in next tick.
			setTimeout(function () {
				dragging.$source.off(clickEvent, disableOneEvent);
			});

			// Normally, this is triggered in render(), but if there
			// is nothing to render, we have to do it manually here.
			if (pos.current === pos.destination && dragging.init) {
				trigger('moveEnd');
			}

			// Resume ongoing cycle
			self.resume(1);

			dragging.init = 0;
		}

		/**
		 * Check whether element is interactive.
		 *
		 * @return {Boolean}
		 */
		function isInteractive(element) {
			return ~_.inArray(element.nodeName, interactiveElements) || $(element).is(o.dragging.interactive);
		}

		/**
		 * Continuous movement cleanup on mouseup.
		 *
		 * @return {Void}
		 */
		function movementReleaseHandler() {
			self.stop();
			$doc.off('mouseup.' + namespace, movementReleaseHandler);
		}

		/**
		 * Buttons navigation handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function buttonsHandler(event) {
			stopDefault(event);

			switch (this) {
				case $forwardButton[0]:
				case $backwardButton[0]:
					self.moveBy($forwardButton.is(this) ? o.moveBy : -o.moveBy);
					$doc.on('mouseup.' + namespace, movementReleaseHandler);
					break;

				case $prevButton[0]:
					self.prev();
					break;

				case $nextButton[0]:
					self.next();
					break;

				case $prevPageButton[0]:
					self.prevPage();
					break;

				case $nextPageButton[0]:
					self.nextPage();
					break;

				case $fullScreenButton[0]:
					self.toggleFullScreen();
					break;
			}
		}

		/**
		 * Mouse wheel delta normalization.
		 *
		 * @param  {Event} event
		 *
		 * @return {Int}
		 */
		function normalizeWheelDelta(event) {
			// wheelDelta needed only for IE8-
			scrolling.curDelta = ((o.navigation.horizontal ? event.deltaY || event.deltaX : event.deltaY) || -event.wheelDelta);
			scrolling.curDelta /= event.deltaMode === 1 ? 3 : 100;
			if (!navigationType) {
				return scrolling.curDelta;
			}
			time = _now();
			if (scrolling.last < time - scrolling.resetTime) {
				scrolling.delta = 0;
			}
			scrolling.last = time;
			scrolling.delta += scrolling.curDelta;
			if (Math.abs(scrolling.delta) < 1) {
				scrolling.finalDelta = 0;
			} else {
				scrolling.finalDelta = Math.round(scrolling.delta / 1);
				scrolling.delta %= 1;
			}
			return scrolling.finalDelta;
		}

		/**
		 * Mouse scrolling handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function scrollHandler(event) {
			// Mark event as originating in a mightySlider instance
			event.originalEvent[namespace] = self;
			// Don't hijack global scrolling
			var time = _now();
			if (lastWheel + o.scrolling.hijack > time) {
				lastWheel = time;
				return;
			}

			// Ignore if there is no scrolling to be done
			if (!o.scrolling.scrollBy || pos.start === pos.end) {
				return;
			}
			stopDefault(event, 1);
			self.slideBy(o.scrolling.scrollBy * normalizeWheelDelta(event.originalEvent));
		}

		/**
		 * Scrollbar click handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function scrollbarHandler(event) {
			// Only clicks on scroll bar. Ignore the handle.
			if (o.scrollBar.clickBar && event.target === $scrollbar[0]) {
				stopDefault(event);
				// Calculate new handle position and sync SLIDEELEMENT to it
				slideTo(handleToSlideElement((o.navigation.horizontal ? event.pageX - $scrollbar.offset().left : event.pageY - $scrollbar.offset().top) - handleSize / 2));
			}
		}

		/**
		 * Keyboard input handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function keyboardHandler(event) {
			if (!o.navigation.keyboardNavBy) {
				return;
			}

			switch (event.which) {
				// Left or Up
				case o.navigation.horizontal ? 37 : 38:
					stopDefault(event);
					self[o.navigation.keyboardNavBy === 'pages' ? 'prevPage' : 'prev']();
					break;

				// Right or Down
				case o.navigation.horizontal ? 39 : 40:
					stopDefault(event);
					self[o.navigation.keyboardNavBy === 'pages' ? 'nextPage' : 'next']();
					break;
			}
		}

		/**
		 * Slides icons click handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function iconsHandler(event) {
			var $this = $(this);

			if ($this.hasClass(minnamespace + 'Close')) {
				// Remove media content
				removeContent();
			}
			else {
				// Insert media content
				insertContent($this.parent()[0]);
			}

			return false;
		}

		/**
		 * Window resize handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function winResizeHandler() {
			if (resizeID) {
				resizeID = clearTimeout(resizeID);
			}

			// Trigger :beforeResize event
			trigger('beforeResize');

			resizeID = setTimeout(function () {
				self.reload();
				scalersHandler($frame, scalers);

				if (thumbnailNav) {
					thumbnailNav.reload();
				}

				if (type(window.orientation) !== 'undefined') {
					orientation = window.orientation;
				}
				else {
					orientation = $win.height() > $win.width() ? 0 : 90;
				}

				// Trigger :resize event
				trigger('resize');
				resizeID = clearTimeout(resizeID);
			}, 100);
		}

		/**
		 * Page visibility change handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function visibilityChangeHandler() {
			// Check for: is visibility state hidden?
			var isHidden = visibilityHidden();

			if (isHidden) {
				// Freeze the slider
				self.freeze();
			}
			else {
				// Unfreeze the slider
				self.unFreeze();
			}
		}

		/**
		 * Parallax Scroll handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function parallaxScrollHandler() {
			if (!scrollParallaxCaptions.length) {
				return;
			}

			var position = _position(self.frame, window),
				out = position.top,
				css = { force3D:true },
				frameRatio = frame.clientHeight * 0.8;

			if (out > 0) {
				out = 0;
			}

			// Prevent parallax if the slider passed the viewport
			if (-out >= frame.clientHeight) {
				return;
			}

			// Handle each parallax caption
			_each(scrollParallaxCaptions, function(layer) {
				css.y = -out * 0.1;
				css.opacity = (1 - Math.min(1, -out / frameRatio));
				TweenMax.set(layer.element, css);
			});
			// Handle the slide parallax
			TweenMax.set(scrollParallaxSlide, { force3D:true, y: -out * 0.4 });
		}

		/**
		 * Window fullscreen change handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function winfullScreenHandler() {
			if (!screenfull.isFullscreen) {
				self.exitFullScreen();
			}
		}

		/**
		 * Click on slide activation handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function activateHandler(event) {
			/*jshint validthis:true */
			// Ignore clicks on interactive elements.
			if (isInteractive(this)) {
				event.stopPropagation();
				return;
			}

			// Accept only events from direct slideElement children.
			if (this.parentNode === $slideElement[0]) {
				self.activate(this);
			}
		}

		/**
		 * Click on page button handler.
		 *
		 * @param {Event} event
		 *
		 * @return {Void}
		 */
		function activatePageHandler() {
			/*jshint validthis:true */
			// Accept only events from direct pages bar children.
			if (this.parentNode === $pagesBar[0]) {
				self.activatePage($pages.index(this));
			}
		}

		/**
		 * Pause on hover handler.
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		function pauseOnHoverHandler(event) {
			if (o.cycling.pauseOnHover) {
				self[event.type === 'mouseenter' ? 'pause' : 'resume'](2);
			}
		}

		/**
		 * Trigger callbacks for event.
		 *
		 * @param  {String} name Event name.
		 * @param  {Mixed}  argX Arguments passed to callbacks.
		 *
		 * @return {Void}
		 */
		function trigger(name, arg1) {
			if (callbacks[name]) {
				l = callbacks[name].length;
				// Callbacks will be stored and executed from a temporary array to not
				// break the execution queue when one of the callbacks unbinds itself.
				tmpArray.length = 0;
				for (i = 0; i < l; i++) {
					push.call(tmpArray, callbacks[name][i]);
				}
				// Execute the callbacks
				for (i = 0; i < l; i++) {
					tmpArray[i].call(self, name, arg1);
				}
			}
		}

		/**
		 * Get slide size in pixels.
		 *
		 * @param {Object}   slide    Slide DOM element.
		 * @param {Mixed}    property
		 *
		 * @return {Int}
		 */
		function getSlideSize(slide, property) {
			var result;

			if (property) {
				result = !is_numeric(property) && indexOf.call(property, '%') !== -1 ? percentToValue(property.replace('%', ''), frameSize) : property;
			}
			else {
				var rect = slide.getBoundingClientRect();
				result = o.navigation.horizontal ? (rect.width || rect.right - rect.left) : (rect.height || rect.bottom - rect.top);
			}

			return parseInt(result);
		}

		/**
		 * Load slide contents.
		 *
		 * @param  {Mixed} slide       Slide DOM element, or index starting at 0.
		 *
		 * @return {Void}
		 */
		function loadSlide(slide) {
			var index = getIndex(slide),
				slideData = items[index],
				element = slideData.element,
				slideLoaded = element.hasAttribute(minnamespace + 'slideloaded');

			// Trigger :beforeSlideLoad event
			trigger('beforeSlideLoad', index);

			// If slide loaded before then prevent pre-loading and trigger :slideLoaded
			if (slideLoaded) {
				// Trigger :slideLoaded event
				trigger('slideLoaded', index);

				return false;
			}

			var $slide = $(element),
			loaderFunc = function() {
				// Find all images urls in element and its children
				var images = findAllImages(element),
					len = images.length;

				// If slide has no images for pre-load then prevent pre-loading and trigger :slideLoaded
				if (len <= 0) {
					// set slideLoaded to the slide to remember that this slide has been preloaded before
					element.setAttribute(minnamespace + 'slideloaded', '1');

					// Trigger :slideLoaded event
					trigger('slideLoaded', index);

					return false;
				}

				// Show loader
				var loader = showLoader($slide);

				preloadimages(images).done(function() {
					// Hide loader
					hideLoader(loader);

					// set slideLoaded to the slide to remember that this slide has been preloaded before
					element.setAttribute(minnamespace + 'slideloaded', '1');

					// Trigger :slideLoaded event
					trigger('slideLoaded', index);
				});
			};

			if (slideData.hasCaptionMediaEnabled) {
				// Find media inserted captions
				var mediaCaptions = filter.call(slideData.captions, function (e, i, arr) {
					return (e.options.cover || e.options.video || e.options.source);
				}),
				inserted = mediaCaptions.length;

				for (var i = 0, len = mediaCaptions.length, caption, captionElement, $layerContainer, cover, icon, coverURL, isVideo, parsed; i < len; i++) {
					caption = mediaCaptions[i],
					captionElement = caption.element,
					$layerContainer = $('<div class="' + minnamespace + 'LayerContainer"></div>'),
					cover = caption.options.cover,

					// Detect caption icon
					icon = caption.type,

					// Set layer cover image URL
					coverURL = cover || parsePhotoURL(caption.options.video) && caption.options.video,
					isVideo = isObject(coverURL) || getTypeByExtension(coverURL) == 'video',
					parsed = parsePhotoURL(coverURL);
						
					$layerContainer.prependTo(captionElement);

					// If cover needed to be parsed
					if (parsed) {
						// Show loader
						var loader = showLoader($slide);

						// Parse OEmbed URL via AJAX
						doAjax(parsed.oembed, function (data) {
							// Hide loader
							hideLoader(loader);

							if (data) {
								// Set cover URL
								coverURL = data[parsed.inJSON];

								// If replace is needed
								if (parsed.replace) {
									coverURL = coverURL.replace(parsed.replace.from, parsed.replace.to);
								}

								// Set cover image
								setCover({
									caption: caption,
									cover: coverURL,
									slideEl: $slide,
									insertEl: $layerContainer,
									callback: function() {
										inserted--;

										if (inserted <= len) {
											loaderFunc();
										}
									}
								});
							}
						});
					}
					else {
						// Set cover image
						setCover({
							caption: caption,
							cover: coverURL,
							isVideo: isVideo,
							slideEl: $slide,
							insertEl: $layerContainer,
							callback: function() {
								inserted--;

								if (inserted <= len) {
									loaderFunc();
								}
							}
						});
					}

					// Set layer icon if slide type is not image
					if (icon !== 'image') {
						var $icon = createSlideIcon($layerContainer, icon);

						// Layer icon click event
						if (icon !== 'link') {
							$icon.on(clickEvent, iconsHandler);
						}
					}
				}
			}
			else {
				loaderFunc();
			}
		}

		/**
		 * Get slides between a rane.
		 *
		 * @param {Number}   start
		 * @param {Number}   end
		 *
		 * @return {Array}
		 */
		function slidesInRange(start, end) {
			var slides = [];

			for (var i = 0, len = items.length, slide, slideStart, slideEnd; i < len; i++) {
				slide = items[i],
				slideStart = slide.start,
				slideEnd = slideStart + slide.size;

				if ((slideStart >= start && slideEnd <= end) || (slideEnd >= start && slideStart <= start) || (slideStart <= end && slideEnd >= end)) {
					push.call(slides, slide);
				}
			}
			
			return slides;
		}

		/**
		 * Insert cover image.
		 *
		 * @param {Object}   obj
		 *
		 * @return {Void}
		 */
		function setCover(obj) {
			// Remove last inserted cover
			$('> .' + minnamespace + 'Cover, > .' + minnamespace + 'LayerCover', obj.insertEl).remove();

			if (!obj.cover) {
				return false;
			}

			if (isObject(obj.cover) && obj.cover.poster && supportTouch) {
				obj.isVideo = 0,
				obj.cover = obj.cover.poster;
			}
			
			if (obj.isVideo && !supportTouch) {
				// Show loader
				var loader = showLoader(obj.slideEl);

				// Trigger :beforeCoverLoad event
				if (obj.slide) {
					trigger('beforeCoverLoad', obj.index);
				}

				var innerTags = [];

				if (isObject(obj.cover)) {
					// delete poster property from cover object for inner tags
					if (obj.cover.poster) {
						obj.cover.poster = null;
					}

					_each(obj.cover, function(key, value) {
						// Set sources to the video element
						push.call(innerTags, {
							name: 'source',
							type: videoTypes[key],
							src: value
						});
					});
				}
				else {
					var videoURL = obj.cover,
						extension = _.getExtension(videoURL);

					// Normalize extension
					extension = (isObject(extension)) ? null : extension.toLowerCase();

					innerTags = [
						{
							name: 'source',
							type: videoTypes[extension],
							src: videoURL
						}
					];
				}

				// Generate video element and cover holder
				var $videoEl = $(createElement('video', { autoplay: 'autoplay', muted: 'muted', loop: 'loop', controls: false }, innerTags)).addClass(minnamespace + 'CoverImage').hide(),
					$cover = $('<div class="' + minnamespace + 'Cover"></div>').append($videoEl);

				// Insert the cover into slide
				obj.insertEl.prepend($cover);

				// Trigger :coverInserted event
				trigger('coverInserted', $cover[0]);
				
				$videoEl.one('loadedmetadata', function() {
					var videoEl = $videoEl[0];

					// Store cover natural width and height
					$videoEl.show().data({ naturalWidth: videoEl.videoWidth, naturalHeight: videoEl.videoHeight });

					// Reposition slides covers
					repositionCovers(obj.slide);

					// Hide loader
					hideLoader(loader);

					// Trigger :coverLoaded event
					trigger('coverLoaded', obj.index);
				});
			}
			else {
				// Show loader
				var loader = showLoader(obj.slideEl),
					objOptions = obj[obj.slide ? 'slide' : 'caption'].options,
					viewport = (objOptions.viewport || o.viewport).toLowerCase(),
					bgSize;

				switch(viewport) {
					default:
					case 'fill':
						bgSize = 'cover';
						break;
					case 'fit':
						bgSize = 'contain';
						break;
					case 'stretch':
						bgSize = '100% 100%';
						break;
					case 'center':
						bgSize = 'auto';
						break;
				}

				// Trigger :beforeCoverLoad event
				if (obj.slide) {
					trigger('beforeCoverLoad', obj.index);
				}

				// Preload the cover
				preloadimages(obj.cover).done(function(img) {
					// Trigger :coverLoaded event
					if (obj.slide) {
						trigger('coverLoaded', obj.index);
					}

					var $cover = $('<div class="' + (minnamespace + ((obj.slide) ? 'Cover' : 'LayerCover')) + '" style="background-image: url(\'' + obj.cover + '\'); background-size: ' + bgSize + ';"></div>'),
					$image = $('img', $cover);

					// Hide loader
					hideLoader(loader);

					// Store cover natural width and height
					$image.data({ naturalWidth: img[0].width, naturalHeight: img[0].height });

					// Insert the cover into slide
					obj.insertEl.prepend($cover);

					// Trigger callback
					if (obj.callback) {
						obj.callback.call(this, $image);
					}

					// Trigger :coverInserted event
					if (obj.slide) {
						trigger('coverInserted', $cover[0]);
					}
				});
			}
		}


		/**
		 * Set slides covers.
		 *
		 * @return {Void}
		 */
		function setSlidesCovers() {
			var start, end;

			switch (o.preloadMode) {
				// Select all slides
				case 'all':
					start = 0,
					end = slideElementSize;
					break;

				// Select nearby slides
				case 'nearby':
					start = within(pos.current - frameSize, pos.start, pos.end),
					end = within(pos.current + (frameSize * 2) - 5, pos.start, pos.end + (frameSize * 2));
					break;

				// Select instant slide
				case 'instant':
					start = within(pos.current + 5 , pos.start, pos.end),
					end = within(pos.current + frameSize - 5, pos.start, pos.end + frameSize);
					break;
			}

			var slides = slidesInRange(start, end);

			var eachHandler = function (slide, i) {
					if (slide.type === 'content') {
						return true;
					}

					var slideEl = slide.element,
						$slide = $(slideEl),
						processed = slideEl.hasAttribute(minnamespace + 'processed'),
						lastCover = slideEl.getAttribute(minnamespace + 'lastcover');

					if (processed && !resizeID) {
						return true;
					}

					var cover = slide.options.cover;

					// If the cover has rules then find right cover image
					if ($.isArray(cover)) {
						for (var i = 0, len = cover.length, element; i < len; i++) {
							element = cover[i];

							// if the rule matched
							if (element[1] && window.matchMedia(element[1]).matches) {
								cover = element[0];
								break;
							}
						}

						// If there is no cover image for rules then automatically
						if ($.isArray(cover)) {
							cover = cover[0][0];
						}
					}

					var coverURL = cover || parsePhotoURL(slide.options.video) && slide.options.video,
						isVideo = isObject(coverURL) || getTypeByExtension(coverURL) == 'video',
						parsed = parsePhotoURL(coverURL),
						coverUN = isObject(coverURL) ? rawurlencode(JSON.stringify(coverURL)) : coverURL;

					if (lastCover === coverUN) {
						return true;
					}

					// If cover needed to be parsed
					if (parsed) {
						// Show loader
						var loader = showLoader($slide);

						// Parse OEmbed URL via AJAX
						doAjax(parsed.oembed, function (data) {
							// Hide loader
							hideLoader(loader);

							if (data) {
								// Set cover URL
								coverURL = data[parsed.inJSON];

								// If replace is needed
								if (parsed.replace) {
									coverURL = coverURL.replace(parsed.replace.from, parsed.replace.to);
								}

								// Set cover image
								setCover({
									cover: coverURL,
									slide: slide,
									slideEl: $slide,
									insertEl: $slide,
									index: getIndex(slideEl)
								});
							}
						});
					}
					else {
						// Set cover image
						setCover({
							cover: coverURL,
							isVideo: isVideo,
							slide: slide,
							slideEl: $slide,
							insertEl: $slide,
							index: getIndex(slideEl)
						});
					}

					slideEl.setAttribute(minnamespace + 'processed', '1');
					slideEl.setAttribute(minnamespace + 'lastcover', coverUN);
				};

			_each(slides, eachHandler);
		}

		/**
		 * Set slides icons.
		 *
		 * @return {Void}
		 */
		function setSlidesIcons() {
			for (var i = 0, len = items.length, slide, $slide, icon, $icon; i < len; i++) {
				slide = items[i];

				if (slide.type === 'content') {
					continue;
				}

				$slide = $(slide.element),
				icon = slide.options.icon || slide.type;

				if ($('.' + minnamespace + ucfirst(icon), $slide)[0]) {
					continue;
				}

				// Set slide icon if slide type is not content and image
				if (icon !== 'content' && icon !== 'image') {
					$icon = createSlideIcon($slide, icon);

					// Slides icons click event
					if (icon !== 'link') {
						$icon.on(clickEvent, iconsHandler);
					}
				}
			}
		}

		/**
		 * Create slide icon.
		 *
		 * @param {Object}   $slide    jQuery object with element.
		 * @param {String}   icon
		 *
		 * @return {Object}    jQuery object with element.
		 */
		function createSlideIcon($slide, icon) {
			var iconName = minnamespace + ucfirst(icon);

			// Return blank if icon is exists
			if ($('.' + iconName, $slide).length) {
				return;
			}

			var $icon = $('<a class="' + minnamespace + 'Icon ' + iconName + '" ondragstart="return false;"></a>');

			if (icon === 'link') {
				var index = getIndex($slide),
					slide = items[index],
					href = slide.options.link.url && _.absolutizeURI(slide.options.link.url) || window.location.href,
					target = slide.options.link.target || null,
					attributes = $.extend(true, { 'href': href, 'target': target }, slide.options.link);

					if (attributes.url) {
						attributes.url = null;
					}

				$icon[$.fn.attr ? 'attr' : 'prop'](attributes);
			}

			// Append icon into $slide
			$slide.append($icon);

			return $icon;
		}

		/**
		 * Insert media content.
		 *
		 * @param {DOM}    element    Dom element.
		 *
		 * @return {Void}
		 */
		function insertContent(element) {
			var index = getIndex(element);

			// Remove previous media content
			if (mediaEnabled) {
				removeContent();
			}

			var $container = $(element);

			if (index !== -1) {
				// Reset and get slide data
				var object = items[index];
			}
			else {
				var $caption = $container.parent(),
					options = getInlineOptions($caption),
					object = {
						type: getSlideType(options),
						options: options
					};

				$caption.addClass(minnamespace + 'Media');
			}

			$container.children().hide();

			// Generate media content
			var mediaContent = generateContent({ type: object.type, options: object.options }),
				closeButton = $('<a class="' + minnamespace + 'Icon ' + minnamespace + 'Close"></a>');

			// Insert mediaContent and closeButton
			$container.prepend(mediaContent).prepend(closeButton);

			// Bind closeButton handler
			closeButton.on(clickEvent, iconsHandler);

			if (index !== -1) {
				$parent.addClass(minnamespace + 'Media');

				// Clear captions
				clearCaptions(rel.activeSlide);
			}

			// Pause cycling
			self.pause(3);

			mediaEnabled = mediaContent;
		}

		/**
		 * Remove media content.
		 *
		 * @return {Void}
		 */
		function removeContent() {
			var $container = mediaEnabled.parent();

			// Remove media content from DOM
			mediaEnabled.remove();
			mediaEnabled = null;
			$('.' + minnamespace + 'Close', $container).off(clickEvent).remove();

			if ($container.hasClass(minnamespace + 'LayerContainer')) {
				$container.parent().removeClass(minnamespace + 'Media');
			}
			else {
				$parent.removeClass(minnamespace + 'Media');
			}

			// Show childs
			$container.children().show();

			// Reset cycling
			if (o.cycling.loop || !o.cycling.loop && index !== items.length - 1) {
				self.isPaused = 0;
				resetCycle();
			}

			// Render captions
			if (!captionID) {
				renderCaptions(rel.activeSlide);
			}
		}

		/**
		 * Generate slide media content.
		 *
		 * @param {Mixed}     obj      Slide object
		 *
		 * @return {Object}            jQuery DOM element
		 */
		function generateContent(obj) {
			// Get type
			var type = obj.type,
				options = obj.options,
				URL = options.mp4 || options.video || options.source,
				$content;

			// Trigger :beforeGenerateMedia event
			trigger('beforeGenerateMedia');

			switch (type) {
				case 'video':
					var extension = _.getExtension(URL),
						videoFrame = options.videoFrame,
						mp4 = options.mp4,
						webm = options.webm,
						ogv = options.ogv,
						localVideo = mp4 || webm || ogv || 0,
						parsedVideo = parseVideoURL(URL);

					// Normalize extension
					extension = (extension) ? extension.toLowerCase() : null;

					// Check video URL, is video file?
					if ((/^(avi|mov|mpg|mpeg|mp4|webm|ogv|3gp|m4v)$/i).test(extension) || videoFrame || mp4 || webm || ogv) {
						// Use videoFrame if available
						if (videoFrame || (localVideo && o.videoFrame)) {
							var source = videoFrame || o.videoFrame,
							mediaFiles = [];

							// Add MP4 Video to mediaFiles
							if (mp4) {
								push.call(mediaFiles, {
									type: videoTypes['mp4'],
									src: _.absolutizeURI(mp4)
								});
							}

							// Add WebM Video to mediaFiles
							if (webm) {
								push.call(mediaFiles, {
									type: videoTypes['webm'],
									src: _.absolutizeURI(webm)
								});
							}

							// Add OGV Video to mediaFiles
							if (ogv) {
								push.call(mediaFiles, {
									type: videoTypes['ogv'],
									src: _.absolutizeURI(ogv)
								});
							}

							if (mediaFiles.length > 0) {
								source += (_.parseURL(source, 'QUERY') ? '&' : '?') + minnamespace.toLowerCase() + 'videos=' + rawurlencode(JSON.stringify(mediaFiles));

								// Set cover image to the video frame
								if (options.cover) {
									source += '&' + minnamespace.toLowerCase() + 'cover=' + rawurlencode(_.absolutizeURI(options.cover));
								}
							}

							$content = $(createElement('iframe', { src: source, scrolling: 'no' }));
						}

						// Check that HTML5 can play this type of video file
						else if (canPlayType(videoTypes[extension]) || (mp4 && canPlayType(videoTypes['mp4'])) || (webm && canPlayType(videoTypes['webm'])) || (ogv && canPlayType(videoTypes['ogv']))) {
							var innerTags = [
								{
									name: 'source',
									type: mp4 && videoTypes['mp4'] || videoTypes[extension],
									src: URL
								}
							];

							// Add WebM Video to HTML5 video tag
							if (webm) {
								push.call(innerTags, {
									name: 'source',
									type: videoTypes['webm'],
									src: webm
								});
							}

							// Add ogv Video to HTML5 video tag
							if (ogv) {
								push.call(innerTags, {
									name: 'source',
									type: videoTypes['ogv'],
									src: ogv
								});
							}

							// Add HTML5 Video other inner tags
							if (options.HTML5Video) {
								_each(options.HTML5Video, function(key, value) {
									push.call(innerTags, $.extend({}, {
										name: key
									}, value));
								});
							}

							$content = $(createElement('video', {}, innerTags));
						}

						// Warn user that video is not supported
						else {
							throw "Video not supported!";
						}
					}
					// Check video URL, is video from social video sharing?
					else if (parsedVideo) {
						$content = $(createElement(parsedVideo.type, { src: parsedVideo.source, scrolling: 'no' }));
					}
					// Warn user that video is not supported
					else {
						throw "Video not supported!";
					}
					break;

				case 'iframe':
					$content = $(createElement('iframe', { src: URL }));
					break;

				case 'flash':
					$content = $(createElement('embed', { src: URL, flashvars: options.flashvars || null }));
					break;
			}

			// Trigger :mediaGenerated event
			trigger('mediaGenerated', $content[0]);

			return $content;
		}

		/**
		 * Reposition slides covers.
		 *
		 * @param {Object}     slide    Slide data object.
		 *
		 * @return {Void}
		 */
		function repositionCovers(slide) {
			var newSlides = (slide) ? [slide] : items;

			for (var i = 0, len = newSlides.length, slide, $slide, $cover, viewport, coverData, slideWidth, slideHeight, width, height, marginLeft, marginTop, newDimensions; i < len; i++) {
				slide = newSlides[i];

				if (!slide.options.cover || slide.type === 'content' || !(isObject(slide.options.cover) || (type(slide.options.cover) == 'string' && getTypeByExtension(slide.options.cover) == 'video'))) {
					continue;
				}

				$slide = $(slide.element),
				$cover = $slide.find('.' + minnamespace + 'CoverImage');

				if (!$cover[0]) {
					continue;
				}

				viewport = (slide.options.viewport || o.viewport).toLowerCase(),
				coverData = $cover.data(),
				slideWidth = $slide.width(),
				slideHeight = $slide.height(),
				width = slideWidth,
				height = slideHeight,
				marginLeft = 0,
				marginTop = 0;

				if (viewport === 'fit') {
					newDimensions = calculateDimensions(width, height, coverData.naturalWidth, coverData.naturalHeight);

					width = newDimensions.width,
					height = newDimensions.height;
				}
				else if (viewport === 'fill') {
					height = (width / coverData.naturalWidth) * coverData.naturalHeight;

					if (height < slideHeight) {
						width = (slideHeight / coverData.naturalHeight) * coverData.naturalWidth,
						height = slideHeight;
					}
				}
				else if (viewport === 'center') {
					newDimensions = calculateDimensions(width, height, coverData.naturalWidth, coverData.naturalHeight, 1);

					width = coverData.naturalWidth,
					height = coverData.naturalHeight;
				}

				marginTop = ((slideHeight > height) ? slideHeight - height : -(height - slideHeight)) / 2,
				marginLeft = ((slideWidth > width) ? slideWidth - width : -(width - slideWidth)) / 2;

				$cover.css({ width: width, height: height, marginTop: marginTop, marginLeft: marginLeft });
			}
		}

		/**
		 * Resize frame equal to slide size.
		 *
		 * @param  {Mixed}   slide        Slide DOM element, or index starting at 0.
		 * @param  {Bool}    immediate    Resize immediately without an animation.
		 *
		 * @return {Void}
		 */
		function resizeFrame(slide, immediate) {
			var index = getIndex(slide),
			slideSize = $(items[index].element)[o.navigation.horizontal ? 'outerHeight' : 'outerWidth'](),
			properties = {};

			properties[o.navigation.horizontal ? 'height' : 'width'] = slideSize;
			$frame.msStop().msAnimate(properties, o.speed, o.easing);
		}

		/**
		 * Normalize slider global elements.
		 *
		 * @return {Void}
		 */
		function normalizeElements() {
			// Normalizing $thumbnailsBar if thumbnails available in commands options but thumbnails bar DOM element is not available
			if (o.commands.thumbnails && !$thumbnailsBar[0]) {
				// Create $thumbnailsBar DOM element
				$thumbnailsBar = $('<ul></ul>');

				// Append thumbnails into $parent
				$parent.append($('<div class="' + minnamespace + 'Thumbnails"></div>').html($thumbnailsBar));
			}

			// Normalizing $pagesBar if pages available in commands options but pages bar DOM element is not available
			if (o.commands.pages && !$pagesBar[0]) {
				// Create $pagesBar DOM element
				$pagesBar = $('<ul class="' + minnamespace + 'Pages"></ul>');

				// Append pages into $parent
				$parent.append($pagesBar);
			}

			// Normalizing $nextPageButton if buttons available in commands options but $nextPageButton DOM element is not available
			if (o.commands.buttons && navigationType && !$nextPageButton[0]) {
				// Create $nextPageButton DOM element
				$nextPageButton = $('<a class="' + minnamespace + 'Buttons ' + minnamespace + 'Next"></a>');

				// Append $nextPageButton into $parent
				$parent.prepend($nextPageButton);
			}

			// Normalizing $prevPageButton if buttons available in commands options but $prevPageButton DOM element is not available
			if (o.commands.buttons && navigationType && !$prevPageButton[0]) {
				// Create $prevPageButton DOM element
				$prevPageButton = $('<a class="' + minnamespace + 'Buttons ' + minnamespace + 'Prev"></a>');

				// Append $prevPageButton into $parent
				$parent.prepend($prevPageButton);
			}

			// Normalizing $forwardButton if buttons available in commands options but $forwardButton DOM element is not available
			if (o.commands.buttons && !navigationType && !$forwardButton[0]) {
				// Create $forwardButton DOM element
				$forwardButton = $('<a class="' + minnamespace + 'Buttons ' + minnamespace + 'Next"></a>');

				// Append $forwardButton into $parent
				$parent.prepend($forwardButton);
			}

			// Normalizing $backwardButton if buttons available in commands options but $backwardButton DOM element is not available
			if (o.commands.buttons && !navigationType && !$backwardButton[0]) {
				// Create $backwardButton DOM element
				$backwardButton = $('<a class="' + minnamespace + 'Buttons ' + minnamespace + 'Prev"></a>');

				// Append $backwardButton into $parent
				$parent.prepend($backwardButton);
			}
		}

		/**
		 * Render captions.
		 *
		 * @param {Object}     slide       Slide DOM element, or index starting at 0.
		 *
		 * @return {Void}
		 */
		function renderCaptions(slide) {
			if (typeof TimelineMax === 'undefined') {
				console && console.warn('Animated layers needs TimelineMax function. Please include the "tweenlite.js" into your page.');
				return;
			}

			var index = getIndex(slide),
				slide = items[index],
				captions = slide.captions;

			if (index === captionRendered) {
				return;
			}

			if (captionHistory[index]) {
				var timeline;

				for (var i = 0, len = captionHistory[index].length, timeline; i < len; i++) {
					timeline = captionHistory[index][i];

					if (!self.isFreezed && timeline) {
						timeline.play(0);
					}
				}
			} else {
				captionHistory[index] = [];
				_each(captions, function(caption, i) {
					var $caption = $(caption.element),
						captionData = $caption.data(minnamespace + 'styles'),
						options = caption.options;

					// Show caption & set necessary caption styles
					var css = { 'position': 'absolute' };

					if (!captionData) {
						captionData = getCaptionStyles($caption);
						$caption.data(minnamespace + 'styles', captionData);
					}

					// Set necessary caption styles
					if (o.autoScale) {
						css = $.extend({}, css, normalizeStyles(captionData, captionResponsiveStyles, frameRatio));
					}

					$caption.show().css(css);

					// Add animation frames to timeline
					if (caption.animation.length) {
						var timeline, splitText;

						if (!captionHistory[index][i]) {
							// Generate timeline
							timeline = new TimelineMax({
								paused: true,
								onComplete: function(){
									// Handle the repeat
									if (options.loop) {
										this.seek(getRepeatDuration(caption));
									}
								}
							});

							_each(caption.animation, function(keyframe) {
								var duration = keyframe.speed ? keyframe.speed / 1000 : 0,
									delay = keyframe.delay ? keyframe.delay / 1000 : 0,
									ease = window.GreenSockGlobals.com.greensock.easing.Ease.map[keyframe.easing || 'swing'],
									mode = keyframe.mode || 'to',
									position = keyframe.position,
									style = keyframe.style || {},
									scrambleText = style.scrambleText,
									staggerText = keyframe.staggerText && $.extend({ type: 'chars', delay: 0.01 }, keyframe.staggerText) || null,
									cssStyle = !!scrambleText ? { delay: delay, ease: ease } : { delay: delay, ease: ease, force3D:true };

								// Extend and normalize styles
								$.extend(cssStyle, style, normalizeStyles(style, captionResponsiveStyles, frameRatio));

								if (staggerText) {
									if (!splitText) {
										splitText = new SplitText(caption.element, { type: 'chars, words, lines', charsClass: 'mSChars', wordsClass: 'mSWords', linesClass: 'mSLines' });
									}

									if (position) {
										timeline['stagger' + ucfirst(mode)](splitText[staggerText.type], duration, cssStyle, staggerText.delay, position);
									}
									else{
										timeline['stagger' + ucfirst(mode)](splitText[staggerText.type], duration, cssStyle, staggerText.delay);
									}
								}
								else {
									timeline[mode](caption.element, duration, cssStyle);
								}
							});

							// Cache the timeline to the history
							captionHistory[index][i] = timeline;
						}
						else {
							timeline = captionHistory[index];
						}

						// Play the 
						if (!self.isFreezed) {
							timeline.play();
						}
					}
				});
			}

			// Remember the slide index that rendered layers
			captionRendered = index;
		}

		/**
		 * Clear captions.
		 *
		 * @param {Mixed} slide       Slide DOM element, or index starting at 0.
		 *
		 * @return {Void}
		 */
		function clearCaptions(slide) {
			var index = getIndex(slide);

			if (index !== captionRendered) {
				return;
			}

			if (items[index] && items[index].captions.length) {
				self.one('moveEnd', function() {
					if (rel.activeSlide === index) {
						return;
					}

					// Pause timelines
					_each(captionHistory[index], function(timeline) {
						timeline && timeline.pause(0, true);
					});
				});
			}
		}

		/**
		 * Get repeat duration.
		 *
		 * @param {Object}     caption
		 * @param {Object}     timeline
		 *
		 * @return {Void}
		 */
		function getRepeatDuration(caption) {
			var options = caption.options,
				startAt = options.startAtOnRepeat || 0;

			if (is_numeric(startAt)) {
				var keyframe, duration, delay, time = 0;
				for (var i = 0, len = caption.animation.length; i < len; i++) {
					keyframe = caption.animation[i];
					duration = keyframe.speed ? keyframe.speed / 1000 : 0;
					delay = keyframe.delay ? keyframe.delay / 1000 : 0;

					if (i === startAt) {
						if (options.dontDelayOnRepeat) {
							time += delay;
						}

						return time;
					}

					time += duration + delay;
				}
			}
			else {
				return hmsToSeconds(startAt);
			}
		}

		/**
		 * Handle parallax effect tick
		 *
		 * @return {Void}
		 */
		function parallaxTick() {
			if (!parallax.parallaxCaptions) {
				this.kill();
				return;
			}

			// Parallax options
			var parallaxOptions = o.parallax;
			var length = parallax.parallaxCaptions.length;
			var X = parallaxTo.X, Y = parallaxTo.Y;
			var caption, transformString, level, parallaxAxises;

			// Loop the parallax compatible captions
			for(var i = 0; i < length; i++) {
				transformString = gpuAcceleration;
				caption = parallax.parallaxCaptions[i];
				// Parallax effect level
				level = caption.options.parallaxLevel;
				// Alowed parallax effect axises
				parallaxAxises = caption.options.parallaxAxises || parallaxOptions;

				// Calculate X parallax axis
				if (parallaxAxises.x) {
					transformString += 'translateX(' + ((parallaxOptions.invertX ? -X : X) / 100 * level) + 'px) ';
				}

				// Calculate Y parallax axis
				if (parallaxAxises.y) {
					transformString += 'translateY(' + ((parallaxOptions.invertY ? -Y : Y) / 100 * level) + 'px) ';
				}

				// Calculate Z parallax axis
				if (parallaxAxises.z && gpuAcceleration) {
					var x = ((parallaxOptions.invertZ ? -X : X) / 100 * level),
						y = ((parallaxOptions.invertZ ? -Y : Y) / 100 * level);

					transformString += 'rotateX(' + (-y / 1.5) + 'deg) rotateY(' + (x / 3) + 'deg)';
				}

				caption.element.style[transform] = transformString;
			}
		}

		/**
		 * Handle parallax effect for captions
		 *
		 * @return {Void}
		 */
		function parallaxCaptions() {
			// Parallax options
			var parallaxOptions = o.parallax;

			var parallaxPos = {
				X: parallaxTo.X,
				Y: parallaxTo.Y,
				useFrames: false,
				ease: parallaxOptions.frictionEasing,
				onUpdate: parallaxTick
			};
			
			parallaxTo.X = 0;
			parallaxTo.Y = 0;

			parallaxTween = tweenLite.to(parallaxTo, parallaxOptions.frictionDuration && parallaxOptions.frictionDuration / 1000 || 0, parallaxPos);
		}

		/**
		 * Revert parallax effect for captions
		 *
		 * @return {Void}
		 */
		function revertParallax() {
			// Parallax options
			var parallaxOptions = o.parallax;
			var revertDuration = o.parallax.revertDuration;
			var parallaxCaptions = parallax.parallaxCaptions;
			var parallaxCaptionsLength = parallaxCaptions.length;

			var caption, transformString, captionEl, parallaxAxises, level, X, Y,
				parallaxPos = {
				X: parallaxTo.X,
				Y: parallaxTo.Y,
				useFrames: false,
				ease: parallaxOptions.revertEasing,
				onUpdate: parallaxTick
			};

			parallaxTween = tweenLite.to(parallaxTo, revertDuration && revertDuration/1000 || 0, parallaxPos);
		}

		/**
		 * Change Hashtag.
		 *
		 * @param {Number}  index
		 *
		 * @return {Void}
		 */
		function changeHashtag(index) {
			var slide = items[index];

			hashLock = 1;
			window.location.hash = o.deeplinking.linkID + o.deeplinking.separator + slide.ID;
			hashLock = 0;
		}

		/**
		 * Get slide by ID.
		 *
		 * @param {String}     ID
		 *
		 * @return {Void}
		 */
		function getSlideById(ID) {
			var index = 0;

			for (var i = 0, len = items.length; i < len; i++) {
				if (ID == items[i].ID) {
					index = i;
				}
			}

			return index;
		}

		/**
		 * Handle Hashtag.
		 *
		 * @param {Event}     event
		 *
		 * @return {Void}
		 */
		function hashtagHandler(event) {
			var hash = window.location.hash.replace("#", ""),
				split = hash.split(o.deeplinking.separator);

			if (hashLock || !o.deeplinking.linkID) {
				return;
			}

			if (event) {
				hashLock = 1;
			}

			if (split[0] === o.deeplinking.linkID) {
				var index = getSlideById(split[1]);
				if (self.initialized) {
					self.activate(index);
				}
				else {
					o.startRandom = 0;
					o.startAt = index;
				}
			}
			else if (event && hash.length === 0) {
				self.activate(o.startAt);
			}

			if (event) {
				hashLock = 0;
			}
		}

		/**
		 * Freeze the slider layers & cycling
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		self.freeze = function () {
			if (freezeID === 0) {
				var index = rel.activeSlide;

				$parent.addClass(o.classes.isInFreeze);

				// Pause cycling
				if (cycleID) {
					cycleID = cancelAnimationFrame(cycleID);
					cycleLastTime = 0;
				}

				if (navigationType && captionHistory[index] && captionHistory[index].length) {
					_each(captionHistory[index], function(timeline) {
						timeline && timeline.pause();
					});
				}

				self.isFreezed = 1;
			}

			if (freezeID >= 0) {
				freezeID++;
			}
		};

		/**
		 * Unfreeze the slider layers & cycling
		 *
		 * @param  {Event} event
		 *
		 * @return {Void}
		 */
		self.unFreeze = function () {
			if (freezeID > 0) {
				freezeID--;
			}

			if (freezeID <= 0) {
				var index = rel.activeSlide;

				$parent.removeClass(o.classes.isInFreeze);

				// Reset cycling
				if (o.cycling.loop || !o.cycling.loop && index !== items.length - 1) {
					resetCycle();
				}

				if (navigationType && captionHistory[index] && captionHistory[index].length) {
					_each(captionHistory[index], function(timeline) {
						timeline && timeline.resume();
					});
				}

				self.isFreezed = 0;
			}
		};

		/**
		 * Destroys instance and everything it created.
		 *
		 * @return {Object}
		 */
		self.destroy = function () {
			// Unbind all events
			var $unbinds = $scrollSource
				.add($handle)
				.add($scrollbar)
				.add($pagesBar)
				.add($forwardButton)
				.add($backwardButton)
				.add($prevButton)
				.add($nextButton)
				.add($prevPageButton)
				.add($nextPageButton)
				.add($fullScreenButton)
				.add($('.' + minnamespace + 'Icon', $frame));

			$unbinds.off('.' + namespace);

			$doc.add($win).off('.' + uniqId);

			// Remove classes
			$prevButton
				.add($nextButton)
				.add($prevPageButton)
				.add($nextPageButton)
				.removeClass(o.classes.disabledClass);

			if ($slides[0]) {
				$slides.removeAttr('style').removeClass(minnamespace + 'Slide').eq(rel.activeSlide).removeClass(o.classes.activeClass);

				// Remove slides covers and icons
				$('.' + minnamespace + 'Cover, .' + minnamespace + 'Icon', $slides).remove();
			}

			// Remove pages
			if ($pagesBar[0]) {
				$pagesBar.empty();
			}

			// Remove thumbnails
			if ($thumbnailsBar[0]) {
				$thumbnailsBar.empty();
			}

			var removeEls = [];

			if (!o.buttons.forward && $forwardButton[0]) {
				push.call(removeEls, $forwardButton[0]);
			}
			if (!o.buttons.backward && $backwardButton[0]) {
				push.call(removeEls, $backwardButton[0]);
			}
			if (!o.buttons.prev && $prevButton[0]) {
				push.call(removeEls, $prevButton[0]);
			}
			if (!o.buttons.next && $nextButton[0]) {
				push.call(removeEls, $nextButton[0]);
			}
			if (!o.buttons.prevPage && $prevPageButton[0]) {
				push.call(removeEls, $prevPageButton[0]);
			}
			if (!o.buttons.nextPage && $nextPageButton[0]) {
				push.call(removeEls, $nextPageButton[0]);
			}
			if (!o.buttons.fullScreen && $fullScreenButton[0]) {
				push.call(removeEls, $fullScreenButton[0]);
			}

			if (!o.pages.pagesBar && $pagesBar[0]) {
				push.call(removeEls, $pagesBar[0]);
			}
			else if($pagesBar[0]) {
				$pagesBar.empty();
			}

			if (!o.thumbnails.thumbnailsBar && $thumbnailsBar[0]) {
				push.call(removeEls, $thumbnailsBar[0]);
			}
			else if($thumbnailsBar[0]) {
				$thumbnailsBar.empty();
			}

			// Remove mightySlider created elements
			$(removeEls).remove();

			// Unbind events from frame
			$frame.off('.' + namespace);
			// Remove horizontal/vertical and mightySlider class
			$parent.removeClass(o.navigation.horizontal ? o.classes.horizontalClass : o.classes.verticalClass).removeClass(namespace);
			// Reset slideElement and handle positions
			$slideElement.add($handle).css(transform || (o.navigation.horizontal ? 'left' : 'top'), transform ? 'none' : 0).removeClass(minnamespace + 'SlideElement ' + minnamespace + 'ScrollbarHandle');
			// Remove the instance from element data storage
			$.removeData(frame, namespace);
			// Remove the classes from FRAME and scrollbar elements
			$frame.add($scrollbar).removeClass(minnamespace + 'Frame ' + minnamespace + 'MouseDraggable ' + minnamespace + 'TouchDraggable ' + minnamespace + 'Scrollbar');

			// Clean up collections
			items.length = pages.length = 0;
			last = {};

			// Reset initialized status and return the instance
			self.initialized = 0;

			// Trigger :destroy event
			trigger('destroy');

			// Remove the frame from the windowSpy
			windowSpy.remove(frame);

			// Remove this instance from instances
			_each(mightySliderInstances, function(instance, i){
				if (instance.uniqId === self.uniqId) {
					 splice.call(mightySliderInstances, i, 1);
				}
			});

			return self;
		};

		/**
		 * Initialize.
		 *
		 * @return {Object}
		 */
		self.init = function () {
			if (self.initialized) {
				return;
			}

			syncThumbnailsbar();

			// Register callbacks map
			self.on(callbackMap);

			// Set required styles to elements
			var $movables = $slideElement.add($handle);
			$frame.css('overflow', 'hidden').addClass(minnamespace + 'Frame');
			if (!transform && $frame.css('position') === 'static') {
				$frame.css('position', 'relative');
			}
			if (transform) {
				if (gpuAcceleration) {
					$movables.css(transform, gpuAcceleration);
				}
			}
			else {
				if ($scrollbar.css('position') === 'static') {
					$scrollbar.css('position', 'relative');
				}
				$movables.css({ position: 'absolute' });
			}

			if (o.dragging.mouseDragging && !supportTouch) {
				$frame.addClass(minnamespace + 'MouseDraggable');
			}
			if (o.dragging.touchDragging && supportTouch) {
				$frame.addClass(minnamespace + 'TouchDraggable');
			}

			$slideElement.addClass(minnamespace + 'SlideElement');
			$scrollbar.addClass(minnamespace + 'Scrollbar');
			$handle.addClass(minnamespace + 'ScrollbarHandle');

			// Normalize slider global elements
			normalizeElements();

			// Load
			load(true);

			// Activate thumbnail for requested position
			if (thumbnailNav) {
				self.on('active', function(name, index) {
					thumbnailNav.activate(index);
				});
			}

			// Handle Hashtag
			if (o.deeplinking.linkID) {
				hashtagHandler();
			}

			// Add '.mSSlide' class to $slides
			$slides.addClass(minnamespace + 'Slide');
			
			// If startRandom
			o.startAt = o.startRandom ? Math.floor(Math.random() * items.length) : o.startAt;

			// Activate requested position
			self.activate(o.startAt, 1);

			// Navigation buttons
			if ($forwardButton[0]) {
				$forwardButton.on(mouseDownEvent, buttonsHandler);
			}
			if ($backwardButton[0]) {
				$backwardButton.on(mouseDownEvent, buttonsHandler);
			}
			if ($prevButton[0]) {
				$prevButton.on(clickEvent, buttonsHandler);
			}
			if ($nextButton[0]) {
				$nextButton.on(clickEvent, buttonsHandler);
			}
			if ($prevPageButton[0]) {
				$prevPageButton.on(clickEvent, buttonsHandler);
			}
			if ($nextPageButton[0]) {
				$nextPageButton.on(clickEvent, buttonsHandler);
			}
			if ($fullScreenButton[0]) {
				$fullScreenButton.on(clickEvent, buttonsHandler);
			}

			// Scrolling navigation
			$scrollSource.on(wheelEvent, scrollHandler);

			// Clicking on scrollbar navigation
			if ($scrollbar[0]) {
				$scrollbar.on(clickEvent, scrollbarHandler);
			}

			// Click on slides navigation
			if (navigationType && o.navigation.activateOn) {
				$frame.on(o.navigation.activateOn + '.' + namespace, '*', activateHandler);
			}

			// Pages navigation
			if ($pagesBar[0] && o.pages.activateOn) {
				$pagesBar.on(o.pages.activateOn + '.' + namespace, '*', activatePageHandler);
			}

			// Dragging navigation
			$dragSource.on(dragInitEvents, { source: 'slideElement' }, dragInit);

			// Scrollbar dragging navigation
			if ($handle[0]) {
				$handle.on(dragInitEvents, { source: 'handle' }, dragInit);
			}

			// Keyboard navigation and Page Visibility Change Handler
			$doc.on(keyDownEvent, keyboardHandler)
			.on(visibilityChangeEvent, visibilityChangeHandler);

			if (o.parallax.scroll && !isMobile.any) {
				$doc.on('scroll.' + uniqId, parallaxScrollHandler);
			}

			// Window resize, fullscreen and hashchange events
			$win.on(resizeEvent, winResizeHandler).on(hashChangeEvent, hashtagHandler);

			if (screenfull.enabled) {
				$win.on(screenfull.raw.fullscreenchange + '.' + uniqId, winfullScreenHandler);
			}

			// Pause on hover
			$frame.on(hoverEvent, pauseOnHoverHandler);
			// Reset native FRAME element scroll
			$frame.on('scroll.' + namespace, resetScroll);

			// Add horizontal/vertical and mightySlider class
			$parent.addClass(o.navigation.horizontal ? o.classes.horizontalClass : o.classes.verticalClass).addClass(supportTouch ? o.classes.isTouchClass : '').addClass(namespace);

			// Initiate automatic cycling
			if (o.cycling.cycleBy) {
				self[o.cycling.startPaused ? 'pause' : 'resume']();
			}

			// Mark instance as initialized
			self.initialized = 1;

			// Trigger :initialize event
			trigger('initialize');

			// Add instance to all mightySlider instances
			push.call(mightySliderInstances, self);

			// Add the frame to the windowSpy
			windowSpy.add(frame);

			// Return instance
			return self;
		};
	}


	/**
	 * Get slide type.
	 *
	 * @param {Object}   options
	 *
	 * @return {String}
	 */
	function getSlideType(options) {
		var type = 'content',
		cover = options.cover,
		source = options.source,
		video = options.mp4 || options.webm || options.ogv || options.video;

		if (options.type) {
			return options.type;
		}
		else if (parseVideoURL(source)) {
			return 'video';
		}

		if (cover) {
			type = 'image';
		}
		if (source) {
			type = 'iframe';
		}
		if (video) {
			type = 'video';
		}
		if (options.link) {
			type = 'link';
		}
		
		return type;
	}

	/**
	 * Get element inline options.
	 *
	 * @param {Object}   $element    jQuery object with element.
	 *
	 * @return {Object}
	 */
	function getInlineOptions($element) {
		var data = $element.data(namespace.toLowerCase());
		return data && eval("({" + data + "})") || {};
	}

	/**
	 * Get caption keyframes.
	 *
	 * @param {Object}   $caption    jQuery object with element.
	 *
	 * @return {Object}
	 */
	function getCaptionKeyFrames($caption) {
		var data = $caption.data(minnamespace.toLowerCase() + 'animation');
		return data ? (isObject(data) ? [data] : eval("([" + data + "])")) : {};
	}

	/**
	 * Get caption default styles.
	 *
	 * @param {Object}   $caption    jQuery object with element.
	 *
	 * @return {Object}
	 */
	function getCaptionStyles($caption) {
		var styles = {};

		for (var i = 0, len = captionResponsiveStyles.length, property, pixel; i < len; i++) {
			property = captionResponsiveStyles[i],
			pixel = getPixel($caption, property);

			if (pixel) {
				styles[property] = pixel;
			}
		}

		return styles;
	}

	/**
	 * Normalize styles.
	 *
	 * @param {Object}   styles
	 * @param {Object}   properties
	 * @param {Number}   ratio
	 *
	 * @return {Object}
	 */
	function normalizeStyles(styles, properties, ratio) {
		var newStyles = {};

		_each(styles, function(property, value) {
			if (indexOf.call(properties, property) === -1) {
				return true;
			}
			var pixel = value * ratio;
			newStyles[property] = pixel;
		});

		return newStyles;
	}

	/**
	 * Event preventDefault & stopPropagation helper.
	 *
	 * @param {Event} event     Event object.
	 * @param {Bool}  noBubbles Cancel event bubbling.
	 *
	 * @return {Void}
	 */
	function stopDefault(event, noBubbles) {
		event.preventDefault();
		if (noBubbles) {
			event.stopPropagation();
		}
	}

	/**
	 * Disables an event it was triggered on and unbinds itself.
	 *
	 * @param  {Event} event
	 *
	 * @return {Void}
	 */
	function disableOneEvent(event) {
		/*jshint validthis:true */
		stopDefault(event, 1);
		$(this).off(event.type, disableOneEvent);
	}

	/**
	 * Resets native element scroll values to 0.
	 *
	 * @return {Void}
	 */
	function resetScroll() {
		/*jshint validthis:true */
		this.scrollLeft = 0;
		this.scrollTop = 0;
	}

	/**
	 * A JavaScript equivalent of PHP’s is_numeric.
	 *
	 * @param {Mixed} value
	 *
	 * @return {Boolean}
	 */
	function is_numeric(value) {
		return (type(value) === 'number' || type(value) === 'string') && value !== '' && !isNaN(value);
	}

	/**
	 * Parse style to pixels.
	 *
	 * @param {Object}   $element   jQuery object with element
	 * @param {String}   property   CSS property to get the pixels from
	 *
	 * @return {Int}
	 */
	function getPixel($element, property) {
		return parseInt($element.css(property), 10) || 0;
	}

	/**
	 * Make sure that number is within the limits.
	 *
	 * @param {Number} number
	 * @param {Number} min
	 * @param {Number} max
	 *
	 * @return {Number}
	 */
	function within(number, min, max) {
		return number < min ? min : number > max ? max : number;
	}

	/**
	 * Return value from percent of a number.
	 *
	 * @param {Number} percent
	 * @param {Number} total
	 *
	 * @return {Number}
	 */
	function percentToValue(percent, total) {
		return parseInt((total / 100) * percent);
	}

	/**
	 * Show slide loader.
	 *
	 * @param {Object}   $slide    jQuery object with element.
	 *
	 * @return {Object} jQuery DOM element
	 */
	function showLoader($slide) {
		var $loader = $('<div class="' + minnamespace + 'Icon ' + minnamespace + 'Loader"></div>'),
			loaderExists = $('.' + minnamespace + 'Loader', $slide);

		if (!loaderExists[0]) {
			$slide.prepend($loader);
		} else {
			$loader = loaderExists;
		}

		// Set instances number for loader icon
		var instancesAttr = $loader[0].getAttribute(minnamespace + 'instances'),
			instances = instancesAttr && parseInt(instancesAttr) || 0;

		// Set last show loader instance
		instances++;
		$loader[0].setAttribute(minnamespace + 'instances', instances);

		return $loader;
	}

	/**
	 * Hide slide loader.
	 *
	 * @param {Object}   $loader    jQuery object with element.
	 *
	 * @return {Void}
	 */
	function hideLoader($loader) {
		var instancesAttr = $loader[0].getAttribute(minnamespace + 'instances'),
			instances = instancesAttr && parseInt(instancesAttr) || 1;

		// Set last hide loader instance
		instances--;
		$loader[0].setAttribute(minnamespace + 'instances', instances);

		if (instances <= 0) {
			return $loader.remove();
		}
	}

	/**
	 * Calculate new dimensions from old dimensions.
	 *
	 * @param {Number}   width
	 * @param {Number}   height
	 * @param {Number}   width_old
	 * @param {Number}   height_old
	 * @param {Number}   factor
	 *
	 * @return {Object}
	 */
	function calculateDimensions(width, height, width_old, height_old, factor) {
		if (!factor) {
			if (!width) {
				factor = height / height_old;
			}
			else if (!height) {
				factor = width / width_old;
			}
			else {
				factor = Math.min( width / width_old, height / height_old );
			}
		}

		return {
			width: Math.round( width_old * factor ),
			height: Math.round( height_old * factor ),
			ratio:factor
		};
	}

	/**
	 * Parse video url
	 *
	 * @param {String}   url
	 *
	 * @return {Object}
	 */
	function parseVideoURL(url) {
		var result = null;

		for (var i = 0, len = videoRegularExpressions.length, object, split; i < len; i++) {
			object = videoRegularExpressions[i];

			// Test url if can be parsed
			if (object.reg.test(url)) {
				split = url.split(object.split);
				result = {
					source: object.url.replace(/\{id\}/g, split[object.index]),
					type: object.iframe && 'iframe' || 'flash'
				};

				break;
			}
		}

		return result;
	}

	/**
	 * Parse photo url
	 *
	 * @param {String}   url
	 *
	 * @return {Object}
	 */
	function parsePhotoURL(url) {
		var result = null;

		for (var i = 0, len = photoRegularExpressions.length, object; i < len; i++) {
			object = photoRegularExpressions[i];

			// Test url if can be parsed
			if (object.reg.test(url)) {
				result = $.extend(true, {}, object, {});
				result.oembed = _.absolutizeURI(object.oembed.replace(/\{URL\}/g, url), url);

				break;
			}
		}

		return result;
	}

	/**
	 * Create DOM element
	 *
	 * @param {String}   type
	 * @param {Object}   params
	 * @param {Object}   innerTags
	 *
	 * @return {Object}  DOM element
	 */
	function createElement(type, params, innerTags) {
		var el;

		params = params || {};
		innerTags = innerTags || {};

		switch (type) {
			case 'video':
				// Create video DOM element
				el = document.createElement( "video" );

				// Set default video attributes
				params = $.extend(true, videoDefaultAttributes, params);
				break;
			case 'iframe':
				// Create iframe DOM element
				el = document.createElement( "iframe" );

				// Set default iframe attributes
				params = $.extend(true, iframeDefaultAttributes, params);
				break;
			case 'flash':
				// Create embed DOM element
				el = document.createElement( "embed" );

				// Set default embed attributes
				params = $.extend(true, embedDefaultAttributes, params);
				break;
			default :
				el = document.createElement( type );
				break;
		}

		// Insert element attributes
		insertTag(el, params);

		// Insert innerTags
		_each(innerTags, function(attributes, i) {
			if (!attributes.name) {
				return true;
			}
			// Insert tags into el
			var newEl = document.createElement( attributes.name );

			attributes.name = null;

			// Insert element attributes
			insertTag(newEl, attributes);
			
			// Append inner tags into el
			el.appendChild(newEl);
		});

		return el;
	}

	/**
	 * Insert attributes to DOM elements
	 *
	 * @param {Object}   el                 HTML DOM element
	 * @param {String}   attributes
	 *
	 * @return {Void}
	 */
	function insertTag(el, attributes) {
		_each(attributes, function(key, value) {
			if (!is_numeric(value) && !value) {
				return true;
			}

			// Insert attribute into el
			insertAttribute(el, key, value);
		});
	}

	/**
	 * Insert an attribute into DOM element
	 *
	 * @param {Object}   el                 HTML DOM element
	 * @param {String}   attributeName
	 * @param {Mixed}    value
	 *
	 * @return {Void}
	 */
	function insertAttribute(el, attributeName, value) {
		var nodeValue = (isObject(value)) ? (function(){
				var query = "",
					i = 0;

				_each(value, function(k, v) {
					if (i!==0) {
						query += "&";
					}
					query += k + "=" + rawurlencode(v);
					i++;
				});
			return query;
		}()) : value;
		el.setAttribute(attributeName, nodeValue);
	}

	/**
	 * Preload images with callback.
	 *
	 * @param {Array} arr
	 *
	 * @return {Object}
	 */
	function preloadimages(arr) {
		var newImages = [], loadedImages = 0,
			postAction = function(){};


		arr = (!isArray(arr)) ? [arr] : arr;

		function imageLoadPost(){
			loadedImages++;
			if (loadedImages === arr.length) {
				// call postAction and pass in newImages array as parameter
				postAction(newImages);
			}
		}

		function handler() {
			imageLoadPost();
		}

		for (var i=0; i < arr.length; i++) {
			newImages[i] = new Image();
			newImages[i].onload = handler;
			newImages[i].onerror = handler;
			newImages[i].src = arr[i];
		}

		// return blank object with done() method
		return {
			done: function(f) {
				// remember user defined callback functions to be called when images load
				postAction = f || postAction;
			}
		};
	}

	/**
	 * Remove unwanted chars from background
	 *
	 * @param {String}  url
	 *
	 * @return {String}
	 */
	function stripUrl(url) {
		url = url.replace(/url\(\"/g, "");
		url = url.replace(/url\(/g, "");
		url = url.replace(/\"\)/g, "");
		url = url.replace(/\)/g, "");

		return url;
	};

	/**
	 * Find all images in a container with callback.
	 *
	 * @param {Object}  element  DOM element
	 *
	 * @return {Array}
	 */
	function findAllImages(element) {
		var url = "",
			$elements = $('*:not(script, style, .' + minnamespace + 'Cover, .' + minnamespace + 'CoverImage, .' + minnamespace + 'LayerCover, .' + minnamespace + 'LayerCoverImage, .' + minnamespace + 'Loader)', element),
			foundUrls = [];

		var obj, urls,
			eachHandler = function (el) {
				obj = $(el);

				if (el.nodeName.toLowerCase() === "img" && el.hasAttribute("src")) {
					//if is img and has src
					url = obj.prop("src");
				} else if (obj.css("background-image") !== "none") {
					//if object has background image
					url = obj.css("background-image");
				}

				//skip if gradient
				if (indexOf.call(url, "gradient") === -1) {
					//remove unwanted chars
					url = stripUrl(url);

					//split urls
					urls = url.split(", ");

					for (var i = 0, len = urls.length; i < len; i++) {
						if ((urls[i].length > 0 && !urls[i].match(/^(data:)/i)) && (indexOf.call(foundUrls, urls[i]) === -1)) {

							//add image to found list
							push.call(foundUrls, urls[i]);
						}
					}
				}
			};

		_each($elements, eachHandler);

		return foundUrls;
	}

	/**
	 * Check HTML5 video element can play given type.
	 *
	 * @param {String} type
	 *
	 * @return {String}
	 */
	function canPlayType(type) {
		var el = document.createElement( "video" );
		return !!(el.canPlayType && el.canPlayType(type).replace(/no/, ''));
	}

	/**
	 * Do the ajax requests with callback.
	 *
	 * @param {String}   url
	 * @param {Function} callback
	 *
	 * @return {Void}
	 */
	function doAjax(url, callback) {
		var docMode = document.documentMode || _.browser.version,
			ieLT10 = _.browser.msie && docMode < 10,
			data = { url: url },
			xhr = $.ajax({
				url: JSONReader,
				data: data,
				dataType: ieLT10 ? 'jsonp' : 'json',
				cache: !ieLT10
			});

		xhr.success(function(data){
			if (data.status === 200)
				callback(data.data);
			else
				callback(false);
		}).error(function(){
			callback(false);
		});

		// IE's that are lower than 10 cannot proccess json urls so we need to use jsonp
		// but because of the 'use strict' mode it's will be an error and also 'use strict'
		// mode in not supported by IE's that are lower than 10 :|
		if (ieLT10) {
			mightySliderCallback = function(data) {
				if (data.status === 200)
					callback(data.data);
				else
					callback(false);
			};
		}
	}

	/**
	 * A JavaScript equivalent of PHP’s rawurlencode.
	 *
	 * @param {String} str
	 *
	 * @return {String}
	 */
	function rawurlencode(str) {
		str = (str + '').toString();

		// Tilde should be allowed unescaped in future versions of PHP (as reflected below), but if you want to reflect current
		// PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
		return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
		replace(/\)/g, '%29').replace(/\*/g, '%2A');
	}

	/**
	 * A JavaScript equivalent of PHP’s ucfirst.
	 *
	 * @param {String} str
	 *
	 * @return {String}
	 */
	function ucfirst(str) {
		str += '';
		var f = str.charAt(0).toUpperCase();
		return f + str.substr(1);
	}


	/**
	 * Get type via extension.
	 *
	 * @param {String} URL
	 *
	 * @return {String}
	 */
	function getTypeByExtension(URL){
		var ext = _.getExtension(URL),
			type;

		if (!ext) {
			return false;
		}

		ext = ext.toLowerCase();
		
		if(indexOf.call(extensions.image, ext) !== -1) type = 'image';
		else if(indexOf.call(extensions.flash, ext) !== -1) type = 'flash';
		else if(indexOf.call(extensions.video, ext) !== -1) type = 'video';
		else type = 'iframe';
		
		return type;
	}

	/**
	 * Cross browser & normalize mouse event offsetX/offsetY.
	 *
	 * @param {Object}  event
	 * @param {DOM}     target
	 *
	 * @return {Object}
	 */
	function getOffset(event, target) {
		var e = event || window.event || { clientX: 0, clientY: 0 },
			el = target || e.target || e.srcElement,
			borderLeftWidth = parseInt(target.style['borderLeftWidth'], 10) || 0,
			borderTopWidth = parseInt(target.style['borderLeftWidth'], 10) || 0,
			rect = el.getBoundingClientRect(),
			offsetX = e.clientX - borderLeftWidth - rect.left,
			offsetY = e.clientY - borderTopWidth - rect.top;

		return { x: offsetX, y: offsetY };
	}

	/**
	 * Convert HH:MM:SS string to seconds.
	 *
	 * @param {String}  str
	 *
	 * @return {Number}
	 */
	function hmsToSeconds(str) {
		var p = str.split(':'),
		s = 0, m = 1;

		while (p.length > 0) {
			s += m * parseFloat(p.pop(), 10);
			m *= 60;
		}

		return s;
	}

	/**
	 * Slides contents scale handler.
	 *
	 * @param {Object}  $frame
	 * @param {Object}  scalers
	 *
	 * @return {Void}
	 */
	function scalersHandler($frame, scalers) {
		if (typeof TweenMax === 'undefined' || scalers.length < 1) {
			if (scalers.length > 0) {
				console && console.warn('Scalers needs TweenMax function. Please include the "tweenlite.js" into your page.');
			}
			return;
		}

		var viewport = { width: getPixel($frame, 'width'), height: getPixel($frame, 'height') };
		_each(scalers, function(el) {
			var $this = $(el),
				offset = { width: el.getAttribute('width') || getPixel($this, 'width'), height: el.getAttribute('height') || getPixel($this, 'height') },
				dims = calculateDimensions(viewport.width, viewport.height, offset.width, offset.height);

			TweenMax.set(el, { scale: dims.ratio });
		});
	}

	/**
	 * A JavaScript equivalent of PHP’s version_compare.
	 *
	 * @param {String} v1
	 * @param {String} v2
	 * @param {String} operator
	 *
	 * @return {Boolean}
	 */
	function version_compare(v1, v2, operator) {
		// Important: compare must be initialized at 0.
		var i = 0,
		x = 0,
		compare = 0,
		// vm maps textual PHP versions to negatives so they're less than 0.
		// PHP currently defines these as CASE-SENSITIVE. It is important to
		// leave these as negatives so that they can come before numerical versions
		// and as if no letters were there to begin with.
		// (1alpha is < 1 and < 1.1 but > 1dev1)
		// If a non-numerical value can't be mapped to this table, it receives
		// -7 as its value.
		vm = {
			'dev': -6,
			'alpha': -5,
			'a': -5,
			'beta': -4,
			'b': -4,
			'RC': -3,
			'rc': -3,
			'#': -2,
			'p': 1,
			'pl': 1
		},
		// This function will be called to prepare each version argument.
		// It replaces every _, -, and + with a dot.
		// It surrounds any nonsequence of numbers/dots with dots.
		// It replaces sequences of dots with a single dot.
		//    version_compare('4..0', '4.0') == 0
		// Important: A string of 0 length needs to be converted into a value
		// even less than an unexisting value in vm (-7), hence [-8].
		// It's also important to not strip spaces because of this.
		//   version_compare('', ' ') == 1
		prepVersion = function (v) {
			v = ('' + v).replace(/[_\-+]/g, '.');
			v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.');
			return (!v.length ? [-8] : v.split('.'));
		},
		// This converts a version component to a number.
		// Empty component becomes 0.
		// Non-numerical component becomes a negative number.
		// Numerical component becomes itself as an integer.
		numVersion = function (v) {
			return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10));
		};
		v1 = prepVersion(v1);
		v2 = prepVersion(v2);
		x = Math.max(v1.length, v2.length);
		for (i = 0; i < x; i++) {
			if (v1[i] === v2[i]) {
				continue;
			}
			v1[i] = numVersion(v1[i]);
			v2[i] = numVersion(v2[i]);
			if (v1[i] < v2[i]) {
				compare = -1;
				break;
			}
			else if (v1[i] > v2[i]) {
				compare = 1;
				break;
			}
		}
		if (!operator) {
			return compare;
		}

		// "No operator" seems to be treated as "<."
		// Any other values seem to make the function return null.
		switch (operator) {
			case '>':
			return (compare > 0);
			case '>=':
			return (compare >= 0);
			case '<=':
			return (compare <= 0);
			case '==':
			return (compare === 0);
			case '!=':
			return (compare !== 0);
			case '<':
			return (compare < 0);
			default:
			return null;
		}
	}

	// Local WindowAnimationTiming interface polyfill
	(function (w) {
		/**
		* Fallback implementation.
		*/
		var prev = _now();
		function fallback(fn) {
			var curr = _now();
			var ms = Math.max(0, 16 - (curr - prev));
			var req = setTimeout(fn, ms);
			prev = curr;
			return req;
		}

		/**
		* Cancel.
		*/
		var cancel = w.cancelAnimationFrame
			|| w.webkitCancelAnimationFrame
			|| w.clearTimeout;

		requestAnimationFrame = w.requestAnimationFrame
			|| w.webkitRequestAnimationFrame
			|| fallback;

		cancelAnimationFrame = function(id){
			cancel.call(w, id);
		};
	}(window));

	// Feature detects
	(function () {
		var prefixes = ['', 'webkit', 'moz', 'ms', 'o'];
		var el = document.createElement('div');

		function testProp(prop) {
			for (var p = 0, pl = prefixes.length; p < pl; p++) {
				var prefixedProp = prefixes[p] ? prefixes[p] + prop.charAt(0).toUpperCase() + prop.slice(1) : prop;
				if (el.style[prefixedProp] != null) {
					return prefixedProp;
				}
			}
		}

		// Global support indicators
		transform = testProp('transform');
		gpuAcceleration = testProp('perspective') ? 'translateZ(0) ' : '';
	}());

	// PageVisibility API detects
	(function () {
		var prefixes = ['', 'webkit', 'moz', 'ms', 'o'], property, prefix;

		while ((prefix = prefixes.pop()) != undefined) {
			property = (prefix ? prefix + 'H': 'h') + 'idden';
			if (type(document[property]) === 'boolean') {
				visibilityEvent = prefix + 'visibilitychange';
				break;
			}
		}

		visibilityHidden = function() {
			return document[property]; 
		};
	}());
	
	/**
	 * matchMedia() polyfill - Test a CSS media type/query in JS.
	 * @Authors & @Copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas, David Knight.
	 * @license: Dual MIT/BSD license
	 */
	window.matchMedia || (window.matchMedia = function () {
		// For browsers that support matchMedium api such as IE 9 and webkit
		var styleMedia = (window.styleMedia || window.media);

		// For those that don't support matchMedium
		// For those that don't support matchMedium
		if (!styleMedia) {
			var style = document.createElement('style'),
				script = document.getElementsByTagName('script')[0],
				info = null;

			style.type = 'text/css';
			style.id = 'matchmediajs-test';

			script.parentNode.insertBefore(style, script);

			// 'style.currentStyle' is used by IE <= 8 and 'window.getComputedStyle' for all other browsers
			info = ('getComputedStyle' in window) && window.getComputedStyle(style, null) || style.currentStyle;

			styleMedia = {
				matchMedium: function (media) {
					var text = '@media ' + media + '{ #matchmediajs-test { width: 1px; } }';

					// 'style.styleSheet' is used by IE <= 8 and 'style.textContent' for all other browsers
					if (style.styleSheet) {
						style.styleSheet.cssText = text;
					} else {
						style.textContent = text;
					}

					// Test if media query is true or false
					return info.width === '1px';
				}
			};
		}

		return function (media) {
			return {
				matches: styleMedia.matchMedium(media || 'all'),
				media: media || 'all'
			};
		};
	}());



	// $.msAnimate and $.msStop functions for using TweenLite and callback to original jQuery $.animate and $.stop
	var	_animate = $.fn.animate,
		_stop = $.fn.stop,
		_enabled = true,
		TweenLite, CSSPlugin, _warned,
		_copy = function(o) {
			var copy = {},
				p;
			for (p in o) {
				copy[p] = o[p];
			}
			return copy;
		},
		_reserved = {overwrite:1, delay:1, useFrames:1, runBackwards:1, easeParams:1, yoyo:1, immediateRender:1, repeat:1, repeatDelay:1, autoCSS:1},
		_defaultLegacyProps = ",scrollTop,scrollLeft,show,hide,toggle,",
		_legacyProps = _defaultLegacyProps,
		_copyCriticalReserved = function(main, sub) {
			for (var p in _reserved) {
				if (_reserved[p] && main[p] !== undefined) {
					sub[p] = main[p];
				}
			}
		},
		_createEase = function(ease) {
			return function(p) {
				return ease.getRatio(p);
			};
		},
		_easeMap = {},
		_init = function() {
			var globals = window.GreenSockGlobals || window,
				version, stale, p;
			TweenLite = globals.TweenMax || globals.TweenLite; //we prioritize TweenMax if it's loaded so that we can accommodate special features like repeat, yoyo, repeatDelay, etc.
			if (TweenLite) {
				version = (TweenLite.version + ".0.0").split("."); //in case an old version of TweenLite is used that had a numeric version like 1.68 instead of a string like "1.6.8"
				stale = !(Number(version[0]) > 0 && Number(version[1]) > 7);
				globals = globals.com.greensock;
				CSSPlugin = globals.plugins.CSSPlugin;
				_easeMap = globals.easing.Ease.map || {}; //don't do just window.Ease or window.CSSPlugin because some other libraries like EaselJS/TweenJS use those same names and there could be a collision.
			}
			if (!TweenLite || !CSSPlugin || stale) {
				TweenLite = null;
				if (!_warned && window.console) {
					window.console.log("The jquery.gsap.js plugin requires the TweenMax (or at least TweenLite and CSSPlugin) JavaScript file(s)." + (stale ? " Version " + version.join(".") + " is too old." : ""));
					_warned = true;
				}
				return;
			}
			if ($.easing) {
				for (p in _easeMap) {
					$.easing[p] = _createEase(_easeMap[p]);
				}
				_init = false;
			}
		};

	$.fn.msAnimate = function(prop, speed, easing, callback) {
		prop = prop || {};
		if (_init) {
			_init();
			if (!TweenLite || !CSSPlugin) {
				return _animate.call(this, prop, speed, easing, callback);
			}
		}
		if (!_enabled || prop.skipGSAP === true || (typeof(speed) === "object" && typeof(speed.step) === "function")) { //we don't support the "step" feature because it's too costly performance-wise, so fall back to the native animate() call if we sense one. Same with scrollTop and scrollLeft which are handled in a special way in jQuery.
			return _animate.call(this, prop, speed, easing, callback);
		}
		var config = $.speed(speed, easing, callback),
			vars = {ease:(_easeMap[config.easing] || ((config.easing === false) ? _easeMap.linear : _easeMap.swing))},
			obj = this,
			specEasing = (typeof(speed) === "object") ? speed.specialEasing : null,
			val, p, doAnimation, specEasingVars;

		for (p in prop) {
			val = prop[p];
			if (val instanceof Array && _easeMap[val[1]]) {
				specEasing = specEasing || {};
				specEasing[p] = val[1];
				val = val[0];
			}
			if (val === "show" || val === "hide" || val === "toggle" || (_legacyProps.indexOf(p) !== -1 && _legacyProps.indexOf("," + p + ",") !== -1)) { //note: slideUp() and slideDown() pass in opacity:"show" or opacity:"hide"
				return _animate.call(this, prop, speed, easing, callback);
			} else {
				vars[(p.indexOf("-") === -1) ? p : $.camelCase(p)] = val;
			}
		}

		if (specEasing) {
			vars = _copy(vars);
			specEasingVars = [];
			for (p in specEasing) {
				val = specEasingVars[specEasingVars.length] = {};
				_copyCriticalReserved(vars, val);
				val.ease = (_easeMap[specEasing[p]] || vars.ease);
				if (p.indexOf("-") !== -1) {
					p = $.camelCase(p);
				}
				val[p] = vars[p];
				delete vars[p];
			}
			if (specEasingVars.length === 0) {
				specEasingVars = null;
			}
		}

		doAnimation = function(next) {
			var varsCopy = _copy(vars),
				i;
			if (specEasingVars) {
				i = specEasingVars.length;
				while (--i > -1) {
					TweenLite.to(this, $.fx.off ? 0 : config.duration / 1000, specEasingVars[i]);
				}
			}
			varsCopy.onComplete = function() {
				if (next) {
					next();
				} else if (config.old) {
					$(this).each(config.old);
				}
			};
			varsCopy.force3D = true;
			TweenLite.to(this, $.fx.off ? 0 : config.duration / 1000, varsCopy);
		};

		if (config.queue !== false) {
			obj.queue(config.queue, doAnimation); //note: the queued function will get called once for each element in the jQuery collection.
			if (typeof(config.old) === "function") {
				obj.queue(config.queue, function(next) {
					config.old.call(this);
					next();
				});
			}
		} else {
			doAnimation.call(obj);
		}

		return obj;
	};


	$.fn.msStop = function(clearQueue, gotoEnd) {
		_stop.call(this, clearQueue, gotoEnd);
		if (TweenLite) {
			if (gotoEnd) {
				var tweens = TweenLite.getTweensOf(this),
					i = tweens.length,
					progress;
				while (--i > -1) {
					progress = tweens[i].totalTime() / tweens[i].totalDuration();
					if (progress > 0 && progress < 1) {
						tweens[i].seek(tweens[i].totalDuration());
					}
				}
			}
			TweenLite.killTweensOf(this);
		}
		return this;
	};


	/*
		Customized jQuery hashchange event v1.3
		https://github.com/cowboy/jquery-hashchange
		Copyright (c) 2010 "Cowboy" Ben Alman
		Dual licensed under the MIT and GPL licenses.
	*/
	(function () {
		var str_hashchange = "hashchange", doc = document, fake_onhashchange, special = $.event.special, doc_mode = doc.documentMode, supports_onhashchange = "on" + str_hashchange in window && (doc_mode === undefined || doc_mode > 7);
		function get_fragment(url) {
			url = url || location.href;
			return "#" + url.replace(/^[^#]*#?(.*)$/, "$1");
		}
		$.fn[str_hashchange] = function(fn) {
			return fn ? this.bind(str_hashchange, fn) : this.trigger(str_hashchange);
		};
		$.fn[str_hashchange].delay = 50;
		special[str_hashchange] = $.extend(special[str_hashchange], {setup:function() {
			if(supports_onhashchange) {
				return false;
			}
			$(fake_onhashchange.start);
		}, teardown:function() {
			if(supports_onhashchange) {
				return false;
			}
			$(fake_onhashchange.stop);
		}});
		fake_onhashchange = function() {
			var self = {}, timeout_id, last_hash = get_fragment(), fn_retval = function(val) {
				return val;
			}, history_set = fn_retval, history_get = fn_retval;
			self.start = function() {
				timeout_id || poll();
			};
			self.stop = function() {
				timeout_id && clearTimeout(timeout_id);
				timeout_id = undefined;
			};
			function poll() {
				var hash = get_fragment(), history_hash = history_get(last_hash);
				if(hash !== last_hash) {
					history_set(last_hash = hash, history_hash);
					$(window).trigger(str_hashchange);
				}else {
					if(history_hash !== last_hash) {
						location.href = location.href.replace(/#.*/, "") + history_hash;
					}
				}
				timeout_id = setTimeout(poll, $.fn[str_hashchange].delay);
			}
			(_.browser.msie) && !supports_onhashchange && function() {
				var iframe, iframe_src;
				self.start = function() {
					if(!iframe) {
						iframe_src = $.fn[str_hashchange].src;
						iframe_src = iframe_src && iframe_src + get_fragment();
						iframe = $('<iframe tabindex="-1" title="empty"/>').hide().one("load", function() {
							iframe_src || history_set(get_fragment());
							poll();
						}).attr("src", iframe_src || "javascript:0").insertAfter("body")[0].contentWindow;
						doc.onpropertychange = function() {
							try {
								if(event.propertyName === "title") {
									iframe.document.title = doc.title;
								}
							}catch(e) {}
						};
					}
				};
				self.stop = fn_retval;
				history_get = function() {
					return get_fragment(iframe.location.href);
				};
				history_set = function(hash, history_hash) {
					var iframe_doc = iframe.document, domain = $.fn[str_hashchange].domain;
					if(hash !== history_hash) {
						iframe_doc.title = doc.title;
						iframe_doc.open();
						domain && iframe_doc.write('<script>document.domain="' + domain + '"\x3c/script>');
						iframe_doc.close();
						iframe.location.hash = hash;
					}
				};
			}();
			return self;
		}();
	}());

	// Expose class globally
	window.mightySlider = mightySlider;

	mightySlider.author  		= 'iProDev (Hemn Chawroka). (www.iprodev.com)';
	mightySlider.version 		= '2.0.0';
	mightySlider.releaseDate 	= 'April 22, 2015';

	// Begin the plugin
	$.fn.mightySlider = function(options, callbackMap) {
		if (version_compare($.fn.jquery, '1.7', '>=')) {
			var method, methodArgs;

			// Attributes logic
			if (!isObject(options)) {
				if (type(options) === 'string' || options === false) {
					method = options === false ? 'destroy' : options;
					methodArgs = slice.call(arguments, 1);
				}
				options = {};
			}

			// Apply to all elements
			return this.each(function (i, element) {
				// Call with prevention against multiple instantiations
				var plugin = $.data(element, namespace);

				if (!plugin && !method) {
					// Create a new object if it doesn't exist yet
					plugin = $.data(element, namespace, new mightySlider(element, options, callbackMap).init());
				}
				else if (plugin && method) {
					// Call method
					if (plugin[method]) {
						plugin[method].apply(plugin, methodArgs);
					}
				}
			});
		}
		else {
			throw "The jQuery version that was loaded is too old. mightySlider requires jQuery 1.7+";
		}
	};

	// Default options
	mightySlider.defaults = {
		// Mixed options
		moveBy:             300,        // Speed in pixels per second used by forward and backward buttons.
		speed:              300,        // Animations speed in milliseconds. 0 to disable animations.
		easing:             'swing',    // Easing for duration based (tweening) animations.
		startAt:            null,       // Starting offset in slides.
		startRandom:        false,      // Starting offset in slides randomly, where: true = random, false = disable.
		viewport:           'fill',     // Sets the resizing method used to fit cover image within the viewport. Can be: 'center', 'fit', 'fill', 'stretch'.
		autoScale:          false,      // Automatically updates slider height based on base width.
		autoResize:         false,      // Auto resize the slider when active slide is bigger than slider FRAME.
		videoFrame:         null,       // The URL of the video frame to play videos with your custom player.
		preloadMode:        'nearby',   // Preloading mode for slides covers. Can be: 'all', 'nearby', 'instant'.

		// Navigation
		navigation: {
			horizontal:      true,            // Switch to horizontal mode.
			navigationType:  'forceCentered', // Slide navigation type. Can be: 'basic', 'centered', 'forceCentered'.
			slideSelector:   null,            // Select only slides that match this selector.
			smart:           true,            // Repositions the activated slide to help with further navigation.
			activateOn:      null,            // Activate an slide on this event. Can be: 'click', 'mouseenter', ...
			activateMiddle:  true,            // Always activate the slide in the middle of the FRAME. forceCentered only.
			slideSize:       0,               // Sets the slides size. Can be: Fixed(500) or Percent('100%') number.
			keyboardNavBy:   null             // Enable keyboard navigation by 'slides' or 'pages'.
		},

		// Deep-Linking
		deeplinking: {
			linkID:     null,  // Sets the deeplinking id.
			scrollTo:   false, // Enable scroll to slider when link changed.
			separator:  '/'    // Separator between linkID and slide ID.
		},

		// Scrolling
		scrolling: {
			scrollSource: null, // Selector or DOM element for catching the mouse wheel scrolling. Default is FRAME.
			scrollBy:     0,    // Slides to move per one mouse scroll. 0 to disable scrolling.
			hijack:       300   // Milliseconds since last wheel event after which it is acceptable to hijack global scroll.
		},

		// Dragging
		dragging: {
			dragSource:    null,  // Selector or DOM element for catching dragging events. Default is FRAME.
			mouseDragging: true,  // Enable navigation by dragging the SLIDEELEMENT with mouse cursor.
			touchDragging: true,  // Enable navigation by dragging the SLIDEELEMENT with touch events.
			releaseSwing:  true,  // Ease out on dragging swing release.
			swingSync:     7.5,   // Swing synchronization.
			swingSpeed:    0.1,   // Swing synchronization speed, where: 1 = instant, 0 = infinite.
			elasticBounds: true,  // Stretch SLIDEELEMENT position limits when dragging past FRAME boundaries.
			syncSpeed:     0.5,   // SLIDEELEMENT synchronization speed, where: 1 = instant, 0 = infinite.
			onePage:       false, // Enable one page move per drag, where: true = enable, false = disable.
			interactive:   null   // Selector for special interactive elements.
		},

		// Scrollbar
		scrollBar: {
			scrollBarSource:   null, // Selector or DOM element for scrollbar container.
			dragHandle:        true, // Whether the scrollbar handle should be draggable.
			dynamicHandle:     true, // Scrollbar handle represents the ratio between hidden and visible content.
			minHandleSize:     50,   // Minimal height or width (depends on mightySlider direction) of a handle in pixels.
			clickBar:          true  // Enable navigation by clicking on scrollbar.
		},

		// Pages
		pages: {
			pagesBar:       null, // Selector or DOM element for pages bar container.
			activateOn:     null, // Event used to activate page. Can be: click, mouseenter, ...
			pageBuilder:          // Page item generator.
				function (index) {
					return '<li>' + (index + 1) + '</li>';
				}
		},

		// Thumbnails
		thumbnails: {
			thumbnailsBar:       null,    // Selector or DOM element for thumbnails bar container.
			horizontal:          true,    // Switch to horizontal mode.
			preloadThumbnails:   true,    // Enable preload thumbnails images.
			thumbnailNav:        'basic', // Thumbnail navigation type. Can be: 'basic', 'centered', 'forceCentered'.
			activateOn:          'click', // Event used to activate thumbnail. Can be: click, mouseenter, ...
			scrollBy:            1,       // Thumbnails to move per one mouse scroll. 0 to disable scrolling.
			mouseDragging:       true,    // Enable navigation by dragging the thumbnailsBar with mouse cursor.
			touchDragging:       true,    // Enable navigation by dragging the thumbnailsBar with touch events.
			thumbnailSize:       0,       // Set thumbnails size. Can be: 500 and '100%'.
			thumbnailBuilder:             // Thumbnail item generator.
				function (index, thumbnail) {
					return '<li><img src="' + thumbnail + '" /></li>';
				}
		},

		// Commands
		commands: {
			thumbnails:     false, // Enable thumbnails navigation.
			pages:          false, // Enable pages navigation.
			buttons:        false  // Enable navigation buttons.
		},

		// Navigation buttons
		buttons: {
			forward:    null, // Selector or DOM element for "forward movement" button.
			backward:   null, // Selector or DOM element for "backward movement" button.
			prev:       null, // Selector or DOM element for "previous slide" button.
			next:       null, // Selector or DOM element for "next slide" button.
			prevPage:   null, // Selector or DOM element for "previous page" button.
			nextPage:   null, // Selector or DOM element for "next page" button.
			fullScreen: null  // Selector or DOM element for "fullscreen" button.
		},

		// Automated cycling
		cycling: {
			cycleBy:       null,   // Enable automatic cycling by 'slides' or 'pages'.
			pauseTime:     5000,   // Delay between cycles in milliseconds.
			loop:          true,   // Repeat cycling when last slide/page is activated.
			pauseOnHover:  false,  // Pause cycling when mouse hovers over the FRAME.
			startPaused:   false   // Whether to start in paused sate.
		},

		// Parallax
		parallax: {
			x:                true,          // Move in X axis parallax layers. where: true = enable, false = disable.
			y:                true,          // Move in Y axis parallax layers. where: true = enable, false = disable.
			z:                false,         // Move in Z axis parallax layers. where: true = enable, false = disable.
			scroll:           false,         // Eanbling the scrolling parallax for layers and covers.
			invertX:          true,          // Invert X axis movements. where: true = enable, false = disable.
			invertY:          true,          // Invert Y axis movements. where: true = enable, false = disable.
			invertZ:          false,         // Invert Z axis movements. where: true = enable, false = disable.
			revert:           true,          // Whether the layers should revert to theirs start position when mouse leaved the slider. where: true = enable, false = disable.
			revertDuration:   1500,          // The duration of the revert animation, in milliseconds.
			revertEasing:     'easeOutExpo', // Easing for revert duration based (tweening) animations.
			frictionDuration: 1500,          // The duration of the friction the layers experience. 0 to disable friction.
			frictionEasing:   'easeOutExpo'  // Easing for friction duration based (tweening) animations.
		},

		// Classes
		classes: {
			isTouchClass:        'isTouch',        // Class for when device has touch ability.
			draggedClass:        'dragged',        // Class for dragged SLIDEELEMENT.
			activeClass:         'active',         // Class for active slides and pages.
			disabledClass:       'disabled',       // Class for disabled navigation elements.
			verticalClass:       'vertical',       // Class for vertical sliding mode.
			horizontalClass:     'horizontal',     // Class for horizontal sliding mode.
			showedLayersClass:   'showed',         // Class for showed layers.
			isInFullScreen:      'isInFullScreen', // Class for when slider is in fullscreen
			isInFreeze:          'isInFreeze'      // Class for when slider is in freeze mode
		}
	};

	// Defining easeOutExpo jQuery easing
	jQuery.easing['easeOutExpo'] = function (x, t, b, c, d) {
		return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
	};

})(jQuery, this);

/**
 * isMobile.js v0.3.6
 *
 * A simple library to detect Apple phones and tablets,
 * Android phones and tablets, other mobile devices (like blackberry, mini-opera and windows phone),
 * and any kind of seven inch device, via user agent sniffing.
 *
 * @author: Kai Mallea (kmallea@gmail.com)
 *
 * @license: http://creativecommons.org/publicdomain/zero/1.0/
 */
(function(global) {

	var apple_phone = /iPhone/i,
		apple_ipod = /iPod/i,
		apple_tablet = /iPad/i,
		android_phone = /(?=.*\bAndroid\b)(?=.*\bMobile\b)/i, // Match 'Android' AND 'Mobile'
		android_tablet = /Android/i,
		windows_phone = /IEMobile/i,
		windows_tablet = /(?=.*\bWindows\b)(?=.*\bARM\b)/i, // Match 'Windows' AND 'ARM'
		other_blackberry = /BlackBerry/i,
		other_blackberry_10 = /BB10/i,
		other_opera = /Opera Mini/i,
		other_firefox = /(?=.*\bFirefox\b)(?=.*\bMobile\b)/i, // Match 'Firefox' AND 'Mobile'
		seven_inch = new RegExp(
			'(?:' + // Non-capturing group

			'Nexus 7' + // Nexus 7

			'|' + // OR

			'BNTV250' + // B&N Nook Tablet 7 inch

			'|' + // OR

			'Kindle Fire' + // Kindle Fire

			'|' + // OR

			'Silk' + // Kindle Fire, Silk Accelerated

			'|' + // OR

			'GT-P1000' + // Galaxy Tab 7 inch

			')', // End non-capturing group

			'i'); // Case-insensitive matching

	var match = function(regex, userAgent) {
		return regex.test(userAgent);
	};

	var IsMobileClass = function(userAgent) {
		var ua = userAgent || navigator.userAgent;

		this.apple = {
			phone: match(apple_phone, ua),
			ipod: match(apple_ipod, ua),
			tablet: match(apple_tablet, ua),
			device: match(apple_phone, ua) || match(apple_ipod, ua) || match(apple_tablet, ua)
		};
		this.android = {
			phone: match(android_phone, ua),
			tablet: !match(android_phone, ua) && match(android_tablet, ua),
			device: match(android_phone, ua) || match(android_tablet, ua)
		};
		this.windows = {
			phone: match(windows_phone, ua),
			tablet: match(windows_tablet, ua),
			device: match(windows_phone, ua) || match(windows_tablet, ua)
		};
		this.other = {
			blackberry: match(other_blackberry, ua),
			blackberry10: match(other_blackberry_10, ua),
			opera: match(other_opera, ua),
			firefox: match(other_firefox, ua),
			device: match(other_blackberry, ua) || match(other_blackberry_10, ua) || match(other_opera, ua) || match(other_firefox, ua)
		};
		this.seven_inch = match(seven_inch, ua);
		this.any = this.apple.device || this.android.device || this.windows.device || this.other.device || this.seven_inch;
		// excludes 'other' devices and ipods, targeting touchscreen phones
		this.phone = this.apple.phone || this.android.phone || this.windows.phone;
		// excludes 7 inch devices, classifying as phone or tablet is left to the user
		this.tablet = this.apple.tablet || this.android.tablet || this.windows.tablet;

		if (typeof window === 'undefined') {
			return this;
		}
	};

	var instantiate = function() {
		var IM = new IsMobileClass();
		IM.Class = IsMobileClass;
		return IM;
	};

	if (typeof module != 'undefined' && module.exports && typeof window === 'undefined') {
		//node
		module.exports = IsMobileClass;
	} else if (typeof module != 'undefined' && module.exports && typeof window !== 'undefined') {
		//browserify
		module.exports = instantiate();
	} else if (typeof define === 'function' && define.amd) {
		//AMD
		define('isMobile', [], global.isMobile = instantiate());
	} else {
		global.isMobile = instantiate();
	}

})(this);

/*!
 * screenfull
 * v2.0.0 - 2014-12-22
 * (c) Sindre Sorhus; MIT License
 */
(function() {
	'use strict';

	var isCommonjs = typeof module !== 'undefined' && module.exports;
	var keyboardAllowed = typeof Element !== 'undefined' && 'ALLOW_KEYBOARD_INPUT' in Element;

	var fn = (function() {
		var val;
		var valLength;

		var fnMap = [
			[
				'requestFullscreen',
				'exitFullscreen',
				'fullscreenElement',
				'fullscreenEnabled',
				'fullscreenchange',
				'fullscreenerror'
			],
			// new WebKit
			[
				'webkitRequestFullscreen',
				'webkitExitFullscreen',
				'webkitFullscreenElement',
				'webkitFullscreenEnabled',
				'webkitfullscreenchange',
				'webkitfullscreenerror'

			],
			// old WebKit (Safari 5.1)
			[
				'webkitRequestFullScreen',
				'webkitCancelFullScreen',
				'webkitCurrentFullScreenElement',
				'webkitCancelFullScreen',
				'webkitfullscreenchange',
				'webkitfullscreenerror'

			],
			[
				'mozRequestFullScreen',
				'mozCancelFullScreen',
				'mozFullScreenElement',
				'mozFullScreenEnabled',
				'mozfullscreenchange',
				'mozfullscreenerror'
			],
			[
				'msRequestFullscreen',
				'msExitFullscreen',
				'msFullscreenElement',
				'msFullscreenEnabled',
				'MSFullscreenChange',
				'MSFullscreenError'
			]
		];

		var i = 0;
		var l = fnMap.length;
		var ret = {};

		for (; i < l; i++) {
			val = fnMap[i];
			if (val && val[1] in document) {
				for (i = 0, valLength = val.length; i < valLength; i++) {
					ret[fnMap[0][i]] = val[i];
				}
				return ret;
			}
		}

		return false;
	})();

	var screenfull = {
		request: function(elem) {
			var request = fn.requestFullscreen;

			elem = elem || document.documentElement;

			// Work around Safari 5.1 bug: reports support for
			// keyboard in fullscreen even though it doesn't.
			// Browser sniffing, since the alternative with
			// setTimeout is even worse.
			if (/5\.1[\.\d]* Safari/.test(navigator.userAgent)) {
				elem[request]();
			} else {
				elem[request](keyboardAllowed && Element.ALLOW_KEYBOARD_INPUT);
			}
		},
		exit: function() {
			document[fn.exitFullscreen]();
		},
		toggle: function(elem) {
			if (this.isFullscreen) {
				this.exit();
			} else {
				this.request(elem);
			}
		},
		raw: fn
	};

	if (!fn) {
		if (isCommonjs) {
			module.exports = false;
		} else {
			window.screenfull = false;
		}

		return;
	}

	Object.defineProperties(screenfull, {
		isFullscreen: {
			get: function() {
				return !!document[fn.fullscreenElement];
			}
		},
		element: {
			enumerable: true,
			get: function() {
				return document[fn.fullscreenElement];
			}
		},
		enabled: {
			enumerable: true,
			get: function() {
				// Coerce to boolean in case of old WebKit
				return !!document[fn.fullscreenEnabled];
			}
		}
	});

	if (isCommonjs) {
		module.exports = screenfull;
	} else {
		window.screenfull = screenfull;
	}
})();