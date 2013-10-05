
var activeSearchComponent = false;
Framework.Components.registerComponent("input[type=search]", {
	growTimer: false,
	updateTimer: false,
	searchResults: false,
	slowSearchTimer: undefined,
	activeSearch: "",
	
	focus: function() {
		this.getElement().focus();
	},
	
	updateResults: function(html) {
		console.log("Updating search results");
		
		try {clearTimeout(this.slowSearchTimer);}catch(e){}
		this.slowSearchTimer = undefined;
		
		this.updateResultsRaw(html);
		try {
			var first = this.searchResults.down("a");
			if(first)
				first.addClassName("active");
		} catch(e) {}
	},
	
	updateResultsRaw: function(html) {
		try {
			this.searchResults.innerHTML = html;
		} catch(e) {}
		this.showResults(true);
	},
	
	updateResultsStyle: function() {
		var offset = this.getElement().cumulativeOffset();
		var size = this.getElement().getLayout();
		size = [size.get("border-box-width"),
				size.get("border-box-height")];
		var position = "absolute";
		var el = this.getElement();
		while(el = el.parentNode) {
			if(el == document.body)
				break;
			
			if(el.getStyle("position") == "fixed") {
				position = "fixed";
				break;
			}
		}
		
		try {
			this.searchResults.setStyle({
				"opacity": this.searchResults.currentOpacity,
				"left": offset.left + "px",
				"display": "block",
				"width": size[0] + "px",
				"height": this.searchResults.currentHeight + "px",
				"top": (offset.top + size[1]) + "px",
				"position": position});
			this.searchResults.scrollTop = this.searchResults.scrollHeight;
		} catch(e) {}
	},
	
	showResults: function(remeasure) {
		if(!this.searchResults) {
			this.searchResults = $(document.createElement("div"));
			this.searchResults.addClassName("searchResults");
			this.searchResults.on("focus", this.focus.bind(this));
			this.searchResults.currentOpacity = 0;
			this.searchResults.currentHeight = 0;
			remeasure = true;
			
			document.body.appendChild(this.searchResults);
		}
		if(remeasure === true) {
			this.searchResults.style.height = "";
			this.searchResults.realHeight = this.searchResults.
									measure("border-box-height");
		}
		
		if(this.searchResults.realHeight > 5) {
			if(this.searchResults.currentOpacity < 1) {
				this.searchResults.currentOpacity += (1 -
					this.searchResults.currentOpacity) / 4;
				if(this.searchResults.currentOpacity > 1)
					this.searchResults.currentOpacity = 1;
			}
		} else {
			if(this.searchResults.currentOpacity > 0) {
				this.searchResults.currentOpacity -= 
					this.searchResults.currentOpacity / 7;
				if(this.searchResults.currentOpacity < 0)
					this.searchResults.currentOpacity = 0;
			}
		}
		
		if(this.searchResults.currentHeight < this.searchResults.realHeight) {
			this.searchResults.currentHeight += (this.searchResults.realHeight -
												this.searchResults.currentHeight + 0.7)/4;
			if(this.searchResults.currentHeight > this.searchResults.realHeight)
				this.searchResults.currentHeight = this.searchResults.realHeight;
		} else if(this.searchResults.currentHeight > this.searchResults.realHeight) {
			this.searchResults.currentHeight += (this.searchResults.realHeight -
												this.searchResults.currentHeight - 0.7)/4;
			if(this.searchResults.currentHeight < this.searchResults.realHeight)
				this.searchResults.currentHeight = this.searchResults.realHeight;
		}
		
		this.updateResultsStyle();
		
		try{clearTimeout(this.growTimer);}catch(e){}
		if(this.searchResults.currentHeight != this.searchResults.realHeight)
			this.growTimer = setTimeout(this.showResults.bind(this), 20);
	},
	
	hideResults: function(now) {
		var thisComponent = this;
		try{clearTimeout(this.growTimer);}catch(e){}
		if(now !== true) {
			this.growTimer = setTimeout(function() {
				thisComponent.hideResults(true);
			}, 150);
			return;
		}
	
		if(this.searchResults.currentOpacity > 0) {
			this.searchResults.currentOpacity -= 
				this.searchResults.currentOpacity / 7;
			if(this.searchResults.currentOpacity < 0)
				this.searchResults.currentOpacity = 0;
		}
		if(this.searchResults.currentHeight > 0) {
			this.searchResults.currentHeight -= (
				this.searchResults.currentHeight+0.7)/4;
			if(this.searchResults.currentHeight < 0)
				this.searchResults.currentHeight = 0;
		}
		this.updateResultsStyle();
		
		if(this.searchResults.currentHeight > 0)
			this.growTimer = setTimeout(function() {
				thisComponent.hideResults(true);
			}, 20);
		else
			this.searchResults.style.display = "none";
	},
	
	update: function() {
		activeSearchComponent = this;
		Framework.API.request("search-core", this.activeSearch);
	},
	
	scheduleUpdate: function() {
		var value = this.getValue().trim().replace(/\s+/g, " ");
		if(this.activeSearch == value)
			return;
		try{clearTimeout(this.updateTimer);}catch(e){}
		this.activeSearch = value;
	
		if(!value) {
			this.updateResults("");
			this.activeSearch = value;
			return;
		}
		
		console.log("Scheduling Search Update");
		this.updateTimer = setTimeout(this.update.bind(this), 200);
		if(this.slowSearchTimer === undefined) {
			var thisComponent = this;
			try {clearTimeout(this.slowSearchTimer);}catch(e) {}
			this.slowSearchTimer = setTimeout(function() {
				thisComponent.updateResultsRaw("<p>Searching...</p>");
			}, 400);
			console.log("Scheduling slow search message", this.slowSearchTimer);
		}
	},
	
	keyHandler: function(e) {
		console.log(e.keyCode);
		
		switch(e.keyCode) {
			case 13: // Enter
				var cur = this.searchResults.down("a.active");
				if(!cur)
					return;
				cur.simulate("click");
				break;
			
			case 38: // Up
				var cur = this.searchResults.down("a.active");
				if(cur) {
					cur.removeClassName("active");
					cur = cur.previous("a");
					if(!cur) {
						cur = this.searchResults.lastChild;
						if(cur.nodeName.toLowerCase() != "a")
							cur = cur.previous("a");
					}
					cur.addClassName("active");
				}
				break;
			
			case 40: // Down
				var cur = this.searchResults.down("a.active");
				if(cur) {
					cur.removeClassName("active");
					cur = cur.next("a");
					if(!cur)
						cur = this.searchResults.down("a");
					cur.addClassName("active");
				}
				break;
			
			default:
				return;
		}
		
		e.stop();
	},
	
	hook: function(el) {
		el.on("keydown", this.keyHandler.bind(this));
		el.on("input", this.scheduleUpdate.bind(this));
		el.on("propertychange", this.scheduleUpdate.bind(this));
		el.on("speechchange", this.scheduleUpdate.bind(this));
		el.on("speechend", this.scheduleUpdate.bind(this));
		el.on("afterpaste", this.scheduleUpdate.bind(this));
		el.on("paste", this.scheduleUpdate.bind(this));
		el.on("focus", this.showResults.bind(this));
		el.on("blur", this.hideResults.bind(this));
		
		Event.on(window, "resize", this.updateResultsStyle.bind(this));
		Event.on(window, "scroll", this.updateResultsStyle.bind(this));
	},

	setup: function(el){
		this.scheduleUpdate();
	},
	
	destroy: function(el){
		console.log("Destroying search box");
	}
}, true);

Framework.API.registerHandler("search-core", function(data) {
	if(!activeSearchComponent)
		return;
		
	var html = "";
	if("results" in data && data.results)
		$H(data.results).each(function(sectionPair) {
			var tmpl = data.templates[sectionPair.key];
			
			sectionPair.value.each(function(result) {
				Object.extend(result, {"small": "<small>", "endsmall": "</small>",
									"strong": "<strong>", "endstrong": "</strong>",
									"italic": "<i>", "enditalic": "</i>",
									"bold": "<b>", "endbold": "</b>"});
				html += "<a href='" + result.url + "'>" +
						tmpl.interpolate(result).replace(/\n/g, "<br />") + "</a>";
			});
		});
	else
		html += "<p>No results</p>";
	activeSearchComponent.updateResults(html);
});
