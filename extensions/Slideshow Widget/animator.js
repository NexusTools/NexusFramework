NexusTools = {};

NexusTools.Animator = {

	setup: function(helper) {
		if(!window.NexusTools)
			window.NexusTools = {};

		window.NexusTools.Animator = this;
	},

	cleanup: function(helper){
		if(window.NexusTools)
			window.NexusTools.Animator = undefined;
	},

	__processCallback: function(element, callback){
		if(typeof callback == "function")
			callback.call(element);
		else
			mergeOver(element, callback);
	},
	__resetTimeout: function(element, uniqueID){
		if(!element.__active_transitions)
			element.__active_transitions = {};

		if(element.__active_transitions[uniqueID]) {
			if(NexusTools.Timer)
				element.__active_transitions[uniqueID].timer.stop();
			else
				clearTimeout(element.__active_transitions[uniqueID].timer);
			element.__active_transitions[uniqueID] = undefined;
			delete element.__active_transitions[uniqueID];
		}
	},
	__setTimeout: function(element, uniqueID, timeout, callback){
		if(timeout) {
			if(NexusTools.Timer)
				element.__active_transitions[uniqueID] = {
						"timer": NexusTools.Timer.runOnce(timeout),
						"callback": callback
					}
			else
				element.__active_transitions[uniqueID] = {
						"timer": setTimeout(timeout, 42),
						"callback": callback
					}
		} else {
			element.__active_transitions[uniqueID] = null;
			delete element.__active_transitions[uniqueID];
		}
	},

	stopAll: function(element){
		return;
		for(var key in element.__active_transitions) {
			var transitionInstance = element.__active_transitions[key];
			NexusTools.Animator.__processCallback(transitionInstance.callback);
			if(NexusTools.Timer)
				transitionInstance.timer.stop();
			else
				clearTimeout(transitionInstance.timer);
			delete element.__active_transitions[key];
		}
	},

	// Convenience Methods
	moveToX: function(element, x, fromX, callback, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "left", x, fromX, callback);
	},
	moveToY: function(element, y, fromY, callback, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "top", y, fromY, callback);
	},
	moveTo: function(element, x, y, fromX, fromY, callback, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "left", x, fromX, callback);
		NexusTools.Animator.__transitionStyleAttribute(element, "top", y, fromY, callback);
	},
	scrollToX: function(element, x, callback, animation){
		NexusTools.Animator.__transitionAttribute(element, "scrollLeft", x, callback);
	},
	scrollToY: function(element, y, callback, animation){
		NexusTools.Animator.__transitionAttribute(element, "scrollTop", y, callback);
	},
	scrollTo: function(element, x, y, callback, animation){
		NexusTools.Animator.__transitionAttribute(element, "scrollLeft", x, callback);
		NexusTools.Animator.__transitionAttribute(element, "scrollTop", y, callback);
	},
	fade: function(element, to, from, callback, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "opacity", to, from, callback, "");
	},
	resizeToWidth: function(element, to, from, callback, suffix, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "width", to, from, callback, suffix);
	},
	resizeToHeight: function(element, to, from, callback, suffix, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "height", to, from, callback, suffix);
	},
	resizeTo: function(element, width, height, fromWidth, fromHeight, callback, suffix, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "width", width, fromWidth, callback, suffix);
		NexusTools.Animator.__transitionStyleAttribute(element, "height", height, fromHeight, callback, suffix);
	},
	borderRadiusTo: function(element, to, from, callback, animation){
		NexusTools.Animator.__transitionStyleAttribute(element, "borderRadius", to, from, callback);
	},

	// Animation Routines
	animations: {
		linear: function(from, to, steps){
			this.value = from;
			this.final = to;
			this.steps = steps;
			this.step = (to - from) / steps;
			this.process = function(){
				this.value += this.step;
				this.steps--;
			
				return this.steps > 0 ? this.value : this.final;
			}
			this.finishNow = function(){
				this.steps = 0;
			}
			this.finished = function(){
				return this.steps <= 0;
			}
		}
	},
	__transitionAttribute: function(element, attrName, to, from, callback, animation){
		if(!animation)
			animation = "linear";
		if(from == undefined)
			from = objectToNumber(element[attrName]);

		animation = new NexusTools.Animator.animations[animation](from, to, 9);
		NexusTools.Animator.transitionAttribute(element, attrName, animation, callback);
	},
	transitionAttribute: function(element, attrName, animation, callback){
		if(animation == undefined)
			throw "Animator Missing";

		var uniqueID = "__attr_timeout_" + attrName;
		NexusTools.Animator.__resetTimeout(element, uniqueID);
		element[attrName] = animation.process();
		if(animation.finished()) {
			NexusTools.Animator.__setTimeout(element, uniqueID, null);
			if(callback)
				NexusTools.Animator.__processCallback(element, callback);
		} else
			NexusTools.Animator.__setTimeout(element, uniqueID, function(){
					NexusTools.Animator.transitionAttribute(element, attrName, animation, callback);
				}, callback);
	},
	__transitionStyleAttribute: function(element, styleName, to, from, callback, suffix, animation){
		if(!animation)
			animation = "linear";
		if(from == undefined)
			from = objectToNumber(element.style[styleName]);

		animation = new NexusTools.Animator.animations[animation](from, to, 9);
		NexusTools.Animator.transitionStyleAttribute(element, styleName, animation, callback, suffix);
	},
	transitionStyleAttribute: function(element, styleName, animation, callback, suffix){
		if(!element.style)
			throw "Element Missing Style Field";
		if(animation == undefined)
			throw "Animator Missing";
		if(suffix == undefined)
			suffix = "px";

		var uniqueID = "__style_timeout_" + styleName;
		NexusTools.Animator.__resetTimeout(element, uniqueID);
		var newValue = animation.process() + suffix;
	
		var oldValue = element.style[styleName];
		element.style[styleName] = newValue;
		if(element.style[styleName] == oldValue){
			var styleBase = styleName.substring(0, 1).toUpperCase() + styleName.substring(1);
			styleName = "Webkit" + styleBase;
			try {
				oldValue = element.style[styleName];
				element.style[styleName] = newValue;
				if(element.style[styleName] == oldValue)
					throw "Failed Test";
			}catch(e){
				styleName = "Moz" + styleBase;
				try {
					oldValue = element.style[styleName];
					element.style[styleName] = newValue;
					if(element.style[styleName] == oldValue)
						throw "Failed Test";
				}catch(e){
					// All Tests Failed, Skip Attribute...
					animation.finishNow();
					alert("Failed to find Compatible Style Attribute: " + styleName);
				}
			}
		
		}
	
		if(animation.finished()) {
			NexusTools.Animator.__setTimeout(element, uniqueID, null);
			if(callback)
				NexusTools.Animator.__processCallback(element, callback);
		} else
			NexusTools.Animator.__setTimeout(element, uniqueID, function(){
					NexusTools.Animator.transitionStyleAttribute(element, styleName, animation, callback, suffix);
				}, callback);
		
	}
}
