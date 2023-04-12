;var downloadCss = '@import "https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css";\
.vn-green { position: fixed; bottom: 0px; right: 12px; text-align: center;}\
.vn-green a{ background-color:#27ae60;display:inline-block;position:relative;margin:30px 5px;padding:20px 20px 20px 80px;color:#fff;transition:all 0.4s ease; text-decoration:none}\
.vn-green a:before{content:"\\f019";font-family:fontAwesome;position:absolute;font-style: normal;font-weight: normal;text-decoration: inherit;font-size:28px;border-radius:0 20px 0 0;color:#000;background-color:#fff;  opacity:1;  padding:14.3px;top:0;left:0}\
.vn-green a:hover{background:#2c3e50; text-decoration:underline}';

var rbtView = function(params){
	this.defaultOptions = {
		title: 'Viewer',
		windowName: 'Visualización de archivo',
		downloadButton: true,
		base: null,
		tipo: null,
		nombre: null
	};

	this.options = Object.assign({},this.defaultOptions,params);
	this.openedWindow = null;
	var ventana = this;

	this.Show = function (params){
		if(ventana.openedWindow){ ventana.Close(); }
		ventana.options = Object.assign({},this.defaultOptions,params);
		if(!ventana.options.base || !ventana.options.tipo || !ventana.options.nombre){ return; }

		ventana.openedWindow = window.open("",ventana.options.windowName);
		let html = '';
		html += '<head>';
		html += '<style>'+downloadCss+'</style>';
		html += '</head>';
		html += '<body style="margin:0!important">';
		html += '<embed width="auto" height="auto" src="data:'+ventana.options.tipo+';base64,'+ventana.options.base+'" type="'+ventana.options.tipo+'"  style="min-width: 100%; min-height: 100%;"/>';
		if(ventana.options.downloadButton){
			html += '<div class="vn-green" ><a target="_blank" href="data:'+ventana.options.tipo+';base64,'+ventana.options.base+'" download="'+ventana.options.nombre+'">Descargar</a></div>'
		}
		html += '</body>';
		ventana.openedWindow.document.getElementsByTagName("html")[0].innerHTML = html;
		ventana.openedWindow.document.title = ventana.options.title;
	}

	this.Close = function() {
		if(!ventana.openedWindow){ return; }
		ventana.openedWindow.close();
	}
}
