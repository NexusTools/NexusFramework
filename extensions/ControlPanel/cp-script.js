var ControlPanel = {};
var popupMode = location.href.endsWith("?popup") || location.href.endsWith("&popup");
if(popupMode)
	console.log("Running in Popup Mode");
ControlPanel.container = $(document.getElementsByTagName("FRAMEWORK:WIDGETS")[0]);

ControlPanel.ScrollEvent = function(e){
	var scrollPos = document.body.scrollTop;
	setTimeout(function() {
		document.body.scrollTop = scrollPos;
	}, 0);
}

ControlPanel.navbar = {};
ControlPanel.navbar.element = $(ControlPanel.container.getElementsByTagName("NAVBAR")[0]);
ControlPanel.navbar.uploads = ControlPanel.navbar.element.select("uploads")[0];
ControlPanel.navbar.items = {};
ControlPanel.navbar.active = {category: null, entry: null};
ControlPanel.loadPage = function(cat, name, getVars, postVars){
	if(ControlPanel.UnsavedChanged) {
	    ControlPanel.confirmDiscardChanges(ControlPanel.loadPage, cat, name, getVars, postVars);
		return;
    }

	try{
		var newItem = ControlPanel.navbar.items[cat];
		if(newItem != ControlPanel.navbar.active.category){
			ControlPanel.navbar.active.category.className = "";
			ControlPanel.navbar.active.category = newItem;
			ControlPanel.navbar.active.category.className = "active";
		}
	
		var entry = ControlPanel.navbar.active.category.entries[name];
		if(entry != ControlPanel.navbar.active.entry) {
			ControlPanel.navbar.active.entry.className = "";
			ControlPanel.navbar.active.entry = entry;
			entry.className = "active";
		}
		delete entry;
	}catch(e){}
	
	var getData = "";
	if(getVars != null)
		getData = "?" + Object.toQueryString(getVars);
	
	
	ControlPanel.loadURI(cat + "/" + name + getData, postVars);
}

ControlPanel.loadPopup = function(cat, name, getVars, postVars){
	var getData = "";
	if(getVars != null)
		getData = "?" + Object.toQueryString(getVars);
	
	ControlPanel.loadPopupURI(cat + "/" + name + getData, postVars);
}

ControlPanel.fadeInWhiteOut = function(){
    if(ControlPanel.page.whiteout.timer)
        clearTimeout(ControlPanel.page.whiteout.timer);
    
    if(ControlPanel.page.whiteout.opacity == undefined)
        ControlPanel.page.whiteout.opacity = 0;
    if(ControlPanel.page.whiteout.opacity < 0.1)
        ControlPanel.page.whiteout.style.display = "block";
    ControlPanel.page.whiteout.opacity += 0.1;
    ControlPanel.page.whiteout.setStyle({opacity: ControlPanel.page.whiteout.opacity});
    if(ControlPanel.page.whiteout.opacity < 1)
        ControlPanel.page.whiteout.timer = setTimeout(ControlPanel.fadeInWhiteOut, 30);
}

ControlPanel.fadeOutWhiteOut = function(){
    if(ControlPanel.page.whiteout.timer)
        clearTimeout(ControlPanel.page.whiteout.timer);
    
    ControlPanel.page.whiteout.opacity -= 0.1;
    ControlPanel.page.whiteout.setStyle({opacity: ControlPanel.page.whiteout.opacity});
    if(ControlPanel.page.whiteout.opacity > 0)
        ControlPanel.page.whiteout.timer = setTimeout(ControlPanel.fadeOutWhiteOut, 30);
    else 
        ControlPanel.page.whiteout.style.display = "none";
}

ControlPanel.setBlur = function(blurred){
    if(blurred) {
        console.log("Blurring Control Panel");
        ControlPanel.fadeInWhiteOut();
        ControlPanel.container.addClassName("blurred");
    } else {
        console.log("Unblurring Control Panel");
        ControlPanel.fadeOutWhiteOut();
        ControlPanel.container.removeClassName("blurred");
    }
}

