Framework.registerModule("Animator", {

	active: [],
	
	algorithms: {
		"linear": function(time) {
			return time;
		},
		"easeIn": function(time) {
			return Math.sqrt(time);
		},
		"easeOut": function(time) {
			return time*time;
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
						
						console.log(dur, pair);
						if(!styleChanges)
							styleChanges = {};
						if(dur < 1) {
							styleChanges[pair.key] = (pair.value.base + pair.value.
								algorithm(dur)*pair.value.anitarget) + pair.value.suffix;
							element.__styleAnimator.set(pair.key, pair.value);
							empty = false;
						} else {
							styleChanges[pair.key] = pair.value.target;
							if(pair.value.callback)
								pair.value.callback(element);
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
		
		opts = opts || {};
		opts.duration = parseFloat(opts.duration) || 200;
		opts.from = opts.from || parseFloat(element.getStyle(style)) || 0;
		opts.suffix = opts.suffix || "";
		opts.from = Math.round(opts.from * 100) / 100;
		var anitarget = Math.round((target - opts.from) * 100) / 100;
		if(anitarget == 0) {
			if(opts.callback)
				opts.callback(element);
			return; // Already set
		}
		
		if(!opts.algorithm)
			opts.algorithm = Framework.Animator.algorithms.linear;
		else if(!Object.isFunction(opts.algorithm))
			opts.algorithm = Framework.Animator.algorithms[opts.algorithm];
		console.log(opts.algorithm);
			
		if(!("__styleAnimator" in element))
			element.__styleAnimator = $H();
			
		var start = (new Date()).getTime();
		element.__styleAnimator.set(style, {
			"element": element,
			"callback": opts.callback,
			"style": style,
			
			"base": opts.from,
			"suffix": opts.suffix,
			"anitarget": anitarget,
			"target" : target,
			
			"start": start,
			"duration": parseFloat(opts.duration),
			"algorithm": opts.algorithm
		});
		console.log(element.__styleAnimator);
		
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
				return thisInstance.animateStyle(element,
						"opacity", 0, opts);
			},
			"fadeIn": function(element, opts) {
				return thisInstance.animateStyle(element,
						"opacity", 1, opts);
			},
			slideTo: function(element, target, opts) {
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
			sizeTo: function(element, target, opts) {
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
