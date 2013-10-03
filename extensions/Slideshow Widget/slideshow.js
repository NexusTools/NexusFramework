imgcontainer = $$("slideshow_widget")[0];
lastChild = imgcontainer.lastChild;
imgelements = imgcontainer.select("img");
imgelements.each(function(img){
	img.style.display = "none";
	if(img.hasAttribute("url")) {
		img.style.cursor = "pointer";
		img.observe("click", function(e){
			location.href = e.element().getAttribute("url");
		});
	}
});

imgelements[0].style.display = "";
visibleElement = 0;

transitions = [
/*

	{ // Horizontal Leaves
		animate: function(transitionHelper){
			var sectionHeight = Math.ceil(transitionHelper.getViewportHeight() / 10);
			var offsetY = 0;
			var timeout = 0;

			for(var i=0; i < 10; i++) {

				var leaf = document.createElement("div");
				leaf.style.position = "absolute";

				leaf.style.overflow = "hidden";
				leaf.style.left = "0px";
				leaf.style.top = offsetY + "px";

				leaf.style.width = transitionHelper.getViewportWidth() + "px";
				leaf.style.height = sectionHeight + "px";
				var leafImage = transitionHelper.cloneImage();

				leafImage.style.height = transitionHelper.getViewportHeight() + "px";
				leafImage.style.position = "relative";
				leafImage.style.top = -offsetY + "px";

				leaf.appendChild(leafImage);
				transitionHelper.appendChild(leaf);

				this.animateLater(leaf, transitionHelper.getViewportWidth() * (i%2 == 0 ? -1 : 1), timeout);
				offsetY += sectionHeight;
				timeout += 50;

			}
		},
	

		animateLater: function(leaf, toX, timeout){
			setTimeout(function(){

							NexusTools.Animator.moveToX(leaf, toX, 0, function(){
											this.parentNode.removeChild(this);
										});

						}, timeout);
		}
	},

	{ // Vertical Leaves

		animate: function(transitionHelper){
			var sectionWidth = Math.ceil(transitionHelper.getViewportWidth() / 10);
			var offsetX = 0;
			var timeout = 0;
			for(var i=0; i < 10; i++) {
				var leaf = document.createElement("div");
				leaf.style.position = "absolute";
				leaf.style.overflow = "hidden";
				leaf.style.left = offsetX + "px";
				leaf.style.top = "0px";
				leaf.style.height = transitionHelper.getViewportHeight() + "px";
				leaf.style.width = sectionWidth + "px";
				var leafImage = transitionHelper.cloneImage();
				leafImage.style.width = transitionHelper.getViewportWidth() + "px";
				leafImage.style.position = "relative";
				leafImage.style.left = -offsetX + "px";
				leaf.appendChild(leafImage);
				transitionHelper.appendChild(leaf);
				this.animateLater(leaf, -transitionHelper.getViewportHeight(), timeout);
				offsetX += sectionWidth;
				timeout += 50;

			}
		},

	
		animateLater: function(leaf, toY, timeout){
			setTimeout(function(){

							NexusTools.Animator.moveToY(leaf, toY, 0, function(){
											this.parentNode.removeChild(this);
										});

						}, timeout);
		}

	},
	{ // Vertical Blinds
		animate: function(transitionHelper){

			var sectionWidth = Math.ceil(transitionHelper.getViewportWidth() / 10);
			var offsetX = 0;
			for(var i=0; i < 10; i++) {

				var leaf = document.createElement("div");
				leaf.style.position = "absolute";

				leaf.style.overflow = "hidden";
				leaf.style.left = offsetX + "px";
				leaf.style.top = "0px";

				leaf.style.height = transitionHelper.getViewportHeight() + "px";
				leaf.style.width = sectionWidth + "px";
				var leafImage = transitionHelper.cloneImage();

				leafImage.style.width = transitionHelper.getViewportWidth() + "px";
				leafImage.style.position = "relative";
				leafImage.style.left = -offsetX + "px";
				leaf.appendChild(leafImage);
				transitionHelper.appendChild(leaf);

			
				NexusTools.Animator.resizeToWidth(leaf, 0, sectionWidth, function(){
							this.parentNode.removeChild(this);

						});
				offsetX += sectionWidth;
			}

		}
	}, */

	{ // Grid Fade
		animate: function(transitionHelper){
			var sectionWidth = Math.ceil(transitionHelper.getViewportWidth() / 5);
			var sectionHeight = Math.ceil(transitionHelper.getViewportHeight() / 5);
			var offsetX = 0;
			for(var x=0; x < 5; x++) {
				var offsetY = 0;
				for(var y=0; y < 5; y++) {
					var chunk = document.createElement("div");
					chunk.style.position = "absolute";
					chunk.style.overflow = "hidden";
					chunk.style.top = offsetY + "px";
					chunk.style.left = offsetX + "px";
					chunk.style.width = sectionWidth + "px";
					chunk.style.height = sectionHeight + "px";
					var chunkImage = transitionHelper.cloneImage();
					chunkImage.style.width = transitionHelper.getViewportWidth() + "px";
					chunkImage.style.height = transitionHelper.getViewportHeight() + "px";
					chunkImage.style.position = "relative";
					chunkImage.style.top = -offsetY + "px";
					chunkImage.style.left = -offsetX + "px";
					chunk.appendChild(chunkImage);
					transitionHelper.appendChild(chunk);

			
					this.animateLater(chunk);

					offsetY += sectionHeight;
				}

				offsetX += sectionWidth;
			}
		},

	
		animateLater: function(chunk){
				

			setTimeout(function(){
				NexusTools.Animator.fade(chunk, 0, 1, function(){

					this.parentNode.removeChild(this);
				});
			}, 10 + Math.random() * 500);

		}
	}/*,
	{ // Grid Radius & Shrink
		animate: function(transitionHelper){
			var sectionWidth = Math.ceil(transitionHelper.getViewportWidth() / 5);
			var sectionHeight = Math.ceil(transitionHelper.getViewportHeight() / 5);
			var midWidth = Math.ceil(sectionWidth / 2);
			var midHeight = Math.ceil(sectionHeight / 2);
			var timeout = 0;
			var offsetX = 0;
			for(var x=0; x < 5; x++) {
				var offsetY = 0;
				for(var y=0; y < 5; y++) {
					var chunk = document.createElement("div");
					chunk.style.position = "absolute";
					chunk.style.overflow = "hidden";
					chunk.style.top = offsetY + "px";
					chunk.style.left = offsetX + "px";
					chunk.style.width = sectionWidth + "px";
					chunk.style.height = sectionHeight + "px";
					var chunkImage = transitionHelper.cloneImage();

					chunkImage.style.width = transitionHelper.getViewportWidth() + "px";

					chunkImage.style.height = transitionHelper.getViewportHeight() + "px";
					chunkImage.style.position = "relative";
					chunkImage.style.top = -offsetY + "px";

					chunkImage.style.left = -offsetX + "px";
					chunk.appendChild(chunkImage);

					transitionHelper.appendChild(chunk);
				

					this.animateLater(chunk, chunkImage, timeout, sectionWidth, sectionHeight, midWidth, midHeight, offsetX, offsetY);
					timeout += 50;
				

					
				
					offsetY += sectionHeight;
				}
				offsetX += sectionWidth;
			}
		},
	
		animateLater: function(chunk, chunkImage, timeout, sectionWidth, sectionHeight, midWidth, midHeight, offsetX, offsetY){
			setTimeout(function(){
				NexusTools.Animator.borderRadiusTo(chunk, sectionWidth > sectionHeight ? sectionHeight : sectionWidth, 0, function(){
							NexusTools.Animator.moveTo(chunk, offsetX + midWidth, offsetY + midHeight, offsetX, offsetY);
							NexusTools.Animator.moveTo(chunkImage, -offsetX - midWidth, -offsetY - midHeight, -offsetX, -offsetY);
							NexusTools.Animator.resizeTo(chunk, 0, 0, sectionWidth, sectionHeight, function(){
									this.parentNode.removeChild(this);
								});
						});

			}, timeout);
		}
	} */
];

