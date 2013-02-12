var Framework = {
	init: {
		Framework.ThemeElement = document.getElementsByTagName("framework:theme")[0];
		Framework.PageElement = document.getElementsByTagName("framework:page")[0];
	},
	
	API: {
		callbacks: {},
		requests: {},
		
		setHandler: function(module, callback){
			Framework.API.callbacks[module] = callback;
		},
		
		request: function(module, args){
		},
		
		makeRequests: function(){
		}
	}
};

Framework.init();
