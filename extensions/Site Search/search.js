
var activeSearchComponent = false;
Framework.Components.registerComponent("input[type=search]", {
	growTimer: false,
	updateTimer: false,
	searchResults: false,
	lastSearch: "",
	
	focus: function() {
		this.getElement().focus();
	},
	
	updateResults: function(html) {
		console.log("Updating search results");
	
		this.searchResults.innerHTML = html;
		this.showResults(true);
	},
	
	updateResultsStyle: function() {
		var offset = this.getElement().cumulativeOffset();
		var size = this.getElement().getLayout();
		size = [size.get("border-box-width"),
				size.get("border-box-height")];
				
		this.searchResults.style.display = "block";
		this.searchResults.style.width = size[0] + "px";
		this.searchResults.style.height = this.searchResults.currentHeight + "px";
		this.searchResults.style.top = (offset.top + size[1]) + "px";
		this.searchResults.style.left = offset.left + "px";
		this.searchResults.setStyle({"opacity": this.searchResults.currentOpacity});
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
			this.growTimer = setTimeout(this.showResults.bind(this), 30);
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
			}, 30);
		else
			this.searchResults.style.display = "none";
	},
	
	update: function() {
		console.log("Updating Search Update");
		var value = this.getValue().trim().replace(/\s+/g, " ");
		if(this.lastSearch == value)
			return;
		this.lastSearch = value;
		
		console.log(value);
		if(!value) {
			this.updateResults("");
			return;
		}
	
		activeSearchComponent = this;
		Framework.API.request("search-core", value);
	},
	
	scheduleUpdate: function() {
		var value = this.getValue().trim().replace(/\s+/g, " ");
		if(this.lastSearch == value)
			return;
	
		this.updateResults("<p>Searching...</p>");
		console.log("Scheduling Search Update");
		try{clearTimeout(this.updateTimer);}catch(e){}
		this.updateTimer = setTimeout(this.update.bind(this), 200);
	},

	setup: function(el){
		console.log("Initializing new search box");
		
		el.on("input", this.scheduleUpdate.bind(this));
		el.on("propertychange", this.scheduleUpdate.bind(this));
		el.on("speechchange", this.scheduleUpdate.bind(this));
		el.on("speechend", this.scheduleUpdate.bind(this));
		el.on("afterpaste", this.scheduleUpdate.bind(this));
		el.on("paste", this.scheduleUpdate.bind(this));
		el.on("focus", this.showResults.bind(this));
		el.on("blur", this.hideResults.bind(this));
		
		this.update();
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
