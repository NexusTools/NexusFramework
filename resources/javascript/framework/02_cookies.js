Framework.registerModule("Cookies", {

	get: function(key) {
		var matches = document.cookie.match(new RegExp("(;|^)" +key+ "=(.+)(;|$)", "i"));
		return matches && matches[2] || null;
	},
	
	set: function(key,value,days) {
		var value=escape(value);
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
