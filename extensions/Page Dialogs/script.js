Framework.registerModule("PageDialogs", {
		
		stack: $A(),
		popupListeners: $A(),
		
		initialize: function(){
			Framework.Components.registerComponent("a[popup]", this.component);
			this.darkOverlay = $(document.createElement("PopupDarkOverlay"));
			this.darkOverlay.addClassName("hidden");
			document.body.appendChild(this.darkOverlay);
		},
		
		component: {
			setup: function(){
				console.log(this.getElement());
				this.getElement().on("click", function(e){
					
					Framework.PageDialogs.loadPage(e.readAttribute("href"));
					e.stop();
				});
			},
			
			destroy: function(){
			}
		},
		
		loadPage: function(uri){
			uri = uri.replace(new RegExp("/^\//i"), "");
			uri = uri.replace(new RegExp("/\/\//i"), "");
			uri = uri.replace(new RegExp("/\/$/i"), "");
			
			console.log("Loading Popup Page `"+uri+"`");
			throw "Unimplemented";
		},
		
		createPopup: function(){
			
		},
		
		showOverlay: function(){
			if(this.darkOverlay.hasClassName("hidden"))
        		this.darkOverlay.removeClassName("hidden");
		},
		
		hideOverlay: function(){
			if(!this.stack.length && !this.darkOverlay.hasClassName("hidden"))
        		this.darkOverlay.addClassName("hidden");
		},
		
		popup: Class.create({
		})
		
	});


