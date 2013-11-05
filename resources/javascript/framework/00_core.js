var Framework = {

	moduleStasis: [],
	
	getQuery: function(key) {
		var matches = location.href.match(new RegExp("(\\?|&)" +key+ "=([^&#]+?)(&|$)", "i"));
		return matches && matches[2] || null;
	},

	registerModule: function(name, structure, reqs) {
		if(name in Framework)
			throw "Module `" + name + "` Already Registered";
			
		console.log("Loading Module `"+ name +"`");
		var depsUnmet = [];
		if(reqs instanceof Array)
			$A(reqs).each(function(reqModule){
				if(!(reqModule in Framework))
					depsUnmet.push(reqModule);
			});
			
		if(depsUnmet.length) {
			console.log(depsUnmet.length + " Unmet Dependencies");
			Framework.moduleStasis.push(arguments);
			return;
		}
		try {
			var impl = Class.create(name, structure);
			Framework[name] = new impl();
			
			if(Framework[name].loaded)
				Event.on(window, "load", Framework[name].loaded.bind(Framework[name]));
			
			console.log("Module `" + name + "` Ready");
			
			while(Framework.moduleStasis.length)
				Framework.registerModule.apply(this, Framework.moduleStasis.shift());
		}catch(e) {
			throw "Error Loading Module `" + name + "`\n" + e.toString();
			delete Framework[name];
		}
	}
	
};

Framework.registerModule("Core", {
	initialize: function() {
		Framework.baseURL = $$("head base")[0].getAttribute("href");
		Framework.baseURI = Framework.baseURL.substring(Framework.baseURL.indexOf('/', Framework.baseURL.indexOf("://") + 3));
		Framework.activePage = location.href.substring(Framework.baseURL.length);
		
		try {
			Framework.Config = document.getElementsByTagName("framework:config")[0].innerHTML;
			Framework.Config = Framework.Config.substring(5, Framework.Config.length - 4);
			Framework.Config = eval(Framework.Config);
			if(!Framework.Config.TITLE_FORMAT || !Framework.Config.DEFAULT_PAGE_NAME || !Framework.Config.FRAMEWORK_VERSION)
				throw "Missing Required Data";
				
			console.log("NexusFramework " + Framework.Config.FRAMEWORK_VERSION);
		} catch(e) {
			throw "Error Parsing Configuration";
		}
		
		if(Framework.Config.LEGACY_BROWSER) {
			Framework.ThemeElement = $$("div.framework_theme")[0];
			Framework.ThemeElement = $$("div.framework_page")[0];
		} else {
			Framework.ThemeElement = $(document.getElementsByTagName("framework:theme")[0]);
			Framework.PageElement = $(document.getElementsByTagName("framework:page")[0]);
		}
		
		delete Framework.init;
	}
});
