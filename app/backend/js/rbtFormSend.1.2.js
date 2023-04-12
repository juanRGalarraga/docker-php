;
/*
	rbtFormSend: 1.33
	Created: 2020-07-30
	Author: Rebrit SRL.
	
	Update: 2020-11-06
	Desc: Agregado formulario completo, en reemplazo del fmdFormSend.

	Establecer una zona y un input:file como zona para arrastrar y soltar un achivo desde el OS para (eventualmente) ser enviado al servidor.
	NO hace el envío.

	Update: 2020-11-12
	Desc: Agregados límites de cantidad de archivos, tamaño por archivo y tamaño total. Ahora haciendo clic en el dropzone se selecciona un archivo.
	Cambiado de nombre de rbtDragDropFile a rbtFormSend que es más descriptivo.
	
	Update: 2021-03-16
	Desc:
		- Solo agregar una vez los estilos CSS.
		- Ahora onFinish pasa cuatro parámetros para mantener la retrocompatibilidad.
	
	Update: 2021-06-28
	Desc:
		- Agregado muestra automática de errores de datos en formulario detectados desde el servidor.
		- Agregado callback para esos errores.

	Update: 2021-11-02
	Desc:
		- Segundo parámetro del método Send ahora puede ser una función que se usa como callback de respuesta del servidor, reemplazando el evento onFinish si estuviese establecido.
*/

var theStyle = document.createElement('style');
	theStyle.setAttribute('media','screen');
	theStyle.setAttribute('type','text/css');
	theStyle.setAttribute('id','rbtFromSendStyles');
	theStyle.appendChild(document.createTextNode(""));
	theStyle.innerHTML = `
.rbtDropZone {
	display: flex;
	flex-flow: row wrap;
	justify-content: space-around;
	border: 1px dashed #9f9a9c;
	background-color: #97dfde;
	transition: background-color 0.5s ease;
}
.rbtDragOver {
	background-color: #cbfffe;
}
.rbtDroppedFile {
	display: inline-block;
	padding: 1rem;
	max-width: 150px;
	overflow: hidden;
}
.rbtDropZone .dd-fileBlock {
	display: flex;
	flex-direction: column;
	flex-flow: column nowrap;
	align-items: auto;
	align-content: stretch;
	padding: 1rem;
	border-radius: 3px;
	border: 1px solid #777;
	min-width: 100px;
	overflow: hidden;
	margin: 0 1rem 1rem 0;
	position: relative;
}
.rbtDropZone .dd-fileContent {
	display: block;
	padding-bottom: 0.5rem;
	flex-basis: auto;
	flex-grow: 1;
	background-color: transparent;
	max-width: 150px;
	position: relative;
	z-index: 1;
}
.rbtDropZone .dd-fileName, .rbtDropZone .dd-fileSize {
	display: block;
	background-color: rgba(255,255,255,0.5);
}
.rbtDropZone .dd-fileName {
	padding: 2px 2px 2px 0.5rem;
	text-align: center;
}
.rbtDropZone .dd-fileSize {
	font-size: 0.8rem;
	text-align: center;
	padding-bottom: 2px
}
.rbtDropZone .dd-fileBlock .dd-close {
	background-color: transparent;
	border-radius: 0.5rem;
	color: #ccc;
	display: block;
	height: 1rem;
	font-size: 0.9rem;
	position: absolute;
	right: 0;
	text-align: center;
	top: 0;
	vertical-align: middle;
	width: 1rem;
}
.rbtDropZone .dd-fileBlock .dd-close:hover {
	color: white;
	background-color: red;
}
`;
document.head.appendChild(theStyle);


