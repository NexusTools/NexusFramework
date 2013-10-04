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
		this.destroyed = false;
		
		if(!Framework.TinyMCE.loaded) {
			Framework.TinyMCE.pendingInstances.push(this);
			return;
		}
		
		this.initMCE();
	},
	
	getOptions: function() {
		return this.options;
	},
	
	getOption: function(key) {
		return this.option[key];
	},
	
	initMCE: function() {
		var el = this.getElement();
		console.log("Initializing TinyMCE Instance", el);
		
		var thisComponent = this;
		this.options = {
			mode: "exact",
			elements: el.identify(),
			plugins: this.plugins,
			init_instance_callback: function(inst) {
				console.log("TinyMCE Instance Initialized");
				thisComponent.tinyMCE = inst || tinyMCE.get(el.identify());
				if(thisComponent.destroyed) {
					thisComponent.tinyMCE.destroy();
					thisComponent.tinyMCE = undefined;
				}
			}
		};
		
		if(el.hasAttribute("plugins"))
			this.options.plugins = el.readAttribute("plugins");
		else
			this.options.plugins = "advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table contextmenu directionality emoticons template paste textcolor";
		
		if(!el.hasAttribute("paragraph")) {
			this.options.forced_root_block = false;
		    this.options.force_br_newlines = true;
		    this.options.force_p_newlines = false;
		}
		
		if(el.hasAttribute("menubar"))
			this.options.menubar = el.readAttribute("menubar");
		else if(el.hasAttribute("nomenubar"))
			this.options.menubar = false;
		
		if(el.hasAttribute("theme"))
			this.options.theme = el.readAttribute("theme");
		else
			this.options.theme = "modern";
		
		tinyMCE.init(this.options);
	},
	
	destroy: function(el) {
		this.destroyed = true;
		
		if(!this.tinyMCE)
			return;
	
		console.log("Removing TinyMCE Instance");
		this.getElement().value = this.tinyMCE.getContent();
		this.tinyMCE.remove();
		
		this.tinyMCE = undefined;
	}

}, true);
