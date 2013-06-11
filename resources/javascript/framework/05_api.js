Framework.registerModule("API", {
		requestTimeout: null,
		minimumNextRequest: 0,
		currentRequests: [],
		callbacks: {},
		requests: {},
		
		initialize: function() {
			this.minTimeout = location.protocol === 'https:' ? 2000 : 750;
			console.log("Using a " + this.minTimeout + "ms minimum API queue timeout");
		},
		
		registerHandler: function(module, callback){
			var oldCallback = Framework.API.callbacks[module];
			Framework.API.callbacks[module] = callback;
			return oldCallback;
		},
		
		unregisterHandler: function(module){
			Framework.API.callbacks[module] = undefined;
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
			Framework.API.minimumNextRequest = (new Date().getTime() + Framework.API.minTimeout);
		},
		
		queueRequests: function(){
			if(Framework.API.requestTimeout != null)
				return;
		
			var callWait = Framework.API.minimumNextRequest - (new Date().getTime());
			if(callWait < 5)
				callWait = 5;
			
			if(callWait > Framework.API.minTimeout)
				callWait = Framework.API.minTimeout;
			
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
				
				for(module in responseData){
					try {
						if(Framework.API.callbacks[module] &&
								!Framework.API.requests[module])
							Framework.API.callbacks[module](responseData[module]);
					} catch(e) {}
				}
			}
		}
	});