var rbtFormSend = function (theForm, param) {
	"use strict";
	if(window.XMLHttpRequest == undefined) {
		console.log('Tu navegador no soporta peticiones AJAX. Lo siento, no puedo continuar');
		return false;
	}

	this.xhr = new XMLHttpRequest();

	this.defaultOptions = {
		checkErrors: true, // Verificar errores detectados desde el servidor.
		url: '<?php echo (defined("URL_ajax"))?URL_ajax:"localhost"; ?>', // Dónde le pega el ajax?
		method: 'POST', // Método HTTP
		asyncronious: true, // La petición es asincrónica por omisión
		intError: false,
		onOpen: function () {},
		onStart: function (total, actual, porc) {}, // loadstart
		onProgress: function (total, actual, porc) {}, // progress
		onEnd: function (total, actual, porc) {}, // loadend <-- Esto sucede aunque haya error.
		onFinish: function(status, statusText, responseText, objResponse) {}, // state 4. objResponse tiene el JSON parseado a objeto.
		onSuccess: function (total, actual, porc) {}, // load
		onError: function (ev) {},
		onTimeout: function (total, actual) {},
		onAbort: function () {},
		onDataError: null, // Callback cuando el servidor detecta un error en los datos.

		extraData: {}, // Qué otros datos enviar
		extraHeaders: {}, // Los headers de la petición
		
		progressBarId: null, // ID del elemento HTML que es la barra de progreso
		dropZoneId: null, // ID del elemento HTML que es la zona de dropeo
		inputFileId: 'droppedFiles', // Qué ID de elemento HTML usar en los files.
		inputName: 'droppedFiles', // Qué name de elemento HTML tiendrán los files

		onDragEnter: function (ev) {}, // Al entrar un archivo en la zona de dropeo.
		onDrop: function (theInput) {}, // Al soltar el archivo
		onDragLeave: function (ev) {}, // Al salir de la zona de dropeo
		onDragEnd: function (elem) {}, // Al terminar el drag.
		maxFileSize: null, // Cuál es el tamaño máximo de un archivo individual
		maxFileCount: null, // Cuántos archivos como máximo subir
		maxOverallSize: null, // Máximo de tamaños de archivos combinados.
		
		onFileLimit: function (limit, value) {} // Evento que se dispara cuando se alcanza uno de los límites, limit es una cadena que indica el límite alcanzado y value, el valor de ese límite.
	}
	
	this.totalPercent = 0;
	this.objResponse = null;
	this.options = null;
	this.theBarWrapper = null;
	this.theBar = null;
	this.theDropZone = null;
	this.randomString = Math.random().toString(36).substring(7);
	var fileCount = 0;
	var overallSize = 0;
	
	var theSender = this;

	// onStart
	this.xhr.upload.addEventListener('loadstart', (ev)=> {
		var cargado = ev.loaded || ev.position;
		var total = ev.total || ev.totalSize;
		theSender.totalPercent = 0;
		theSender.options.onStart.call(theSender, cargado, total, theSender.totalPercent);
	});

	// onProgress
	this.xhr.upload.addEventListener('progress', (ev)=> {
		var cargado = ev.loaded || ev.position;
		var total = ev.total || ev.totalSize;
		if(ev.lengthComputable){
			theSender.totalPercent = Math.ceil(cargado / total * 100);
			if (theSender.theBarWrapper && theSender.theBar) {
				theSender.theBar.style.width = theSender.totalPercent+'%';
			}

		}
		theSender.options.onProgress.call(theSender, cargado, total, theSender.totalPercent);
	});
	

	// onFinish
	this.xhr.upload.addEventListener('loadend', (ev)=> {
		var cargado = ev.loaded || ev.position;
		var total = ev.total || ev.totalSize;
		theSender.totalPercent = 0;
		theSender.options.onEnd.call(theSender, cargado, total);
	});
	
	// Dispara eventos del Ajax.
	this.xhr.onreadystatechange = function (ev) { // ev es el objeto evento.
		switch (theSender.xhr.readyState) {
			case 0:  // nada
				// console.log('No se ha enviado nada todavía.'); 
				break;
			case 1: // Hay una petición que se acaba de abrir con open()
				theSender.intError = false;
				if (theSender.theBarWrapper && theSender.theBar) {
					theSender.theBar.style.width = '0%';
				}
				theSender.options.onOpen.call(theSender);
				break;
			case 2: // Se recibieron las cabeceras del servidor.
				// console.log('Cabeceras recibidas.');
				break;
			case 3:  // Se recibió un paquete de datos desde el servidor.
				// console.log('Datos llegando.');
				break;
			case 4: // La petición se completó. Esto es lo que nos interesa.
				// console.log('Petición completada');
				theSender.objResponse = theSender.parseJson(theSender.xhr.responseText);
				if (theSender.objResponse !== null) {
					theSender.intError = ((theSender.objResponse.dataerr !== null && theSender.objResponse.dataerr !== undefined) || (theSender.objResponse.generr !== null && theSender.objResponse.generr !== undefined));
					if (theSender.objResponse.dataerr !== null && theSender.objResponse.dataerr !== undefined && theSender.options.checkErrors) {
						for(var x in theSender.objResponse.dataerr) {
							var elem = document.getElementById(x);
							if (elem !== null && elem !== undefined) {
								if (theSender.options.onDataError !== null && (typeof theSender.options.onDataError == 'function')) {
									theSender.options.onDataError.call(theSender, elem, theSender.objResponse.dataerr[x]); // Llamar al manejador de errores personalizado.
								} else {
									if (elem.msgerr !== undefined) {
										elem.msgerr(theSender.objResponse.dataerr[x]); 
									} // if 
								} // else 
							} // if
						} // for
					} // if
				} // if
				if (/("|')xdebug-error\b.*xe-/im.test(theSender.xhr.responseText)) {
					theSender.intError = true;
					console.error(theSender.xhr.responseText);
				}
				theSender.options.onFinish.call(theSender, theSender.xhr.status, theSender.xhr.statusText, theSender.xhr.responseText, theSender.objResponse, theSender.intError);
				break;
		}
		return true;
	}


	// Inicia el envío del formulario.
	this.Send = function (formulario, param) {
		if (formulario != undefined) { 
			if (!theSender.SetForm(formulario)) { return false; }
		}
		if (param !== null && param != undefined) {
			if (typeof param == 'function') {
				theSender.options.onFinish = param
			} else {
				theSender.SetOptions(param);
			}
		}
		for (let x in theSender.options.extraHeaders) {
			theSender.xhr.setRequestHeader(x, theSender.options.extraHeaders[x]);
		}
		var theFormData = new FormData(theForm);
		for(let x in theSender.options.extraData) {
			theFormData.append(x, theSender.options.extraData[x]);
		}
		theSender.xhr.open(theSender.options.method || 'POST', theSender.options.url || 'localhost', theSender.options.asyncronious);
		theSender.xhr.send(theFormData);
	};

	// Parsear la respuesta del servidor
	this.parseJson = function (texto) {
		var regexjson = /<json>(.*?)<\/json>/m;
		var json = null;
		texto = texto.replace(/(\r\n|\r|\n)/gm,"");
		if (regexjson.test(texto)) {
			let aux = regexjson.exec(texto);
			json = aux[1];
		}
		try {
			json = JSON.parse(json);
		} catch(err) {
		}
		return json;
	}
	
	// Establecer las opciones
	this.SetOptions = function(parametros) {
		if (!theSender.options) {
			theSender.options = Object.assign({},theSender.defaultOptions,parametros);
		} else {
			theSender.options = Object.assign(theSender.options,parametros);
		}
		//console.log(theSender.options);
		theSender.SetProgressBar();
		theSender.SetDropZone();
	}
	
	// Establecer el formulario con el cual trabajar.
	this.SetForm = function(formulario) {
		if (!formulario) { console.log('No es un form.'); return false; }
		if ((formulario instanceof Element || formulario instanceof HTMLDocument) && 'FORM' != formulario.tagName) { console.log('Solamente puedo actuar en tags <form>.'); return false; }
		if (window.FormData === undefined) {
			console.log('Tu navegador no soporta la API FormData. Lo siento, no puedo continuar...');
			return false;
		}
		theForm = formulario;
		return true;
	}
	
	// Establecer la barra de progreso.
	this.SetProgressBar = function () {
		if (!theSender.options.progressBarId) { return false;}
		if ('string' != (typeof theSender.options.progressBarId)) { return false; }
		theSender.theBarWrapper = document.getElementById(theSender.options.progressBarId);
		if (theSender.theBarWrapper && (theSender.theBarWrapper instanceof Element || theSender.theBarWrapper instanceof HTMLDocument)) {
			theSender.theBarWrapper.style.cssText = 'position:relative;background-color:white;height:10px;';
			theSender.theBar = document.createElement('SPAN');
			theSender.theBar.style.cssText = 'position:relative;width:0%;height:100%;background-color:green;padding:0;margin:0;display:block;overflow:hidden;z-index:1;';
			theSender.theBarWrapper.appendChild(theSender.theBar);
		}
	}
	
	/* Drag and Drop */
	this.enterTheZone = function (event) { // Drag Enter
		event.preventDefault();
		event.stopPropagation();
		theSender.theDropZone.classList.add('rbtDragOver');
		if (theSender.options.onDragEnter && (typeof theSender.options.onDragEnter == 'function')) {
			theSender.options.onDragEnter(event);
		}
	}
	this.overTheZone = function (event) { // Drag Over
		event.preventDefault();
		event.stopPropagation();
		theSender.theDropZone.classList.add('rbtDragOver');
		if (theSender.options.onDragOver && (typeof theSender.options.onDragOver == 'function')) {
			theSender.options.onDragOver(event);
		}
	}
	this.leaveTheZone = function (event) { // Drag Leave
		theSender.theDropZone.classList.remove('rbtDragOver');
		if (theSender.options.onDragLeave && (typeof theSender.options.onDragLeave == 'function')) {
			theSender.options.onDragLeave(event);
		}
	}
	
	this.dropInTheZone = function (event) {
		event.preventDefault();
		event.stopPropagation();
		if (event.dataTransfer.items) {
			for (var i=0; i < event.dataTransfer.items.length; i++) {
				if (event.dataTransfer.items[i].kind === 'file') {
					var arch = event.dataTransfer.items[i].getAsFile();
					theSender.addInput(arch);
				}
			}
		}
		console.log(event);
	}
	
	this.addInput = function (theFile) { // Poner la cartita con los datos del archivo dropeado y además el input file.
		if (theSender.options.maxFileCount && (fileCount >= theSender.options.maxFileCount)) {
			theSender.options.onFileLimit('fileCount',fileCount);
			return false;
		}
		if (theSender.options.maxFileSize && (theFile.size > theSender.options.maxFileSize)) {
			theSender.options.onFileLimit('fileSize',theFile.size);
			return false;
		}
		if (theSender.options.maxOverallSize && ((theFile.size+overallSize) > theSender.options.maxOverallSize)) {
			theSender.options.onFileLimit('overallSize',(theFile.size+overallSize));
			return false;
		}
		
		overallSize = theFile.size+overallSize;
		
		const theId = ('file-'+RandStr(6,1)).toLowerCase();
		const dt = new DataTransfer(); // This is the trick. As you cannot add files entry to the files input's attribute, you have to create a DataTransfer object on the fly, add the file to that object and then, assign the object to the files input's attribute.
		dt.items.add(theFile);
		
		var theList = theSender.theDropZone.querySelectorAll('input[type=file]');
		if (theList.length > 0) {
			if (theList.length == 1) {
				theSender.options.inputName = theSender.options.inputName+'[]';
				theList[0].setAttribute('name',theSender.options.inputName);
			}
		}
		
		
		var theInput = document.createElement('INPUT');
		theInput.setAttribute('type','file');
		//theInput.setAttribute('id',this.options.inputfileId);
		theInput.setAttribute('name',theSender.options.inputName);
		theInput.setAttribute('id',theId);
		theInput.style.display = 'none';
		theInput.files = dt.files;
		
		
		var theSpan = document.createElement('SPAN');
		theSpan.classList.add('dd-fileBlock');
		theSpan.setAttribute('title',theFile.name+' ('+theFile.size+' bytes)');
		theSpan.dataset.id = theId;
		
		var theContent = document.createElement('SPAN');
		theContent.classList.add('dd-fileContent');

		var theFileName = document.createElement('SPAN');
		theFileName.classList.add('dd-fileName');
		theFileName.innerHTML = ''+theFile.name+'';

		var theFileSize = document.createElement('SPAN');
		theFileSize.classList.add('dd-fileSize');
		theFileSize.innerHTML = formatFileSizes(theFile.size,2);
		
		var theClose = document.createElement('SPAN');
		theClose.classList.add('dd-close');
		theClose.innerHTML = '<i class="far fa-times-circle">X</i>';
		theClose.setAttribute('title','Quitar archivo');
		theClose.addEventListener('click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			this.parentElement.parentElement.removeChild(this.parentElement);
			fileCount--;
		});

		
		theSpan.appendChild(theInput);
		theContent.appendChild(theFileName);
		theContent.appendChild(theFileSize);
		theSpan.appendChild(theContent);
		theSpan.appendChild(theClose);
		
		theSender.theDropZone.appendChild(theSpan);
		
		if (/^image\/.+/.test(theInput.files[0].type)) { // El archivo droppeado es una imagen?
			var thePreview = document.createElement('DIV');
			thePreview.style.cssText = 'position:absolute;bottom:0;left:0;width:100%;z-index:-1;';
			var theImage = document.createElement('IMG');
			theImage.style.cssText = 'width:100%;';
			thePreview.appendChild(theImage);
			theContent.appendChild(thePreview);
			
			var reader = new FileReader();
			reader.onload = function () {
				theImage.src = reader.result;
			}
			reader.readAsDataURL(theInput.files[0]);
		}
		fileCount++;
	}

	// Establecer la zona de dropeo.
	this.SetDropZone = function () {
		if (!theSender.options.dropZoneId) { return false;}
		if ('string' != (typeof theSender.options.dropZoneId)) { return false; }
		theSender.theDropZone = document.getElementById(theSender.options.dropZoneId);
		console.log(theSender.theDropZone);
		if (theSender.theDropZone && (theSender.theDropZone instanceof Element || theSender.theDropZone instanceof HTMLDocument)) {
			theSender.theDropZone.addEventListener('dragenter', theSender.enterTheZone);
			theSender.theDropZone.addEventListener('dragover', theSender.overTheZone);
			theSender.theDropZone.addEventListener('drop', theSender.dropInTheZone);
			theSender.theDropZone.addEventListener('dragleave', theSender.leaveTheZone);
			theSender.theDropZone.classList.add('rbtDropZone');
			var hiddenInput = document.createElement('INPUT');
			hiddenInput.type="file";
			hiddenInput.style.cssText = 'display:none;width:0;height:0;background-color:transparent;z-index:-10;';
			theSender.theDropZone.addEventListener('click', function () { hiddenInput.click(); })
			hiddenInput.addEventListener('input', function () { theSender.addInput(this.files[0]); });
			theSender.theDropZone.setAttribute('title','Clic para adjuntar archivo');
		}
	}

	this.SetOptions(param);
	if (!this.SetForm(theForm)) { return false; }
	
	/* Helpers functions */
}
formatFileSizes = function(size, decimals = 2) {
	if (size === 0) return '0 Bytes';
	const k = 1024;
	const dm = decimals < 0 ? 0 : decimals;
	const sizes = ['Bytes', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
	const i = Math.floor(Math.log(size) / Math.log(k));
	return parseFloat((size / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

;