ControlPanel.closePopup = function(){
    ControlPanel.popupOpen = false;
    ControlPanel.page.whiteout.popup.removeClassName("open");
    ControlPanel.setBlur(false);
}

ControlPanel.popupOpen = false;
ControlPanel.popupLoading = false;
ControlPanel.loadURI = function(uri, postVars){
    console.log("ControlPanel.loadURI");
    var hasPostData = postVars instanceof Object;
    
    if(!hasPostData && (uri.endsWith("&popup=true") || uri.endsWith("?popup=true")))
        ControlPanel.loadPopupURI(uri.substring(0, uri.length - 11), postVars);
    else if(!hasPostData && uri.endsWith("&del"))
        ControlPanel.loadPopupURI(uri, postVars);
    else {
        ControlPanel.setBlur(true);
        if(ControlPanel.UnsavedChanged) {
            ControlPanel.confirmDiscardChanges(ControlPanel.loadURI, uri, postVars);
		    return;
		}
    
        if(ControlPanel.popupOpen) {
            ControlPanel.popupOpen = false;
            ControlPanel.page.whiteout.popup.removeClassName("open");
        }
    
        console.log("hasPostData: " + (hasPostData ? "YES" : "NO"));
        if(!popupMode && !hasPostData && uri.indexOf('?') == -1){
            var urlStart = location.href.substring(0,
                    location.href.indexOf("/control") + 8) + "/";
            
            if(location.href.indexOf("/control/") == -1) {
                location.href = urlStart + uri;
                return;
            }
            
            var curUri = location.href.substring(urlStart.length);
            var quPos = curUri.indexOf("?");
            if(quPos > 0)
                curUri = curUri.substring(0, quPos);

            if(!uri.startsWith(curUri)) {
                location.href = urlStart + uri;
                return;
            }
        }
		
		ControlPanel.popupLoading = false;
		if(popupMode) {
			if(uri.indexOf("?") > -1)
				uri = uri + "&popup";
			else
				uri += "?popup";
		}
		Framework.API.request("controlpanel", uri, postVars);
    }
    
	
}

ControlPanel.loadPopupURI = function(uri, postVars){
    ControlPanel.setBlur(true);
    console.log("ControlPanel.loadPopupURI");
    ControlPanel.popupUri = uri;
    var quPos = ControlPanel.popupUri.indexOf("?");
    if(quPos !== -1)
        ControlPanel.popupUri = ControlPanel.popupUri.substring(0, quPos);
    console.log("Popup URI set to `" + ControlPanel.popupUri + "`");
    ControlPanel.popupLoading = true;
    Framework.API.request("controlpanel", uri, postVars);
}

ControlPanel.discardChanges = function(){
	alert("Not Yet Implemented.");
}

ControlPanel.submitForm = function(button, getVars){
	var postVars = {};
	ControlPanel.UnsavedChanged = false;
	if(ControlPanel.popupOpen)
	    var form = ControlPanel.page.whiteout.popup.select("form")[0];
	else
	    var form = Element.select(ControlPanel.page.content, "form")[0];
	Framework.Components.destroyContainer(form);
	
	var badBrowser = false;
	form.select("input[name], textarea[name], select[name]").each(function(input){
		if(badBrowser)
			return;
	
		var parent = input;
		
		console.log("Scanning Input");
		try{
			do {
				if(!parent.visible())
					return;
					
				console.log(parent);
			} while((parent = $(parent.parentNode)) && parent.tagName != "FORM");
		}catch(e){}
	
		if(input.visible()) {
			if(input.type)
				if(input.type == "radio") {
					if(input.checked)
						postVars[input.getAttribute("name")] = input.getAttribute("value");
					return;
				} else if(input.type == "checkbox") {
					postVars[input.getAttribute("name")] = input.checked ? "Yes" : "No";
					return;
				} else if(input.type == "file") {
					if(input.files && window.FormData) {
						if(input.files.length)
							postVars[input.getAttribute("name")] = input.files[0];
						alert(typeof input.files[0]);
					} else
						badBrowser = true;
				}
			
			postVars[input.getAttribute("name")] = input.getValue();
		}
	});
	if(badBrowser) {
		alert("Your browser does not support dynamically uploading files.");
		badBrowser = true;
		return;
	}
	
	if(button)
		postVars['action'] = $(button).getValue();
	
	var getData = "";
	if(getVars != null)
		getData = "?" + Object.toQueryString(getVars);
		
	console.log("Submitting Form");
	console.log(form.readAttribute("action").substring(10));
	console.log(getData);
	console.log(postVars);
	
	ControlPanel.loadURI(form.readAttribute("action").substring(10) + getData, postVars);
}

