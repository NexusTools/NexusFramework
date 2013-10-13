Framework.registerModule("Components", {

		registered: $H(),
		
		registerComponent: function(expression, structure, raw){
			console.log("Registered Component for `" + expression + "`");
		
			var claz = Class.create(Framework.Components.baseClass, structure);
			if(!raw && Framework.Config.LEGACY_BROWSER)
				expression = "div." + expression;
			Framework.Components.registered.set(expression, claz);
			console.log(claz);
		},
		
		registerWidgetType: function(type, structure){
			type = Framework.StringFormat.idForDisplay(type);
			Framework.Components.registerComponent("widget." + type, structure);
		},
		
		setupContainer: function(container){
			if(container.element instanceof Function)
				container = container.element();
			
			console.log("Setting up Container", container, Framework.Components.registered.keys());
			Framework.Components.registered.each(function(component){
				try {
					console.log(component);
					Element.select(container, component.key).each(function(element){
						console.log(element);
						try {
							if(!element.components)
								element.components = {};
						
							var cComponent;
							if(component.key in element.components) {
								cComponent = element.components[component.key];
								if(cComponent.isSetup) {
									console.log("Component already setup");
									return;
								}
						
								cComponent.setup();
							} else {
								cComponent = new component.value(element);
								element.components[component.key] = cComponent;
							}
							cComponent.isSetup = true;
						} catch(e) {
							console.log("" + e);
							console.log(e.stack);
						}
					});
				} catch(e) {
					console.log("" + e);
					console.log(e.stack);
				}
			});
		},
		
		destroyContainer: function(container){
			if(container.element instanceof Function)
				container = container.element();
				
			console.log("Destroying Container", container);
			Framework.Components.registered.each(function(component){
				console.log(component.key);
				container.select(component.key).each(function(element){
					console.log(element);
					try {
						console.log(element.components);
						$H(element.components).each(function(pair) {
							if(!pair.value.isSetup) {
								console.log("Component not setup, can't destroy");
								return; // Not setup, skip
							}
						
							console.log(pair);
							pair.value.destroy(pair.value.getElement());
							pair.value.isSetup = false;
						});
					} catch(e) {
						console.log("" + e);
						console.trace(e);
					}
				});
			});
		},
		
		loaded: function(){
			this.setupContainer(Framework.ThemeElement);
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
	
			getValue: function() {
				return this.getElement().value;
			},
			
			hook: function(el) {
			},
			
			setup: function(el){
			},
			
			destroy: function(el){
			}
			
		})
		
	});
