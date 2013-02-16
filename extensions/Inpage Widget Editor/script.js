var editableWidgets = $$("widget[vpages-widget]");
editableWidgets.each(function(widget) {
		var widgetID = widget.readAttribute("vpages-widget") * 1;
		if(!widgetID)
			return;
		
		widget.edit = document.createElement("widget-toolbar");
		widget.edit.addClassName("hidden");
		var editTool = document.createElement("edit");
		editTool.on("click", function(){
			var url = Framework.baseURL + "control/Pages/Edit Widget?popup&id=" + widgetID;
			createPopup("<iframe border='0' style='border: none; width: 600px; height: 450px' src='" + url + "'></iframe>");
		});
		
		widget.on("mouseover", function(){
			widget.edit.addClassName("widget-hover");
		});
		
		widget.on("mouseout", function(){
			widget.edit.removeClassName("widget-hover");
		});
		
		widget.edit.appendChild(editTool);
		//widget.edit.writeAttribute("src", "http://anup26.files.wordpress.com/2009/08/screwdriverandwrenchicon.jpg?w=29");
		//widget.edit.setStyle({"position": "absolute", "z-index": 500});
		//widget.edit.writeAttribute("title", "Edit `" + widget.readAttribute("vpages-name").trim() + "`");
	
		widget.edit.updatePosition = function(){
			var layout = widget.getLayout();
			if(layout.get("height") > 19 &&
				layout.get("width") > 29) {
				var offset = widget.cumulativeOffset();
			
				widget.edit.setStyle({"left": (offset[0] + (layout.get("border-box-width") - 5 - widget.edit.measure("border-box-width"))) + "px",
									"top": (offset[1] + 5) + "px",
									"display": "block"});
								
				console.log(layout.get("left"));
			} else
				widget.edit.setStyle("visible", false);
		};
		
		document.body.appendChild(widget.edit, widget.firstChild);
});

function updateAllPositions() {
	editableWidgets.each(function(widget) {
		widget.edit.updatePosition();
	});
	setTimeout(updateAllPositions, 500);
}

setTimeout(function(){
	editableWidgets.each(function(widget) {
		widget.edit.removeClassName("hidden");
	});
	
	Event.observe(window, "resize", updateAllPositions);
	updateAllPositions();
}, 500);
