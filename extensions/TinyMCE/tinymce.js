Framework.registerModule("TinyMCE", {

	loaded: false,
	pendingInstances: [],

	initialize: function() {
		console.log("Loading TinyMCE Script");
		var script = $(document.createElement("script"));
		script.writeAttribute("src", Framework.baseURL +
							"res:tinymce/tinymce.min.js");
		script.writeAttribute("type", "text/javascript");
		script.writeAttribute("async");
		document.body.appendChild(script);
		
		script.on("load", function() {
			console.log("TinyMCE Loaded", Framework.TinyMCE.pendingInstances);
			Framework.TinyMCE.pendingInstances.each(function(el) {
				el.initMCE();
			});
			Framework.TinyMCE.pendingInstances = undefined;
			delete Framework.TinyMCE.pendingInstances;
			Framework.TinyMCE.loaded = true;
		});
	}

});

Framework.Components.registerComponent("textarea[code=html]", {

	setup: function(el) {
		if(!Framework.TinyMCE.loaded) {
			Framework.TinyMCE.pendingInstances.push(this);
			return;
		}
		
		this.initMCE();
	},
	
	initMCE: function() {
		var el = this.getElement();
		console.log("Initializing TinyMCE Instance", el);
		
		this.options = {
			mode: "exact",
            theme: "modern",
			elements: el.identify(),
			plugins: this.plugins
		};
		
		if(el.hasAttribute("plugins"))
			this.options.plugins = el.readAttribute("plugins");
		else
			this.options.plugins = "advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table contextmenu directionality emoticons template paste textcolor";
		
		if(!el.hasAttribute("paragraphs")) {
			this.options.forced_root_block = false;
		    this.options.force_br_newlines = true;
		    this.options.force_p_newlines = false;
		}
		
		tinyMCE.init(this.options);
	},
	
	destroy: function(el) {
		tinyMCE.execCommand('mceRemoveControl', false, el.identify());
	}

}, true);
