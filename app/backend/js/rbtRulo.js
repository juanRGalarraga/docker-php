;
/**
	fmdRulo 2.0
	
	Pone un rulo de espera. Pensado para mostrar al usuario un rulo de espera mientras se hace una petición Ajax... o un proceso muy largo.
	
	Created: 2021-03-14
	Author: DriverOp
	Desc: 
		Versión 2.0 del rulo.
*/

var theRuloStyle = document.createElement('style');
theRuloStyle.setAttribute('media','screen');
theRuloStyle.setAttribute('type','text/css');
theRuloStyle.setAttribute('id','rbtRuloStyles');
theRuloStyle.appendChild(document.createTextNode(""));
theRuloStyle.innerHTML = `div.rbtRuloVail{display:block;position:absolute;width:100%;height:100%;top:0;left:0;background-color:rgba(0,0,0,0.5);z-index:5000;}div.rbtRuloMain {position:absolute;background-position:center center;background-repeat:no-repeat;z-index:5001;padding:5px;min-width:25px;min-height:25px;border-radius:3px;background-color:transparent;}div.rbtRuloMain>img{display:block;margin:auto;max-width:200px;}div>p.rbtRuloMsg{font-size:10px;color:#333;font-weight:normal;line-height:12px;margin:3px 0 0 0;text-align: center;}`;
document.addEventListener('DOMContentLoaded', ()=>{document.head.appendChild(theRuloStyle);});



var rbtRulo = function (param) {
	this.defaultOptions = {
		ownClass: 'rulo',
		idPrefix: 'rulo_',
		id: '01',
		message: '',
		animateImage: 'imgs/indicator.gif'
	}


	this.options = Object.assign({},this.defaultOptions,param);
	var idTheRulo = this.options.idPrefix+this.options.id;

	this.theVail = document.createElement('DIV');
	this.theVail.classList.add('rbtRuloVail');

	this.theCurl = document.createElement('DIV');
	this.theCurl.setAttribute('id',idTheRulo);
	this.theCurl.classList.add('rbtRuloMain');
	//this.theCurl.style.backgroundImage = 'url("'+this.options.animateImage+'")';
	this.theCurl.classList.add(this.options.ownClass);
	
	this.theImg = document.createElement('IMG');
	this.theImg.setAttribute('src',this.options.animateImage);
	
	this.theMsg = document.createElement('P');
	this.theMsg.classList.add('rbtRuloMsg');
	this.messageInserted = false;

	this.theCurl.appendChild(this.theImg);

	var TheRulo = this;
	var theContainer = null;

/**
* Summary. Muestra el rulo en el elemento señalado y el mensaje indicado.
* @param mixed selector Puede ser un string con selector CSS o una referencia a un elemento, si es nulo, entonces se asume el BODY del documento.
* @param string message Es el mensaje opcional que se mostrará con el rulo.
*/
	this.Show = function (selector, message) {
		TheRulo.theContainer = TheRulo.getContainer(selector);
		
		if (TheRulo.theContainer == false || TheRulo.theContainer == null) { return false; }
		if (message && (typeof message == 'string')) {
			TheRulo.options.message = message;
		}
		
		if (TheRulo.options.message != '') {
			TheRulo.theMsg.innerHTML = TheRulo.options.message;
			if (!TheRulo.messageInserted) {
				TheRulo.theCurl.appendChild(TheRulo.theMsg);
				TheRulo.messageInserted = true;
			}
		} else if (TheRulo.messageInserted) {
			TheRulo.theCurl.removeChild(TheRulo.theMsg);
			TheRulo.messageInserted = false;
		}
		
		TheRulo.theCurl.style.top = '101%';
		TheRulo.theCurl.style.left = '101%';
		document.getElementsByTagName("BODY")[0].appendChild(TheRulo.theCurl);
		
		rh = TheRulo.theCurl.clientHeight;
		rw = TheRulo.theCurl.clientWidth;
		
		eleh = TheRulo.theContainer.clientHeight;
		elew = TheRulo.theContainer.clientWidth;
		
		rtop = parseInt((eleh/2)-(rh/2))+'px';
		rleft = parseInt((elew/2)-(rw/2))+'px';
		
		TheRulo.theCurl.style.top = rtop;
		TheRulo.theCurl.style.left = rleft;
		
		if (TheRulo.options.zIndex !== null && TheRulo.options.zIndex !== undefined) {
			TheRulo.theVail.style.zIndex = parseInt(TheRulo.options.zIndex)-1;
			TheRulo.theCurl.style.zIndex = parseInt(TheRulo.options.zIndex);
		}
		
		TheRulo.theContainer.appendChild(TheRulo.theVail);
		TheRulo.theContainer.appendChild(TheRulo.theCurl);
	}
/**
* Summary. Obtener efectivamente el elemento HTML apuntado por selector, falling back al body del documento.
*/
	this.getContainer = function (selector) {
		var result = document.getElementsByTagName("BODY")[0];
		if ((typeof selector == 'object') && (selector instanceof HTMLElement)) {
			result = selector;
		}
		if (typeof selector == 'string') {
			result = document.querySelector("#"+selector);
		}
		if (result !== null) {
			if (['none','inline'].includes(window.getComputedStyle(result).display)) {
				return false;
			}
			if (window.getComputedStyle(result).visibility == 'hidden') {
				return false;
			}
			if (result) {
				result.style.position = 'relative';
			}
		}
		return result;
	}
/**
* Summary. Remover el rulo del contenedor.
*/
	this.Hide = function () {
		if (TheRulo.theContainer == null) { return; }
		if (TheRulo.theCurl.parentNode == TheRulo.theContainer) {
			TheRulo.theContainer.removeChild(TheRulo.theCurl);
			TheRulo.theContainer.removeChild(TheRulo.theVail);
		}
	}

}

var rulo = new rbtRulo();
