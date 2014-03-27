Framework.registerModule("PrototypeIntegration", {

	initialize: function() {
		var prototypeShowMethod = Element.show;
		var prototypeUpdateMethod = Element.update;
		var prototypeVisibleMethod = Element.visible;
		var prototypeSetOpacityMethod = Element.setOpacity;
		var prototypeWriteAttributeMethod = Element.writeAttribute;
		var prototypeAddClassNameAttributeMethod = Element.addClassName;
		var prototypeRemoveClassNameAttributeMethod = Element.removeClassName;
		var setContentMethod = function(element, content, dontProcess) {
			if(!dontProcess)
				Framework.Components.destroyContainer(element);
			var ret = prototypeUpdateMethod.apply(this, arguments);
			if(!dontProcess)
				Framework.Components.setupContainer(element);
			return ret;
		}
		Element.addMethods({
			"update": setContentMethod,
			"setContent": setContentMethod,
			"setTextContent": function(element, text) {
				Framework.Components.destroyContainer(element);
				var ret = prototypeUpdateMethod.apply(this, [element, ""]);
				if("textContent" in element)
					element.textContent = text;
				else if("innerText" in element)
					element.innerText = text;
				else {
					var txtArea = document.createElement("textarea");
					txtArea.value = text;
					
					element.innerHTML = txtArea.innerHTML;
				}
				return ret;
			},
			"getTextContent": function(element) {
				if("textContent" in element)
					return element.textContent;
				else if("innerText" in element)
					return element.innerText;
				
				return false;
			},
			"writeAttribute": function(element) {
				prototypeWriteAttributeMethod.apply(this, arguments);
				if(Framework.Components.isProcessing)
					return;
				
				if(Element.visible(element))
					Framework.Components.redetect(element);
			},
			/*"addClassName": function(element, name) {
				//if(Element.hasClassName(element, name))
				//	return;
			
				prototypeAddClassNameAttributeMethod.apply(this, element, name);
				if(Framework.Components.isProcessing)
					return;
				
				if(Element.visible(element))
					Framework.Components.redetect(element);
			},
			"removeClassName": function(element, name) {
				//if(!Element.hasClassName(element, name))
				//	return;
				
				prototypeRemoveClassNameAttributeMethod.apply(this, element, name);
				if(Framework.Components.isProcessing)
					return;
				
				if(Element.visible(element))
					Framework.Components.redetect(element);
			},*/
			"show": function(element) {
				if(!prototypeVisibleMethod(element) &&
					 !Framework.Components.isProcessing)
					Framework.Components.redetect(element);
				prototypeShowMethod(element);
			},
			"setOpacity": function(element, opacity) {
				if(Element.getOpacity(element) == 0 && opacity > 0
						&& !Framework.Components.isProcessing)
					Framework.Components.redetect(element);
				prototypeSetOpacityMethod(element, opacity);
			},
			"pushChild": function(element, child) {
				element.appendChild(child);
				if(Element.visible(element) && !Framework.Components.isProcessing)
					Framework.Components.redetect(child);
			},
			"visible": function(element) {
				return Element.isDisplayVisible(element)
					&& Element.isOpacityVisible(element)
					&& Element.isOnPage(element);
			},
			"isDisplayVisible": function(element) {
				return prototypeVisibleMethod(element);
			},
			"isOpacityVisible": function(element) {
				return Element.getOpacity(element) > 0;
			},
			"isOnPage": function(element) {
				while(element) {
					if(element == document.body)
						return true;
					element = element.parentNode;
				}
				return false;
			}
		});
	}

});
