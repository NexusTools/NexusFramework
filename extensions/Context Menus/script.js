var contextBackground = document.createElement("ContextBackground");
contextBackground.hide();
document.body.appendChild(contextBackground);
var activeContextMenu = false;
var activeContextElement = null;

function getActiveContextElement() {
	return activeContextElement;
}

function closeLastContextMenu(e){
	contextBackground.hide();
	activeContextMenu.hide();
	e.stop();
}

function contextNoop(e){
	e.stop();
}

Event.observe(contextBackground, "click", closeLastContextMenu);
Event.observe(contextBackground, "contextmenu", closeLastContextMenu);

function setupContextElement(contextElement){
	console.log(contextElement);
	var contextMenuElement = $(contextElement.readAttribute("contextmenu"));
	
	contextElement.on("contextmenu", function(e){
		if(contextMenuElement.hasAttribute("type")) {
			contextMenuElement.remove();
			contextMenuElement.writeAttribute("type", false);
			contextMenuElement.addClassName("jsmenu");
			
			contextMenuElement.select("menuitem").each(function(menuItem){
				if(menuItem.hasAttribute("label")) {
					menuItem.on("click", function(){
						contextBackground.hide();
						contextMenuElement.hide();
					});
					menuItem.on("contextmenu", contextNoop);
					menuItem.on("mousedown", contextNoop);
					menuItem.textContent = menuItem.readAttribute("label");
					menuItem.writeAttribute("label", false);
				} else
					menuItem.remove();
			});
			
			contextBackground.appendChild(contextMenuElement);
		} else
			contextMenuElement.show();
		
		activeContextElement = contextElement;
		var scrollOffsets = document.viewport.getScrollOffsets();
		contextMenuElement.setStyle({"left": (e.pointerX() - scrollOffsets.left) + "px", "top": (e.pointerY() - 10 - scrollOffsets.top) + "px"});
		activeContextMenu = contextMenuElement;
		contextBackground.show();
		e.stop();
	});
}

$$("*[contextmenu]").each(setupContextElement);
