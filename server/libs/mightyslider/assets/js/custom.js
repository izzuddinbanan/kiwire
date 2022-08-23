$(function(){
	'use strict';

	var $win = $(window),
		isTouch = !!('ontouchstart' in window),
		clickEvent = isTouch ? 'tap' : 'click',
		$preloader = $("#preloader"),
		$intro = $('#intro'),
		$timerEL = $('canvas', $intro),
		timerEL = $timerEL[0],
		ctx = timerEL && timerEL.getContext("2d"),
		$scalers, $intro_slider, $frame, intro_slider, transform;

	//make section height of window
	(function(){
		var url = window.location.href,
			split = url.split('#');

		if(split[1]) $win.one('scroll', function() {
			$win.scrollTop(0);
		});

		var viewport = getViewport();
		$intro.css({ width: viewport.width, height: viewport.height });
	})();

	// Feature detects
	(function () {
		var prefixes = ['', 'webkit', 'moz', 'ms', 'o'];
		var el = document.createElement('div');

		function testProp(prop) {
			for (var p = 0, pl = prefixes.length; p < pl; p++) {
				var prefixedProp = prefixes[p] ? prefixes[p] + prop.charAt(0).toUpperCase() + prop.slice(1) : prop;
				if (el.style[prefixedProp] !== undefined) {
					return prefixedProp;
				}
			}
		}

		// Global support indicators
		transform = testProp('transform');
	}());

	/**
	 * Calculate new dimensions by old dimensions.
	 *
	 * @param {Number}   width
	 * @param {Number}   height
	 * @param {Number}   width_old
	 * @param {Number}   height_old
	 *
	 * @return {Object}
	 */
	function calculateDimensions(width, height, width_old, height_old) {
		var factor = Math.min( width / width_old, height / height_old );

		return {
			width: Math.round( width_old * factor ),
			height: Math.round( height_old * factor ),
			ratio: factor
		};
	}

	/**
	 * Parse style to pixels.
	 *
	 * @param {Object}   $element   jQuery object with element.
	 * @param {Property} property   CSS property to get the pixels from.
	 *
	 * @return {Int}
	 */
	function getPixel($element, property) {
		return parseInt($element.css(property), 10) || 0;
	}

	/**
	 * Get viewport/window size (width and height).
	 *
	 * @return {Object}
	 */
	function getViewport() {
		var e = window,
			a = 'inner';

		if (!('innerWidth' in window)) {
			a = 'client';
			e = document.documentElement || document.body;
		}

		return {
			width: e[a + 'Width'],
			height: e[a + 'Height']
		}
	}

	/**
	 * Draw segment on canvas element
	 *
	 * @param {Number}   angle
	 *
	 * @return {Void}
	 */
	function drawSegment(angle) {
		var startingAngle = degreeToRadian(0),
			endingAngle = degreeToRadian(angle),
			center = 30;

		//Pie
		ctx.clearRect(0, 0, 60, 60);
		ctx.beginPath();
		ctx.moveTo(center, center);
		ctx.arc(center, center, 26, startingAngle, endingAngle, false);
		ctx.fillStyle = "#000";
		ctx.fill();
		ctx.closePath();
	}

	/**
	 * Cover degree to radian
	 *
	 * @param {Number}   degree
	 *
	 * @return {Number}
	 */
	function degreeToRadian(degree) {
		return ((degree - 90) * Math.PI) / 180;
	}

	function scalersHandler() {
		$scalers.each(function() {
			var $this = $(this),
				offset = { width: getPixel($this, 'width'), height: getPixel($this, 'height') },
				viewport = getViewport(),
				nW = viewport.width,
				nH = viewport.height,
				dims = calculateDimensions(nW, nH, offset.width, offset.height);

			if (dims.ratio < 1)
				$this[0].style[transform] = 'translate(-50%, -50%) scale(' + dims.ratio + ')';
			else
				$this[0].style[transform] = null;
		});
	}

	//custom scrollbar
	$(document).ready(function() {
		window.prettyPrint && prettyPrint();

		$intro_slider = $('#intro_slider'),
		$scalers = $('.scaler', $intro_slider),
		$frame = $('.frame', $intro_slider);

		if ($intro_slider[0]) {
			intro_slider = new mightySlider($frame, {
				speed: 700,
				easing: 'easeOutExpo',

				navigation: {
					horizontal: 0,
					slideSize: '100%'
				},

				pages: {
					activateOn: clickEvent
				},
				
				commands: {
					pages: 1
				},
				
				dragging: {
					touchDragging: 0,
					onePage: 1
				},

				cycling: {
					cycleBy: 'slides',
					loop: 0,
					pauseTime: 10000
				}
			},
			{
				// Call :active event
				active: function(name, index) {
					var skin = this.slides[index].options.skin || '';
					$intro_slider.removeClass('black').addClass(skin);

					// Clear the timer
					ctx.clearRect(0, 0, 60, 60);
				},

				// Call :pause event
				pause: function() {
					timerEL.setAttribute('title', 'Resume');
				},

				// Call :resume event
				resume: function() {
					timerEL.setAttribute('title', 'Pause');
				},

				// Call :progress event
				progress: function(name, progress) {
					// Draw pie timer based on progress
					drawSegment(360 / 1 * progress);
				}
			});

			$timerEL.on(clickEvent, function(){
				intro_slider.toggleCycling();
			});
		}

		if(navigator.userAgent.indexOf('Macintosh') === -1 && navigator.userAgent.indexOf('Firefox') === -1 && !isTouch && $.srSmoothscroll)
			$.srSmoothscroll({
				step: 115,
				speed: 350
			});

		$("[data-toggle=popover]").popover();

		$('.nav-tabs a').click(function (e) {
			e.preventDefault();

			var $this = $(this),
				$parent = $this.parent();

			if ($parent.hasClass('active')) {
				var $parents = $this.parents('.ms-tabs'),
					$tabContent = $('.tab-content', $parents),
					$target = $($this[0].getAttribute('href'), $tabContent);

				$parent.add($target).removeClass('active');
				return false;
			}
		});


		$('.carousel').carousel({
			interval: 10000,
			pause: "false"
		});
	});

	//smooth scroll on page
	$('[href^="#"]').bind('click', function(event) {
		event.preventDefault();

		var $this = $(this);

		if (this.hasAttribute('data-toggle') && this.getAttribute('data-toggle') === 'tab' || this.hasAttribute('data-slide') || window.location.href.indexOf('/documentation') !== -1)
			return;

		var hash = $this.attr('href'),
			target = $(hash);

		$('html, body').stop().animate({
			scrollTop: target.offset().top -61
		}, 1500,'easeInOutExpo');

		window.location.hash = hash.replace('#', '');
	});

	$win.load(function() {
		var url = window.location.href,
			split = url.split('#'),
			split2 = split[1] && split[1].split('/') || '',
			hash = !split2[1] && '#' + split[1] || null,
			target = $(hash);

		if ($intro_slider[0])
			intro_slider.init();

		// Examples alignment
		if ($.fn.isotope)
			(function(){
				var $container = $('div.thumbnails');
				$container.isotope({
					itemSelector : 'div.col-md-4',
					masonry: { columnWidth: $container.width() / 3 }
				});
				
				$win.smartresize(function(){
				  $container.isotope({
					// update columnWidth to a percentage of container width
					masonry: { columnWidth: $container.width() / 3 }
				  });
				});
			})();

		/*if ($.fn.stellar && !isTouch)
			$.stellar();*/

		if(target[0] && url.indexOf('/documentation') === -1) $('html, body').animate({
			scrollTop: target.offset().top -60
		}, 1500,'easeInOutExpo');

		$('[data-spy="scroll"]').each(function () {
			var $spy = $(this).scrollspy('refresh')
		});

		$("#status", $preloader).fadeOut();
		$preloader.delay(350).fadeOut("slow", function(){
			$preloader.remove();
		});

		scalersHandler();

		if(isTouch)
			setTimeout(function() {
				window.scrollTo(0, document.documentElement.scrollTop + 1);
			}, 0);
	}).resize(function() {
		var viewport = getViewport();
		$intro.css({ width: viewport.width, height: viewport.height });
		scalersHandler();
	});

	//collapse menu on click on mobile and tablet devices
	$('.nav a').click(function () { $(".nav-collapse").collapse("hide") });


	// Enable google pretty print
	window.prettyPrint && prettyPrint();
});