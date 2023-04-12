/*
	EvalResult 2.1
	
	Created: 2019-10-22
	Author: DriverOp
	Desc: Evalúa las respuestas custom en formato JSON enviadas en el tag <json> desde el servidor.
	Arma y pone la caja con el mensaje.
	Modif: Agregado evento manageMessage para que sea una función externa la que ponga el mensaje.
	Modif: 2019-11-18
	Desc: Ahora no intenta mostrar un mensaje si el resultado no incluye los campos generr o goodmsg
	Modif: 2020-01-07
	Desc: Agregado soporte para <SELECT> con atributo múltiple.
	Modif: 2020-02-03
	Desc: Incorporar la fecha regresada por el servidor si existe.
	Modif: 2020-04-16
	Desc: Cuando la respuesta del servidor no incluye un tag <json> ahora se trata de evaluar si lo que se devolvió es una estructura JSON "pura".
	Modif: 2021-02-07
	Desc:
		- Desagregar en una función aparte, el proceso que pone los mensajes en los elementos. Método Print().
		- Esto permite usar EvalResult como mostrador de mensajes pero sin necesidad de parsear el texto JSON.
	Modif: 2021-03-16
		- Agregar solo una vez los estilos CSS.
*/

var cFechas = function () {
	var salida = null;
	var LaFecha = this;
	
	var dia_semana = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];
	var meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
	
	this.SQLDate2Str = function (entrada) {
		var aux = entrada.split(" ");
		var work = new Date(aux[0]+'T'+aux[1]);
		salida = (dia_semana[work.getDay()]+', '+work.getDate()+' de '+meses[work.getMonth()]+' de '+work.getFullYear()+' a las '+aux[1]);
		return salida;
	}
	
}

var theStyle = document.createElement('style');
	theStyle.setAttribute('media','screen');
	theStyle.setAttribute('type','text/css');
	theStyle.setAttribute('id','rbtEvalresultStyles');
	theStyle.appendChild(document.createTextNode(""));
	theStyle.innerHTML = `.rbtMsgErr { display:block;background-color:white;padding:0.5rem;border-radius:10px;border:1px solid red;margin:auto;box-shadow: 0 0 10px 10px rgba(0, 0, 0, 0.5);transition:transform 0.3s ease-out; height:auto;transform:scale(0,0);transform-origin:center center;}
.rbtMsgErr > p { display:block;font-size:1em;margin:0;padding:0.5em;text-align:center;background-color:transparent;vertical-align:baseline;font-size:12pt;line-height:1.2; }
.rbtMsgErrVail { display:flex;justify-content:center;align-items:center;position:fixed;z-index:32000;background-color:rgba(0,0,0,0.4);width:100%;height:100%;top:0;left:0; }
.rbtMsgErr .rbtMsgErrShow { overflow:hidden; }
.rbtMsgErr.aparecium { transform:scale(1,1); }`;
document.head.appendChild(theStyle);

