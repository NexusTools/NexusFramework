Framework.registerModule("Components", {

	registered: $H(),
	
	registerComponent: function(expression, structure, raw){
		//console.log("Registered Component for `" + expression + "`");
	
		var claz = Class.create(Framework.Components.baseClass, structure);
		if(!raw && Framework.Config.LEGACY_BROWSER)
			expression = "div." + expression;
		Framework.Components.registered.set(expression, claz);
		//console.log(claz);
	},
	
	registerWidgetType: function(type, structure){
		type = Framework.StringFormat.idForDisplay(type);
		Framework.Components.registerComponent("widget." + type, structure);
	},
	
	redetect: function(element) {
		var children = element.parentNode.childNodes;
			
		var seekMethod = false;
		if(children.length == 1)
			seekMethod = function(expr) {
				return Element.down(element.parent, expr);
			}
		else {
			var pos = 0;
			for(var pos = 0; pos < Math.floor(children.length/2); pos++) {
				if(element == children[pos] && element.nextSibling) {
					seekMethod = function(expr) {
						return Element.previous(element.nextSibling, expr);
					};
					break;
				}
			}
			if(!seekMethod)
				seekMethod = function(expr) {
					return Element.next(element.previousSibling, expr);
				};
		}
		
		Framework.Components.registered.each(function(component){
			if(seekMethod(component.key) == element)
				Framework.Components.setupComponent(element, component);
			else
				Framework.Components.destroyComponent(element, component);
		});
	},
	
	setupComponent: function(element, component) {
		Framework.Components.isProcessing ++;
		//console.log("Setup components on element", element);
		try {
			if(!element.__framework_components__) {
				element.__framework_components__ = {
					initialized: {},
					byName: {}
				}
			}
		
			var cComponent;
			if(component.key in element.__framework_components__.initialized) {
				cComponent = element.__framework_components__.initialized[component.key];
				cComponent.configure(element);
			} else {
				cComponent = new component.value(element);
				element.__framework_components__.initialized[component.key] = cComponent;
				try{
					element.__framework_components__.byName[cComponent.getName()] = cComponent;
				}catch(e) {}
			}
		} catch(e) {
			if(typeof e.stack !== 'undefined')
				console.log(e.stack);
			else
				console.log("" + e);
		}
		Framework.Components.isProcessing --;
	},
	
	destroyComponent: function(element, component) {
		Framework.Components.isProcessing ++;
		try {
			if(!element.__framework_components__)
				return;
			
			var cComponent = element.__framework_components__.initialized[component.key];
			if(cComponent)
				cComponent.deconfigure(element);
		} catch(e) {
			if(typeof e.stack !== 'undefined')
				console.log(e.stack);
			else
				console.log("" + e);
		}
		Framework.Components.isProcessing --;
	},
	
	findComponents: function(regexp) {
		var components = [];
		Framework.Components.registered.each(function(component){
			if(regexp.match(component.key))
				components.push(component);
		});
		
		return components;
	},
	
	setupContainer: function(container){
		if(container.element instanceof Function)
			container = container.element();
		
		Framework.Components.isProcessing ++;
		//console.log("Setting up Container", container, Framework.Components.registered.keys());
		Framework.Components.registered.each(function(component){
			try {
				//console.log(component);
				Element.select(container, component.key).each(function(element){
					Framework.Components.setupComponent(element, component);
				});
			} catch(e) {
				console.log("" + e);
				console.log(e.stack);
			}
		});
		Framework.Components.isProcessing --;
	},
	
	destroyContainer: function(container){
		if(container.element instanceof Function)
			container = container.element();
			
		Framework.Components.isProcessing ++;
		//console.log("Destroying Container", container);
		Framework.Components.registered.each(function(component){
			//console.log(component.key);
			Element.select(container, component.key).each(function(element){
				Framework.Components.destroyComponent(element, component);
			});
		});
		Framework.Components.isProcessing --;
	},
	
	loaded: function(){
		this.setupContainer(Framework.ThemeElement);
	},
	
	initialize: function() {
		this.isProcessing = false;
		Element.addMethods({
			getComponentByName: function(element, name) {
				try {
					return element.__framework_components__.byName[name];
				} catch(e) {}
				return null;
			},
			invokeComponentMethod: function(element, component, method) {
				var component = Element.getComponentByName(element, component);
				if(!component)
					throw "`" + component + "` is not initialized.";
				
				return (component[method])();
			}
		});
	},
	
	dragHandler: new (Class.create({
	
		initialize: function() {
			this.boundMouseMove = this.mouseMove.bind(this);
			this.boundStartDragging = this.startDragging.bind(this);
			this.boundStopDragging = this.stopDragging.bind(this, undefined);
		},
		
		mouseMove: function(e) {
			if(!this.active)
				return;
			
			var pos = {
				left: e.pointerX() - this.offset.left,
				top: e.pointerY() - this.offset.top
			};
			if(!Event.fire(this.active, "drag:move", pos).stopped)
				Element.setStyle(this.active, {
					left: pos.left + "px",
					top: pos.top + "px"
				});
			e.stop();
		},
		
		startDragging: function(target, filterTargets, offset) {
			if(this.active) {
				if(target == this.active)
					return;
				
				Event.stopObserving(this.active, "mouseup", this.boundStopDragging);
				Event.stopObserving(this.active, "mousemove", this.boundMouseMove);
				Element.removeClassName(this.active, "grabbed");
				Event.fire(this.active, "drag:stop");
			} else {
				this.lastBodyCursor = Element.getStyle(document.body, "cursor");
				Event.observe(document.body, "mousemove", this.boundMouseMove, true);
				Event.observe(document.body, "mouseup", this.boundStopDragging, true);
				Event.observe(document, "mouseup", this.boundStopDragging, true);
				Event.observe(document, "mousemove", this.boundMouseMove, true);
				Event.observe(window, "blur", this.boundStopDragging, true);
			}
			
			this.offset = offset || {top: 0, left: 0};
			Event.setFilters("mouseenter mousemove", filterTargets);
			Element.removeClassName(document.body, "dragging");
			Element.setStyle(document.body, {
				"cursor": Element.getStyle(target, "cursor")
			});
			Event.observe(target, "mouseup", this.boundStopDragging, true);
			Event.observe(target, "mousemove", this.boundMouseMove, true);
			Element.addClassName(document.body, "dragging");
			Element.addClassName(target, "grabbed");
			this.filterTargets = filterTargets;
			Event.fire(target, "drag:start");
			if(target.setCapture)
				target.setCapture(true);
			this.active = target;
		},
		
		stopDragging: function(target, e) {
			if(!this.active || (target && this.active !== target))
				return;
			
			if(e) {
				var targetEl = e.findElement(this.filterTargets);
				if(targetEl)
					Event.fire(targetEl, "drag:drop");
			}
			Element.setStyle(document.body, {
				"cursor": this.lastBodyCursor
			});
			Element.removeClassName(document.body, "dragging");
			Event.stopObserving(window, "blur", this.boundStopDragging);
			Event.stopObserving(this.active, "mousemove", this.boundMouseMove);
			Event.stopObserving(document.body, "mousemove", this.boundMouseMove, true);
			Event.stopObserving(document.body, "mouseup", this.boundStopDragging, true);
			Event.stopObserving(this.active, "mouseup", this.boundStopDragging);
			Event.stopObserving(document, "mouseup", this.boundStopDragging);
			Event.stopObserving(document, "mousemove", this.boundMouseMove);
			Element.removeClassName(this.active, "grabbed");
			Event.unsetFilters("mouseenter mousemove");
			Event.fire(this.active, "drag:stop");
			this.filterTargets = undefined;
			if(document.releaseCapture)
				document.releaseCapture();
			this.active = undefined;
		}
	
	})),
	
	baseClass: Class.create({
		
		initialize: function(el) {
			this.__mutationHandler = (function() {
				this.updateAttributes(el);
				if(!this.__setupLayoutTimeout) {
					this.__setupLayoutTimeout = setTimeout((function() {
						this.updateLayout(el);
				
						delete this.__setupLayoutTimeout;
					}).bind(this), 0);
				}
			}).bind(this, el);
			this.element = el;
			
			this.hook(el);
			this.configure(el);
		},
		
		configure: function(el) {
			if(this.__setup)
				return;
			
			Event.observe(el, "DOMAttrModified", this.__mutationHandler);
			Event.observe(el, "dom:attrmodified", this.__mutationHandler);
			
			this.setup(el);
			this.updateAttributes(el);
			this.__setupLayoutTimeout = setTimeout((function() {
				this.setupLayout(el);
				this.updateLayout(el);
				
				delete this.__setupLayoutTimeout;
			}).bind(this), 0);
			this.__setup = true;
		},
		
		deconfigure: function(el) {
			if(!this.__setup)
				return;
			
			Event.stopObserving(el, "DOMAttrModified", this.__mutationHandler);
			Event.stopObserving(el, "dom:attrmodified", this.__mutationHandler);
			cComponent.destroy(el);
			cComponent.__setup = false;
		},
		
		isSetup: function() {
			return this.__setup;
		},
		
		makeDraggable: function(el, filterTargets) {
			Element.absolutize(el);
			Element.writeAttribute(el, "unselectable", "on");
			Element.setStyle({
				"userSelected": "none"
			});
			Event.on(el, "select", Event.stop);
			el.on("mousedown", function(e) {
				var pointer = {
					left: e.pointerX(),	
					top: e.pointerY()
				};
				var offset = {
					left: Element.measure(el, "left"),	
					top: Element.measure(el, "top")
				};
				
				offset.left = pointer.left - offset.left;
				offset.top = pointer.top - offset.top;
				
				Framework.Components.dragHandler.startDragging(el, filterTargets, offset);
				e.stop();
			});
		},
		
		getElement: function(){
			return this.element;
		},
		
		giveFocus: function() {
			this.getElement().focus();
		},

		getValue: function() {
			return this.getElement().value;
		},

		setValue: function(val) {
			this.getElement().value = val;
		},
		
		hook: function(el) {
		},
		
		setup: function(el){
		},
		
		updateAttributes: function(el) {
		},
		
		updateLayout: function(el) {
		},
		
		setupLayout: function(el) {
		},
		
		destroy: function(el){
		}
		
	})
	
});
