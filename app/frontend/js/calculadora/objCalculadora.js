<?php
/*
	Objeto que encapsula toda la funcionalidad de la calculadora del lado del cliente.
	Created: 2021-09-02
	Author: DriverOp
*/
?>

;
/*
	objCalculadora ver 1.0
	Created: 2021-09-02
	Author: DriverOp
*/

var objCalculadora = function (params) {
	"use strict";
	this.defaultOptions = {
		archivo:'getCotiz', // El archivo que procesa los datos en el servidor.
		content:'cotiz', // El subdirectorio.
		cotiz: null, // Los datos a cotizar.
		extraparams: null, // Parámetros extra al servidor.
		form: null // El formulario donde están los datos a cotizar.
	}

	this.events = []; // Lista de funciones a ejecutar cuando la petición termina.
	this.objResponse = null; // Objeto con la respuesta del servidor.
	this.intError = false; // Indica si el servidor respondió con error o no.
	this.options = Object.assign({},this.defaultOptions,params);
	
	this.objAjax = new XMLHttpRequest();
	this.objAjax.onreadystatechange = (ev)=> {
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
				this.intError = false;
				this.objResponse = this.parseJson(this.objAjax.responseText);
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
	} // ajaxResponse
	
	this.Finished = function () {
		console.log('La petición Ajax ha finalizado');
		if (this.events.length == 0) { return; }
		let respuesta;
		if (this.intError) {
			if (this.objResponse.generr) { respuesta = this.objResponse.generr; }
			if (this.objResponse.dataerr) { respuesta = this.objResponse.dataerr; }
		} else {
			respuesta = this.objResponse.respuesta??null;
		}
		this.events.forEach((ele)=>{
			ele(respuesta, this.intError);
		});
	}
/**
* Summary. Parsear la respuesta del servidor, se usa en el estado 4.
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
* Summary. Obtener una cotización.
* @param object params Cualquier otra cosa que el usuario quiera enviar, incluyendo sobreescribir las opciones por omisión.
*/
	this.Get = function (params) {
		this.options = Object.assign({},this.options,params);
		
		this.options.cotiz = this.harvestData();
		
		var queryStrings = new Object();
		
		queryStrings.archivo = this.options.archivo??null;
		queryStrings.content = this.options.content??null;
		queryStrings.cotiz = JSON.stringify(this.options.cotiz??null);
		queryStrings.token = sessionStorage.getItem('token');
		

		if (this.options.extraparams) { // Estos son los parámetros fijos puestos por el usuario.
			queryStrings = Object.assign({},queryStrings,this.options.extraparams);
		}
		/* Como queryStrings es un object, hay que convertirlo en un string que HTTP entienda en la forma campo1=valor1&campo2=valor2*/
		let valores = Object.keys(queryStrings).map(function(idx) { return idx+'='+((queryStrings[idx])?queryStrings[idx]:''); }).join('&');
		this.objAjax.open('POST','<?php echo URL_ajax; ?>');
		this.objAjax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		this.objAjax.send(valores);
		
	}
/**
* Summary. Recolecta los datos desde el formulario asingado.
* @return object result
*/
	this.harvestData = function () {
		let result = {};
		if (null === this.options.form) {
			console.error('No hay formulario asignado'); return;
		}
		if (!(this.options.form instanceof HTMLFormElement)) {
			console.error('Elemento asignado no es de tipo FORM'); return;
		}

		/* Como hay un formulario asignado, tomamos los valores de la cotización desde allí. */
		if (this.options.form.numberMonto !== undefined && this.options.form.numberMonto !== null) {
			if (this.options.form.numberMonto instanceof HTMLInputElement) {
				result.numberMonto = this.options.form.numberMonto.value.trim();
			}
		}

		if (this.options.form.numberPlazo !== undefined && this.options.form.numberPlazo !== null) {
			if (this.options.form.numberPlazo instanceof HTMLInputElement) {
				result.numberPlazo = this.options.form.numberPlazo.value.trim();
			}
		}
		return result;
	}
/**
* Summary. Agregar una funciòn "callback" a la lista de funciones a ejecutar cuando la petición termina.
* @param function fnc.
*/
	this.addResponseListener = function (fnc) {
		if (undefined === fnc) { return; }
		if (null === fnc) { return; }
		if (typeof fnc != 'function') { return; }
		this.events.push(fnc);
	}
} // objCalculadora
