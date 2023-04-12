<?php /*
	File: base.js
	Author: DriverOp
	Created: 2010-07-11
	Modified: 2014-06-10
	Last modif: Improved LoadJS and LoadCSS functions. Added replaceDots().
	Added deleteDots().
	Added restoreComma().
	Modif: 2018-10-26
	Desc: Agregado String.sprintf
	Modif: 2018-11-04
	Desc: Agregado String.stripSpaces
	Modif: 2018-11-14
	Desc: Agregado String.replaceSpaces
	Modif: 2020-01-08
	Desc: isNumeric() ahora pasa la validación cuando n contiene la coma como separador de decimales.
	Modif: 2020-09-10
	Desc: Agregado forEach como método de NodeList
	Modif: 2020-09-25
	Desc: Agregar getValueSelect
	Modif: 2021-02-03
	Desc: Agreegados prototipos de Storage para poder almacenar y recuperar objetos.
*/ ?>
;
String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, "");    };
String.prototype.replaceComma = function() { return this.replace(/,/g, ".");    };
String.prototype.restoreComma = function() { return this.replace(/\./g, ",");    };
String.prototype.replaceDecimalDot = function() { return this.replace(/\./g, ",");    };
String.prototype.isint = function() {
	var regint = /^[\+|\-]*\d+$/;
	return regint.test(this.trim());
};
String.prototype.isfloat = function() {
	var regfloat = /^[\+|\-]*\d+(\.?\d*)$/;
	return regfloat.test(this.trim().replaceComma());
};
String.prototype.replaceDots = function() {	return this.replace(/\./g,"-"); }
String.prototype.deleteDots = function() {	return this.replace(/\./g,''); }
String.prototype.replaceHypen = function() { return this.replace(/-/g,"/"); }
String.prototype.deleteNonNumericChars = function() {
	if (this.length == 0) {
		return this;
	}
	var aux = '';
	for (x=0;x<this.length;x++) {
		if (/[\d,\.]/.test(this[x])) {
			aux = aux+this[x];
		}
	}
	return aux;
}
String.prototype.sprintf = function () {
	var args = arguments;
	return this.replace(/\[(\d+)\]/g,
		function (coincidencia, numero) {
			return (typeof args[numero] != 'undefined'?args[numero]:coincidencia);
		}
	);
}

String.prototype.stripSpaces = function() {
	return this.replace(/\s+/g, "");
}
String.prototype.replaceSpaces = function(rip) {
	return this.replace(/\s+/g, rip);
}
String.prototype.replaceAll = function(str1, str2, ignorar) {
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignorar?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
}

Number.prototype.lpad = function (width, z) {
  z = z || '0';
  n = this + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

Number.prototype.rpad = function (width, z) {
  z = z || '0';
  n = this + '';
  return n.length >= width ? n : n + new Array(width - n.length + 1).join(z);
}

function isNumeric(n) {
  return (Object.prototype.toString.call(n) === '[object Number]' || Object.prototype.toString.call(n) === '[object String]') &&!isNaN(parseFloat(n.toString().replaceComma())) && isFinite(n.toString().replaceComma().replace(/^-/, ''));
}

String.prototype.capitalize = function () {
	return this.charAt(0).toUpperCase() + this.slice(1);
}

NodeList.prototype.forEach = Array.prototype.forEach;

Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
}

Storage.prototype.getObject = function(key) {
    var value = this.getItem(key);
    return value && JSON.parse(value);
}

function Debug(texto,add) {
<?php
	if (DEVELOPE) {
?>
	var d = document.getElementById("debug");
	if (d == null) {
		d = document.createElement('DIV');
		d.id = 'debug';
		d.setAttribute("style","display:block;position:fixed;background-color:white;z-index:5001;width:100%;min-height:4em;max-height:10em;color:black;font-family:monospace;padding:5px;top:0;left:0;overflow:auto;max-height:200px;");
		document.getElementsByTagName('body')[0].appendChild(d);
	}
	d.ondblclick = function () {
		this.style.display = 'none';
	}
	if (typeof texto == 'object') {
		texto = JSON.stringify(texto);
	}
	if (add) {
		d.innerHTML = d.innerHTML + texto;
	} else {
		d.innerHTML = texto;
	}
	d.style.display = 'block';
<?php
	}
?>
}

