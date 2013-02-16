Framework.registerModule("Components", {

		registered: $H(),
		
		registerComponent: function(expression, structure){
			Framework.Components.registered[expression] = Class.create(Framework.Components.baseClass, structure);
		},
		
		registerWidgetType: function(type, structure){
			type = Framework.StringFormat.idForDisplay(type);
		
			if(Framework.Config.LEGACY_BROWSER)
				type = "div." + type.replace(/\-/, "_");
			else
				type = "widget." + type;
				
			Framework.Components.registerComponent(type, structure);
		},
		
		setupContainer: function(container){
			if(container.element instanceof Function)
				container = container.element();
				
			console.log("Setting up Components");
			console.log(container);
			Framework.Components.registered.each(function(component){
					container.select(component.key).each(function(element){
							try {
								if(!element.components)
									element.components = [];
								element.components.push(new component.value(element));
							} catch(e) {}
						});
				});
		},
		
		baseClass: Class.create({
				
				element: false,
				
				initialize: function(element) {
					this.element = element;
					this.setup();
				},
				
				getElement: function(){
					return this.element;
				},
				
				setup: function(){
					throw "Create Not Implemented";
				},
				
				destroy: function(){
					throw "Destroy Not Implemented";
				}
				
			})
		
	});
