Framework.registerModule("Animator", {

	active: [],
	
	algorithms: {
		"linear": function(time) {
			return time;
		},
		"easeIn": function(time) {
			return time*time;
		},
		"easeOut": function(time) {
			return Math.sqrt(time);
		},
		"easeInAndOut": function(time) {
			if(time > 0.5) {
				time -= 0.5;
				time *= 2;
				return 0.5+(time*time)/2;
			} else
				return Math.sqrt(time*2)/2;
		},
		"easeCenter": function(time) {
			if(time > 0.5) {
				time -= 0.5;
				return 0.5+Math.sqrt(time*2)/2;
			} else {
				time *= 2;
				return (time*time)/2;
			}
		}
	},
	
	tick: function() {
		var elements = Framework.Animator.active;
		Framework.Animator.active = [];
		
		// Keep animations consistent even if they take long
		var timeForTick = (new Date()).getTime(); 
		elements.each(function(element) {
			try {
				if(!element.parentNode)
					return; // Element was most likely removed
			
				var active = element.__styleAnimator;
				element.__styleAnimator = $H();
				
				var empty = true;
				var styleChanges = false;
				active.each(function(pair) {
					try {
						var dur = (timeForTick - pair.value.start) / pair.value.duration;
						if(dur > 1)
							dur = 1;
						
						//console.log(dur, pair);
						if(!styleChanges)
							styleChanges = {};
						if(dur < 1) {
							styleChanges[pair.key] = (pair.value.base + pair.value.
								algorithm(dur)*pair.value.anitarget) + pair.value.suffix;
							element.__styleAnimator.set(pair.key, pair.value);
							empty = false;
						} else {
							styleChanges[pair.key] = pair.value.target + pair.value.suffix;
							if(pair.value.callback)
								pair.value.callback(element, pair.key);
						}
					} catch(e) {}
				});
				// Bunch processing up as much as possible to avoid browser quirks
				if(styleChanges) 
					element.setStyle(styleChanges);
				if(empty)
					throw "No animations left for this element.";
			
				Framework.Animator.active.push(element);
			} catch(e) {}
		});
		
		if(Framework.Animator.active.length < 1)
			Framework.Animator.timer.stop();
	},
	
	animateStyle: function(element, style, target, opts) {
		element = $(element);
		target = Math.round(parseFloat(target) * 100) / 100;
		
		opts = opts || {};
		var anitarget = true;
		if(!("__styleAnimator" in element))
			element.__styleAnimator = $H();
		else if(opts.unique) {
			var styleState = element.__styleAnimator.get(style);
			if(styleState && styleState.unique == opts.unique)
				anitarget = 0;
		}
		
		if(anitarget) {
			opts.duration = parseFloat(opts.duration) || 200;
			opts.from = opts.from;
			if(Object.isUndefined(opts.from)) {
				opts.from = element.getStyle(style);
				// Only allow pixel or raw values, ignore things like em and %
				if(/^\d+(\.\d+)?(px)?$/.match(opts.from))
					opts.from = parseFloat(opts.from);
				else {
					opts.from = element.measure(style);
					if(!Object.isNumber(opts.from))
						opts.from = 0;
				}
			}
			
			opts.suffix = opts.suffix || "";
			opts.from = Math.round(opts.from * 100) / 100;
			anitarget = Math.round((target - opts.from) * 100) / 100;
		}
		
		if(anitarget == 0) {
			if(opts.callback)
				opts.callback(element, style);
			return; // Already set
		}
		
		if(!opts.algorithm)
			opts.algorithm = Framework.Animator.algorithms.linear;
		else if(!Object.isFunction(opts.algorithm))
			opts.algorithm = Framework.Animator.algorithms[opts.algorithm];
			
		var start = (new Date()).getTime();
		element.__styleAnimator.set(style, {
			"element": element,
			"callback": opts.callback,
			"style": style,
			
			"base": opts.from,
			"suffix": opts.suffix,
			"anitarget": anitarget,
			"unique": opts.unique,
			"target" : target,
			
			"start": start,
			"duration": parseFloat(opts.duration),
			"algorithm": opts.algorithm
		});
		//console.log(element.__styleAnimator);
		
		if(Framework.Animator.active.indexOf(element))
			Framework.Animator.active.push(element);
		Framework.Animator.timer.start();
		return element;
	},

	//style, target, from, suffix, duration, routine
	initialize: function() {
		var thisInstance = this;
		this.timer = new Framework.Timers.AccurateTimer(this.tick);
		Element.addMethods({
			"animateStyle": thisInstance.animateStyle,
			"fadeOut": function(element, opts) {
				opts = opts || {};
				opts.callback = opts.callback || Element.hide;
				opts.unique = opts.unique || "fadeOut";
				return thisInstance.animateStyle(element,
						"opacity", 0, opts);
			},
			"fadeIn": function(element, opts, startInvisible) {
				if(startInvisible || !Element.visible(element))
					Element.setOpacity(element, 0);
				Element.show(element);
				
				opts = opts || {};
				opts.unique = opts.unique || "fadeIn";
				return thisInstance.animateStyle(element,
						"opacity", 1, opts);
			},
			"slideTo": function(element, target, opts) {
				opts = opts || {};
				opts.suffix = opts.suffix || "px";
				if("top" in target)
					Framework.Animator.animateStyle(element,
										"top", target.top, opts);
				if("left" in target)
					Framework.Animator.animateStyle(element,
									"left", target.left, opts);
				return element;
			},
			"sizeTo": function(element, target, opts) {
				opts = opts || {};
				opts.suffix = opts.suffix || "px";
				if("width" in target)
					Framework.Animator.animateStyle(element,
									"width", target.width, opts);
				if("height" in target)
					Framework.Animator.animateStyle(element,
									"height", target.height, opts);
				return element;
			}
		});
	}

});
