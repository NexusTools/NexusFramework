Framework.registerModule("PrototypeIntegration", {

	initialize: function() {
		var prototypeUpdateMethod = Element.update;
		var setContentMethod = function(element, content) {
			Framework.Components.destroyContainer(element);
			var ret = prototypeUpdateMethod.apply(this, arguments);
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
			}
		});
	}

});
