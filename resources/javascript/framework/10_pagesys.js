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
			var page = false;
			if(link.hasAttribute("href")){
				var page = link.getAttribute("href");
				page = page.startsWith(Framework.baseURL) ? page.substring(Framework.baseURL.length) :
						(!Framework.PageSystem.validProtocol.test(page) ? page : false);
				if(page && page.startsWith("/"))
					page = page.substring(1);
			}
			
			if(!page) {
				if(link.hasAttribute("page"))
					link.removeAttribute("page");
			} else
				link.setAttribute("page", page);
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
		
			if(false){
				var links = Prototype.Selector.select("a[href]");
				for(var i=0; i<links.length; i++){
					var link = links[i];
			
					if(link.hasAttribute("fw_processed") || link.hasAttribute("extern"))
						continue;
				
					Framework.PageSystem.UpdateDynamicURL(link);
					link.observe("DOMAttrModified", Framework.PageSystem.UpdateDynamicURL);
					link.observe("click", Framework.PageSystem.LinkCallback);
					link.setAttribute("fw_processed", true);
				}
			}
			
			var form = $$("column.pagearea contents form")[0];
			if(form)
				form.select("input")[0].focus();
		},
		
		PageUnloaded: function(e){
			document.body.style.cursor = "progress";
			Framework.PageElement.innerHTML = "<h1>Loading Page...</h1>";
		},
		
		LinkCallback: function(e){
			return;
		
			var link = e.element();
			while(link != null && link.tagName != "A")
				link = link.parentNode;
		
			if(!link.hasAttribute("page"))
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
			Framework.PageElement.innerHTML = data.html;
			
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
		
		init: function(){
			Framework.API.registerHandler("page", Framework.PageSystem.PageCallback);
			Framework.PageElement.observe("pagesys:ready", Framework.PageSystem.PageReady);
			Framework.PageElement.observe("pagesys:unloaded", Framework.PageSystem.PageUnloaded);
			Framework.PageSystem.redirectToMain = !history.pushState && Framework.activePage.length > 1;
		
			if(history.replaceState){
				var data = {title: document.title,
					page: Framework.activePage,
					html: Framework.PageElement.innerHTML};
				history.replaceState(data, data.title, Framework.baseURI + data.page);
			}
			Framework.PageElement.observe("pagesys:ready", Framework.Components.setupContainer);
		
			// Initialize Framework Addons
			Framework.PageElement.fire("pagesys:loaded");
			Framework.PageElement.fire("pagesys:ready");
		}
		
	}, ["API", "Components"]);
