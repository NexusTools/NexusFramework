(function() {
	var widget = $$("Toolbar Widget[name=online-users]")[0];
	console.log(widget);
	if(!widget)
		return;
	
	var text = widget.down("span");
	var menu = widget.down("menu");

	function newLink(html) {
		var link = document.createElement("a");
		link.innerHTML = html;
		link.on("click", function(e) {
			e.stopPropagation();
		});
		return link;
	}

	Framework.API.registerHandler("online-users", function(data) {
		var count = data.members*1 + data.staff.length*1;
		text.innerHTML = count + " User(s) Online";
		menu.innerHTML = "";
		
		if(data.staff.length) {
			data.staff.each(function(user) {
				var html = "";
				if(user[0])
					html += "<img src='" +user[0]+ "' />";
				html += user[1];
				html += "<span>";
				html += user[2];
				html += "</span>";
				menu.appendChild(newLink(html));
			});
			menu.appendChild(document.createElement("hr"));
		}
		menu.appendChild(newLink(data.members + " Members"));
		menu.appendChild(newLink(data.guests + " Guests"));
	});
	Framework.API.registerIntervalRequest("online-users");
}());
