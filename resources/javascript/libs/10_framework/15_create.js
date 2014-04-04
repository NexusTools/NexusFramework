function $C() { // [parent], expr, [raw]
	var parent = null;
	var args = Array.prototype.slice.call(arguments, 0);
	if(Object.isElement(args[0]))
		parent = args.shift();
	
	if(!args[1] && Framework.Config.LEGACY_BROWSER)
		expr = "div." + expr;
	
	var matches = /^(\w+)((\.\w+)*)?(#\w+)?$/.exec(args[0]);
	if(matches) {
		var hashStr;
		var classStr;
		var el = document.createElement(matches[1]);
		switch(matches.length) {
			case 5: // (tag)((.class))(#hash)
				hashStr = matches[4];
			case 4: // (tag)((.class))
				classStr = matches[2];
				break;
			
			case 3: // (tag)(#hash)
				hashStr = matches[2];
				break;
		}
		
		if(classStr) {
			classStr = classStr.substring(1); // Remove first period
			do {
				var nextPos = classStr.indexOf(".");
				var ePos = nextPos > -1 ? nextPos : classStr.length;
				var clazz = classStr.substring(0, ePos);
				Element.addClassName(el, clazz);
				if(nextPos < 0)
					break;
				
				classStr = classStr.substring(ePos + 1);
			} while(true);
		}
		
		if(hashStr)
			Element.writeAttribute(el, "id", hashStr.substring(1));
		
		if(parent)
			parent.appendChild(el);
		
		return $(el);
	}
	return null;
}
