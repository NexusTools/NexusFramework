Framework.registerModule("Timers", {

	initialize: function(){
		if("performance" in window) {
			this.now = performance.now ||
				performance.mozNow ||
				performance.msNow ||
				performance.oNow ||
				performance.webkitNow;
			if(this.now)
				this.now = this.now.bind(performance);
		}
		
		if(!this.now)
			this.now = Date.now.bind(Date);
		
		if(!this.now)
			this.now = function() {
				return (new Date()).getTime();
			};
		
		
		
		this.AccurateTimer = Class.create("AccurateTimer", {
			initialize: function(callback, frequency, autostart) {
				this.callback = callback;
				this.frequency = frequency*1 || 20; // Default 50fps
				
				if(autostart)
					this.start();
			},

			start: function(){
				if (this.timer !== undefined) return;
				//console.log("Starting Timer", this);
				this.nextTimeout = Framework.Timers.now();
				this.onTimerEvent();
			},

			execute: function() {
				return this.callback(this);
			},

			stop: function() {
				if (this.timer === undefined) return;
				//console.log("Stopping Timer", this);
				try{clearTimeout(this.timer);}catch(e){}
				this.timer = undefined;
			},

			onTimerEvent: function() {
				try{clearTimeout(this.timer);}catch(e){}
				this.timer = true;
				
				try {
					var timeout; // assume timer precision sucks
					while((timeout = (this.nextTimeout - Framework.Timers.now())) <= 10) {
						this.execute();
						if(this.timer === undefined)
							throw "Stopped While Executing";
						this.nextTimeout += this.frequency;
					}
					this.timer = setTimeout(this.onTimerEvent.bind(this), timeout);
					return;
				} catch(e) {}
				this.timer = undefined;
			}
		});

		this.EventQueue = Class.create("EventQueue", this.AccurateTimer, {
			initialize: function($super, frequency) {
				$super(null, frequency);
			},

			execute: function() {
				var activeQueue = this.queue;
				this.queue = [];

				while(activeQueue.length > 0){
					var eventInstance = activeQueue.shift();
					try{
						if(eventInstance.callback() !== false)
							this.queue.push(eventInstance);
					}catch(e){}
				}
				
				if(this.queue.length <= 0)
					this.stop();
			},

			queue: [],
			addCallback: function(callback){
				var eventInstance = this.eventForCallback(callback);
				if(!eventInstance) {
					var thisEventQueue = this;
					eventInstance = {
						"callback": callback,
					
						"isQueued": function() {
							return !!thisEventQueue.contains(callback);
						},
						
						"remove": function() {
							thisEventQueue.removeCallback(this.callback);
						}
					};
					this.queue.push(eventInstance);
					this.start();
				}
				return eventInstance;
			},
		
			removeCallback: function(callback){
				var pos = this.indexOf(callback);
				if(pos > -1) {
					this.queue.splice(pos, 1);
					if(this.queue.length <= 0)
						this.stop();
				}
			},
			
			eventForCallback: function(callback) {
				var pos = this.indexOf(callback);
				if(pos > -1)
					return this.queue[pos];
				
				return null;
			},
		
			indexOf: function(callback){
				var index = 0;
				this.queue.each(function(event) {
					if(event.callback == callback)
						return index;
					
					index ++;
				});
				return -1;
				return this.queue.indexOf(callback);
			},
			
			contains: function(callback){
				return this.indexOf(callback) != -1;
			}

		});
		
		this.DormanceTimer = Class.create("DormanceTimer", {
		
			initialize: function(test, callback, opts) {
				this.test = test;
				this.callback = callback;
				
				this.increment = opts.increment || 2;
				this.decrement = opts.decrement || 10;
				this.timeout = opts.timeout || Math.pow(increment, 4);
				this.maxTimeout = opts.maxTimeout || 300+Math.random()*100;
				this.minTimeout = opts.minTimeout || 10;
				
				setTimeout(this.process.bind(this), this.timeout);
			},
			
			process: function() {
				try {
					if(!this.test())
						throw "Test failed";
					
					this.callback();
					this.timeout /= this.decrement;
					this.timeout = Math.max(this.timeout, this.minTimeout);
				} catch(e) {
					if(!Object.isUndefined(e.stack))
						console.log(e.stack);
					
					this.timeout *= this.increment;
					this.timeout = Math.min(this.timeout, this.maxTimeout);
				}
			
				setTimeout(this.process.bind(this), this.timeout);
			}
			
		});
		
		this.DelayTimer = Class.create("DelayTimer", {
		
			initialize: function(actionToggleMode) {
				this.actions = {};
				this.actionToggleMode = actionToggleMode;
				if(this.actionToggleMode)
					this.activatedName = false;
			},
		
			registerAction: function(name, callback, delay) {
				delay = delay || 200;
				
				this.actions[name] = {
					"callback": callback,
					"delay": delay
				}
				
				return {
					"revoke": (function() {
						this.revokeAction(name)
					}).bind(this)
				}
			},
			
			revokeAction: function(name) {
				delete this.actions[name];
			},
			
			invoke: function(name) {
				if(!(name in this.actions))
					throw "`" + name + "` is not a registered action.";
				
				if(this.actionToggleMode)
					this.activatedName = name;
				
				try{clearTimeout(this.activationTimeout);}catch(e){}
				this.actions[name].callback();
			},
			
			activate: function(name, delay) {
				if(!(name in this.actions))
					throw "`" + name + "` is not a registered action.";
				
				if(this.actionToggleMode) {
					if(this.activatedName == name)
						return;
					
					this.activatedName = name;
				}
				
				//console.log("Activating", name);
				var action = this.actions[name];
				try{clearTimeout(this.activationTimeout);}catch(e){}
				this.activationTimeout = setTimeout(action.callback, delay || action.delay);
			}
		
		});
		
		this.SystemQueue = new this.EventQueue();
	}

});
