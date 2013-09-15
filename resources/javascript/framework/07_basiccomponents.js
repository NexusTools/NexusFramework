Framework.Components.registerComponent("select", {
		setup: function(el) {
			el.style.display = "none";
			var optionsWidget = $(document.createElement("widget"));
			optionsWidget.addClassName("component");
			optionsWidget.addClassName("options");
			el.parentNode.insertBefore(optionsWidget, el);
			optionsWidget.innerHTML = el.text;
		}
	}, true);

if(!("max" in document.createElement('progress')))
	Framework.Components.registerComponent("progress", {
			setup: function(el) {
			
			}
		});
