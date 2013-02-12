function findChildByTag(el, tag){
	var child = el.firstChild;
	if(child == null)
		return null;
	
	do {
		if(child.tagName == tag)
			return child;
	} while((child = child.nextSibling) != null)
	
	return null;
}

function findChildrenByTag(el, tag){
	var children = [];
	var child = el.firstChild;
	if(child == null)
		return children;
	
	do {
		if(child.tagName == tag)
			children.push(child);
	} while((child = child.nextSibling) != null)
	
	return children;
}
