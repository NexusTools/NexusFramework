Framework.Components.registerWidgetType("switch", {

	hook: function(el) {
		this.labels = el.select("label[for]");
		if(this.labels.length != 2)
			throw "Switches require exactly 2 parts";
		this.labels.each((function(label) {
			var input = label.readAttribute("for");
			input = el.select("#" + input)[0];
			if(!input)
				throw "Input for label missing";
			label.input = input;
			
			input.on("change", this.updateState.bind(this));
		}).bind(this));
		this.thumb = $(document.createElement("thumb"));
		this.thumb.on("click", (function() {
			var toggled = this.isToggled();
			console.log("Toggling State", toggled);
			
			if(toggled) {
				this.labels[0].input.setAttribute("checked", true);
				this.labels[1].input.removeAttribute("checked");
			} else {
				this.labels[0].input.removeAttribute("checked");
				this.labels[1].input.setAttribute("checked", true);
			}
			this.updateState();
		}).bind(this));
		this.updateState();
	},
	
	setup: function(el) {
		el.addClassName("real");
		this.labels[0].addClassName("first");
		this.labels[1].addClassName("second");
		
		var width = Math.max(this.labels[0].getWidth(), this.labels[1].getWidth());
		this.labels[0].style.width = width + "px";
		this.labels[1].style.width = width + "px";
		el.appendChild(this.thumb);
	},
	
	isToggled: function() {
		return this.labels[1].input.hasAttribute("checked");
	},
	
	updateState: function() {
		if(this.isToggled())
			this.getElement().addClassName("toggled");
		else
			this.getElement().removeClassName("toggled");
	},
	
	destroy: function(el) {
		el.removeChild(this.thumb);
		this.labels[1].removeClassName("second");
		this.labels[0].removeClassName("first");
		this.labels[0].style.width = "";
		this.labels[1].style.width = "";
		el.removeClassName("real");
	}

});
