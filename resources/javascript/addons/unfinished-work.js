var UnfinishedWork = Class.create({
	finished: false,
	initialize: function(message){
		this.message = message.gsub("\n", "\\n").gsub("\"", "\\\"");
		console.log(this.message);
		this.install();
	},
	install: function(){
		if(window.onbeforeunload)
			UnfinishedWork.QUEUE.push(this);
		else
			eval("window.onbeforeunload = function(){\n\treturn \"" + this.message + "\";\n}");
	},
	finish: function(){
		window.onbeforeunload = null;
		this.finished = true;
		
		if(UnfinishedWork.QUEUE.length) {
			var next;
			while(next = UnfinishedWork.QUEUE.shift()) {
				if(next.finished)
					continue;
					
				next.install();
				break;
			}
		}
	},
	closeEvent: function(){
		return this.message;
	}
});
window.onbeforeunload = null;
UnfinishedWork.QUEUE = [];
