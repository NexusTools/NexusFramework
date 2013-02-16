Framework.registerModule("Components", {

		registered: $H(),
		
		registerComponent: function(expression, structure){
			console.log("Registered Component for `" + expression + "`");
		
			var claz = Class.create(Framework.Components.baseClass, structure);
			Framework.Components.registered.set(expression, claz);
			console.log(claz);
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
				
			Framework.Components.registered.each(function(component){
					console.log(component.key);
					container.select(component.key).each(function(element){
							console.log(element);
							try {
								if(!element.components)
									element.components = [];
								element.components.push(new component.value(element));
							} catch(e) {
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
