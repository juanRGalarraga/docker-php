;
/*
	modalBsLte 2.0
	Created: 2020-03-22
	Author: DriverOp
	
	Modif: 2021-01-22
	Desc: Agregada propiedad size con cuatro posibles valores que relacionan con clases Bootstrap para controlar el tamaño de la ventana.

	Modif: 2021-03-13
	Author: DriverOp
	Desc: Desarrollo de la versión 2.0!.
	
	Crea una ventana modal al estilo Bootstrap 4 y LTE.
	
	Require bootstrap 4 y JQuery.
	Ya no requiere objEvalresult.js,fmdRulo.js

	Ahora ejecuta JavaScript que venga en el contenido de la ventana.
	
*/

if ('undefined' != typeof NodeList.prototype.forEach) NodeList.prototype.forEach = Array.prototype.forEach;
if ('undefined' != typeof String.prototype.replaceAll) String.prototype.replaceAll = function(str1, str2, ignorar) {
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignorar?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
}


let modalBsLTECreator = class {
	defaultOptions = {
		archivo: '',
		content: '',
		extraparams: {},
		extraclass: '',
		size: 'lg',
		windowId: 'modalBsLte',
		onShow: null,
		onClose: null
	}

	waiting = false;
	intError = false;
	theWindow = null;
	theContainder = null;
	
	objAjax = null;
	
	constructor(params) {
		this.options = Object.assign({},this.defaultOptions,params);
		this.objAjax = new XMLHttpRequest();
		this.objAjax.onreadystatechange = this.TresTristesTigres;
	}
	
	Set = function (newParams) {
		this.options = Object.assign({},this.options,newParams);
	}
/**
* Summary. Callback o función de retorno del objeto XMLHttpRequest.
* @note Función flecha para mantener en ámbito "this".
*/
	TresTristesTigres = (ev)=> {
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
	Finished = function () {
		console.log('HTTP Status: '+this.objAjax.status);
		this.theContainer.innerHTML = this.objAjax.responseText.replaceAll('%winid%', this.options.windowId);
		document.getElementsByTagName('BODY')[0].appendChild(this.theWindow);
		$(this.theWindow).modal('show');
		$(this.theWindow).on('hidden.bs.modal', (e) => {
			if (this.options.onClose && (typeof this.options.onClose == 'function')) {
				this.options.onClose(this.theWindow);
			}
			document.getElementsByTagName('BODY')[0].removeChild(this.theWindow);
		});
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

	Show = function (inline_params) {

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
			this.objAjax.open('POST','<?php echo URL_ajax; ?>');
			this.objAjax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			this.objAjax.send(valores);
	}
/**
* Summary. Parsear la respuesta del servidor, se usa en el estado 4. Esto viene a reemplazar a Evalresult.
* @param string texto el contenido que llega del servidor.
*/
	parseJson = function (texto) {
		var regexjson = /<json>(.*?)<\/json>/m;
		var json = null;
		var cleanText = texto.replace(/(\r\n|\r|\n)/gm,"");
		if (regexjson.test(cleanText)) {
			let aux = regexjson.exec(cleanText);
			json = aux[1];
		}
		try {
			json = eval("("+json+")");
		} catch(err) {
		}
		return json;
	}
/**
* Summary. Crea la ventana modal al estilo bootstrap
*/
	CreateWindow = function () {
		var TheDiv = document.createElement('DIV');
		TheDiv.classList.add('modal','fade');
		TheDiv.setAttribute('id',this.options.windowId);
		TheDiv.setAttribute('tabindex','-1');
		TheDiv.setAttribute('role','dialog');
		TheDiv.setAttribute('aria-labelledby','Label'+this.options.windowId);
		TheDiv.setAttribute('aria-hidden','true');

		var TheModal = document.createElement('DIV');
		TheModal.classList.add('modal-dialog');
		if (['sm','lg','xl'].includes(this.options.size)) {
			TheModal.classList.add('modal-'+this.options.size);
		} else {
			TheModal.classList.add('modal-lg');
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
} // Class modalBsLte
