var darkOverlay = $(document.createElement("PopupDarkOverlay"));
darkOverlay.addClassName("hidden");
document.body.appendChild(darkOverlay);

var __popupStack = $A();
var __closeLast;
var __popupListeners = $A();

function registerPopupListener(listener){
	__popupListeners.push(listener);
}

function popupOpenOverlay(){
	if(darkOverlay.hasClassName("hidden"))
        darkOverlay.removeClassName("hidden");
}

function popupLoadPage(page){
    page = page.replace(/^\/|\/\/|\/$/g, "");
    
    console.log("Loading " + page);
    
    popupOpenOverlay();
    var foundPopup = false;
    $$("popup.preload[preload-page]").each(function(popup){
        if(foundPopup)
            return;
        
        if(popup.readAttribute("preload-page") == page) {
            console.log("Found Preloaded Popup");
            popup.removeClassName("hidden");
            popup.allowClose = true;
            __popupStack.push(popup);
            foundPopup = true; 
            var pageFormsInputs = popup.select("form input");
            if(pageFormsInputs.length)
                setTimeout(function(){pageFormsInputs[0].focus();}, 50);
            positionPopups();
        }
    });
    
    if(!foundPopup)
        Framework.API.request("page-dialog", page);
}

function _setupPopup_Link(el){
    console.log(el);
    el.on("click", function(e){
        var link = e.findElement("a[href]");
        var href = link.readAttribute("href");
        if(href.startsWith(Framework.Config.BASE_URI))
            href = href.substring(Framework.Config.BASE_URI.length);
        else if(href.startsWith(Framework.Config.BASE_URL))
            href = href.substring(Framework.Config.BASE_URL.length);
            
        if(__closeLast = link.hasAttribute("loading-text"))
            createPopup(link.readAttribute("loading-text"));
        
        popupLoadPage(href);
        e.stop();
    });
}

$$("a[popup]").each(_setupPopup_Link);

function createPopup(html, allowClose){
    var popup = $(document.createElement("popup"));
    popup.allowClose = allowClose !== false;
    
    if(popup.allowClose) {
		var close = $(document.createElement("close"));
		close.on("click", closeLastPopup);
		close.innerHTML = "X";
		popup.appendChild(close);
    }
    
    var content = $(document.createElement("contents"));
    content.innerHTML = html;
    
    popup.appendChild(content);
    
    popupOpenOverlay();
    __popupStack.push(popup);
    
    positionPopups();
    var pageFormsInputs = content.select("form input");
    if(pageFormsInputs.length)
        setTimeout(function(){pageFormsInputs[0].focus();}, 50);
    
    content.select("a[popup]").each(_setupPopup_Link);
    content.select("button.close, input[type=button].close, input[type=submit].close").each(function(d){
    	d.purge();
    	d.writeAttribute("onclick", null);
    	d.on("click", function(e){
    		e.stop();
    		closeLastPopup(true);
    	});
    });
    
    __popupListeners.each(function(listener){
    	listener(popup);
    });
    
    document.body.appendChild(popup);
    setTimeout(positionPopups, 0);
    setTimeout(positionPopups, 200); // Delayed Reposition for Content Relayout Catching
    return popup;
}

Framework.API.registerHandler("page-dialog", function(data){
    if(__closeLast)
        closeLastPopup(false);
        
    if(data.error) {
    	data.html = "<h1>Error Processing Request</h1><pre>";
    	data.html += Object.toJSON(data.error);
    	data.html += "</pre>";
    }
    createPopup(data.html);
});

function positionPopups(){
    var y = document.viewport.getScrollOffsets().top; 
    var viewport = document.viewport.getDimensions();
    __popupStack.each(function(popup){
        var popupDimensions = popup.getDimensions();
        var newPosition = [viewport.width / 2 - popupDimensions.width / 2,
                           viewport.height / 2 - popupDimensions.height / 2];
                           
        if(popupDimensions.height < viewport.height)
            newPosition[1] += y;
            
            
        if(newPosition[0] < 0)
            newPosition[0] = 0;
        if(newPosition[1] < 0)
            newPosition[1] = 0;
        popup.setStyle({"top": newPosition[1] + "px",
                               "left": newPosition[0] + "px"});
    });
}

Event.observe(window, "resize", positionPopups);
Event.observe(window, "scroll", positionPopups);

function closeLastPopup(force) {
	if(force === undefined)
		force = true;
	
    if(__popupStack.length > 0) {
        var lastPopup = __popupStack.pop();
        if(!force && !lastPopup.allowClose) {
        	__popupStack.push(lastPopup);
        	return;
        }
        
        if(lastPopup.hasClassName("preload"))
            lastPopup.addClassName("hidden");
        else
            document.body.removeChild(lastPopup);
    }
    if(__popupStack.length < 1)
        darkOverlay.addClassName("hidden");
}
darkOverlay.on("click", function(){
	closeLastPopup(false);
});
