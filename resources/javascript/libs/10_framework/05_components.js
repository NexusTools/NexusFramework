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
				if(!element.components) {
					element.components = {};
					element.__componentsByName = {};
					element.getComponentByName = function(name) {
						return element.componentsByName[name];
					}
					element.invokeComponentMethod = function(component, method) {
						component = element.getComponentByName(component);
						method = component[method];
						return method();
					}
				}
			
				var cComponent;
				if(component.key in element.components) {
					cComponent = element.components[component.key];
					if(cComponent.isSetup) {
						Framework.Components.isProcessing --;
						return;
					}
			
					cComponent.setup();
				} else {
					cComponent = new component.value(element);
					element.components[component.key] = cComponent;
					try{
						element.__componentsByName[cComponent.getName()] = cComponent;
					}catch(e) {}
				}
				cComponent.isSetup = true;
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
				//console.log(element.components);
				$H(element.components).each(function(pair) {
					if(!pair.value.isSetup)
						return; // Not setup, skip
				
					//console.log(pair);
					pair.value.destroy(pair.value.getElement());
					pair.value.isSetup = false;
				});
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
		},
		
		baseClass: Class.create({
				
			element: false,
			isSetup: false,
			
			initialize: function(el) {
				this.element = el;
				this.hook(el);
				this.setup(el);
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
			
			destroy: function(el){
			}
			
		})
		
	});
