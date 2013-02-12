var Framework = {
	init: function() {
		console.log("NexusFramework Init Script");
	
		Framework.baseURL = $$("head base")[0].getAttribute("href");
		Framework.baseURI = Framework.baseURL.substring(Framework.baseURL.indexOf('/', Framework.baseURL.indexOf("://") + 3));
		Framework.activePage = location.href.substring(Framework.baseURL.length);
		
		try {
			Framework.Config = document.getElementsByTagName("framework:config")[0].innerHTML;
			Framework.Config = Framework.Config.substring(5, Framework.Config.length - 4);
			Framework.Config = eval(Framework.Config);
			if(!Framework.Config.TITLE_FORMAT || !Framework.Config.DEFAULT_PAGE_NAME)
				throw "Missing Required Data";
		} catch(e) {
			console.log("Error Parsing Configuration");
			console.dir(e);
			Framework.Config = {TITLE_FORMAT: "{{PAGENAME}} | Internal Error",
								DEFAULT_PAGE_NAME: "Internal Error"};
		}
		Framework.ThemeElement = $(document.getElementsByTagName("framework:theme")[0]);
		Framework.PageElement = $(document.getElementsByTagName("framework:page")[0]);
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
		//Event.observe(window, "popstate", Framework.PageSystem.PopState);
		Framework.PageElement.observe("pagesys:ready", Framework.UI.runExtensions);
		
		// Initialize Framework Addons
		Framework.PageElement.fire("pagesys:loaded");
		Framework.PageElement.fire("pagesys:ready");
		console.log("NexusFramework Initialized");
	},
	
	UI: {
		extensions: new Array(),
		
		runExtensions: function(){
			Framework.UI.extensions.each(function(e){
				Framework.PageElement.select(e.selector).each(function(el){
					e.callback(el);
				});
			});
		},
	
		registerExtension: function(selector, callback){
			Framework.UI.extensions.push({"selector": selector, "callback": callback});
		},
		
		unregisterExtension: function(selector, callback){
			var part = Framework.UI.extensions.reject(function(e){
				return e.selector == selector && e.callback == callback;
			});
		}
	},
	
	PageSystem: {
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
			
			Prototype.Selector.select("PageScript[hash]").each(function(e){
				console.log(e.getAttribute("hash"));
			});
			
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
		}
	},
	
	API: {
		requestTimeout: null,
		minimumNextRequest: 0,
		currentRequests: [],
		callbacks: {},
		requests: {},
		
		registerHandler: function(module, callback){
			Framework.API.callbacks[module] = callback;
		},
		
		unregisterHandler: function(module){
			Framework.API.callbacks[module] = null;
		},
		
		request: function(module, data, postVars){
			if(!Framework.API.callbacks[module])
				throw "Missing Handler for " + module;
				
			if(!data)
				data = "";
			
			Framework.API.requests[module] = {"uri": encodeURIComponent(data), "postVars": postVars};
			Framework.API.queueRequests();
		},
		
		resetTimer: function(){
			Framework.API.minimumNextRequest = (new Date().getTime() + 750);
		},
		
		queueRequests: function(){
			if(Framework.API.requestTimeout != null)
				return;
		
			var callWait = Framework.API.minimumNextRequest - (new Date().getTime());
			if(callWait < 5)
				callWait = 5;
			
			if(callWait > 750)
				callWait = 750;
			
			console.log("Scheduled next API call in " + callWait + "ms");
			Framework.API.requestTimeout = setTimeout(Framework.API.makeRequests, callWait);
		},
		
		makeRequests: function(){
			var requestURL = Framework.baseURL + "?api";
			var postData = {};
			
			for(var req in Framework.API.requests){
				var data = Framework.API.requests[req];
				requestURL += "&" + encodeURIComponent(req);
				if(data['uri'] != null)
					requestURL += "=" + data['uri'];
					
				if(data['postVars'] != null)
					postData[req] = Object.toQueryString(data['postVars']);
			}
			
			Framework.API.currentRequests = [];
			for(var module in Framework.API.requests)
				Framework.API.currentRequests.push(module);
			Framework.API.requests = {};
			
			var transport = Ajax.getTransport();
			if(Object.keys(postData).length > 0){
				postData = Object.toQueryString(postData);
			
				transport.open("POST", requestURL, true);
				transport.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				transport.setRequestHeader("Content-length", postData.length);
				transport.setRequestHeader("Connection", "close");
			} else {
				transport.open("GET", requestURL, false);
				postData = null;
			}
			
			transport.onreadystatechange = Framework.API.TransportCallback;
			transport.send(postData);
		},
		
		TransportCallback: function(e){
			if(e.target.readyState == 4){
				Framework.API.resetTimer();
				Framework.API.requestTimeout = null;
				if(Framework.API.requests.length > 0)
					Framework.API.queueRequests();
					
				var responseData = {"error": "Missing from response..."};
				try {
					if(e.target.status == 0)
						e.target.status = 404;
					if(e.target.status == 200) {
						try {
							responseData = e.target.responseText.evalJSON();
						} catch(e) {
							throw "Response Corrupt";
						}
						if(!(responseData instanceof Object))
							throw "Response Corrupt";
							
					} else
						throw "Server returned error code " + e.target.status;
				} catch(e) {
					if(responseData instanceof Array)
						responseData['error'] = e.toString();
					else
						responseData = {"error": e.toString()}
				}
				
				$A(Framework.API.currentRequests).each(function(module){
						if(!responseData[module])
							responseData[module] = {"error": responseData['error']};
					});
					
				console.log(responseData);
				
				for(module in responseData){
					try {
						if(Framework.API.callbacks[module] &&
								!Framework.API.requests[module])
							Framework.API.callbacks[module](responseData[module]);
					} catch(e) {}
				}
			}
		}
	}
};

Framework.init();
