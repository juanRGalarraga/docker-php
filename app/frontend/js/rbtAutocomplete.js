/*
	rbtAutocomplete.
	Created: 2021-03-20
	Author: DriverOp
	
		Ahora sin jQuery!.



*/

if ('undefined' == typeof NodeList.prototype.forEach) NodeList.prototype.forEach = Array.prototype.forEach;
if ('undefined' == typeof String.prototype.replaceAll) String.prototype.replaceAll = function(str1, str2, ignorar) {
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignorar?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
}


var theStyle = document.createElement('style');
theStyle.setAttribute('media','screen');
theStyle.setAttribute('type','text/css');
theStyle.setAttribute('id','rbtAutocomplete-styles');
theStyle.appendChild(document.createTextNode(""));
theStyle.innerHTML = `
ul.rbtAutocomplete {
	position: fixed;
	top: 0;
	left: 0;
	cursor: default;
	list-style: none;
    padding: 2px;
    margin: 0;
    display: block;
    outline: none;
	box-sizing: border-box;
	background-color: #FFFFFF;
	border: 1px solid #ced4da;
	z-index: 1052;
	box-shadow: 0px 5px 5px -5px #000000;
}
ul.rbtAutocomplete > li.rbtAutocomplete-item {
    margin: 0;
    padding: 0 3px;
    width: 100%;
    box-sizing: border-box;
    display: block;
    outline: none;
	line-height: 1.6em;
}
ul.rbtAutocomplete > li.rbtAutocomplete-item:nth-child(2n) {
	background-color: #EFEFEF;
}
ul.rbtAutocomplete > li.rbtAutocomplete-item:hover {
	background-color: #AAAAAA;
	color: #FFFFFF;
}

ul.rbtAutocomplete > li.rbtAutocomplete-item.selected {
	background-color: #007bff;
	color: #FFFFFF;
}
.rbtAutocomplete-loading {
	background-image: url('../imgs/indicator.gif');
	background-repeat: no-repeat;
	background-position: 99% center;
}
`
document.addEventListener('DOMContentLoaded', ()=>{document.head.appendChild(theStyle);});