ControlPanel.page = {};
ControlPanel.page.element = $(ControlPanel.container.getElementsByTagName("FRAMEWORK:PAGE")[0]);
ControlPanel.page.breadcrum = $(ControlPanel.page.element.getElementsByTagName("BREADCRUMB")[0]);
ControlPanel.page.whiteout = $(ControlPanel.container.getElementsByTagName("WHITEOUT")[0]);
ControlPanel.page.whiteout.popup = $(ControlPanel.page.whiteout.getElementsByTagName("POPUP")[0]);
ControlPanel.page.content = $(ControlPanel.page.element.getElementsByTagName("CONTENT")[0]);
ControlPanel.page.buttons = false;

ControlPanel.HandleLink = function(e){
    try {
	    var link = e.element();
	    console.log(link);
	    while(link && (!link.hasAttribute("href"))) {
		    link = link.parentNode;
		    console.log(link);
	    }
	    link = link.getAttribute("href");
        if(link.startsWith("control://")){
            e.stop();
            console.log("Loading URI: " + link);
	        ControlPanel.loadURI(link.substring(10));
        } else
	        e.stopPropagation();
	}catch(e){
	    console.log(e);
	}
	
}

ControlPanel.UnsavedChanged = false;
ControlPanel.PanelReady = function() {
	ControlPanel.UnsavedChanged = false;
	var links = ControlPanel.container.select("a[href]").each(function(link){
		link.observe("click", ControlPanel.HandleLink);
	});
	
	function setupActionRow(row){
		row.on("mousedown", function(e){
			var el = e.element();
			while(el != row) { // abort on link click
				if(el.tagName == "A")
					return;
				el = el.parentNode;
			}
		
			row.start = [e.screenX, e.screenY];
			row.mousepressed = true;
		});
		
		row.on("mousemove", function(e){
			if(row.mousepressed){
				pos = [e.screenX, e.screenY];
				if(pos[0] > row.start[0] + 5 || pos[0] < row.start[0] - 5 ||
					pos[1] > row.start[1] + 5 || pos[1] < row.start[1] - 5)
					row.mousepressed = false;
			}
		});
		
		row.on("mouseup", function(e){
			if(row.mousepressed) {
				ControlPanel.loadURI(row.getAttribute('action'));
				row.mousepressed = false;
			}
			
		});
	}
	
	ControlPanel.container.select("tr[action]").each(function(row){
		setupActionRow(row);
		
		//onmousepress=\"this.moved=false;\" onmousemove=\"this.moved=true;\" onclick=\"if(this.moved) return; ControlPanel.loadURI(this.getAttribute('action'))\"
	});
	
	ControlPanel.container.select("textarea[code=html]").each(function(mce){
		if(!mce.hasAttribute("id"))
			mce.setAttribute("id", "mce_id_" + Math.round(Math.random() * 10000));
			
		//tinyMCE.execCommand('mceAddControl', false, mce.getAttribute("id"));
	});
	
	ControlPanel.container.select("form select, form input, form textarea").each(function(sel){
		sel.observe("change", function(){
			ControlPanel.UnsavedChanged = true;
		});
	});
	
/*	Element.select(ControlPanel.page.content, "filedrop").each(function(dropTarget){
		var drop = new FileDrop({
			autoUpload: true,
			targetElement: dropTarget,
			targetURL: "upload?path=" + dropTarget.getAttribute("folder")});
		drop.observe("uploadReady", function(e){
			var entry = document.createElement("entry");
			var progress = document.createElement("progressbar");
			entry.appendChild(progress);
			
			var title = document.createElement("div");
			title.textContent = e.upload.file.name;
			entry.appendChild(title);
			
			ControlPanel.navbar.uploads.appendChild(entry);
			
			e.upload.observe("started", function(){
				entry.className = "started";
			});
			e.upload.observe("progress", function(e){
				progress.style.width = (Math.round((e.loaded / e.total) * 10000) / 100) + "%";
			});
			e.upload.observe("complete", function(){
				entry.parentNode.removeChild(entry);
			});
			e.upload.observe("error", function(){
				alert("`" + e.upload.file.name + "` Failed to Upload");
				entry.parentNode.removeChild(entry);
			});
		});
	});*/
	
	try{
		ControlPanel.page.buttons.parentNode.removeChild(ControlPanel.page.buttons);
	}catch(e){}
	console.log(ControlPanel.page.content);
	
	ControlPanel.page.buttons = Element.select(ControlPanel.page.content, "pagebuttons")[0];
	try{
		ControlPanel.page.buttons.parentNode.removeChild(ControlPanel.page.buttons);
		ControlPanel.page.breadcrum.appendChild(ControlPanel.page.buttons);
	}catch(e){}
}