function parseJson(texto) {
	var json = "{}";
	var jsondata;
	var regexjson = /<json>(.*?)<\/json>/m;
	texto = texto.replace(/(\r\n|\r|\n)/gm,"");
	if (regexjson.test(texto)) {
		aux = regexjson.exec(texto);
		json = aux[1];
		jsondata = eval("("+json+")");
		return jsondata;
	} else {
		return texto;
	}
}


var cssLoader = function (list, params) {
	"use strict";
	this.defaultOptions = {
		url: "<?php echo BASE_URL; ?>css/",
		mode: "f",
		id: 'style-',
		replace: true
	}
	var theCssLoader = this;
	this.options = Object.assign({},this.defaultOptions,params);
	
	this.theId = this.options.id+list.trim().replaceDots();
	if (document.getElementById(this.theId)) {
		if (!this.options.replace) { return; }
		document.getElementById(this.theId).remove();
	}
	
	const cssStyle=document.createElement("link");
	cssStyle.setAttribute("type", "text/css");
	cssStyle.setAttribute("rel", "stylesheet");
	cssStyle.setAttribute("id", this.options.id+list.trim().replaceDots());
	cssStyle.setAttribute("href", this.options.url+this.options.mode+'/'+list+'/');
	document.getElementsByTagName("head")[0].appendChild(cssStyle);
}

function LoadCSS(cssFile) {  
	var d = new Date();
	var id = "css-"+cssFile.trim().replaceDots();
	var e = document.getElementById(id);
	if (e != undefined) {
		document.getElementsByTagName("head")[0].removeChild(e);
	}
	var cssLink=document.createElement("link");
	cssLink.setAttribute("rel", "stylesheet");
	cssLink.setAttribute("type", "text/css");
	cssLink.setAttribute("media", "all");
	cssLink.setAttribute("href", "<?php echo BASE_URL; ?>css/f/"+cssFile+"/"+d.getTime());
	cssLink.setAttribute("id",id);
	document.getElementsByTagName("head")[0].appendChild(cssLink);
}

var jsLoader = function (list, params) {
	"use strict";
	this.defaultOptions = {
		url: "<?php echo BASE_URL; ?>js/",
		mode: "f",
		loadAsync: false,
		fncCallBack: null,
		id: 'js-',
		replace: true
	}
	var theJsLoader = this;
	this.options = Object.assign({},this.defaultOptions,params);
	
	this.theId = this.options.id+list.trim().replaceDots();
	if (document.getElementById(this.theId)) {
		if (!this.options.replace) { return; }
		document.getElementById(this.theId).remove();
	}
	
	var jsScript=document.createElement("script");
	jsScript.setAttribute("type", "text/javascript");
	jsScript.setAttribute("id", this.options.id+list.trim().replaceDots());
	jsScript.setAttribute("src", this.options.url+this.options.mode+'/'+list+'/');
	if (this.loadAsync) {
		jsScript.setAttribute("async","true");
	}
	jsScript.addEventListener('load', function () {
		if (typeof window[theJsLoader.options.fncCallBack] == 'function') {
			window[theJsLoader.options.fncCallBack]();
		}
	});
	
	document.getElementsByTagName("head")[0].appendChild(jsScript);
}

