/**
 * mightySlider Animate plugin
 * http://mightyslider.com/
 * 
 * @version:  1.0.1
 * @released: June 8, 2015
 * 
 * @author:   Hemn Chawroka
 *            http://iprodev.com/
 * 
 */
(function ($, window, undefined) {
	'use strict';

	// Begin the plugin
	$.fn.mightySliderAnimate = function(options, callbackMap) {
		// Default settings. Play carefully.
		options = jQuery.extend({}, {
			animateSpeed: 1000,
			animateEasing: 'ease',
			animateIn: '',
			animateOut: 'fadeOut'
		}, options);

		// Apply to all elements
		return this.each(function (i, element) {

			// Global slider's DOM elements and variables
			var $frame = $(element),
				$parent = $frame.parent(),
				$slidesElement = $frame.children().eq(0),
				$slides = $slidesElement.children(),
				lastIndex = 0,
				changeIndex = 0,
				mSOptions = jQuery.extend(true, {}, options, {
					startAt: 0,
					speed: 0,
					navigation: {
						horizontal:      true,
						navigationType:  'forceCentered',
						smart:           true,
						activateMiddle:  true,
						slideSize:       '100%'
					}
				});

			$parent.addClass('mightySliderAnimate');

			// Calling new mightySlider class
			var slider = new mightySlider($frame, mSOptions, callbackMap);

			slider.on('move', function() {
					changeIndex++;
			});
			slider.on('moveEnd', function(name) {
				var index = this.relative.activeSlide;
				if (this.slides.length > 1 && lastIndex !== index && changeIndex === 1 && (options.animateOut || options.animateIn)) {
					swapAnimation.call(slider, options, lastIndex);
				}

				lastIndex = index;
				changeIndex = 0;
			});

			// Initiate the mightySlider
			slider.init();

			lastIndex = slider.relative.activeSlide;

			// Reload the mightySlider
			slider.reload();

			// Cache the slider instance to the element
			$.data(element, 'mightySlider', slider);
		});
	};

	function clearAnimation(e, animationClass, options) {
		$(e.target).css( { '-webkit-animation-duration': '', 'animation-duration': '', 'left': '' } )
			.removeClass('animated ms-animated-out ms-animated-in ' + options.animateEasing)
			.removeClass(animationClass);
	};

	function swapAnimation(options, lastIndex) {
		var left,
			previous = this.slides[lastIndex],
			next = this.slides[this.relative.activeSlide],
			$prevSlide = $(previous.element),
			$nextSlide = $(next.element),
			incoming = options.animateIn,
			outgoing = options.animateOut;

		if ($.isArray(outgoing)) {
			outgoing = outgoing[Math.floor(Math.random() * outgoing.length)];
		}
		if ($.isArray(incoming)) {
			incoming = incoming[Math.floor(Math.random() * incoming.length)];
		}

		if (outgoing) {
			left = next.start - previous.start;
			$prevSlide.css( { 'left': left + 'px' } )
				.addClass('animated ms-animated-out ' + options.animateEasing)
				.addClass(outgoing)
				.css( { '-webkit-animation-duration': options.animateSpeed + 'ms', 'animation-duration': options.animateSpeed + 'ms' } )
				.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(e){
					clearAnimation(e, outgoing, options);
				});
		}

		if (incoming) {
			$nextSlide.addClass('animated ms-animated-in ' + options.animateEasing)
				.addClass(incoming)
				.css( { '-webkit-animation-duration': options.animateSpeed + 'ms', 'animation-duration': options.animateSpeed + 'ms' } )
				.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(e){
					clearAnimation(e, incoming, options);
				});
		}
	}

})(jQuery, this);