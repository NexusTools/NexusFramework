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
			
			console.log("Setting up Container", container);
			Framework.Components.registered.each(function(component){
					console.log(component.key);
					container.select(component.key).each(function(element){
							console.log(element);
							try {
								if(!element.components)
									element.components = {};
								if(component.key in element.components)
									element.components[component.key].setup();
								element.components[component.key] = new component.value(element);
							} catch(e) {
								console.log("" + e);
								console.trace(e);
							}
						});
				});
		},
		
		destroyContainer: function(container){
			if(container.element instanceof Function)
				container = container.element();
				
			Framework.Components.registered.each(function(component){
					console.log(component.key);
					container.select(component.key).each(function(element){
							console.log(element);
							try {
								element.components.each(function(component) {
									component.destroy();
								});
								
								if(!element.components)
									element.components = [];
								element.components.push(new component.value(element));
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
				
				initialize: function(el) {
					this.element = el;
					this.setup(el);
				},
				
				getElement: function(){
					return this.element;
				},
		
				getValue: function() {
					return this.getElement().value;
				},
				
				setup: function(el){
				},
				
				destroy: function(el){
				}
				
			})
		
	});