function LoadJS(jsFile, loadAsync, fncCallback) {
	var d = new Date();
	var id = "js-"+jsFile.trim().replaceDots();
	var e = document.getElementById(id);
	if (e != undefined) {
		document.getElementsByTagName("head")[0].removeChild(e);
	}
	var jsScript=document.createElement("script");
	jsScript.setAttribute("type", "text/javascript");
	jsScript.setAttribute("id", id);
	jsScript.setAttribute("src", "<?php echo BASE_URL; ?>js/f/"+jsFile+"/"+d.getTime());
	if (loadAsync) {
		jsScript.setAttribute("async","true");
	}
	if (typeof fncCallback == 'function') {
		jsScript.addEventListener('load', function () { fncCallback() });
	}
	document.getElementsByTagName("head")[0].appendChild(jsScript);
}

function ReplaceCSS(cssFile, idlink) {  
	var d = new Date();
	var e = document.getElementById(idlink);
	if (e != undefined) {
		document.getElementsByTagName("head")[0].removeChild(e);
	}
	var cssLink=document.createElement("link");
	cssLink.setAttribute("rel", "stylesheet");
	cssLink.setAttribute("type", "text/css");
	cssLink.setAttribute("media", "all");
	cssLink.setAttribute("href", "<?php echo BASE_URL; ?>f/"+cssFile+"&rnd="+d.getTime());
	cssLink.setAttribute("id",idlink);
	document.getElementsByTagName("head")[0].appendChild(cssLink);
}

function RandStr(len, t) {
	var uppernumchars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var numchars = "0123456789abcdefghiklmnopqrstuvwxyz";
	var num = "0123456789";
	var chars = "abcdefghiklmnopqrstuvwxyz";
	var string_length = 8;
	if (len) {
		string_length = len;
	}
	switch (t) {
		case 0:chars = uppernumchars; break;
		case 1:chars = numchars; break;
		case 2:chars = num; break;
	}
	var result = '';
	for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		result += chars.substring(rnum,rnum+1);
	}
	return result;
}

var CKEDITOR_BASEPATH = '<?php echo BASE_URL; ?>js/';