ControlPanel.centerPopup = function() {
    var w, h, pw, ph;
    var d = ControlPanel.page.whiteout.popup.getDimensions();
    var v = document.viewport.getDimensions();

    var top = (v.height/2) - (d.height/2);
    var left = (v.width/2) - (d.width/2);
    if(top < 20)
        top = 20;
    ControlPanel.page.whiteout.popup.style.top = top + "px";
    ControlPanel.page.whiteout.popup.style.left = left + "px";
}

ControlPanel.alertDialog = function(text, title){
    ControlPanel.setBlur(true);
    ControlPanel.popupLoading = false;
    ControlPanel.popupOpen = true;
    ControlPanel.page.whiteout.popup.innerHTML = "";
    if(!title)
        title = "Alert";
    setTimeout(function(){
        var content = document.createElement("center");
        content.innerHTML = "<h2>" + title + "</h2><div style='margin-bottom: 8px; margin-top: -8px;'>" + text + "</div>";
        var pageButtons = document.createElement("pagebuttons");
        var btn = $(document.createElement("input"));
        btn.className = "button";
        btn.writeAttribute("type", "button");
        btn.writeAttribute("value", "Okay");
        btn.on("click", ControlPanel.closePopup);
        pageButtons.appendChild(btn);
        content.appendChild(pageButtons);
        ControlPanel.page.whiteout.popup.appendChild(content);
        ControlPanel.centerPopup();
        Framework.Components.setupContainer(content);
        ControlPanel.page.whiteout.popup.addClassName("open");
    });
}

ControlPanel.customDialog = function(contentHTML, buttons, callback){
    ControlPanel.setBlur(true);
    ControlPanel.popupLoading = false;
    ControlPanel.popupOpen = true;
    ControlPanel.page.whiteout.popup.innerHTML = "";
    
    setTimeout(function(){
        var content = document.createElement("center");
        content.innerHTML = contentHTML;
        var pageButtons = document.createElement("pagebuttons");
        if(buttons)
        	buttons.each(function(btn) {
        		pageButtons.appendChild(btn);
        	});
        else {
		    var btn = $(document.createElement("input"));
		    btn.className = "button";
		    btn.writeAttribute("type", "button");
		    btn.writeAttribute("value", "Close");
		    btn.on("click", ControlPanel.closePopup);
		    pageButtons.appendChild(btn);
        }
        content.appendChild(pageButtons);
        ControlPanel.page.whiteout.popup.appendChild(content);
        ControlPanel.centerPopup();
        Framework.Components.setupContainer(content);
        ControlPanel.page.whiteout.popup.addClassName("open");
        
        if(callback instanceof Funtion)
        	callback(content, pageButtons);
    });
}

