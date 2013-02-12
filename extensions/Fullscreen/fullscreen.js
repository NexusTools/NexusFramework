FullscreenShared = Class.create({
	stateListeners: $A(),
	errorListeners: $A(),
	initialize: function(){
		console.log("Death Browser");
	},
	addStateListener: function(listener){
		this.stateListeners.push(listener);
	},
	addErrorListener: function(listener){
		this.errorListeners.push(listener);
	},
	emitError: function(info){
		console.log("ERROR: " + info);
		this.errorListeners.each(function(listener){
			listener(info);
		});
	},
	emitState: function(fullscreenElement){
		console.log("Fullscreen State Changed");
		console.log(fullscreenElement);
		this.stateListeners.each(function(listener){
			listener(fullscreenElement);
		});
	},
	impl: function(){
		throw "Not supported by browser.";
	},
	request: function(element){
		if(!element)
			element = document.body;
		try {
			if(!this.isEnabled())
				throw "Fullscreen disabled by browser.";
			this.impl(element);
		} catch(e) {
			this.emitError(e.toString());
		}
	},
	isEnabled: function(){
		return true;
	},
	exit: function(){}
});

if(!("ALLOW_KEYBOARD_INPUT" in Element))
	Element.ALLOW_KEYBOARD_INPUT = null;

if("onfullscreenchange" in document.body)
	Fullscreen = new (Class.create(FullscreenShared, {
		initialize: function(){
			
			console.log("Standards Compliant Browser");
			var thisObject = this;
			Event.observe(document, "fullscreenchange", function(){
				thisObject.emitState(document.fullscreenElement);
			});
			Event.observe(document, "fullscreenerror", function(){
				thisObject.emitError("Error while entering fullscreen.");
			});
		},
		isEnabled: function(){
			return document.fullscreenEnabled;
		},
		impl: function(element) {
			element.requestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		},
		exit: function(){
			document.exitFullscreen();
		}
	}))();
else if("onwebkitfullscreenchange" in document.body)
	Fullscreen = new (Class.create(FullscreenShared, {
		initialize: function(){
			
			console.log("WebKit Browser");
			var thisObject = this;
			Event.observe(document, "webkitfullscreenchange", function(){
				thisObject.emitState(document.webkitFullscreenElement);
			});
			Event.observe(document, "webkitfullscreenerror", function(){
				thisObject.emitError("Error while entering fullscreen.");
			});
		},
		isEnabled: function(){
			return document.webkitFullscreenEnabled;
		},
		impl: function(element) {
			element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		},
		exit: function(){
			document.webkitExitFullscreen();
		}
	}))();
else if("onmozfullscreenchange" in document.body)
	Fullscreen = new (Class.create(FullscreenShared, {
		initialize: function(){
			console.log("Mozilla Browser");
			var thisObject = this;
			Event.observe(document, "mozfullscreenchange", function(){
				thisObject.emitState(document.mozFullscreenElement);
			});
			Event.observe(document, "mozfullscreenerror", function(){
				thisObject.emitError("Error while entering fullscreen.");
			});
		},
		isEnabled: function(){
			return document.mozFullscreenEnabled;
		},
		impl: function(element) {
			element.mozRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		},
		exit: function(){
			document.mozCancelFullScreen();
		}
	}))();
else
	Fullscreen = new FullscreenShared();
	
if(!Element.prototype.requestFullscreen)
	Element.prototype.requestFullscreen = function(){
		Fullscreen.request(this);
	}
