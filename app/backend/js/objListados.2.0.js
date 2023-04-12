/*
	ListadoCreators Ver 2.0.
	
	Ahora la petición Ajax y la evaluación del resultado se realizan en el propio componente para no depender de externos.
	Eliminadas toda referencia a JQuery.
	
*/

var theStyle = document.createElement('style');
theStyle.setAttribute('media','screen');
theStyle.setAttribute('type','text/css');
theStyle.setAttribute('id','mgrListadoCreatorStyles');
theStyle.appendChild(document.createTextNode(""));
theStyle.innerHTML = `p.msgGenErr{text-align: center;width: auto;margin: 1rem;padding: 1rem;background-color: rgba(255,255,255,0.5);color: red;font-size: 12pt;font-weight: 400;line-height: 14pt;border: 1px solid red;border-radius: 4px;clear: both;position:static;}`
document.addEventListener('DOMContentLoaded', ()=>{document.head.appendChild(theStyle);});

var mgrListadoCreator = function (param) {
	"use strict";

	if(window.XMLHttpRequest == undefined) {
		console.log('Tu navegador no soporta peticiones AJAX. Lo siento, no puedo continuar');
		return false;
	}

	this.defaultOptions = {
		archivo: '<?php echo (isset($objeto_contenido->alias))?$objeto_contenido->alias:null; ?>',
		content: '<?php echo (isset($objeto_contenido->content))?$objeto_contenido->content:null; ?>',
		tableClassName: 'tabla-general',
		targetElementIdName: 'mainlist',
		idPrefix: '',
		fixedParams: {},
		pagManager: null,
		append: false,
		rulo: null, // Para referenciar una instancia de rbtRulo
		parseResponse: true, // La respuesta debe parsearse (busca un JSON en los tags <json></json>)
		onOpen: null, // Evento 1
		onFirstArrive: null, // Evento 2
		onLoading: null, // Evento 3
		onFinish: function (idTable) {} // Evento 4.
	}

	this.objAjax = new XMLHttpRequest();
	
	this.ttm = null; // temporizador
	
	this.userParams = param;
	this.objResponse = null;
	
	this.options = Object.assign({},this.defaultOptions,param);
	
	this.extraparams = {};
	
	var TheList = this;
	var theId = TheList.options.idPrefix+TheList.options.targetElementIdName;
	var targetElement = null;

/**
* Summary. Esto es el grueso de la petición Ajax.
*/
	this.objAjax.onreadystatechange = function (ev) { // ev es el objeto evento.
		switch (TheList.objAjax.readyState) {
			case 0:  // nada
				// console.log('No se ha enviado nada todavía.'); 
				break;
			case 1: // Hay una petición que se acaba de abrir con open()
				TheList.intError = false;
				if (typeof TheList.options.onOpen == 'function') {
					TheList.options.onOpen.call(TheList);
				}
				break;
			case 2: // Se recibieron las cabeceras del servidor.
				if (typeof TheList.options.onFirstArrive == 'function') {
					TheList.options.onFirstArrive.call(TheList);
				}
				break;
			case 3:  // Se recibió un paquete de datos desde el servidor.
				if (typeof TheList.options.onLoading == 'function') {
					TheList.options.onLoading.call(TheList);
				}
				break;
			case 4: // La petición se completó. Esto es lo que nos interesa.
				//console.log('Petición completada');
				TheList.objResponse = (TheList.options.parseResponse)?TheList.parseJson(TheList.objAjax.responseText):null;
				if (TheList.objResponse != null) {
					if (TheList.objResponse.dataerr) {
						TheList.intError = true;
						console.log('Se devolvió error de datos: '+TheList.objResponse.dataerr.toString());
					} else {
						if (TheList.objResponse.generr) {
							TheList.intError = true;
							console.log('Se devolvió error general: '+TheList.objResponse.generr.toString());
						}
					}
				}
<?php
if (DEVELOPE) {
?>
				TheList.intError = /("|')xdebug-error\b.*xe-/im.test(TheList.objAjax.responseText) || TheList.intError;
<?php
}
?>
				TheList.Finished();
				break;
		}
	}

/**
* Summary. Esto se ejecuta cuando la petición Ajax regresa el resultado del servidor
*/
	this.Finished = function () {
		//TheList.objAjax.status, TheList.objAjax.statusText,  , TheList.objResponse, TheList.intError
		if (targetElement) {
			if (TheList.options.rulo && ('undefined' != typeof rbtRulo) && (TheList.options.rulo instanceof rbtRulo)) {
				TheList.options.rulo.Hide();
			}
			let contenido = (TheList.options.append)?targetElement.innerHTML:'';
			if (TheList.intError && (TheList.objResponse != null) && (typeof TheList.objResponse.generr != 'undefined')) {
				contenido = '<p class="msgGenErr">'+TheList.objResponse.generr+'</p>'+contenido;
			}
			contenido = contenido+TheList.objAjax.responseText;
			targetElement.style.position = 'static';
			targetElement.style.height = 'auto';
			targetElement.innerHTML = contenido;
			TheList.ApplyEvents();
		} else {
			console.log('Elemento objetivo no indicado!');
			return;
		}
	} // Finished
	
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
			json = eval("("+json+")");
		} catch(err) {
		}
		return json;
	}