ControlPanel.confirmDiscardChanges = function(){
    var callbackArgs = Array.prototype.slice.call(arguments);
    var callback = callbackArgs.shift();
    console.log(callbackArgs);

    ControlPanel.setBlur(true);
    setTimeout(function(){
        ControlPanel.popupLoading = false;
        ControlPanel.popupOpen = true;
        console.log("Popup Data Loaded");
        ControlPanel.page.whiteout.popup.innerHTML = "";
        
        var content = document.createElement("center");
        content.innerHTML = "<h2>Unsaved Changes</h2><div style='margin-bottom: 8px; margin-top: -8px;'>You have made changes to the open asset.<br />If you choose to continue all your changes will be lost!</div>";
        var pageButtons = document.createElement("pagebuttons");
        var btn = $(document.createElement("input"));
        btn.className = "button";
        btn.writeAttribute("type", "button");
        btn.writeAttribute("value", "Discard Changes");
        btn.on("click", function(){
            ControlPanel.UnsavedChanged = false;
            callback.apply(null, callbackArgs);
        });
        pageButtons.appendChild(btn);
        pageButtons.appendChild(document.createTextNode(" "));
        btn = $(document.createElement("input"));
        btn.className = "button";
        btn.writeAttribute("type", "button");
        btn.writeAttribute("value", "Nevermind");
        btn.on("click", ControlPanel.closePopup);
        pageButtons.appendChild(btn);
        content.appendChild(pageButtons);
        ControlPanel.page.whiteout.popup.appendChild(content);
        ControlPanel.centerPopup();
        ControlPanel.page.whiteout.popup.addClassName("open");
    }, 200);
}

