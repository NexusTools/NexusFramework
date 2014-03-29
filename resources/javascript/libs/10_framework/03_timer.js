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
		

		this.AccurateTimer = Class.create({
			initialize: function(callback, frequency, autostart) {
				this.callback = callback;
				this.frequency = frequency*1 || 20; // Default 50fps
				
				if(autostart)
					this.start();
			},

			start: function(){
				if (this.timer !== undefined) return;
				console.log("Starting Timer", this);
				this.nextTimeout = Framework.Timers.now();
				this.onTimerEvent();
			},

			execute: function() {
				return this.callback(this);
			},

			stop: function() {
				if (this.timer === undefined) return;
				console.log("Stopping Timer", this);
				try{clearTimeout(this.timer);}catch(e){}
				this.timer = undefined;
			},

			onTimerEvent: function() {
				try{clearTimeout(this.timer);}catch(e){}
				this.timer = true;
				
				try {
					var timeout;
					while((timeout = (this.nextTimeout - Framework.Timers.now())) <= 0) {
						this.execute();
						if(this.timer === undefined)
							throw "Stopped While Executing";
						this.nextTimeout += this.frequency;
					}
					this.timer = setTimeout(this.onTimerEvent.bind(this), timeout);
					return;
				} catch(e) {
					console.log(this, "" + e);
					console.log(e.stack);
				}
				this.timer = undefined;
			}
		});

		this.EventQueue = Class.create(this.AccurateTimer, {
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
						
						"stop": function() {
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
		
		this.SystemQueue = new this.EventQueue();
	}

});
