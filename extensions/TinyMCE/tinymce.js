Framework.registerModule("TinyMCE", {

	initialize: function() {
		var script = $(document.createElement("script"));
		script.writeAttribute("src", Framework.baseURL +
							"res:tinymce/tinymce.min.js");
		document.head.appendChild(script);
	}

});

Framework.Components.registerComponent("textarea[code=html]", {

	setup: function(el) {
		tinyMCE.init({
			mode : "exact",
    		theme : "modern",
    		elements: el.identify()
		});
	},
	
	destroy: function(el) {
		tinyMCE.execCommand('mceRemoveControl',false,"textarea#"+el.identify());
	}

}, true);
