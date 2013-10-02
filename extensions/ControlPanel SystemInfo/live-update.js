(function() {
	var widget = $$("Toolbar Widget[name=sysinfo]")[0];
	console.log(widget);
	if(!widget)
		return;
	
	var text = widget.down("span");
	var menu = widget.down("menu").select("a");
	var memory = menu[1];
	var swap = menu[2];
	var load = menu[3];
	
	var loadColors = [];
	loadColors.push([127, 127, 127]);
	loadColors.push([232, 185, 0]);
	loadColors.push([244, 0, 0]);
	loadColors.push([150, 30, 190]);
	
	function fromColor(base, percent) {
		var mod = [];
		var to = loadColors[base+1];
		var from = loadColors[base].slice(0);
		mod.push((to[0] - from[0])*percent);
		mod.push((to[1] - from[1])*percent);
		mod.push((to[2] - from[2])*percent);
		return [from[0]+mod[0],from[1]+mod[1],from[2]+mod[2]];
	}

	function colorLoad(load, lighten) {
		load = parseFloat(load);
		var color;
		if(load >= 1) {
			if(load > 5)
				color = loadColors[3];
			else if(load >= 3)
				color = fromColor(2, (load-3)/2);
			else
				color = fromColor(1, (load-1)/2);
		} else
			color = fromColor(0, load/1);
			
		var hexColor = "#";
		for(var i=0; i<3; i++) {
			var code = color[i];
			if(lighten)
				code *= 1.5;
			if(code > 255)
				code = 255;
			else if(code < 0)
				code = 0;
			code = Math.round(code).toString(16);
			if(code.length < 2)
				hexColor += "0";
			hexColor += code;
		}
		
		return "<font color='" + hexColor + "'>" + load.toFixed(2) + "</font>";
	}
	
	function formatSize(size) {
		var prefix = "KB";
		if(size >= 1000) {
			size /= 1024;
			prefix = "MB";
		}
		if(size >= 1000) {
			size /= 1024;
			prefix = "GB";
		}
		if(size >= 1000) {
			size /= 1024;
			prefix = "TB";
		}
		
		return Math.round(size*100)/100 + prefix;
	}

	Framework.API.registerHandler("sysinfo", function(data) {
		text.innerHTML = "Load: " + colorLoad(data.loadavg[0], true)
				+ ", Mem: " + Math.round((data.memory.system.used /
										data.memory.system.total)
										* 1000)/10 + "%";
		
		memory.innerHTML = "Memory: " + formatSize(data.memory.system.used)
							+ " of " + formatSize(data.memory.system.total);
		
		swap.innerHTML = "Swap: " + formatSize(data.memory.swap.used)
							+ " of " + formatSize(data.memory.swap.total);
		
		load.innerHTML = "Load Averages<span>" + colorLoad(data.loadavg[0]) +
							"&nbsp;&nbsp;&nbsp;" + colorLoad(data.loadavg[1]) +
							"&nbsp;&nbsp;&nbsp;" + colorLoad(data.loadavg[2]) + "</span>";
	});
	Framework.API.registerIntervalRequest("sysinfo");
	
}());
