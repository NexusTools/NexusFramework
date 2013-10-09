Framework.registerModule("StringFormat", {
	
		idForDisplay: function(display){
			return display;
		},
		
		displayForID: function(id){
			console.log("displayForID");
		
			return id.replace(/[^\w\d]+/g, " ")
					.replace(/(\w)(\w+)/g, function(match, p1, p2) {
				console.log("match", arguments);
				return p1.toUpperCase() + p2.toLowerCase();
			});
		}
	
	
	});
	
