var editableWidgets = $$("widget[vpages-widget], widget[control-page]");
editableWidgets.each(function(widget) {
	if(!widget.hasAttribute("control-page")) {
		var widgetID = widget.readAttribute("vpages-widget") * 1;
		if(!widgetID)
			return;
		
		widget.writeAttribute("control-page", "Pages/Edit Widget?id=" + widgetID);
		widget.writeAttribute("vpages-widget", null);
		if(widget.hasAttribute("vpages-name")) {
			widget.writeAttribute("edit-title", "Edit `" + widget.readAttribute("vpages-name") + "`");
			widget.writeAttribute("vpages-name", null);
		}
	}
	
	console.log(widget);
	
	widget.edit = document.createElement("widget-toolbar");
	widget.edit.addClassName("hidden");
	var editTool = document.createElement("edit");
	if(widget.hasAttribute("edit-title")) 
		editTool.writeAttribute("title", widget.readAttribute("edit-title"));
	
	editTool.on("click", function(){
		var url = Framework.baseURL + "control/" + widget.readAttribute("control-page");
		if(url.indexOf("?") > -1)
			url += "&popup";
		else
			url += "?popup";
		var popup = createPopup("<iframe border='0' style='border: none; width: 800px; height: 500px' src='" + url + "'></iframe>");
		popup.down("iframe").contentWindow.close = function(reload){
			closeLastPopup();
			if(reload) {
				var url = location.href;
				var get;
				var getPos = url.indexOf("?");
				if(getPos > -1) {
					get = url.substring(getPos+1);
					url = url.substring(0, getPos);
					
					if(get.indexOf("__nocache__=") == -1)
						get += "&__nocache__=*style*,*script*";
				} else
					get = "__nocache__=*style*,*script*";
				
				location.href = url + "?" + get;
			}
		};
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
		//if(layout.get("height") > 19 &&
		//	layout.get("width") > 29) {
			var offset = widget.cumulativeOffset();
		
			widget.edit.setStyle({"left": (offset[0] + (layout.get("border-box-width") - 5 - widget.edit.measure("border-box-width"))) + "px",
								"top": (offset[1] + 5) + "px",
								"display": "block"});
							
		//} else
		//	widget.edit.setStyle("visible", false);
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
