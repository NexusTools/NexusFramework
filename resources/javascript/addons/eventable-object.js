var FawkEvent = Class.create({
	initialize: function(data){
		this.stopped = false;
		if(data)
			Object.extend(this, data);
	},
	stop: function(){
		this.stopped = true;
	}
});
var EventableObject = Class.create({
	observe: function(event, callback){
		if(!this.eventHandlers)
			this.eventHandlers = {};
		if(!this.eventHandlers[event])
			this.eventHandlers[event] = $A();
		this.eventHandlers[event].push(callback);
	},
	trigger: function(event, eventObject){
		if(!this.eventHandlers || !this.eventHandlers[event])
			return;
			
		var eventObject = new FawkEvent(eventObject);
		this.eventHandlers[event].each(function(handler){
			if(eventObject.stopped)
				return;
			handler(eventObject);
		});
	}
});
