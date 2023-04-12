;
/*
	rbtMsgerr 1.0.
	Esta es la versión sin jQuery de msgerr ver 2.0
	
	Author: Rebrit.
	Created: 2021-02-07
	Desc: 
		Para mostrar mensajes en los inputs y select en forma de globo encima de ellos con la intención de señalar errores en la entrada de datos.
		Necesita de una clase css 'balloon' para formatear el globo con el mensaje (msgerr.balloon.css).
		Cómo se usa:
		
			ele.msgerr('Este es el mensaje de error');
			
		Donde ele es un elemento HTML de tipo INPUT, SELECT o TEXTAREA.
		Para establecer opciones:
		
			ele.msgerr({msg:'Este es el mensaje de error',pos:'tl'});
*/

var msgerrDelay = 5; // En segundos
var msgerrTopShift = 30; // En pixeles

HTMLElement.prototype.msgerr = function (params) {
	//console.log('I\'m a '+this.tagName);
	if (['INPUT','SELECT','TEXTAREA'].indexOf(this.tagName) < 0) { // Solamente actuar en estos elementos.
		return false;
	}
	if (this.type == 'hidden' || this.disabled || this.readonly) { // No mostrar mensajes en elementos desactivados o escondidos.
		return false;
	}
	var defaultOptions = {
		cssclass: 'balloon',
		msg: '',
		pos: 'bm',
		exclam: '<i class="fas fa-exclamation-triangle exclamation"></i> ',
		delay: msgerrDelay*1000
	};
	if (typeof params == 'string') {
		params = {msg:params}
	}
	if (this.dataset.msgerrId) {
		thePrevious = document.getElementById(this.dataset.msgerrId);
		if (thePrevious) {
			thePrevious.parentElement.removeChild(thePrevious); // Eliminar el mensaje anterior que el elemento podría tener.
		}
	}
	var thisId = this.id;
	if (thisId == '') {
		thisId = this.name+(Math.floor(Math.random()*100000) + 1);
	}
	
	thisId = 'rbtMsgerr-'+thisId;
	this.dataset.msgerrId = thisId;
	var settings = Object.assign({}, defaultOptions, params);

				var theSpan = document.createElement('SPAN');
				theSpan.classList.add(settings.cssclass);
				theSpan.classList.add(settings.pos);
				theSpan.setAttribute('id',thisId);
				var theTimer = null;
				var theInput = this;
				
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
				
				var Relocate = function () {
					var rect = theInput.getOffsetRect(theInput);
					var Left = (theInput.clientWidth/2) + rect.left;
					theSpan.style.top = (rect.top-(rect.height+theSpan.clientHeight+msgerrTopShift)) + "px";
					Left = Left - (theSpan.clientWidth/2);
					
					theSpan.style.left = parseInt(Left) + "px";
				}
				
				var Show = function () {
					
					var rect = getOffsetRect(theInput);
					var Left = (theInput.clientWidth/2) + rect.left;
					
					theSpan.innerHTML = settings.exclam+settings.msg;
					theSpan.style.top = '101%';
					
					document.body.appendChild(theSpan);
					
					theSpan.style.top = (rect.top-(rect.height+theSpan.clientHeight+msgerrTopShift)) + "px";
					
					
					
					Left = Left - (theSpan.clientWidth/2);
					
					theSpan.style.left = parseInt(Left) + "px";
					theSpan.style.opacity = '0.9';
					if ((settings.delay != null) && (!isNaN(parseInt(settings.delay,10)))) {
						theTimer = setTimeout(
							Hide,
						settings.delay);
					}
					
				}
				
				var Hide = function () {
					if (theSpan && theSpan.parentElement) {
						theSpan.parentElement.removeChild(theSpan);
						theSpan = null;
					}
					theInput.classList.remove('olred');
				}
				
				theInput.addEventListener('focus', function () { Hide(); });
				theInput.addEventListener('click', function () { Hide(); });
				
				theSpan.addEventListener('click', Hide);
				window.addEventListener('resize', Relocate);
				window.addEventListener('scroll', function (e){
					if (window.scrollY != theInput.actualpos.top) {
						rect = theInput.getOffsetRect(theInput);
						theSpan.style.top = (rect.top-theSpan.clientHeight) + "px";
						theInput.actualpos.top = window.scrollY;
					}
				});

				Show();
				theInput.classList.add('olred');
	return false;
}
;