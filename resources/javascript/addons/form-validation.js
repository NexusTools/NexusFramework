function ucwords (str) {
    // Uppercase the first character of every word in a string  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/ucwords
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Waldo Malqui Silva
    // +   bugfixed by: Onno Marsman
    // +   improved by: Robin
    // +      input by: James (http://www.james-bell.co.uk/)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: ucwords('kevin van  zonneveld');
    // *     returns 1: 'Kevin Van  Zonneveld'
    // *     example 2: ucwords('HELLO WORLD');
    // *     returns 2: 'HELLO WORLD'
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}

FormValidation = Class.create({

	hideError: function(el){
		if(el.errorElement)
			el.errorElement.hide();
	},

	showError: function(el, mess){
		console.log(mess);
		if(!el.errorElement) {
			el.errorElement = document.createElement("span");
			el.errorElement.style.fontSize = "10px";
			el.errorElement.style.color = "red";
			el.errorElement.style.marginLeft = "8px";
			el.insert({
				after: el.errorElement
			});
		}
		
		el.errorElement.innerHTML = "*" + mess;
		el.errorElement.show();
	},

	handleSubmit: function(e){
		var thisValidator = this;
	
		var badElement = false;
		console.log(this.form);
		this.form.select("input, select, textarea").each(function(element){
			var empty = true;
			try{
				empty = !element.getValue() || element.getValue().trim().length == 0;
			}catch(e){
				console.log(e);
			}
			
			if(element.hasAttribute("required") && empty)
				thisValidator.showError(element, "Required");
			else if(!empty) {
				if(element.hasAttribute("pattern") && !new RegExp(element.getAttribute("pattern")).match(element.getValue()))
					thisValidator.showError(element, element.hasAttribute("title")
								? element.getAttribute("title") : "Invalid Format");
				else {
					thisValidator.hideError(element);
					return;
				}
			} else {
				thisValidator.hideError(element);
				return;
			}
			
			if(!element.errorLIsten){
				element.errorLIsten = true;
				element.observe("keypress", function(e){
					element.style.border = "";
					thisValidator.hideError(element);
				});
				element.onchange = function(e){
					element.style.border = "";
					thisValidator.hideError(element);
				};
			}
			
			element.style.border = "solid 1pt #dd6666";
			if(!badElement)
				badElement = element;
		});
	
	
		if(badElement) {
			badElement.focus();
			e.stop();
			return false;
		}// else {
		//	this.form.select("input.text").each(function(input){
		//		input.setValue(ucwords(input.getValue().toLowerCase()));
		//	});
		//}
	},

	initialize: function(form){
		this.form = form;
		$(form).observe("submit", this.handleSubmit.bind(this));
	}
	
});

FormValidation.SetupPageForms = function(){
	Framework.PageElement.select("form").each(function(form){
		new FormValidation(form);
	});
}

FormValidation.SetupPageForms();
//Framework.PageElement.observe("pagesys:ready", );
