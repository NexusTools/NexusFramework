Framework.registerModule("Timers", {

	initialize: function(){

		this.AccurateTimer = Class.create({
			initialize: function(callback, frequency, autostart) {
				this.callback = callback;
				this.frequency = frequency*1 || 20; // Default 50fps
				
				if(autostart)
					this.start();
			},

			start: function(){
				console.log("Starting Timer", this);
				this.nextTimeout = (new Date()).getTime();
				this.onTimerEvent();
			},

			execute: function(step) {
				return this.callback(step, this);
			},

			stop: function() {
				if (!this.timer) return;
				console.log("Stopping Timer", this);
				clearTimeout(this.timer);
				this.timer = undefined;
			},

			onTimerEvent: function() {
				try{clearTimeout(this.timer);}catch(e){}
				try {
					var timeout;
					while((timeout = (this.nextTimeout - (new Date()).getTime())) <= 0) {
						this.nextTimeout += this.frequency;
						this.execute();
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
				$super(null, frequency, true);
			},

			execute: function(step) {
				var activeQueue = this.queue;
				this.queue = [];

				while(activeQueue.length > 0){
				var event = activeQueue.shift();
				try{
					if(event(step) !== false)
					this.queue.push(event);
				}catch(e){}
				}
			},

			queue: [],
			addCallback: function(callback){
				if(this.contains(callback))
					return;
				this.queue.push(callback);
			},
		
			removeCallback: function(callback){
				var pos = this.indexOf(callback);
				if(pos > -1)
					this.queue.splice(pos, 1);
			},
		
			indexOf: function(callback){
				return this.queue.indexOf(callback);
			},
		
			contains: function(callback){
				return this.indexOf(callback) != -1;
			}

		});
		
		this.SystemQueue = new this.EventQueue();
	}

});