transitionHelper = function(viewport, lastChild, oldImage) {
	this.getViewportWidth = function(){
		return viewport.clientWidth;
	}
	this.getViewportHeight = function(){
		return viewport.clientHeight;
	}
	this.appendChild = function(element){
		if(lastChild == viewport.lastChild)
			viewport.appendChild(element);
		else
			viewport.insertBefore(element, lastChild.nextSibling);
	}
	this.removeChild = function(element){
		viewport.removeChild(element);
	}
	this.cloneImage = function(){
		var clone = oldImage.cloneNode(false);
		clone.style.display = "none";
		setTimeout(function(){
			clone.style.display = "";

		}, 20);
		return clone;
	}

}

updateActiveSlide = function(oldImage){
	var newImage = imgelements[visibleElement];
	var transition = transitions[Math.floor(Math.random() * transitions.length)];
	transition.animate.call(transition, new transitionHelper(imgcontainer, lastChild, oldImage));
	setTimeout(function(){
		newImage.style.display = "";
		oldImage.style.display = "none";
	}, 42);
	//resetTimer();
},
nextSlide = function(){
	var oldImage = imgelements[visibleElement];

	visibleElement++;
	if(visibleElement >= imgelements.length)
		visibleElement = 0;

	updateActiveSlide(oldImage);
	autoplayTimeout = setTimeout(nextSlide, 5000);
}

