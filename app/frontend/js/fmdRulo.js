
/*
	fmdRulo 1.0
	Created: 2019-10-25
	Author: DriverOp
	Desc: Pone un rulo de espera. Pensado para mostrar al usuario un rulo de espera mientras se hace una petición Ajax... o un proceso muy largo.
	Modif: 2019-11-12
	Desc: Cuando a Show() no se le pasa ningún elemento, pone el rulo en el body.
	Modif: 2019-11-18
	Desc: Respetar el style.position del contenedor para no causar problemas con otros scripts.
*/

var fmdRulo = function (param) {

	this.defaultOptions = {
		ownClass: 'rulo',
		idPrefix: 'rulo_',
		id: '01',
		message: ''
	}
	
	this.options = Object.assign({},this.defaultOptions,param);
	var idTheRulo = this.options.idPrefix+this.options.id;
	
	this.theVail = document.createElement('DIV');
	this.theVail.setAttribute("style","display:block;position:absolute;width:100%;height:100%;top:0;left:0;background-color:rgba(0,0,0,0.5);z-index:5000;");

	this.theRulo = document.createElement('DIV');
	this.theRulo.setAttribute('id',idTheRulo);
	this.theRulo.setAttribute("style","display:block;position:absolute;background-position:center center;background-repeat:no-repeat;z-index:5001;min-width:25px;min-height:25px;background-color:white;border-radius:3px;border:1px solid #ccc;");
	
	this.theRulo.style.backgroundImage = 'url("imgs/indicator.gif")';
	this.theRulo.classList.add(this.options.ownClass);
	
	var Instance = this;
	var theContainer = null;

	this.Show = function (selector, message) {
		if (typeof selector == 'string') {
			var ele = document.getElementById(selector);
			if ((ele == null) || (typeof ele == 'undefined')) {
				ele = document.querySelector(selector);
			}
		}
		if ((typeof ele == 'undefined') || (ele == null)) {
			var ele = document.getElementsByTagName("BODY")[0];
		}
		if (typeof message == 'string') {
			Instance.options.message = message;
		}
		
		Instance.theContainer = ele;
		
		if ((ele.style.position == 'static') || (ele.style.position == '')) {
			ele.style.position = 'relative';
		}
		
		rh = Instance.theRulo.clientHeight;
		rw = Instance.theRulo.clientWidth;
		
		eleh = ele.clientHeight;
		elew = ele.clientWidth;
		
		rtop = parseInt((eleh/2)-(rh/2))+'px';
		rleft = parseInt((elew/2)-(rw/2))+'px';
		
		Instance.theRulo.style.top = rtop;
		Instance.theRulo.style.left = rleft;
		
		ele.appendChild(Instance.theVail);
		ele.appendChild(Instance.theRulo);
	}
	
	this.Hide = function () {
		if (Instance.theContainer == null) { return; }
		if (Instance.theRulo.parentNode == Instance.theContainer) {
			Instance.theContainer.removeChild(Instance.theRulo);
			Instance.theContainer.removeChild(Instance.theVail);
		}
	}
}