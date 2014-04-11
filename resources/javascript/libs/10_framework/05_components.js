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
			if(target == this.active)
				return;
			
			if(Event.fire(target, "drag:start").stopped)
				return; // allow preventing start events
			
			if(this.active) {
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
			
			Element.addClassName(target, "grabbed");
			this.offset = offset || {top: 0, left: 0};
			Event.setFilters("mouseenter mousemove", filterTargets);
			Element.removeClassName(document.body, "dragging");
			Element.setStyle(document.body, {
				"cursor": Element.getStyle(target, "cursor")
			});
			Event.observe(target, "mouseup", this.boundStopDragging, true);
			Event.observe(target, "mousemove", this.boundMouseMove, true);
			Element.addClassName(document.body, "dragging");
			this.filterTargets = filterTargets;
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
	
		scheduleLayoutUpdate: function() {
			if(this.__layoutUpdateScheduled)
				return;
			
			this.__layoutUpdateScheduled = true;
			if(!this.__ignoreLayoutScheduler)
				this.layoutScheduleCallback.bind(this).defer();
		},
		
		layoutScheduleCallback: function() {
			delete this.__layoutUpdateScheduled;
			if(!this.__layoutUpdateScheduled || this.__ignoreLayoutScheduler)
				return;
			
			//console.trace();
			this.__ignoreMutations = true;
			this.updateLayout(this.__element);
			delete this.__ignoreMutations;
		},
		
		initialize: function(el) {
			var mutationQueue = [];
			this.__mutationHandler = (function() {
				this.__ignoreMutations = true;
				this.__ignoreLayoutScheduler = true;
				//console.log("Handling Mutations", mutationQueue);
				
				var matches = false;
				if(mutationQueue.length) {
					mutationQueue = mutationQueue.uniq();
					try {
						var self = this;
						var helper = {
							needsUpdate: function(attr, simple) {
								var found = mutationQueue.indexOf(attr) > -1;
								if(found) {
									matches = true;
									if(!simple)
										self.scheduleLayoutUpdate();
								}
								return found;
							},
							foundMatches: function() {
								return matches;
							}
						};
						this.updateAttributes(el, helper);
					} catch(e) {
						if(Object.isUndefined(e.stack))
							console.log(e.stack);
						else
							console.log("" + e);
					}
					mutationQueue = [];
				}
				
				try {
					if(this.__layoutUpdateScheduled)
						this.updateLayout(el);
				} catch(e) {
					if(Object.isUndefined(e.stack))
						console.log(e.stack);
					else
						console.log("" + e);
				}
				
				delete this.__ignoreMutations;
				delete this.__mutationDeferred;
				delete this.__layoutUpdateScheduled;
				delete this.__ignoreLayoutScheduler;
			}).bind(this);
			this.__mutationCoupler = (function(attrs) {
				if(this.__ignoreMutations || !attrs.length)
					return;
				
				mutationQueue = mutationQueue.concat(attrs);
				if(!this.__mutationDeferred)
					this.__mutationDeferred = this.__mutationHandler.defer();
			});
			
			this.__mutationCapture = (function(e) {
				var attrs;
				if("memo" in e)
					attrs = e.memo;
				else if("attrName" in e && e.attrName) // DOMAttrChanged
					attrs = [e.attrName];
				else if("propertyName" in e) // IE PropertyChange event
					attrs = [e.propertyName];
				
				if(attrs && attrs.length) {
					var goodAttrs = [];
					attrs.each(function(attr) {
						if(!attr.match(/^style(\/|\.|$)/))
							goodAttrs.push(attr);
					});
					if(goodAttrs.length)
						this.__mutationCoupler(goodAttrs);
				}
			}).bind(this);
			this.__element = el;
			
			this.hook(el);
			this.configure(el);
		},
		
		configure: function(el) {
			if(this.__setup)
				return;
			
			if("MutationObserver" in window) {
				console.log("Has Mutation Observer");
				this.__mutationObserver = new MutationObserver((function(mutations) {
					var attrs = [];
					mutations.each(function(mutation) {
						if(mutation.attributeName && !mutation.attributeName.match(/^style(\/|\.|$)/))
							attrs.push(mutation.attributeName);
					});
					if(attrs)
						this.__mutationCoupler(attrs);
				}).bind(this));
				this.__mutationObserver.observe(el, {attributes:true});
			} else {
				Event.observe(el, "propertychange", this.__mutationCapture);
				Event.observe(el, "DOMAttrModified", this.__mutationCapture);
				Event.observe(el, "dom:attrmodified", this.__mutationCapture);
			}
			this.__ignoreMutations = true;
			this.__ignoreLayoutScheduler = true;
			
			this.setup(el);
			var matches = false;
			this.updateAttributes(el, {
				needsUpdate: function(){
					matches = true;
					return true;
				},
				foundMatches: function(){
					return matches;
				}
			});
			this.setupLayout(el);
			this.updateLayout(el);
			delete this.__layoutUpdateScheduled;
			delete this.__ignoreLayoutScheduler;
			delete this.__ignoreMutations;
			this.__setup = true;
		},
		
		deconfigure: function(el) {
			if(!this.__setup)
				return;
			
			if(this.__mutationObserver) {
				this.__mutationObserver.disconnect();
				delete this.__mutationObserver;
			} else {
				Event.stopObserving(el, "propertychange", this.__mutationCapture);
				Event.stopObserving(el, "DOMAttrModified", this.__mutationCapture);
				Event.stopObserving(el, "dom:attrmodified", this.__mutationCapture);
			}
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
				"userSelect": "none"
			});
			Event.on(el, "select", Event.stop);
			el.on("drag:stop", (function(e) {
				delete this.__ignoreMutations;
			}).bind(this));
			el.on("mousedown", (function(e) {
				if(e.button)
					return;
				
				var pointer = e.pointer();
				var offset = {
					left: Element.measure(el, "left"),	
					top: Element.measure(el, "top")
				};
				
				offset.left = pointer.x - offset.left;
				offset.top = pointer.y - offset.top;
				this.__ignoreMutations = true;
				
				Framework.Components.dragHandler.startDragging(el, filterTargets, offset);
				e.stop();
			}).bind(this));
		},
		
		getElement: function(){
			return this.__element;
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