/**
*	Summary. Agrega un parámetro fijo a la lista de parámetros fijos.
* @param object param Un objeto contenido la lista de nombres y valores.
*/
	this.SetFixedParam = function (param) {
		TheList.userParams.fixedParams = Object.assign({}, TheList.userParams.fixedParams, param);
	}

/**
* Summary. Establece el elemento HTML que contendrá el listado resultado de la petición Ajax.
* @param string/HTMLElement.
*/
	this.SetTargetElement = function(target) {
		if (typeof target == 'string') {
			target = document.querySelector(target);
		}
		if (target instanceof HTMLElement == false) {
			return;
		}
		targetElement = target;
	}
	
	this.Get = function (ele, extraparam) {
		if (ele) {
			
			if ((ele.tagName == 'SELECT') && (ele.multiple == true)) {
				console.log('Es un select múltiple');
				if (ele.selectedOptions.length > 0) {
					for (var x = 0;x<ele.selectedOptions.length;x++) {
						TheList.extraparams[ele.name+'['+x+']'] = (typeof ele.selectedOptions[x].value == 'string')?ele.selectedOptions[x].value.trim():ele.selectedOptions[x].value;
					}
				} else {
					TheList.extraparams[ele.name] = '';
				}
			} else {
				TheList.extraparams[ele.name] = (typeof ele.value == 'string')?ele.value.trim():ele.value;
			}
		}
		if (extraparam) {
			TheList.extraparams = Object.assign({},extraparam,TheList.extraparams);                        
		}
		TheList.Trigger();
	} // Get
	
	this.Trigger = function () {
		if (TheList.ttm != null) {
			clearTimeout(TheList.ttm);
		}
		TheList.ttm = setTimeout(TheList.CoreFunction,500);                
	} // Trigger
	
	this.CoreFunction = function () {
		var queryStrings = new Object();
		queryStrings.cifid = '<?php echo @$objeto_contenido->cifid; ?>';
		if (TheList.options.content) {
			queryStrings.content = TheList.options.content;
		}
		if (TheList.options.archivo) {
			queryStrings.archivo = TheList.options.archivo;
		}
		if (TheList.userParams.fixedParams) { // Estos son los parámetros fijos puestos por el usuario.
			queryStrings = Object.assign({},queryStrings,TheList.userParams.fixedParams);
		}
                
		if (TheList.extraparams) {
			
			queryStrings = Object.assign({},queryStrings,TheList.extraparams);
			TheList.extraparams = {}; // Limpiar los parámetros extra para que no se acumulen.
			
		}
		if (targetElement == null) {
			targetElement = document.getElementById(theId);
		}
		if (targetElement) {
			targetElement.style.position = 'relative';
			if (TheList.options.rulo && ('undefined' != typeof rbtRulo) && (TheList.options.rulo instanceof rbtRulo)) {
				TheList.options.rulo.Show(targetElement);
			}
			if (parseInt(targetElement.offsetHeight) < 100) { targetElement.style.height = '100px'; }
		} else {
			console.log('No encontré un elemento con ID: '+theId);
			return;
		}
		
		/* Como queryStrings es un object, hay que convertirlo en un string que HTTP entienda en la forma campo1=valor1&campo2=valor2*/
		let valores = Object.keys(queryStrings).map(function(idx) { return idx+'='+((queryStrings[idx])?queryStrings[idx]:''); }).join('&');

		/* Esto efectivamente efectua la petición al servidor */
			TheList.objAjax.open('POST','<?php echo URL_ajax; ?>');
			TheList.objAjax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			TheList.objAjax.send(valores);
	} // CoreFunction
	
	this.SetOrder = function (ele) {
		TheList.extraparams.ord = ele.dataset.field;
		TheList.Trigger();
	}
	
	this.ApplyEvents = function () {
		if (targetElement) {
			var columnOrder = targetElement.querySelectorAll('.col-order');
			if (columnOrder.length > 0) {
				columnOrder.forEach(function (th) {
					if (!th.dataset.objlevent) {
						th.addEventListener('click', function () { TheList.SetOrder(th); });
						th.dataset.objlevent = true;
					}
				});
			}
			if (typeof TheList.options.onFinish == 'function') {
				var laTabla = targetElement.querySelector('.tabla-general');
				if (laTabla) {
					TheList.options.onFinish(laTabla);
				} else {
					TheList.options.onFinish(targetElement);
				}
			}
			if (TheList.options.pagManager && (typeof TheList.options.pagManager == 'function')) {
				var pagLinks = targetElement.querySelectorAll('a.page-link.active-link');
				if (pagLinks.length > 0) {
					pagLinks.forEach(function (theLink) {
						theLink.addEventListener('click', function (ev) {
							ev.preventDefault();
							TheList.options.pagManager(theLink,theLink.dataset.page);
							return false;
						});
					});
				}
			}
			
			var span = targetElement.querySelector("#lid.lid[hidden]");
			if(span){
				TheList.SetFixedParam({lid:span.textContent});
			}
		}
	}

	this.setFinish = function (fnc) {
		TheList.options.onFinish = fnc;
	}
	return this;
} // ListadoCreator 2.0
