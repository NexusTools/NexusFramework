Framework.registerModule("PageSystem", {
		validProtocol: /[a-z]+:.+/i,
		activeScripts: {},
		
		PageScript: Class.create({
			source: "",
			id: ""
		}),
		
		PageScriptHelper: Class.create({
			registeredWidgets: [],
		
			registerWidget: function(tag, handler) {
				this.registeredWidgets.push(tag);
			}
		}),
		
		setTitle: function(title){
			document.title = Framework.Config.TITLE_FORMAT.interpolate({PAGENAME: title});
		},
		
		UpdateDynamicURL: function(link){
			var page = null;
			if(!link.hasAttribute("extern") && link.hasAttribute("href")){
				var page = link.getAttribute("href");
				page = page.startsWith(Framework.baseURL) ? page.substring(Framework.baseURL.length) : (!Framework.PageSystem.validProtocol.test(page) ? page : false);
				if(page && page.startsWith("/"))
					page = page.substring(1);
			}
			
			link.writeAttribute("page", page);
		},
		
		PopState: function(event) {
			if(!event.state)
				return;
		
			if(event.state.load)
				Framework.PageSystem.LoadPage(event.state.load, event.state.postData);
			else
				Framework.PageSystem.PageCallback(event.state);
		},
	
		PageReady: function(){
			document.body.style.cursor = "";
			
			setTimeout(function() {
				if(document.viewport.getScrollOffsets().top != 0)
					return; // Already scrolled down a bit
			
				$$("column.pagearea form").each(function(form) {
					if(form.hasAttribute("nofocus"))
						return; // Continue
				
					form.select("input, select, textarea").each(function(element) {
						if(element.hasAttribute("disabled"))
							return; // Continue
						var type = element.readAttribute("type");
						if(type && element.nodeName.toLowerCase() == "input")
							switch(type.toLowerCase()) {
								case "text":
								case "number":
								case "date":
								case "datetime":
								case "datetime-local":
								case "month":
								case "time":
								case "url":
								case "week":
								case "range":
								case "color":
								case "password":
								case "tel":
									break; // Supported Types
									
								default:
									return; // Skip
							}
						
						element.focus();
						throw $break;
					});
					throw $break;
				});
			}, 150);
		},
		
		PageUnloaded: function(e){
			document.body.style.cursor = "progress";
			Framework.PageElement.update("<working>Loading...</working>");
		},
		
		LinkCallback: function(e){
			var link = e.findElement("a");
			
			if(!link || !link.hasAttribute("page"))
				return;
				
			var page = link.getAttribute("page");
			if(page == Framework.activePage) {
				e.stop();
				return;
			}
			Framework.activePage = page;
			
			Framework.PageSystem.loadPage(page);
			e.stop();
		},
		
		loadPage: function(page, postData){
			Framework.PageElement.fire("pagesys:unloading");
			Framework.PageSystem.setTitle("Loading");
			
			if(Framework.PageSystem.redirectToMain)
				location.href = Framework.baseURL + "#" + e.element().getAttribute("page");
			else {
				Framework.API.request("page", page, postData);
				if(history.pushState)
					history.pushState({load: page}, "Loading", Framework.baseURI + page);
				else
					location.hash = page;
			}
			
			Framework.PageElement.fire("pagesys:unloaded");
		},
	
		PageCallback: function(data){
			
			Framework.PageElement.fire("pagesys:data", data);
			
			Framework.PageSystem.setTitle(data.title);
			Framework.PageElement.update(data.html);
			
			Framework.PageElement.fire("pagesys:loaded", data);
			Framework.PageElement.fire("pagesys:ready", data);
			
			if(history.replaceState && !data.page) {
				data.page = Framework.activePage;
				history.replaceState(data, data.title, Framework.baseURI + Framework.activePage);
			}
		},
		
		registerHandler: function(event, callback){
			Framework.PageElement.observe("pagesys:" + event, callback);
		},
		
		unregisterHandler: function(event, callback){
			Framework.PageElement.stopObserving("pagesys:" + event, callback);
		},
		
		initialize: function(){
			Framework.API.registerHandler("page", this.PageCallback);
			Framework.PageElement.observe("pagesys:ready", this.PageReady);
			Framework.PageElement.observe("pagesys:unloaded", this.PageUnloaded);
			this.redirectToMain = !history.pushState && Framework.activePage.length > 1;
			
			/*Framework.Components.registerComponent("a[href]", {
				
				setup: function(link) {
					Framework.PageSystem.UpdateDynamicURL(link);
					link.observe("dom:attrmodified[href]", Framework.PageSystem.UpdateDynamicURL);
					link.observe("click", Framework.PageSystem.LinkCallback);
				},
				
				destroy: function() {
					link.stopObserving("dom:attrmodified[href]", Framework.PageSystem.UpdateDynamicURL);
					link.stopObserving("click", Framework.PageSystem.LinkCallback);
				}
				
			});*/
			
			if(history.replaceState){
				var data = {title: document.title,
					page: Framework.activePage,
					html: Framework.PageElement.innerHTML};
				history.replaceState(data, data.title, Framework.baseURI + data.page);
			}
			
		},
		
		loaded: function(){
			Framework.PageElement.fire("pagesys:loaded");
			Framework.PageElement.fire("pagesys:ready");
			
			Framework.PageElement.observe("pagesys:loaded", function() {
				Framework.Components.setupContainer(Framework.PageElement);
			});
		}
		
	}, ["API", "Components"]);
