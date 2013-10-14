Framework.Components.registerComponent("input[type=condition]", {
	
	
	hook: function(el) {
		this.easyButton = $(document.createElement("button"));
		this.easyButton.writeAttribute("title", "Easy Editor");
		this.easyButton.setStyle({margin: "0px", marginLeft: "2px"});
		this.easyButton.innerHTML = "...";
		
		this.easyButton.on("click", function(e) {
			e.stop();
			
			ControlPanel.customDialog("<h2>Condition Editor</h2><p>Unfinished</p>");
		});
	},
	
	setup: function(el) {
		el.parentNode.insertBefore(this.easyButton, el.nextSibling);
	
	},
	
	destroy: function(el) {
		el.parentNode.removeChild(this.easyButton);
	}
	
});
