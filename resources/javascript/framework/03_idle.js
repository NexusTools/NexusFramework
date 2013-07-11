Framework.registerModule("Idle", {

	isIdle: false,
	idleTimeout: false,
	
	initialize: function() {
		console.log(this);
		Event.on(window, "blur", this.goIdle);
		Event.on(window, "focus", this.interaction);
		Event.on(document.body, "mousemove", this.interaction);
		Event.on(document.body, "keydown", this.interaction);
		Event.on(document.body, "mouseleave", this.goIdle);
	
		this.idleTimeout = setTimeout(this.interaction, 50);
	},
	
	interaction: function() {
		try{clearTimeout(Framework.Idle.goIdle);}catch(e){}
		Framework.Idle.idleTimeout = setTimeout(Framework.Idle.goIdle, 60000);
		if(Framework.Idle.isIdle) {
			console.log("User is alive.");
			Framework.Idle.isIdle = false;
		}
	},
	
	goIdle: function() {
		try{clearTimeout(Framework.Idle.goIdle);}catch(e){}
		if(!Framework.Idle.isIdle){
			console.log("User is asleep.");
			Framework.Idle.isIdle = true;
		}
		
	}


});
