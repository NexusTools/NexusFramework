function attachEvent(k, event, handler, capture){
	if(k.addEventListener){
		k.addEventListener(event, handler, capture);
		return;
	}

	var tag = "__events_" + event;
	if(!k[tag]) {
		k[tag] = [];
		function chainHandler(e){
			var handlers = k[tag];
			for(var i=0; i<handlers.length; i++){
				handlers[i](e);
			}
		}
		if(k.attachEvent) {
			k.attachEvent("on"+event, chainHandler);
		} else {
			k["on"+event] = chainHandler;
		}
	}
	k[tag].push(handler);
}

function detachEvent(k, event, handler){
	if (k.removeEventListener) {
		k.removeEventListener(event, handler, true);
	} else if(k.detachEvent) {
		k.detachEvent("on"+event, handler);
	} else {
		k["on"+event] = null;
	}
}

function eventTargetAttribute(e, attr) {
	var targ;
	if (!e) var e = window.event;
	targ = e.target ? e.target : e.srcElement;
	
	if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;
		
	if(targ.hasAttribute) {
		while(!targ.hasAttribute(attr))
			targ = targ.parentNode;
		return targ.getAttribute(attr);
	} else {
		while(!targ[attr])
			targ = targ.parentNode;
		return targ[attr];
	}
			
}

function eventCancel(e){
    if (!e)
        if (window.event) e = window.event;
        else return;
    if (e.cancelBubble != null) e.cancelBubble = true;
    if (e.stopPropagation) e.stopPropagation();
    if (e.preventDefault) e.preventDefault();
    if (window.event) e.returnValue = false;
    if (e.cancel != null) e.cancel = true;
}

function html_entity_decode(str){
    var tarea=document.createElement('textarea');
    tarea.innerHTML = str;
    var value = tarea.value;
    if(tarea.parentNode)
        tarea.parentNode.removeChild(tarea);
    return value;
}

var __base64_keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
function base64encode (input) {
	var output = "";
	var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
	var i = 0;

	input = utf8_encode(input);

	while (i < input.length) {

		chr1 = input.charCodeAt(i++);
		chr2 = input.charCodeAt(i++);
		chr3 = input.charCodeAt(i++);

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2)) {
			enc3 = enc4 = 64;
		} else if (isNaN(chr3)) {
			enc4 = 64;
		}

		output = output +
		__base64_keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
		__base64_keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

	}

	return output;
}
function base64decode(input) {
	var output = "";
	var chr1, chr2, chr3;
	var enc1, enc2, enc3, enc4;
	var i = 0;

	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

	while (i < input.length) {

		enc1 = __base64_keyStr.indexOf(input.charAt(i++));
		enc2 = __base64_keyStr.indexOf(input.charAt(i++));
		enc3 = __base64_keyStr.indexOf(input.charAt(i++));
		enc4 = __base64_keyStr.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output = output + String.fromCharCode(chr1);

		if (enc3 != 64) {
			output = output + String.fromCharCode(chr2);
		}
		if (enc4 != 64) {
			output = output + String.fromCharCode(chr3);
		}

	}

	output = utf8_decode(output);

	return output;

}
function utf8_encode(string) {
	string = string.replace(/\r\n/g,"\n");
	var utftext = "";

	for (var n = 0; n < string.length; n++) {

		var c = string.charCodeAt(n);

		if (c < 128) {
			utftext += String.fromCharCode(c);
		}
		else if((c > 127) && (c < 2048)) {
			utftext += String.fromCharCode((c >> 6) | 192);
			utftext += String.fromCharCode((c & 63) | 128);
		}
		else {
			utftext += String.fromCharCode((c >> 12) | 224);
			utftext += String.fromCharCode(((c >> 6) & 63) | 128);
			utftext += String.fromCharCode((c & 63) | 128);
		}

	}

	return utftext;
}
_utf8_decode : function utf8_decode (utftext) {
	var string = "";
	var i = 0;
	var c = c1 = c2 = 0;

	while ( i < utftext.length ) {

		c = utftext.charCodeAt(i);

		if (c < 128) {
			string += String.fromCharCode(c);
			i++;
		}
		else if((c > 191) && (c < 224)) {
			c2 = utftext.charCodeAt(i+1);
			string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
			i += 2;
		}
		else {
			c2 = utftext.charCodeAt(i+1);
			c3 = utftext.charCodeAt(i+2);
			string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			i += 3;
		}

	}

	return string;
}

function getErrorInformation(e){
	var stackcalls = getErrorStacktrace(e);
	var firstFrame = stackcalls.length > 0 ? stackcalls.shift() : undefined;
	return {
			stack: stackcalls,
			message: e.message,
			lineNumber: e.lineNumber ? e.lineNumber : (firstFrame ? firstFrame.substring(firstFrame.indexOf(':') + 1, firstFrame.lastIndexOf(")")) : "<unknown>"),
			fileName: e.fileName ? e.fileName : (firstFrame ? firstFrame.substring(firstFrame.indexOf('(') + 1, firstFrame.indexOf(":")) : "<unknown>"),
			stackToString: function(){
				var output = false;
				for(var i=0, len=this.stack.length; i < len; i++){
					if(output)
						output += "\n";
					else
						output = "";
					output += "\t" + this.stack[i].toString();
				}
				return output ? output : "Stack Trace Unavailable";
			},
			toString: function(){
				return this.message + " at " + this.fileName + ":" + this.lineNumber + "\n" + this.stackToString();
			}
		};
}

function getErrorStacktrace(e) {
	var callstack = [];
	
	if (e.stack) {
		var lines = e.stack.split('\n');
		while(lines.length > 0){
			var line = lines.shift().trim();
			if (line.startsWith("at")) {
				callstack.push(line.substring(3));
			} else if (line.match(/^\s*[A-Za-z0-9\-_\$]+\(/)) {
				callstack.push(line);
			}
		}
	} else if (window.opera && e.message) {
		var lines = e.message.split('\n');
		for (var i=0, len=lines.length; i < len; i++) {
			if (lines[i].match(/^\s*[A-Za-z0-9\-_\$]+\(/)) {
				var entry = lines[i];
				
				if (lines[i+1]) {
					entry += ' at ' + lines[i+1];
					i++;
				}
				callstack.push(entry);
			}
		}
		callstack.shift();
	}
	return callstack;
}

function mergeOver(baseObject, defineObject){
	for(var key in defineObject)
		switch(typeof baseObject[key]){
			case "undefined":
			case "string":
			case "number":
			case "function":
				baseObject[key] = defineObject[key];
				break;
			
			default:
				try {
					mergeOver(baseObject[key], defineObject[key]);
				}catch(e){
					alert("Error With Type Merge: " + (typeof baseObject[key]));
					baseObject[key] = defineObject[key];
				}
				break;
		}
}

function objectToNumber(obj){
	switch(obj){
		case "string":
			obj = parseFloat(element[attrName]);
			if(!obj)
				obj = parseInt(element[attrName]);
			if(!obj)
				obj = 0;
			return obj;
			
		case "number":
			return element[attrName];
			
		default:
			return 0;
	}
}

if(!String.prototype.trim)
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	}

if(!String.prototype.startsWith)
	String.prototype.startsWith = function(part){
		if(this.length < part.length)
		    return false;

		for(var i=0; i<part.length; i++){
		    if(part.charAt(i) != this.charAt(i))
		        return false;
		}
		return true;
	}
