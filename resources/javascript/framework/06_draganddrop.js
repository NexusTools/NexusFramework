Framework.registerModule("DragAndDrop", {

		dropTarget: false,
		dragElement: false,
		
		initialize: function() {
			Event.on(window, "blur", this.cancelActive);
			Event.on(document.body, "mousemove", this.dragMove);
			Event.on(document.body, "mouseleave", this.cancelActive);
			Event.on(document.body, "mouseup", this.dropAction);
		},
		
		startDragging: function(display, data) {
			Framework.DragAndDrop.dragElement = $(document.createElement("div"));
			Framework.DragAndDrop.dragElement.setStyle({
				"position": "absolute",
				"opacity": 0.8
					});
			Framework.DragAndDrop.innerHTML = display;
			document.body.appendChild(Framework.DragAndDrop.dragElement);
		},
		
		cancelActive: function(e) {
			/*if(Framework.DragAndDrop.dragElement) {
				Framework.DragAndDrop.dragElement.remove();
				Framework.DragAndDrop.dragElement = false;
			}*/
		},
		
		dropAction: function(e) {
			Framework.DragAndDrop.dragMove(e);
		},
		
		dragMove: function(e) {
			
		}
		
	});


