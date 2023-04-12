/*
	rbtMicroAjax 1.1.
	Author: Rebrit.
	Created: 2020-11-20
	
		Implementación de Ajax.
	
	Update: 2021-07-02
	Author: DriverOp
	Desc:
		- Ejecutar código JS que venga en la respuesta ajax.
*/
var rbtMicroAjax = function (params, callback) {
	"use strict";
	if(window.XMLHttpRequest == undefined) {
		console.log('Tu navegador no soporta peticiones AJAX. Lo siento, no puedo continuar');
		return false;
	}
	this.xhr = new XMLHttpRequest();

	this.defaultOptions = {
		onOpen: null, // Evento 1
		onFirstArrive: null, // Evento 2
		onLoading: null, // Evento 3
		onFinished: null, // Evento 4
		method: 'GET', // Método de petición
		baseUrl: null, // Destino de la petición
		asyncronious: true, // La petición es asincrónica por omisión
		parseResponse: true, // La respuesta debe parsearse (busca un JSON en los tags <json></json>)
		showMsgerr: true, // Mostrar mensajes de error. Funciona si parseResponse es true solamente.
		dataToSend: null, // La lista de parámetros y valores a enviar en la petición.
		
	}
	this.options = Object.assign({},this.defaultOptions,params);
	
	var evalResult = null;
	if (typeof rbtEvalResult == 'function') {
		this.evalResult = new rbtEvalResult();
	}
	
	var intError = false; // Cuando se procesa por errores devueltos en la petición, se cambia esta propiedad.
	
	var theSender = this;


	// Parsear la respuesta del servidor, se usa en el estado 4.
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

	// Dispara eventos del Ajax.
	this.xhr.onreadystatechange = function (ev) { // ev es el objeto evento.
		switch (theSender.xhr.readyState) {
			case 0:  // nada
				// console.log('No se ha enviado nada todavía.'); 
				break;
			case 1: // Hay una petición que se acaba de abrir con open()
				theSender.intError = false;
				if (typeof theSender.options.onOpen == 'function') {
					theSender.options.onOpen.call(theSender);
				}
				break;
			case 2: // Se recibieron las cabeceras del servidor.
				if (typeof theSender.options.onFirstArrive == 'function') {
					theSender.options.onFirstArrive.call(theSender);
				}
				break;
			case 3:  // Se recibió un paquete de datos desde el servidor.
				if (typeof theSender.options.onLoading == 'function') {
					theSender.options.onLoading.call(theSender);
				}
				break;
			case 4: // La petición se completó. Esto es lo que nos interesa.
				// console.log('Petición completada');
				theSender.objResponse = (theSender.options.parseResponse)?theSender.parseJson(theSender.xhr.responseText):null;
				if (theSender.objResponse !== null && theSender.objResponse !== undefined) {
					if (theSender.objResponse.generr !== null && theSender.objResponse.generr !== undefined) {
						theSender.intError = true;
					}
					if (theSender.objResponse.dataerr !== null && theSender.objResponse.dataerr !== undefined) {
						theSender.intError = true;
					}
					if (theSender.evalResult) {
						if (theSender.objResponse.dataerr) {
							theSender.evalResult.Print(theSender.objResponse)
						}
						if (theSender.objResponse.generr) {
							theSender.evalResult.ShowMessage(theSender.objResponse.generr);
						}
					}
				}
				if (/("|')xdebug-error\b.*xe-/im.test(theSender.xhr.responseText)) {
					theSender.intError = true;
					console.error(theSender.xhr.responseText);
				}
				if (typeof theSender.options.onFinish == 'function') {
					theSender.options.onFinish.call(theSender, theSender.xhr.status, theSender.xhr.responseText, theSender.objResponse, theSender.intError);
				}
				if (typeof callback == 'function') {
					callback(theSender.xhr.status, theSender.xhr.statusText,  theSender.xhr.responseText, theSender.objResponse, theSender.intError);
				}
				setTimeout(function () {
					// Procesar el código JS embebido en responseText
				},500);
				break;
		}
		return true;
	}
	
	// Enviar la petición
	this.Get = function () {
		var valores = null;
		if (theSender.options.dataToSend) {
			valores = Object.keys(theSender.options.dataToSend).map(function(idx) { return idx+'='+((theSender.options.dataToSend[idx])?theSender.options.dataToSend[idx]:''); }).join('&');
		}
		theSender.xhr.open(theSender.options.method || 'POST', theSender.options.url || 'localhost', theSender.options.asyncronious);
		if (theSender.options.method == 'POST')	theSender.xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		theSender.xhr.send(valores);
	}
	return this;
} // rbtMicroAjax
function getAjax(params, callback) {
	var localOptions = {
		method: 'POST',
		url: '<?php echo URL_ajax; ?>',
		dataToSend: params
	}
	new rbtMicroAjax(localOptions, callback).Get();
} // getAjax