Framework.API.registerHandler("controlpanel", function(data){
	if(!data['error'] && !data['html'])
		data.error = "HTML missing from response.";
	
    if((ControlPanel.popupUri && !ControlPanel.popupLoading &&
    		data.uri == ControlPanel.popupUri) || data['error']) {
    	if(data['error']) {
    		data.uri = "errorhandler";
    		data.html = "<h1>Error Occured</h1><pre>";
    		data.html += Object.toJSON(data.error);
    		data.html += "</pre>";
    	}
    	
        ControlPanel.popupLoading = true;
        ControlPanel.setBlur(true);
    }
    if(ControlPanel.popupLoading) {
        ControlPanel.popupLoading = false;
        ControlPanel.popupOpen = true;
        console.log("Popup Data Loaded");
        ControlPanel.page.whiteout.popup.innerHTML = data.html;
        Framework.Components.setupContainer(ControlPanel.page.whiteout.popup);
        
        var dialogButtons = [];
        var defButton = false;
        
        ControlPanel.page.whiteout.popup.select("pagebuttons").each(function(pageButtons){
            pageButtons.remove();
            
            
            pageButtons.select("button").each(function(e){
                var val = e.readAttribute("value");
                switch(val){
                    case "apply":
                    case "save-close":
                        var text = (val == "save-close" ? "Save" : "Apply");
                        var icon = e.select(".icon");
                        if(icon.length) {
                            icon = icon[0];
                            icon.remove();
                            e.textContent = "";
                            e.appendChild(icon);
                            e.appendChild(document.createTextNode(text));
                        } else
                            e.textContent = text;
                        
                        defButton = e;
                        dialogButtons.push(e);
                        break;
                        
                    case "new":
                    case "create":
                    	defButton = e;
                        dialogButtons.push(e);
                        break;

                    case "discard":
                        var icon = e.select(".icon");
                        if(icon.length) {
                            icon = icon[0];
                            icon.remove();
                            e.textContent = "";
                            e.appendChild(icon);
                            e.appendChild(document.createTextNode("Close"));
                        } else
                            e.textContent = "Close";
                        
                        e.writeAttribute("onclick", false);
                        e.on("click", function(e){
                            e.stop();
                            ControlPanel.closePopup();
                        });
                        dialogButtons.push(e);
                        break;
                }
                
                
           
                
                e.remove();
            });
        });
        
        ControlPanel.page.whiteout.popup.select("form").each(function(form) {
        	var formButton = defButton;
        	form.on("beforesubmit", function(e) {
        		if(formButton)
        			formButton.click();
        		e.stop();
        	});
        });
        
        if(dialogButtons.length){
            var breadCrumb = document.createElement("h2");
            breadCrumb.setStyle({"text-align": "center"});
            breadCrumb.textContent = (data.breadcrumb[1] instanceof Object ? data.breadcrumb[1].title : data.breadcrumb[1]);
            ControlPanel.page.whiteout.popup.insertBefore(breadCrumb,ControlPanel.page.whiteout.popup.firstChild);
        }
        ControlPanel.page.whiteout.popup.select("center input.button").each(function(e){
            dialogButtons.push(e);
        });
        ControlPanel.page.whiteout.popup.select("center button").each(function(e){
            dialogButtons.push(e);
        });
        
        if(!dialogButtons.length) {
            var btn = document.createElement("input");
            btn.className = "button";
            btn.on("click", ControlPanel.closePopup);
            btn.writeAttribute("value", "Close");
            btn.writeAttribute("type", "button");
            dialogButtons.push(btn);
        }
        var pageButtons = document.createElement("pagebuttons");
        var i;
        var addText = false;
        for(i = 0; i < dialogButtons.length; i++){
            if(addText){
                var space = document.createTextNode(" ");
                pageButtons.appendChild(space);
            } else
                addText = true;
            pageButtons.appendChild(dialogButtons[i]);
        }
        ControlPanel.page.whiteout.popup.appendChild(pageButtons);
        
        var toolDiv=false;
        if(data.tools instanceof Object)
            for(var tool in data.tools){
                if(!toolDiv) {
                    toolDiv = document.createElement("div");
                    toolDiv.className = "tools";
                }
            
                var entry = data.tools[tool];
	
	            var item = document.createElement("img");
	            item.writeAttribute("src", entry.icon);
	            item.writeAttribute("action", entry.action);
	            if(entry.action){
		            item.setAttribute("action", entry.action);
		            $(item).observe("click", function(e){
			            eval(e.target.getAttribute("action"));
		            });
	            }
	            toolDiv.appendChild(item);
            }
        if(toolDiv)
            ControlPanel.page.whiteout.popup.insertBefore(toolDiv,ControlPanel.page.whiteout.popup.firstChild);
        
        ControlPanel.page.whiteout.popup.select("a[href]").each(function(link){
		    link.observe("click", ControlPanel.HandleLink);
	    });
        ControlPanel.centerPopup();
        ControlPanel.page.whiteout.popup.addClassName("open");
        
        var formElements = ControlPanel.page.whiteout.popup.select("form input.text, form textarea");
        console.log(formElements);
        if(formElements.length)
            setTimeout(function(){
                formElements[0].focus();
            }, 200);
        return;
    }
    
    if(popupMode) {
    	var urlStart = location.href.substring(0,
                    location.href.indexOf("/control") + 8) + "/";
		var curUri = location.href.substring(urlStart.length);
        var quPos = curUri.indexOf("?");
        if(quPos > 0)
            curUri = curUri.substring(0, quPos);
            
        if(!data.uri.startsWith(curUri)) {
        	if(!window.close)
        		alert("CLOSE FUNCTION UNDEFINED");
        	else
        		try {
        			var div = document.createElement("div");
        			div.innerHTML = data.html;
            		window.close(Element.select(div, "banner[class=success]").length);
            	}catch(e){
            		console.log("Error in Exit");
            		console.dir(e);
            	}
            return;
        }
    }

    ControlPanel.popupUri = false;
    ControlPanel.setBlur(false);
    console.log("Page Data Loaded");
	ControlPanel.page.content.innerHTML = data.html;
	
	var child = ControlPanel.page.breadcrum.firstChild;
	try{
		var lastChild = child;
		child = child.nextSibling;
		while(lastChild != null) {
			ControlPanel.page.breadcrum.removeChild(lastChild);
			if(child == null)
				break;
			lastChild = child;
			child = child.nextSibling;
		}
	}catch(e){}
	
	var first = true;
	for(var i=0; i<data.breadcrumb.length; i++){
		if(first)
			first = false;
		else {
			var item = document.createTextNode("   »   ");
			ControlPanel.page.breadcrum.appendChild(item);
		}
		
		var entry = data.breadcrumb[i];
		
		var item = document.createElement("item");
		if(entry.title){
			if(entry.action){
				item.setAttribute("action", entry.action);
				$(item).observe("click", function(e){
					eval(e.target.getAttribute("action"));
				});
			}
			item.textContent = entry.title;
		} else
			item.textContent = entry;
		ControlPanel.page.breadcrum.appendChild(item);
	}
	
	var dumpSpacing = true;
	if(data.tools  instanceof Object)
        for(var tool in data.tools){
            if(dumpSpacing) {
                var span = document.createElement("span");
                span.innerHTML = "&nbsp;&nbsp;";
                ControlPanel.page.breadcrum.appendChild(span);
                dumpSpacing = false;
            }
        	
            var entry = data.tools[tool];
	
	        var item = document.createElement("img");
	        item.writeAttribute("src", entry.icon);
	        item.writeAttribute("action", entry.action);
	        if(entry.action){
		        item.setAttribute("action", entry.action);
		        $(item).observe("click", function(e){
			        eval(e.target.getAttribute("action"));
		        });
	        }
	        ControlPanel.page.breadcrum.appendChild(item);
        }
	

	ControlPanel.PanelReady();
    Framework.Components.setupContainer(ControlPanel.page.content);
});