var rbtAutocomplete = function (elem, param) {
	"use strict";
	this.defaultOptions = {
		source: window.location.href,
		delay: 250,
		archivo: null, // Nombre del archivo en el servidor que atiende la petición de autocomplete.
		content: null, // Directorio en el servidor donde está archivo.
		fixedData: null, // Datos a enviar al servidor además de los clásicos
		extraClass: null, // Clase extra para el selector.
		onFetch: null, // Evento justo antes de hacer la petición al servidor.
		onOpen: null, // Petición al servidor abierta.
		onFirstArrive: null, // Llega el primer dato pero la petición no está completa.
		onLoading: null,// Los datos siguen llegando.
		onFinished: null, // La petición llegó y se tienen todos los datos.
		onSelect: null, // Evento cuando el usuario selecciona una opción.
		dummy: ''
	};

	if (typeof elem == 'string') {
		if (elem == '') {
			console.log('Selector elemento está vacío.');
			return false;
		}
		elem = document.querySelector(elem);
	}

	if (elem == null) {
		console.log('Elemento no asignado');
		return false;
	}

	if (typeof elem != 'object') {
		console.log('Elemento es de tipo no válido.');
		return false;
	}
	
	if (elem instanceof HTMLInputElement != true) {
		console.log('Elemento no es un input.');
		return false;
	}
	if(window.XMLHttpRequest == undefined) {
		console.log('Tu navegador no soporta peticiones AJAX. Lo siento, no puedo continuar');
		return false;
	}
	
	elem.setAttribute('autocomplete','off');

	this.objAjax = new XMLHttpRequest();

	this.idcounter = 0;
	this.ttm = null; // temporizador
	this.objResponse = null;
	this.extraData = {}; // Parámetros al servidor efímeros.
	
	this.options = Object.assign({},this.defaultOptions,param);
	

	this.theID = ++this.idcounter;
	this.theUl = document.createElement('ul');
	this.theUl.classList.add('rbtAutocomplete');
	while (document.getElementById('rbtAutocomplete_'+this.theID)) { this.theID = ++this.idcounter; }
	this.theUl.setAttribute('id','rbtAutocomplete_'+this.theID);
	if (this.options.extraClass) this.theUl.classList.add(this.options.extraClass);
	elem.setAttribute('rbtAutocomplete',this.theID);
	elem.classList.add('rbtAutocomplete-trigger');


	elem.setAttribute('autocomplete','off');
	this.theUl.style.display = 'none';
	var TheAutocomplete = this;
	
	document.getElementsByTagName('BODY')[0].style.position = 'relative';
	document.getElementsByTagName('BODY')[0].appendChild(TheAutocomplete.theUl);

	elem.addEventListener('keyup', function (e) {
		//console.log('Tecla: '+e.which);
		switch (e.which) {
			case  9: TheAutocomplete.Off(); break; // Tab
			case 16: TheAutocomplete.Off(); break; // Alt+tab
			case 27: TheAutocomplete.Off(); break; // Esc
			case 38: TheAutocomplete.Prev(); break; // Arrow Up
			case 40: TheAutocomplete.Sig(); break; // Arrow Down
			case 13: TheAutocomplete.Select(); break; // Enter
		}
		
		if (/^(38|40|27|16|13|9)$/.test(e.which)) return
		if (TheAutocomplete.ttm != null) {
			clearTimeout(TheAutocomplete.ttm);
		}
		if (elem.value.replace(/^\s+|\s+$/gm,'') == '') { return; } // Evita que se dispare la petición ajax con una consulta vacía.
		TheAutocomplete.ttm = setTimeout(function () {
			TheAutocomplete.triggerAjax();
		}, TheAutocomplete.options.delay);
		e.preventDefault()
		e.stopPropagation()
	});

	this.On = function () {
		var rect = elem.getBoundingClientRect();
		TheAutocomplete.theUl.style.width = rect.width+'px';
		//TheAutocomplete.theUl.style.top = (rect.top+rect.height)+'px';
		TheAutocomplete.theUl.style.top = (rect.bottom)+'px'; // El viejo truco...
		TheAutocomplete.theUl.style.left = rect.left+'px';
		TheAutocomplete.theUl.style.display = 'block';
	}
	
	this.Off = function () {
		TheAutocomplete.theUl.style.display = 'none';
		elem.setAttribute('autocomplete','off');
	}
	
	this.Sig = function () {
		if (TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item') == null) { return; } // Significa que no hay ítems en el selector.
		if (elem.getAttribute('autocomplete') == 'off') { TheAutocomplete.On(); }
		var lis = TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item.selected');
		if (lis == null) {
			// No había ninguno seleccionado, por lo tanto seleccionar el primero y salid...
			TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item:first-child').classList.add('selected');
			return;
		}
		lis.classList.remove('selected');
		lis = lis.nextElementSibling;
		if (lis) {
			// Hay un siguiente del actual, poner ese como seleccionado.
			lis.classList.add('selected');
		} else {
			// Si no, volver el primero como seleccionado.
			TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item:first-child').classList.add('selected');
		}
	}
	
	this.Prev = function () {
		if (TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item') == null) { return; } // Significa que no hay ítems en el selector.
		var lis = TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item.selected');
		if (lis == null) {
			// No había ninguno seleccionado, por lo tanto seleccionar el primero y salid...
			TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item:last-child').classList.add('selected');
			return;
		}
		lis.classList.remove('selected');
		lis = lis.previousElementSibling;
		if (lis) {
			// Hay un siguiente del actual, poner ese como seleccionado.
			lis.classList.add('selected');
		} else {
			// Si no, volver al primero.
			TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item:last-child').classList.add('selected');
		}
	}
	
	this.Select = function () {
		var lis = TheAutocomplete.theUl.querySelector('li.rbtAutocomplete-item.selected');
		TheAutocomplete.Off();
		lis.click();
	}
	
	this.ExtraData = function (param) {
		TheAutocomplete.extraData = Object.assign({}, TheAutocomplete.extraData, param);
	}
	
	this.triggerAjax = function () {
		if (TheAutocomplete.options.onFetch && (typeof TheAutocomplete.options.onFetch == 'function')) {
			TheAutocomplete.options.onFetch();
		}
		var queryStrings = new Object();
		if (TheAutocomplete.options.content) {
			queryStrings.content = TheAutocomplete.options.content;
		}
		if (TheAutocomplete.options.archivo) {
			queryStrings.archivo = TheAutocomplete.options.archivo;
		}
		if (TheAutocomplete.options.fixedData) {
			queryStrings = Object.assign({},queryStrings,TheAutocomplete.options.fixedData);
		}
		if (TheAutocomplete.extraData) {
			queryStrings = Object.assign({},queryStrings,TheAutocomplete.extraData);
			TheAutocomplete.extraData = {}; // Limpiar los parámetros extra para que no se acumulen.
		}
		queryStrings.term = elem.value.trim().replace(/^\s+|\s+$/gm,''); // Eliminar espacios repetidos.
		/* Como queryStrings es un object, hay que convertirlo en un string que HTTP entienda en la forma campo1=valor1&campo2=valor2*/
		let valores = Object.keys(queryStrings).map(function(idx) { return idx+'='+((queryStrings[idx])?queryStrings[idx]:''); }).join('&');

		/* Esto efectivamente efectua la petición al servidor */
		TheAutocomplete.objAjax.open('POST',TheAutocomplete.options.source);
		TheAutocomplete.objAjax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		TheAutocomplete.objAjax.send(valores);
		elem.setAttribute('autocomplete','on');
		elem.classList.add('rbtAutocomplete-loading');
	}
	
/**
* Summary. Esto es el grueso de la petición Ajax.
*/
	this.objAjax.onreadystatechange = function (ev) { // ev es el objeto evento.
		switch (TheAutocomplete.objAjax.readyState) {
			case 0:  // nada
				// console.log('No se ha enviado nada todavía.'); 
				break;
			case 1: // Hay una petición que se acaba de abrir con open()
				TheAutocomplete.intError = false;
				if (typeof TheAutocomplete.options.onOpen == 'function') {
					TheAutocomplete.options.onOpen.call(TheAutocomplete);
				}
				break;
			case 2: // Se recibieron las cabeceras del servidor.
				if (typeof TheAutocomplete.options.onFirstArrive == 'function') {
					TheAutocomplete.options.onFirstArrive.call(TheAutocomplete);
				}
				break;
			case 3:  // Se recibió un paquete de datos desde el servidor.
				if (typeof TheAutocomplete.options.onLoading == 'function') {
					TheAutocomplete.options.onLoading.call(TheAutocomplete);
				}
				break;
			case 4: // La petición se completó. Esto es lo que nos interesa.
				//console.log('Petición completada');
				TheAutocomplete.objResponse = TheAutocomplete.parseJson(TheAutocomplete.objAjax.responseText);
				if (TheAutocomplete.objResponse != null) {
					if (TheAutocomplete.objResponse.dataerr) {
						TheAutocomplete.intError = true;
						console.log('Se devolvió error de datos: '+TheAutocomplete.objResponse.dataerr.toString());
					} else {
						if (TheAutocomplete.objResponse.generr) {
							TheAutocomplete.intError = true;
							console.log('Se devolvió error general: '+TheAutocomplete.objResponse.generr.toString());
						}
					}
				}
/* <?php
 if (DEVELOPE) {
 ?> */
				 TheAutocomplete.intError = /("|')xdebug-error\b.*xe-/im.test(TheAutocomplete.objAjax.responseText) || TheAutocomplete.intError;
/* <?php
 }
 ?> */
				if (TheAutocomplete.options.onFinished && (typeof TheAutocomplete.options.onFinished == 'function')) {
					TheAutocomplete.options.onFinished(elem, TheAutocomplete.objResponse);
				}
				TheAutocomplete.Finished();
				break;
		}
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
			cleanText = aux[1];
		}
		try {
			json = eval("("+cleanText+")");
		} catch(err) {
		}
		return json;
	}

/**
* Summary. Esto se ejecuta cuando la petición Ajax regresa el resultado del servidor
*/
	this.Finished = function () {
		elem.classList.remove('rbtAutocomplete-loading');
		TheAutocomplete.theUl.innerHTML = '';
		elem.setAttribute('autocomplete','on');
		var data = TheAutocomplete.objResponse;
		if ((data == null) || (typeof data != 'object')) { console.log('Respuesta errónea del servidor'); TheAutocomplete.theUl.innerHTML = '<li class="rbtAutocomplete-item">No hay resultados</li>'; return; }
		if (data.length == 0) { TheAutocomplete.theUl.innerHTML = '<li class="rbtAutocomplete-item">No hay resultados</li>'; return; }
		for (let i=0;i<data.length;i++) {
			var li = document.createElement('li');
			li.classList.add('rbtAutocomplete-item');
			if (data[i].label) {
				li.innerHTML = data[i].label;
			} else {
				li.innerHTML = data[i];
			}
			li.frmdata = data[i];
			/* Esto es lo que pasa cuando el usuario selecciona un resultado */
			li.addEventListener("click", function () { 
				if (typeof this.frmdata == 'string') {
					elem.value = this.frmdata;
				} else {
					if (this.frmdata.value) {
						elem.value = this.frmdata.value;
					} else {
						if (this.frmdata.label) {
							elem.value = this.frmdata.label;
						}
					}
				}
				if (TheAutocomplete.options.onSelect && (typeof TheAutocomplete.options.onSelect == 'function')) {
					TheAutocomplete.options.onSelect(this, this.frmdata);
				}
			});
			TheAutocomplete.theUl.appendChild(li);
		}
		TheAutocomplete.On();
	}
/* El <body> debe tener position: relative */
		document.getElementsByTagName('body')[0].style.position = 'relative';

/* Si se hace clic en cualquier parte, hay que cerrar el UL */
		document.getElementsByTagName('body')[0].addEventListener("mouseup", function () {
			TheAutocomplete.theUl.style.display = 'none';
			elem.setAttribute('autocomplete','off');
		});

/* Si la ventana se redimensiona, hay que redimensionar los UL según cómo se redimensionen los inputs al que están atados. */
		window.addEventListener('resize', function () {
			if (TheAutocomplete.theUl) {
				var rect = elem.getBoundingClientRect();
				TheAutocomplete.theUl.style.width = rect.width+'px';
				TheAutocomplete.theUl.style.top = ((rect.bottom))+'px';
				TheAutocomplete.theUl.style.left = rect.left+'px';
			} // if
		}); // addEventListener
		
		window.addEventListener('scroll', function () {
			if (TheAutocomplete.theUl) {
				var rect = elem.getBoundingClientRect();
				TheAutocomplete.theUl.style.width = rect.width+'px';
				TheAutocomplete.theUl.style.top = ((rect.bottom))+'px';
				TheAutocomplete.theUl.style.left = rect.left+'px';
			} // if
		})
}