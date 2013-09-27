Framework.registerModule("API", {
		minimumNextRequest: 0,
		requestInterval: null,
		requestTimeout: null,
		intervalRequests: {},
		currentRequests: [],
		callbacks: {},
		requests: {},
		
		initialize: function() {
			this.minTimeout = location.protocol === 'https:' ? 250 : 100;
			this.intervalTime = location.protocol === 'https:' ? 6000 : 3000;
			console.log("Using a " + this.minTimeout + "ms minimum API queue timeout");
			console.log("Using a " + this.intervalTime + "ms API request interval timer");
		},
		
		registerHandler: function(module, callback){
			var oldCallback = Framework.API.callbacks[module];
			Framework.API.callbacks[module] = callback;
			return oldCallback;
		},
		
		unregisterHandler: function(module){
			Framework.API.callbacks[module] = undefined;
		},
		
		registerIntervalRequest: function(module, data, dontReplace) {
			if(!Framework.API.callbacks[module])
				throw "Missing Handler for " + module;
			
			var alreadyExists = module in Framework.API.intervalRequests;
			if(dontReplace && alreadyExists)
				return;
			
			data = {"uri": encodeURIComponent(data ? data : ""), "postVars": null};
			if(!alreadyExists && !(module in Framework.API.requests)) {
				Framework.API.requests[module] = data; // Make the request right away so that the website can populate onload
				Framework.API.queueRequests();
			}
			
			Framework.API.intervalRequests[module] = data;
			if(Framework.API.requestInterval != null)
				return;
			
			console.log("Creating interval timer with " + Framework.API.intervalTime + "ms delay");
			Framework.API.requestInterval = setTimeout(Framework.API.intervalCallback, Framework.API.intervalTime);
		},
		
		intervalCallback: function() {
			clearTimeout(Framework.API.requestInterval);
			if(!("Idle" in Framework) || !Framework.Idle.isIdle) {
				console.log("Making interval requests");
				for(var module in Framework.API.intervalRequests){
					if(module in Framework.API.requests)
						continue; // Don't replace existing data
			
					var data = Framework.API.intervalRequests[module];
					Framework.API.requests[module] = data;
				}
				Framework.API.queueRequests();
			}
			Framework.API.requestInterval = setTimeout(Framework.API.intervalCallback, Framework.API.intervalTime);
		},
		
		request: function(module, data, postVars, dontReplace){
			if(!Framework.API.callbacks[module])
				throw "Missing Handler for " + module;
			
			if(dontReplace && module in Framework.API.requests) {
				console.log("Discarded replacement for", module);
				return;
			}
			
			Framework.API.requests[module] = {"uri": data ? encodeURIComponent(data) : "", "postVars": postVars};
			Framework.API.queueRequests();
		},
		
		resetTimer: function(){
			Framework.API.minimumNextRequest = (new Date().getTime() + Framework.API.minTimeout);
		},
		
		queueRequests: function(){
			if(Framework.API.requestTimeout != null)
				return;
			
			var callWait = Framework.API.minimumNextRequest - (new Date().getTime());
			if(callWait < 5 || !callWait)
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
				transport.open("GET", requestURL, true);
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