function formatMoney(number, decPlaces, decSep, thouSep) {
	decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
	decSep = typeof decSep === "undefined" ? "," : decSep;
	thouSep = typeof thouSep === "undefined" ? "." : thouSep;
	var sign = number < 0 ? "-" : "";
	var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
	var j = (j = i.length) > 3 ? j % 3 : 0;

	return sign +
		(j ? i.substr(0, j) + thouSep : "") +
		i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
		(decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
}

function FillSelect(elSelect, laData, lasOpciones) {
	elSelect.length = 0;
	settings = {
		value:'value',
		text:'text'
	}
	settings = Object.assign({},settings,lasOpciones);
	if (laData) {
		for (x in laData) {
			var option = document.createElement('OPTION');
			option.value = laData[x][settings.value];
			option.text = laData[x][settings.text];
			elSelect.options.add(option);
		}
	}
}

function GetValueSelect(elSelect) {
	if(typeof elSelect == "string"){
		elSelect = document.querySelector(elSelect);
	}else if(typeof elSelect != "object"){
		elSelect = null;
	}
	var respuesta = [];
	if(elSelect && elSelect.nodeName && elSelect.nodeName.toLowerCase()  == "select" && elSelect.options && elSelect.options.length > 0){
		for(o in elSelect.options){
			if(elSelect.options[o].selected){
				respuesta.push(elSelect.options[o].value);
			}
		}
	}
	return respuesta;
}

function GetVal(elElement,newVal) {
	//console.log("GetVal ",elElement);
	var resp = null;
	try{
		if(typeof elElement == "string"){
			elElement = document.querySelector(elElement);
		}
		if(typeof elElement == "object"){
			if(newVal){
				elElement.value = newVal;
			}
			resp = elElement.value;
		}
	}catch(e){
		//console.log(elElement,e);
	}
	return resp;
}

var qs = function (selector) {
	this.list = document.querySelectorAll(selector);
	var theQS = this;
	this.Show = function () {
		if (theQS.list !== null && theQS.list !== undefined) {
			theQS.list.forEach((item)=>{ item.classList.remove('d-none'); });
		}
	}
	this.Hide = function () {
		if (theQS.list !== null && theQS.list !== undefined) {
			theQS.list.forEach((item)=>{ item.classList.add('d-none'); });
		}
	}
	return this;
}

var getElem = function (selector) {
	if (typeof selector != 'string') return null;
	if (document.getElementById(selector)) {
		return document.getElementById(selector);
	}
	return null;
}

var parseClassList = function(classList) {
	var theClasses = [];
	if (typeof classList == 'string') {
		theClasses = theClasses.concat(classList.split(/[,|\s]/));
	}
	if (typeof classList == 'object') {
		theClasses = theClasses.concat(classList);
	}
	return theClasses;
}

HTMLElement.prototype.addClass = function (classList) {
	let theClasses = parseClassList(classList);
	if (theClasses.length > 0) {
		for(let i = 0; i<theClasses.length; i++) {
			this.classList.add(theClasses[i]);
		}
	}
}

HTMLElement.prototype.removeClass = function (classList) {
	let theClasses = parseClassList(classList);
	if (theClasses.length > 0) {
		for(let i = 0; i<theClasses.length; i++) {
			this.classList.remove(theClasses[i]);
		}
	}
}

/**
 * Verifica si una cadena de texto está vacía, es undefined o null
 * @param {string} string Cadena de texto a verificar
 * @returns true si está vacía, false de lo contrario
 */

function empty(string){
	return (
		!string.trim() 
		|| string.trim().length == 0 
		|| typeof string == "undefined" 
		|| typeof string == "null"
	)
}
var addClasses = function (selector, clases) {
	var list = document.querySelectorAll(selector);
	if (list) {
		list.forEach((ele)=>{
			ele.addClass(clases);
		})
	}
}

var removeClasses = function (selector, clases) {
	var list = document.querySelectorAll(selector);
	if (list) {
		list.forEach((ele)=>{
			ele.removeClass(clases);
		})
	}
}

function Giro(self,giro = true){
    a = self;//button OR <a> 
    self = self.querySelector("i");//<i>
    if(self && giro){
        self.setAttribute("style","transform: rotate(-180deg); transition: all 0.38s ease 0.38s;");
        a.setAttribute("onclick","Giro(this,false);");
        setTimeout(()=> self.classList.replace("fa-plus-circle","fa-minus-circle"),550);
    }else{
        if(self && !giro){
            self.setAttribute("style","transform: rotate(0deg); transition: all 0.38s ease 0.38s;");
            a.setAttribute("onclick","Giro(this, true);");
            setTimeout(()=> self.classList.replace("fa-minus-circle","fa-plus-circle"), 550);
        }
    }
}

function formatMoney(number, decPlaces, decSep, thouSep) {
	decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
	decSep = typeof decSep === "undefined" ? "." : decSep;
	thouSep = typeof thouSep === "undefined" ? "," : thouSep;
	var sign = number < 0 ? "-" : "";
	var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
	var j = (j = i.length) > 3 ? j % 3 : 0;

	return sign +
		(j ? i.substr(0, j) + thouSep : "") +
		i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
		(decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
}

function AsignarElementosTipoPrecio(){
	var listInputs = document.querySelectorAll("input");
	for(i in listInputs){
		if(typeof listInputs[i] == "object"){
			if(listInputs[i].getAttribute("filtro-data") && listInputs[i].getAttribute("filtro-data") == "monto"){
				AgregarMontoMex(listInputs[i]);
			}
		}
	}
}

function AgregarMontoMex(eleInpu){
	eleInpu.setAttribute("type","text");
	eleInpu.addEventListener('keyup', function (event) {
		var posicion = null;
		if(event){
			event.preventDefault();
			// si viene con event y son flechitas o suprimir o borrar no hace el evento
			if(!((event.keyCode >= 37 && event.keyCode <= 40) || event.keyCode == 8 || event.keyCode == 46)){
				posicion = event.target.selectionStart;
				FomratearPrecioComas(this,posicion);
			}
		}else{
			FomratearPrecioComas(this,posicion);
		}
	});
	eleInpu.addEventListener('blur', function () { DesfomratearPrecioComas(this); });
	FomratearPrecioComas(eleInpu);
}

function FomratearPrecioComas(element,posicion){
    var permitidos = ["1","2","3","4","5","6","7","8","9","0",",",".","-"];
    var monto = "";
    if(typeof element == "object" && element.value){
    	monto = element.value;
    }else if(typeof element == "string"){
    	monto = element;
    }else{
    	return element;
    }
    // montoSeparado 0 -> es la parte entera y 1 -> es la parte decimal
	var montoSeparado = monto.split(",");
    // Variable que se utilizara para almacenar el numero natural
	var montoAux = "";
	var inicialLength = monto.length;
	// Verficio que el ultimo caracter no sea un punto porque si es un punto no hago nada
    if(monto[(monto.length-1)] != ","){
        // pregunto si existe la parte entera
		if(montoSeparado[0]){
			// les scao las comas a la parte entera
            monto = montoSeparado[0].replaceAll(".","");
            // Recorro el monto natural procesado
			for(c in monto){
				// Verifico que el char que esta siendo analizado sea un string y este dentro de los chars permitidos
                if(typeof monto[c] == "string" && permitidos.includes(monto[c])){
                    // Agrego al numero natural este char
					montoAux += monto[c];
                }
            }
            // Aca le digo que monto es el numero natural recien procesado arriba
			monto = montoAux;
            montoAux = "";
            // Aca revierto el string procesado para agregar facilmente de atras para adelante las comas
			var montoRev = monto.split("").reverse();
            var char = 0;
            var lenMont = monto.length;
            for(c in montoRev){
                char++;
                lenMont--;
                montoAux += montoRev[c];
                // a cada 3 caracteres voy agregando una coma
				if(char == 3 && lenMont > 0){
                    char = 0;
                    montoAux += ".";
                }
            }
			// Realizo un reverse nuevamente para mostrar el numero naturalmente
            montoAux = montoAux.split("").reverse().join("");
        }
		// Pregunto si el numero contiene una parte decimal y si la tiene la sumo al numero
        if(montoSeparado[1] && montoSeparado[1] != ""){
            var parteDecimal = montoSeparado[1].replace(",","");
			montoAux = montoAux+","+parteDecimal.substr(0,2);
        }
		// Consulto si el texto procesado contiene un signo negativo 
		if(montoAux.includes("-")){
			var numeroNegativo = false;
			// Pregunto si esta en la primera posiciòn
			if(montoAux[0] == "-"){ numeroNegativo = true; }
			// toma el simbolo menos por un numero y agrega una coma de mas por eso lo tengo que  eliminar
			if(montoAux[1] == ","){ montoAux = montoAux.replace(".",""); }
			// Directamente elimino todos los simbolos negativos
			montoAux = montoAux.replaceAll("-","");
			// Pregunto si el primer caracter era un menos coloco nuevamente un simbolo negativo
			if(numeroNegativo){ montoAux ="-"+montoAux;	}
		}
		// Consulto si el elemento que me pasaron es distinto  de un string
        if(typeof element != "string"){
	        // El elemento vale el numero procesado
			element.value = montoAux;
	        // Consulto si tengo la posicion
			if(posicion){
				var finalLength = element.value.length;
				var restPos = (finalLength-inicialLength); 
				posicion = (posicion+restPos);
	            ColocarCursorEnLaPosicion(element,posicion);
	        }
        }
        return montoAux;
    }
}

function DesfomratearPrecioComas(element){
    var valorNatural = element.value.replaceAll(".","");
    element.setAttribute("data-valor",parseFloat(valorNatural).toFixed(2));
}

function ColocarCursorEnLaPosicion(ele, pos) {
    if (ele.setSelectionRange) {
		ele.focus();
		ele.setSelectionRange(pos, pos,"forward");
    } else if (ele.createTextRange) {
        var range = ele.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}

