Framework.registerModule("Timers", {

	init: function(){

		this.AccurateTimer = Class.create({
			initialize: function(callback, frequency) {
				this.callback = callback;
				this.frequency = frequency;

				this.start();
			},

			calculateSlowness: function() {
				return ((this.nextTimeout - (new Date().getTime()))
						+ this.frequency) / this.frequency;
			},

			start: function(){
				this.nextTimeout = (new Date().getTime()) + this.frequency;
				this.registerCallback();
			},

			registerCallback: function() {
				var timeout = this.frequency/this.calculateSlowness();
				if(timeout < 10)
					timeout = 10;

				this.timer = setTimeout(this.onTimerEvent.bind(this), timeout);

				this.nextTimeout = (new Date().getTime()) + this.frequency;
			},

			execute: function(step) {
				return this.callback(step, this);
			},

			stop: function() {
				if (!this.timer) return;
				clearTimeout(this.timer);
				this.timer = null;
			},

			onTimerEvent: function() {
				if (!this.currentlyExecuting) {
					try {
						this.currentlyExecuting = true;

						this.calculateSlowness();
						this.execute(this.calculateSlowness());
						this.registerCallback();

						this.currentlyExecuting = false;
						return;
					} catch(e) {
						this.currentlyExecuting = false;
						throw e;
					}
				}

				this.timer = false;
			}
		}),

		this.EventQueue = Class.create(this.AccurateTimer, {
			initialize: function($super, frequency) {
				$super(null, frequency);
				this.frequency = frequency;

				this.registerCallback();
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

		})
	}

});