autoplayTimeout = setTimeout(nextSlide, 5000);

//javascript:alert($("slideshow_widget"));void(0);

/*

var slideshow = {
	init: function(helper) {
		this.imgcontainer = document.getElementById("slideshow_slide_container");
		imgelements = [];
		this.slideIndex = document.getElementById("slideshow_slide_index");
		this.slideTitle = document.getElementById("slideshow_slide_title");
		this.lastChild = this.imgcontainer.lastChild;
		for(var i = 0; i < this.imgcontainer.childNodes.length; i++){
			if(this.imgcontainer.childNodes[i].nodeName != "IMG")
				continue;
			
			if(imgelements.length)
				this.imgcontainer.childNodes[i].style.display = "none";
			imgelements.push(this.imgcontainer.childNodes[i]);
		}
		this.__preloadImagesNear(0);
	
		this.slideIndex.innerHTML = "1 of " + imgelements.length;
		this.slideTitle.innerHTML = imgelements[0].getAttribute ? imgelements[0].getAttribute("title") : imgelements[0].title;
		this.animatedElements = [];
		visibleElement = 0;
		var thisObject = this;
		this.autoplay = true;
		this.locked = false;
		this.autoplayTimeout = setTimeout(function(){
			thisObject.autoplaySlide.call(thisObject);
		}, 5000);
	},

	__preloadImageAt: function(index){
		if(index < 0)
			index = imgelements.length + index;
		if(index >= imgelements.length)
			index -= imgelements.length;
		
	
		var image = imgelements[index];
		if(image.hasAttribute("url")){
			image.setAttribute("src", image.getAttribute("url"));
			image.removeAttribute("url");
		}
	},
	__preloadImagesNear: function(index){
		this.__preloadImageAt(index);
		this.__preloadImageAt(index+1);
		this.__preloadImageAt(index-1);
	},

	,
	__,
	,
	prevSlide: function(){
		var oldImage = imgelements[visibleElement];

		visibleElement--;
		if(visibleElement < 0)
			visibleElement = imgelements.length - 1;

		this.__updateActiveSlide(oldImage);
	},
	resetTimer: function(){
		clearTimeout(this.autoplayTimeout);
		if(this.autoplay){
			var thisObject = this;
			this.autoplayTimeout = setTimeout(function(){
				thisObject.autoplaySlide.call(thisObject);
			}, 5000);
		}
	},
	autoplaySlide: function(){
		this.nextSlide();
		var thisObject = this;
		clearTimeout(this.autoplayTimeout);
		this.autoplayTimeout = setTimeout(function(){
			thisObject.autoplaySlide.call(thisObject);
		}, 5000);
	},
	toggleAutoPlay: function(helper, e){
		clearTimeout(this.autoplayTimeout);
		this.autoplay = helper.eventTarget(e).checked;
		if(this.autoplay) {
			var thisObject = this;
			this.autoplayTimeout = setTimeout(function(){
				thisObject.autoplaySlide.call(thisObject);
			}, 5000);
		}
	}
}

slideshow.init();
*/
