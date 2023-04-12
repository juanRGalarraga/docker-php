<?php
/*
	Versión inicial del creador dinámico de ventanas modal para BootStrap 5.
	Created: 2021-09-23
	Author: DriverOp
*/

?>
;
/*
	modalBs5Creator 1.0β
	Created: 2021-09-23
	Author: DriverOp

*/
if ('undefined' != typeof NodeList.prototype.forEach) NodeList.prototype.forEach = Array.prototype.forEach;
if ('undefined' != typeof String.prototype.replaceAll) String.prototype.replaceAll = function(str1, str2, ignorar) {
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignorar?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
}

var theModalBS5Style = document.createElement('style');
theModalBS5Style.setAttribute('media','screen');
theModalBS5Style.setAttribute('type','text/css');
theModalBS5Style.setAttribute('id','customModalBs5');
theModalBS5Style.appendChild(document.createTextNode(""));
theModalBS5Style.innerHTML = `.modal-dialog.modal-auto{max-width: fit-content !important;}`;
document.addEventListener('DOMContentLoaded', ()=>{document.head.appendChild(theModalBS5Style);});



var modalBs5Creator = function (params) {
	this.defaultOptions = {
		archivo: '',
		content: '',
		extraparams: {},
		extraclass: '',
		centered: false,
		noclose: false,
		size: 'lg',
		windowId: 'modalBs5',
		onShow: null,
		onClose: null,
		lado : '',
		fullscreen: false
	}

	this.waiting = false;
	this.intError = false;
	this.theWindow = null;
	this.theContainder = null;
	this.rulo = null;
	
	this.options = Object.assign({},this.defaultOptions,params);
	this.objAjax = new XMLHttpRequest();
	
	this.Set = function (newParams) {
		this.options = Object.assign({},this.options,newParams);
	}
/**
* Summary. Callback o función de retorno del objeto XMLHttpRequest.
* @note Función flecha para mantener en ámbito "this".
*/
	this.TresTristesTigres = (ev)=> {
		switch (this.objAjax.readyState) {
			case 0:  // nada
				// console.log('No se ha enviado nada todavía.'); 
				break;
			case 1: // Hay una petición que se acaba de abrir con open()
				this.intError = false;
				if (typeof this.options.onOpen == 'function') {
					this.options.onOpen.call(this);
				}
				break;
			case 2: // Se recibieron las cabeceras del servidor.
				if (typeof this.options.onFirstArrive == 'function') {
					this.options.onFirstArrive.call(this);
				}
				break;
			case 3:  // Se recibió un paquete de datos desde el servidor.
				if (typeof this.options.onLoading == 'function') {
					this.options.onLoading.call(this);
				}
				break;
			case 4: // La petición se completó. Esto es lo que nos interesa.
				//console.log('Petición completada');
				this.objResponse = (this.options.parseResponse)?this.parseJson(this.objAjax.responseText):null;
				if (this.objResponse) {
					if (this.objResponse.dataerr) {
						this.intError = true;
						console.log('Se devolvió error de datos: '+this.objResponse.dataerr.toString());
					} else {
						if (this.objResponse.generr) {
							this.intError = true;
							console.log('Se devolvió error general: '+this.objResponse.generr.toString());
						}
					}
				}
<?php
if (DEVELOPE) {
?>
				this.intError = /("|')xdebug-error\b.*xe-/im.test(this.objAjax.responseText) || this.intError;
<?php
}
?>
				this.Finished();
				break;
		} // switch
	} // TresTristesTigres
/**
* Summary. Responder apropiadamente al finalizar la petición Ajax.
*/
	this.Finished = function () {
		//console.log('HTTP Status: '+this.objAjax.status);
		this.theContainer.innerHTML = this.objAjax.responseText.replaceAll('%winid%', this.options.windowId);
		document.getElementsByTagName('BODY')[0].appendChild(this.theWindow);
		let myModal = bootstrap.Modal.getOrCreateInstance(this.theWindow);
		
		this.theWindow.addEventListener('hidden.bs.modal', (e) => {
			if (this.options.onClose && (typeof this.options.onClose == 'function')) {
				this.options.onClose(this.theWindow);
			}
			document.getElementsByTagName('BODY')[0].removeChild(this.theWindow);
		});
		
		myModal.show();
		
		if (this.options.onShow && (typeof this.options.onShow == 'function')) {
			this.options.onShow(this.theWindow);
		}
		this.theContainer.querySelectorAll('script').forEach((t,i)=> {
			eval(t.innerText);
		}); // foreach
		if (this.objAjax.status == 401) {
			//this.theContainer.innerHTML = '<p class="msgerr">Inicio de sesión requerido.</p>'+;
		}
		if (this.objAjax.status == 404) {
			this.theContainer.innerHTML = '<p class="msgerr">Archivo no encontrado: '+this.options.archivo+'</p>';
		}
		//console.log('contenido: '+this.objAjax.responseText);
	}

	this.Show = function (inline_params) {

		this.theWindow = this.CreateWindow();




		var queryStrings = new Object();
		queryStrings.cifid = '<?php echo @$objeto_contenido->cifid; ?>';
		if (this.options.content) {
			queryStrings.content = this.options.content;
		}
		if (this.options.archivo) {
			queryStrings.archivo = this.options.archivo;
		}
		if (this.options.extraparams) { // Estos son los parámetros fijos puestos por el usuario.
			queryStrings = Object.assign({},queryStrings,this.options.extraparams);
		}
		if (inline_params) { // Estos son los parámetros que le pasan a este método.
			queryStrings = Object.assign({},queryStrings,inline_params);
		}
		/* Como queryStrings es un object, hay que convertirlo en un string que HTTP entienda en la forma campo1=valor1&campo2=valor2*/
		let valores = Object.keys(queryStrings).map(function(idx) { return idx+'='+((queryStrings[idx])?queryStrings[idx]:''); }).join('&');

		/* Esto efectivamente efectua la petición al servidor */
			this.objAjax.onreadystatechange = this.TresTristesTigres;
			this.objAjax.open('POST','<?php echo URL_ajax; ?>');
			this.objAjax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			this.objAjax.send(valores);
	}
/**
* Summary. Cerrar el modal por código.
*/
	this.Hide = function () {
		if (typeof this.theWindow == 'undefined') { return; }
		if ('undefined' !== typeof rbtRulo && this.rulo !== null)
			this.rulo.Hide();
		let myModal = bootstrap.Modal.getOrCreateInstance(this.theWindow);
		myModal.hide();
	}
/**
* Summary. Parsear la respuesta del servidor, se usa en el estado 4. Esto viene a reemplazar a Evalresult.
* @param string texto el contenido que llega del servidor.
*/
	this.parseJson = function (texto) {
		var regexjson = /<json>(.*?)<\/json>/m;
		var json = null;
		var cleanText = texto.replace(/(\r\n|\r|\n)/gm,"");
		if (regexjson.test(cleanText)) {
			let aux = regexjson.exec(cleanText);
			json = aux[1];
		}
		try {
			json = JSON.parse(json);
		} catch(err) {
		}
		return json;
	}
/**
* Summary. Crea la ventana modal al estilo bootstrap
*/
	this.CreateWindow = function () {
		var TheDiv = document.createElement('DIV');
		TheDiv.classList.add('modal','fade');
		if(this.options.lado){
			TheDiv.classList.add('modal-'+this.options.lado);			
		}
		TheDiv.setAttribute('id',this.options.windowId);
		TheDiv.setAttribute('tabindex','-1');
		TheDiv.setAttribute('role','dialog');
		TheDiv.setAttribute('aria-labelledby','Label'+this.options.windowId);
		TheDiv.setAttribute('aria-hidden','true');
		if (typeof this.options.noclose == 'boolean' && this.options.noclose) {
			TheDiv.setAttribute('data-backdrop','static');
			TheDiv.setAttribute('data-keyboard','false');
		}
		var TheModal = document.createElement('DIV');
		TheModal.classList.add('modal-dialog');
		if(typeof this.options.centered == "boolean" && this.options.centered){
			TheModal.classList.add('modal-dialog-centered');
		}

		if(typeof this.options.fullscreen === "boolean" && this.options.fullscreen){
			TheModal.classList.add('modal-fullscreen');
		} else if (this.options.fullscreen && ["sm", "md", "lg", "xl", "xxl"].includes(this.options.fullscreen)){
			TheModal.classList.add(`modal-fullscreen-${this.options.fullscreen}-down`);
		}
																 

		//-------------------------------------------------------------
		if (['sm','lg','xl','auto'].includes(this.options.size)) {
			TheModal.classList.add('modal-'+this.options.size);
		}

		var TheContainer = document.createElement('DIV');
		TheContainer.classList.add('modal-content');
		if (typeof this.options.extraclass == 'object') {
			for (var x in this.options.extraclass) {
				TheContainer.classList.add(this.options.extraclass[x]);
			}
		} else {
			if (this.options.extraclass != '') {
				TheContainer.classList.add(this.options.extraclass);
			}
		}
		TheContainer.setAttribute('id',this.options.windowId+'_content');

		TheModal.appendChild(TheContainer);
		TheDiv.appendChild(TheModal);
		
		this.theWindow = TheDiv;
		this.theContainer = TheContainer;

		return TheDiv;
	}
	
	this.Wait = function (wait) {
		this.waiting = wait;
		if (wait) { 
			if ('undefined' !== typeof rbtRulo) {
				this.rulo = new rbtRulo();
				this.rulo.Show(this.theContainer);
			}
		}
		else {
			if ('undefined' !== typeof rbtRulo && this.rulo !== null)
				this.rulo.Hide();
		}
	} // Wait

} // Class modalBs5
