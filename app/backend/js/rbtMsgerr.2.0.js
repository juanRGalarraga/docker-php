;
/*
	rbtMsgerr 2.0.
	Esta es la versión sin jQuery de msgerr ver 3.0
	
	Author: Juampa.
	Created: 2021-06-25
	Desc: 
		Para mostrar mensajes en los inputs y select con un item dentro del elemento
		Cómo se usa:
			ele.msgerr('Este es el mensaje de error');	
		Donde ele es un elemento HTML de tipo INPUT, SELECT o TEXTAREA.
		Para establecer opciones:
			ele.msgerr({msg:'Este es el mensaje de error',pos:'tl'});
*/
HTMLElement.prototype.msgerr = function (params) {
	//console.log('I\'m a '+this.tagName);
	if (['INPUT','SELECT','TEXTAREA'].indexOf(this.tagName) < 0) { // Solamente actuar en estos elementos.
		return false;
	}
	if (this.type == 'hidden' || this.disabled || this.readonly) { // No mostrar mensajes en elementos desactivados o escondidos.
		return false;
	}
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

	var theSpan = document.createElement('i');
	theSpan.setAttribute("class","fas fa-exclamation-triangle text-danger");
	theSpan.setAttribute('id',thisId);
	theSpan.setAttribute('title',params["msg"]);
	theSpan.setAttribute('data-toggle',"tooltip");
	theSpan.setAttribute('data-placement',"top");
	var theTimer = null;
	var theInput = this;
	
	var Show = function () {
		theInput.classList.add("border-danger");
		theInput.parentElement.classList.add("position-relative");
		theInput.parentElement.appendChild(theSpan);
		theSpan.setAttribute("style","top: 75%; transform: translate(-60%, -89%); right: 30px; position: absolute; padding: 5px;  background-color: #FFF;");
		// $(theSpan).tooltip();
	}
	var Hide = function () {
		theInput.classList.remove("border-danger");
		if (theSpan && theSpan.parentElement) {
			theSpan.parentElement.removeChild(theSpan);
			theSpan = null;
		}
	}
	var VerError = function(){
		$(theSpan).tooltip("show");
	}

	theInput.addEventListener('focus', function () { Hide(); });
	theInput.addEventListener('click', function () { Hide(); });
	theSpan.addEventListener('click', VerError);
	Show();
	return false;
};