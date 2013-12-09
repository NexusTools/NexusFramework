/*Framework.Components.registerComponent("select", {
		setup: function(el) {
			var first = true;
			el.style.display = "none";
			this.widget = $(document.createElement("widget"));
			this.widget.addClassName("component");
			this.widget.addClassName("options");
			el.parentNode.insertBefore(this.widget, el);
			console.log(el.options);
			$A(el.options).each(function(option) {
				if(option.value == el.value || first) {
					this.widget.innerHTML = option.innerHTML;
					this.widget.value = option.value;
				}
				first = false;
			});
			
			el.on("click", function() {
			});
		},
		
		destroy: function(el) {
			this.widget.parentNode.removeChild(this.widget);
		},
		
		getValue: function() {
			return this.widget.value;
		}
	}, true);
	
Framework.Components.registerComponent("form", {
		setup: function(el) {
			el.getFormData = function() {
				
			}
		}
	}, true);

if(!("max" in document.createElement('progress')))
	Framework.Components.registerComponent("progress", {
			setup: function(el) {
			
			}
		});*/
		
Framework.Components.registerComponent("form input[type=submit]", {
			hook: function(el) {
				console.log("Hooking Form");
				
				var form = el.up("form");
				el.on("click", function(e) {
					console.log("Destroying Form Container");
					Framework.Components.destroyContainer(form);
				});
			}
		});
		
Framework.Components.registerComponent("form input, form submit, form textarea", {
			hook: function(el) {
				console.log("Hooking Form");
				
				var form = el.up("form");
				el.on("invalid", function(e) {
					console.log("Form Invalid, Reinitializing Form Container");
					setTimeout(function() {
						Framework.Components.setupContainer(form);
					}, 500);
				});
			}
		});
		
Framework.Components.registerComponent("input", {
			hook: function(el) {
				var type = el.readAttribute("type") || "text";
				el.addClassName(("type-" + type).camelize());
			}
		});