function hookSubMenuItem(cat, item){
	var itemName = item.textContent.trim();
	if(item.hasAttribute("popup"))
	    Event.observe(item, "click", function(e){
		    ControlPanel.loadPopup(cat, itemName);
		    e.stop();
	    });
	else
	    Event.observe(item, "click", function(e){
		    ControlPanel.loadPage(cat, itemName);
		    e.stop();
	    });
}

var item = ControlPanel.navbar.element.firstChild;
do {
	if(item.tagName == "ITEM") {
		var cat = item.firstChild.data.trim();
		item.entries = [];
		
		$(item).observe("click", function(e){
			ControlPanel.loadPage(e.element().firstChild.data.trim(), e.element().def);
			e.stop();
		});
		if(item.className == "active")
			ControlPanel.navbar.active.category = item;
		ControlPanel.navbar.items[item.firstChild.data.trim()] = item;
		
		var subMenu = $(item.getElementsByTagName("SUBMENU")[0]);
		subMenu.setAttribute("for", cat);
		var subItem = subMenu.firstChild;
		item.def = null;
		do {
			if(subItem.tagName == "ITEM") {
				if(item.def == null || item.def == "Create" || item.def == "Upload"
						|| item.def == "Theme")
					 item.def = subItem.textContent.trim();
			
				if(subItem.className == "active")
					ControlPanel.navbar.active.entry = subItem;
				item.entries[subItem.textContent.trim()] = subItem;
				hookSubMenuItem(cat, subItem);
			}
		} while((subItem = subItem.nextSibling) != null);
	}
	
		
} while((item = item.nextSibling) != null);

ControlPanel.popupUri = false;
Event.observe(window, 'resize', ControlPanel.centerPopup);
ControlPanel.PanelReady();

$$("Toolbar Widget[href]").each(function(widget){
	widget.observe("click", ControlPanel.HandleLink);
});

$$("Toolbar Widget[href] a").each(function(widget){
	widget.observe("click", function(e) {
		e.stopPropagation();
	});
});

