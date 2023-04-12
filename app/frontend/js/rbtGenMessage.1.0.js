;
var theGenMsgStyle = document.createElement('style');
	theGenMsgStyle.setAttribute('media','screen');
	theGenMsgStyle.setAttribute('type','text/css');
	theGenMsgStyle.setAttribute('id','rbtGenErrStyles');
	theGenMsgStyle.appendChild(document.createTextNode(""));
	theGenMsgStyle.innerHTML = `.rbtGenErr{}`;
document.head.appendChild(theGenMsgStyle);

/*
	rbtGenMessage 1.0.
	Created: 2021-10-04
	Author: DriverOp
	Desc:
		Position generic message attached to an input or textarea or button element.
		guachin
		Params: An object or string like...
			{
				msg: the message (whatever you want to put in there actually).
				topShift: shift from the top of message in pixels, can be negative
				class: the CSS class or classes (space separated) to apply to the message element
			}
*/

HTMLElement.prototype.genMessage = function (params) {
	if (['INPUT','SELECT','TEXTAREA','BUTTON'].indexOf(this.tagName) < 0) { // Solamente actuar en estos elementos.
		return false;
	}
	if (this.type == 'hidden' || this.disabled || this.readonly) { // No mostrar mensajes en elementos desactivados o escondidos.
		return false;
	}
	if (typeof params == 'string') {
		params = {msg:params}
	}
	
	var defaultOptions = {
		msg: 'Default message',
		topShift: 2,
		class: ''
	}
	var settings = Object.assign({}, defaultOptions, params);

	if (this.dataset.genmsgId) {
		thePrevious = document.getElementById(this.dataset.genmsgId);
		if (thePrevious) {
			thePrevious.parentElement.removeChild(thePrevious); // Eliminar el mensaje anterior que el elemento podría tener.
		}
	}
	var thisId = this.id;
	if (thisId == '') {
		thisId = this.name+(Math.floor(Math.random()*100000) + 1);
	}

	thisId = 'rbtGenMsg-'+thisId;
	this.dataset.genmsgId = thisId;
	
	var theInput = this;

	var theSpan = document.createElement('SPAN');
	theSpan.classList.add('rbtGenErr');
	if (settings.class != '') {
		let classes = settings.class.split(' ');
		if (classes.length > 0) {
			classes.forEach((ele)=>{ theSpan.classList.add(ele); });
			
		}
	}
	theSpan.setAttribute('id',thisId);
	theSpan.style.position = 'absolute';
	
	theInput.actualpos = {top:0, left: 0};

	var getOffsetRect = function(elem) {
		var box = elem.getBoundingClientRect()
		var body = document.body
		var docElem = document.documentElement
		var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
		var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft
		var clientTop = docElem.clientTop || body.clientTop || 0
		var clientLeft = docElem.clientLeft || body.clientLeft || 0
		var top = box.top + scrollTop - clientTop
		var left = box.left + scrollLeft - clientLeft
		return { top: Math.round(top), left: Math.round(left), width:box.width, height:box.height }
	} // getOffsetRect

	var Hide = function () {
		if (theSpan && theSpan.parentElement) {
			theSpan.parentElement.removeChild(theSpan);
			theSpan = null;
		}
		theInput.classList.remove('olred');
	} // Hide
	
	var Relocate = function () {
		if (theSpan) { 
			var rect = getOffsetRect(theInput);
			theSpan.style.top = (rect.top-(theSpan.clientHeight+settings.topShift)) + "px";
			var Left = (theInput.clientWidth/2) + rect.left;
			Left = Left - (theSpan.clientWidth/2);
			
			theSpan.style.left = parseInt(Left) + "px";
		}
	} // Relocate

	var Show = function () {
		
		var rect = getOffsetRect(theInput);
		
		
		theSpan.innerHTML = settings.msg;
		theSpan.style.top = '101%';
		
		
		if (window.getComputedStyle(document.body).position != 'relative') {
			document.body.style.position = 'relative';
		}
		
		document.body.appendChild(theSpan);
		if (parseInt(window.getComputedStyle(theSpan).width) > parseInt(window.getComputedStyle(theInput).width)) {
			theSpan.style.maxWidth = window.getComputedStyle(theInput).width;
		}
		
		theSpan.style.top = (rect.top-(theSpan.clientHeight+settings.topShift)) + "px";
		
		
		var Left = (theInput.clientWidth/2) + rect.left;
		Left = Left - (theSpan.clientWidth/2);
		
		theSpan.style.left = parseInt(Left) + "px";
		if ((settings.delay != null) && (!isNaN(parseInt(settings.delay,10)))) {
			theTimer = setTimeout(
				Hide,
			settings.delay);
		}
		
	} // Show

	
	this.addEventListener('focus', function () { Hide(); });
	this.addEventListener('click', function () { Hide(); });
	theSpan.addEventListener('click', Hide);
	window.addEventListener('resize', Relocate);
	Show();
	theInput.classList.add('olred');
	return false;
}
;