var rbtEvalResult = function (param) {
	
	this.defaultOptions = {
		ownClass: 'rbtMsgErr',
		idPrefix: 'aviso_',
		manageMessage: null,
		silent: false, //Cuando true, no se muestran los mensajes pero aún así Eval regresa error.
		onError: null // Evento cuando se recibe un mensaje de error.
	}
	this.TheResult = null;
	this.success = false;
	
	this.options = Object.assign({},this.defaultOptions,param);
	
	this.mode = 'err';
	
	this.theDiv = document.createElement('DIV');
	this.theDiv.setAttribute('id',this.options.idPrefix+'box');
	this.theDiv.classList.add(this.options.ownClass);
		this.theDiv.classList.add("rbtMsgErrShow","collapsed");
	if (typeof(jQuery) != 'undefined') {
	}
	
	this.theP = document.createElement('P');
	this.theP.setAttribute("id",this.options.idPrefix+'texto');
	this.theP.setAttribute("style", "");
	this.theDiv.appendChild(this.theP);
	
	this.theVail = document.createElement('DIV');
	this.theVail.setAttribute("id",this.options.idPrefix+'cortina');
	this.theVail.classList.add(this.options.ownClass+'Vail');
	this.theVail.appendChild(this.theDiv);
	var TheEval = this;
	
	this.theVail.addEventListener('click',function () {
		TheEval.theDiv.classList.remove('aparecium');
		document.getElementsByTagName('body')[0].removeChild(TheEval.theVail);
		TheEval.theP.innerHTML = '';
	});
	
	
	
	this.parseJson = function (texto) {
		var regexjson = /<json>(.*?)<\/json>/m;
		var json = "{}";
		var jsondata;
		TheEval.success = true;
		cleanText = texto.replace(/(\r\n|\r|\n)/gm,"");
		if (regexjson.test(cleanText)) {
			aux = regexjson.exec(cleanText);
			json = aux[1];
			jsondata = eval("("+json+")");
			return jsondata;
		} else {
			try {
				jsondata = eval("("+texto+")");
				return jsondata;
			} catch(err) {
				TheEval.success = false;
			}
			return texto;
		}
	}

	this.ShowBox = function () {
		var texto = '';
		if (TheEval.mode == 'gen') {
			TheEval.theP.style.color = 'red';
			texto = TheEval.TheResult.generr;
		}
		if (TheEval.mode == 'good') {
			TheEval.theP.style.color = 'green';
			texto = TheEval.TheResult.goodmsg;
		}
		TheEval.theP.innerHTML = texto;
		document.getElementsByTagName('body')[0].appendChild(TheEval.theVail);
		setTimeout(function () {TheEval.theDiv.classList.add('aparecium');},50); // Fix
	}
	
	this.SetMsgErr = function (elem, msgText) {
		if (elem != null) {
			if (typeof elem.msgerr == 'function') {
				elem.msgerr(msgText);
			} else {
				console.log(msgText);
			}
		}
	}
	
	this.Print = function (msgList) {
		if (typeof msgList == 'undefined' || msgList == null) return false;
		if (typeof msgList.dataerr == 'undefined' || msgList.dataerr == null) return false;
		TheEval.mode = 'data';
		var x;
		for (x in msgList.dataerr) {
			TheEval.SetMsgErr(document.getElementById(x),msgList.dataerr[x]);
			if (TheEval.options.addclass) {
				document.getElementById(x).classList.add(theEval.options.addclass);
			}
		}

	}
	
	this.Eval = function (Content, lafecha) {
		if (typeof Content == 'string') {
			TheEval.TheResult = TheEval.parseJson(Content);
		} else {
			TheEval.TheResult = Content;
			TheEval.success = true;
		}
		
		if (!TheEval.success) { return true; }
		if (typeof TheEval.TheResult.dataerr != 'undefined') {
			if (TheEval.TheResult.dataerr) {
				TheEval.mode = 'data';
				if (!TheEval.options.silent) {
					TheEval.Print(TheEval.TheResult); // Print verifica que exista dataerr.
				}
				if (TheEval.options.onError != null && typeof TheEval.options.onError == 'function') {
					TheEval.options.onError(TheEval);
				}
			}
			return false;
		}
		if (typeof TheEval.TheResult.ok != 'undefined') {
			TheEval.mode = 'ok';
			if (lafecha) {
				if ((document.getElementById(lafecha) != null) && (TheEval.TheResult.time != null)) {
					var f = new cFechas;
					document.getElementById(lafecha).innerHTML = f.SQLDate2Str(TheEval.TheResult.time);
				}
			}
			return true;
		}
		var result = false;
		var theMessage = '';
		if (typeof TheEval.TheResult.generr != 'undefined') {
			TheEval.mode = 'gen';
			theMessage = TheEval.TheResult.generr;
		}
		
		
		if (typeof TheEval.TheResult.goodmsg != 'undefined') {
			TheEval.mode = 'good';
			theMessage = TheEval.TheResult.goodmsg;
			result = true;
		}
		if ((TheEval.options.manageMessage != null) && (typeof TheEval.options.manageMessage == 'function')) {
			TheEval.options.manageMessage(TheEval.mode, theMessage);
		} else {
			if ((TheEval.mode == 'gen') || (TheEval.mode == 'good')) {
				TheEval.ShowBox();
			}
		}
		if (result == false && TheEval.options.onError != null && typeof TheEval.options.onError == 'function') {
			TheEval.options.onError(TheEval);
		}
		return result;

	} // Eval
	this.ShowMessage = function(theMessage,mode) {
		if (mode) {
			TheEval.mode = mode;
		} else {
			TheEval.mode = 'gen';
		}
		TheEval.TheResult = {generr:theMessage,goodmsg:theMessage};
		TheEval.ShowBox();
	}
} // rbtEvalResult 2.1
;