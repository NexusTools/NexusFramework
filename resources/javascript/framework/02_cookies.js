Framework.registerModule("Cookies", {

	get: function(key) {
		var matches = document.cookie.match(new RegExp("(;\\s+|^)" +key+ "=([^;]+?)(;|$)", "i"));
		return matches && matches[2] || null;
	},
	
	set: function(key,value,path,days) {
		var value=escape(value);
		if(days === undefined)
			days = 30; // default
		if(days) {
			var exdate=new Date();
			exdate.setDate(exdate.getDate() + days);
			value += "; expires="+exdate.toUTCString();
		}
		if(path)
			value += "; path="+path;
		document.cookie=key + "=" + value;
		return this.get(key) == value;
	},
	
	remove: function(key) {
		setCookie(key,"",-360);
	}
	
});
