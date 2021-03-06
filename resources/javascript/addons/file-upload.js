/*
@dep "eventable-object"
@dep "unfinished-work"
*/

var FileDropBackend = Class.create(EventableObject, {
	initialize: function(params) {
		this.target = false;
		if(!params.targetElement)
			throw "Target Required";

		//this.acceptPattern = params.acceptPattern || /^.*$/;
		this.autoUpload = params.autoUpload || false;
		this.setTarget(params.targetElement);
		this.targetURL = params.url || params.targetURL || false;
		this.postName = params.postName || params.fileField || "upload";
		this.postVars = params.postVars || {};
		this.getVars = params.getVars || {};
		
		if(!this.targetURL)
			throw "Upload URL Required";
	}
});
var FileUploadBackend = Class.create(EventableObject, {
	formatSize: function(){
		var suffix = "B";
		var size = this.file.size;
		if(size > 1024) {
			suffix = "KB";
			size /= 1024;
		}
		if(size > 1024) {
			suffix = "MB";
			size /= 1024;
		}
		if(size > 1024) {
			suffix = "GB";
			size /= 1024;
		}
		return (Math.round(size * 100) / 100) + suffix;
	}
});
if(window.FormData && window.File && window.XMLHttpRequest) { // HTML5 Backend
	var FileUpload = Class.create(FileUploadBackend, {
		initialize: function(file, params){
			if(params.formObject) {
				this.formData = new FormData(params.formObject);
				this.targetURL = params.formObject.readAttribute("action");
			} else {
				this.file = file;
				this.targetURL = params.url || params.targetURL;
				this.formData = new FormData();
				this.formData.append(params.fileField || "upload", this.file);
				if(params.postVars)
					for(var name in params.postVars)
						this.formData.append(name, params.postVars[name]);
			}
		},
		addFormEntry: function(variable, value){
			this.formData.append(variable, value);
		},
		start: function(){
			if(FileUpload.ACTIVE_UPLOADS >= FileUpload.MAX_SYNCHRONOUS) {
				FileUpload.QUEUE.push(this);
				return;
			} else
				FileUpload.ACTIVE_UPLOADS++;
		
			this.trigger("started");
			this.uploadWork = new UnfinishedWork("A File Upload is in Progress,\nAbort it?");
			this.req = new XMLHttpRequest();
			
			this.req.open("POST", this.targetURL);
			(this.req.upload || this.req).onprogress = this.progress.bind(this);
			this.req.onreadystatechange = this.statechange.bind(this);  
			this.req.send(this.formData);
		},
		abort: function(){
			this.req.abort();
		},
		progress: function(e){
			var position = e.position || e.loaded;
			var total = e.totalSize || e.total;
			this.trigger("progress", {"loaded": position, "total": total});
		},
		statechange: function(e) {  
			if (this.req.readyState == 4) 
			{  
				this.uploadWork.finish();
				this.uploadWork = null;

				FileUpload.ACTIVE_UPLOADS--;
				if(FileUpload.QUEUE.length)
					FileUpload.QUEUE.shift().start();

				var eventObject = {responseText: this.req.responseText};
				if(this.req.status == 200)
					this.trigger("complete", eventObject);
				else
					this.trigger("error", eventObject);
			}  
		}
	});
	var FileDrop = Class.create(FileDropBackend, {
		setTarget: function(element){
			this.target = $(element);
			if(!this.target)
				throw "Invalid Target";
			
			element.observe("dragenter", this.dragEnter.bind(this));
			element.observe("dragleave", this.dragLeave.bind(this));
			element.observe("dragover", this.dragOver.bind(this));
			element.observe("drop", this.drop.bind(this));
			this.target = element;
		},
		dragEnter: function(e){
			this.target.addClassName("dropHover");
			console.log("dragEnter");
			e.stop();
		},
		dragLeave: function(e){
			//console.log(e.element());
			//console.log(this.target);
			//if(e.element() != this.target)
			//	return;

			this.target.removeClassName('dropHover');
			e.stop();
		},
		dragOver: function(e){
			e.stop();
		},
		drop: function(e){
			console.log("drop");
			e.preventDefault();
			e.stopPropagation();
			this.target.removeClassName("dropHover");
			
			$A(e.dataTransfer.files).each(function(file){
				var upload = new FileUpload(file, {
									"targetURL": this.targetURL,
									"fileField": this.fileField,
									"postVars": this.postVars
											});
				this.trigger("uploadReady", {"upload": upload});
				if(this.autoUpload)
					upload.start();
			}, this);
		}
	});
} else {
	console.error("HTML5 Features Unavailable");
}

FileUpload.MAX_SYNCHRONOUS = 2;
FileUpload.ACTIVE_UPLOADS = 0;
FileUpload.QUEUE = [];
FileUpload.setMaximumUploads = function(num){
	FileUpload.MAX_SYNCHRONOUS = num;
}
