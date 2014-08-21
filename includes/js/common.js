// JavaScript Document
var common__productImageMouseDownStatus = false;
var common__productImageMouseX = 0;
var common__productImageMouseY = 0;
function common__productImageMouseDown(evnt) {
	evnt.stop();
	common__productImageMouseDownStatus = true;
	common__productImageMouseX = evnt.pointerX();
	common__productImageMouseY = evnt.pointerY();
}

function common__productImageMouseUp(evnt) {
	evnt.stop();
	common__productImageMouseDownStatus = false;
}

function common__productImageMouseMove(evnt) {
	evnt.stop();
	if(!common__productImageMouseDownStatus) return;

	var mouseDeltaX = evnt.pointerX() - common__productImageMouseX;
	var mouseDeltaY = evnt.pointerY() - common__productImageMouseY;
	//console.log(mouseDeltaX+", "+mouseDeltaY);

	this.style.left = (parseFloat(this.style.left) + mouseDeltaX) + "px";
	this.style.top	= (parseFloat(this.style.top)  + mouseDeltaY) + "px";

	common__productImageMouseX = evnt.pointerX();
	common__productImageMouseY = evnt.pointerY();
}

function common__productImageScroll(evnt) {
	evnt = evnt == null ? Event.extend(window.event) : evnt;
	Event.stop(evnt);
	/**
	 * Event::wheelDelta is IE & WebKit
	 * Event::detail is Firefox.
	 * Firefox units are a factor of 40 smaller than
	 * those used by IE & WebKit, and are also sign-inverse
	 */
	var wheelDelta = typeof(evnt.wheelDelta) != 'undefined' ? evnt.wheelDelta : (evnt.detail * -40.0);
	/**
	 * OK after we've 'normalized' the mouse-wheel amount
	 * we're in a position where each "tick" is 120,
	 * with "up" or "away from the user" being positive.
	 */

	var imageWidth = parseFloat($('imageWidth').value);
	var imageHeight = parseFloat($('imageHeight').value);
	var oldScaleFactor = parseFloat(this.style.width) / imageWidth;
	var newScaleFactor = oldScaleFactor + (wheelDelta/25000.0);
	this.style.width = (imageWidth * newScaleFactor)+"px";
	this.style.height = (imageHeight * newScaleFactor)+"px";
}

function common__productImageSaveCrop(evnt) {
	try {
		//alert(evnt);
		//alert(window.location.pathname);
		var image = $('headerImage');
		var transX = parseFloat(image.style.left);
		var transY = parseFloat(image.style.top);
		var scale = parseFloat(image.style.width) / parseFloat($('imageWidth').value);

		
		var form = new Element("form");
		form.action = "/admin/images/crop_process";
		form.method = "post";
		form.style.display = "none";
		document.body.appendChild(form);
		
		form.appendChild($('imageID'));
		
		var scaleInp  = new Element("input");
		scaleInp.value = scale;
		scaleInp.name = "scale";
		form.appendChild(scaleInp);
		
		var transXInp  = new Element("input");
		transXInp.value = transX;
		transXInp.name = "transX";
		form.appendChild(transXInp);
		
		var transYInp  = new Element("input");
		transYInp.value = transY;
		transYInp.name = "transY";
		form.appendChild(transYInp);
		
		var module = new Element("input");
		pathArray = window.location.pathname.split( '/' );
		var abc = pathArray[5];
		//alert(abc);
		module.value = abc;
		module.name = "module";
		form.appendChild(module);
		
		form.submit();
	}
	catch(e) {
		alert(e);	
	}
}