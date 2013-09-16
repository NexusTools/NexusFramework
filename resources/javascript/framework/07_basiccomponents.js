Framework.Components.registerComponent("select", {
		setup: function(el) {
			var first = true;
			el.style.display = "none";
			var optionsWidget = $(document.createElement("widget"));
			optionsWidget.addClassName("component");
			optionsWidget.addClassName("options");
			el.parentNode.insertBefore(optionsWidget, el);
			console.log(el.options);
			$A(el.options).each(function(option) {
				if(option.value == el.value || first)
					optionsWidget.innerHTML = option.innerHTML;
				first = false;
			});
		}
	}, true);

if(!("max" in document.createElement('progress')))
	Framework.Components.registerComponent("progress", {
			setup: function(el) {
			
			}
		});
