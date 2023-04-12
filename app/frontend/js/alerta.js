/*
    Scrip js para crear alertar
    Create: 2021-01-20.
    Author: Juampa.
*/
/**
 * Summary. Genera una alerta para notificar
 * @param array Se pasa un array que que contiene el "texto","titulo","icon","error:true","textClass","textStyle","TextCancel","OnCancel","TextConfirm","OnConfirm","position". siendo que OnConfirm puede ser el nombre de la funcion en string o bien una funcion como parametro.
 */
function Alerta(config){
    // Propiedades
    this.instancias = 0;
    this.titulo = "";
    this.cssTitulo = null;
    this.styleTitulo = null;

    this.texto = "";
    this.efectoCss = null;
    this.cssTexto = null;
    this.altoTotalAlerta = null;
    this.posTotalAlerta = null;
    this.ccsBtnConfirm = null;
    this.styleTexto = null;
    this.multiplesInstancias = false;

    this.cerrarAlConfirar = false;
    this.cerrarAlCancelar = false;
    this.autoCerrar = null;
    this.posicion = "TR";
	this.relativeTo = 'fixed';
    this.tipo = "white";
    this.iconoAlerta = null;
    this.verEfecto = true;
    this.valorQuantity = null;
    this.minQuantity = null;
    this.maxQuantity = null;
    this.autoLanzar = true;
    this.textConfirm = null;
    this.textCancel = null;
    this.OnChange = null;
    this.OnConfirm = null;
    this.OnCancel = null;
    this.modoAlerta = "white";
    this.backConten = false;

    // Objetos
    this.contAlerta = [];
    this.contBackConten = [];
    this.contContenido = null;
    this.contContenidoCard = null;
    this.contCardFooter = null;
    this.buttonConten = null;
    this.contCard = null;
    this.contIcono = null;
    this.elemConfirm = null;
    this.elemCancel = null;
    this.elemClose = null;
    this.elemIcono = null;
    this.elemTitulo = null;
    this.elemTexto = null;
    this.elemAuxAlerta = null;
	this.autoCerrarTmr = null;

    if(config){
        if(config["ccsBtnConfirm"]){ this.ccsBtnConfirm = config["ccsBtnConfirm"]; }
        if(config["posicion"]){ this.posicion = config["posicion"]; }
		if(config["relativeTo"]){ this.relativeTo = config["relativeTo"]; }
        if(config["tipo"]){ this.tipo = config["tipo"]; }
        if(config["titulo"]){ this.titulo = config["titulo"]; }
        if(config["cssTitulo"]){ this.cssTitulo = config["cssTitulo"]; }
        if(config["styleTitulo"]){ this.styleTitulo = config["styleTitulo"]; }
        if(config["texto"]){ this.texto = config["texto"]; }
        if(config["cssTexto"]){ this.cssTexto = config["cssTexto"]; }
        if(config["styleTexto"]){ this.styleTexto = config["styleTexto"]; }
        if(config["icono"]){ this.iconoAlerta = config["icono"]; }
        if(config["modo"]){ this.modoAlerta = config["modo"]; }
        if(config["textConfirm"]){ this.textConfirm = config["textConfirm"]; }
        if(config["textCancel"]){ this.textCancel = config["textCancel"]; }
        if(config["OnChange"]){ this.OnChange = config["OnChange"]; }
        if(config["OnConfirm"]){ this.OnConfirm = config["OnConfirm"]; }
        if(config["OnCancel"]){ this.OnCancel = config["OnCancel"]; }
        if(config["autoCerrar"]){ this.autoCerrar = config["autoCerrar"]; }
        if(config["efecto"] && config["efecto"] == "N"){ this.verEfecto = false; }
        if(config["backConten"] && config["backConten"] == "Y"){ this.backConten = true; }
        if(config["autoLanzar"] && config["autoLanzar"] == "N"){ this.autoLanzar = false; }
        if(typeof config["valorQuantity"] == "number"){ this.valorQuantity = config["valorQuantity"]; }
        if(typeof config["minQuantity"] == "number"){ this.minQuantity = config["minQuantity"]; }
        if(typeof config["maxQuantity"] == "number"){ this.maxQuantity = config["maxQuantity"]; }
    }

    if(this.modoAlerta != "default"){
        if(this.modoAlerta == "confirm"){
            if(!this.textConfirm){ this.textConfirm = "Ok"; }
        }
        if(this.modoAlerta == "quantity"){
            if(!this.textConfirm){ this.textConfirm = "Ok"; }
        }
        if(this.modoAlerta == "color"){
            if(!this.textConfirm){ this.textConfirm = "Ok"; }
        }
    }

    this.CrearContenedorAlerta = function(){
        var body = document.querySelector("body");
        if(!this.multiplesInstancias){ this.instancias = 0; }
        this.contAlerta[this.instancias] = document.createElement("div");
        this.contAlerta[this.instancias].setAttribute("class",'col-3 p-5 bg-'+this.tipo+((this.efectoCss)? " "+this.efectoCss:""));
        if(this.posicion){
            var nZIndex = (9001+(this.instancias*500));
            if(this.posicion == "TR" || this.posicion == "RT"){
                var posAlert = (5+(this.instancias*((this.altoTotalAlerta)? this.altoTotalAlerta:0)));
                this.posTotalAlerta = posAlert;
                this.estiloAlerta = "box-shadow: #000 2px 5px 10px 0px; border-radius: 10px; z-index: "+nZIndex+";right: 5px; top: "+posAlert+"px; position: "+this.relativeTo+";";
            }
            if(this.posicion == "CC"){
                var posAlert = (50+(this.instancias*5));
                this.estiloAlerta = "box-shadow: #000 2px 5px 10px 0px; border-radius: 10px; z-index: "+nZIndex+";left: "+posAlert+"%;top: "+posAlert+"%;transform: translate(-50%, -50%); position: "+this.relativeTo+";";
            }
            if(this.posicion == "TL" || this.posicion == "LT"){
                var posAlert = (5+(this.instancias*((this.altoTotalAlerta)? this.altoTotalAlerta:0)));
                this.posTotalAlerta = posAlert;
                this.estiloAlerta = "box-shadow: #000 2px 5px 10px 0px; border-radius: 10px; z-index: "+nZIndex+";left: 5px; top: "+posAlert+"px; position: "+this.relativeTo+";";
            }
            if(this.posicion == "BR" || this.posicion == "RB"){
                var posAlert = (5+(this.instancias*((this.altoTotalAlerta)? this.altoTotalAlerta:0)));
                this.posTotalAlerta = posAlert;
                this.estiloAlerta = "box-shadow: #000 2px 5px 10px 0px; border-radius: 10px; z-index: "+nZIndex+"; top: inherit; right: 5px; bottom: "+posAlert+"px; position: "+this.relativeTo+";";
            }
            if(this.posicion == "BL" || this.posicion == "LB"){
                var posAlert = (5+(this.instancias*((this.altoTotalAlerta)? this.altoTotalAlerta:0)));
                this.posTotalAlerta = posAlert;
                this.estiloAlerta = "box-shadow: #000 2px 5px 10px 0px; border-radius: 10px; z-index: "+nZIndex+"; top: inherit; right: inherit;left: 5px; bottom: "+posAlert+"px; position: "+this.relativeTo+";";
            }
        }
        this.contAlerta[this.instancias].setAttribute("style",this.estiloAlerta);
        this.contAlerta[this.instancias].setAttribute("role",'alert');
        body.appendChild(this.contAlerta[this.instancias]);

        this.elemClose = document.createElement("button");
        this.elemClose.setAttribute("style",'background-color: #000; color: #FFF; border: none; border-radius: 50%; padding: 1px 9px; font-weight: bold; position: '+this.relativeTo+'; right: -14px; top: -14px; font-size: 18px; z-index: 999999999999999999;');
        this.elemClose.setAttribute("type",'button');
        this.elemClose.setAttribute("alert-id",this.instancias);
        this.elemClose.onclick = this.CerrarAlerta;
        this.elemClose.innerHTML = '<i class="fas fa-times" style="vertical-align: middle;"></i>';
        this.contAlerta[this.instancias].appendChild(this.elemClose);

        if(this.backConten){
            if(!this.multiplesInstancias){ this.instancias = 0; }
            var nZIndex = (8999+(this.instancias*499));
            this.contBackConten[this.instancias] = document.createElement("div");
            this.contBackConten[this.instancias].setAttribute("style",'background-color: #000; width: 100%; height: 100%; position: fixed; opacity: 70%; z-index: '+nZIndex+'; top: 0; left: 0;');
            this.contBackConten[this.instancias].onclick = this.CerrarAlerta;
            body.appendChild(this.contBackConten[this.instancias]);
        }
        
        if(this.iconoAlerta){
            this.contContenido = document.createElement("div");
            this.contContenido.setAttribute("class",'row');
            this.contAlerta[this.instancias].appendChild(this.contContenido);

            this.contIcono = document.createElement("div");
            this.contIcono.setAttribute("class",'col-3 ');
            this.contContenido.appendChild(this.contIcono);

            this.contContenidoCard = document.createElement("div");
            this.contContenidoCard.setAttribute("class",'col-9 ');
            this.contContenido.appendChild(this.contContenidoCard);
            this.ColocarIcono();
        }else{
            this.contContenidoCard = this.contAlerta[this.instancias];
        }

        this.contCard = document.createElement("div");
        this.contCard.setAttribute("class",'card-header border-0');
        this.contContenidoCard.appendChild(this.contCard);
    }

    this.ColocarIcono = function(){
        this.elemIcono = document.createElement("div");
        this.elemIcono.setAttribute("style",'top: 0;height: 100%; width: 100%;position: absolute;');
        this.elemIcono.innerHTML = '<i style="font-size: 11vh; left: 50%; top: 50%; position: relative; transform: translate(-50%, -50%);" class="text-center fa '+this.iconoAlerta+((this.efectoCss)? " animarIcono":"")+'"></i>';
        this.contIcono.appendChild(this.elemIcono);
    }

    this.ColocarContenidos = function(){
        if(this.titulo){
            this.elemTitulo = document.createElement("h4");
            if(this.cssTitulo){
                this.elemTitulo.setAttribute("class",this.cssTitulo);
            }
            if(this.styleTitulo){
                this.elemTitulo.setAttribute("style",this.styleTitulo);
            }
            this.elemTitulo.innerHTML = this.titulo;
            this.contCard.appendChild(this.elemTitulo);
        }
        if(this.texto){
            this.elemTexto = document.createElement("div");
            if(this.cssTexto){
                this.elemTexto.setAttribute("class",this.cssTexto);
            }
            if(this.styleTexto){
                this.elemTexto.setAttribute("style",this.styleTexto);
            }
            this.elemTexto.innerHTML = this.texto;
            this.contCard.appendChild(this.elemTexto);
        }
        if(this.modoAlerta == "quantity"){
            this.CrearQuantity();
        }
        if(this.modoAlerta == "color"){
            this.CrearSelectColor();
        }
    }

    this.CrearSelectColor = function(){
        this.elemAuxAlerta = [];

        this.elemAuxAlerta["contenedor"] = document.createElement("div");
        this.elemAuxAlerta["contenedor"].setAttribute("class",'form-group');
        this.contCard.appendChild(this.elemAuxAlerta["contenedor"]);

        this.elemAuxAlerta["eleInput"] = document.createElement("input");
        this.elemAuxAlerta["eleInput"].setAttribute("class",'form-control mt-4 mb-4');
        this.elemAuxAlerta["eleInput"].setAttribute("type",'color');
        this.elemAuxAlerta["eleInput"].setAttribute("style",'text-align: center; height: 40px;');
        this.elemAuxAlerta["eleInput"].onChange = function(){
            if(self.OnChange){
                self.OnChange(self.elemAuxAlerta["eleInput"].value);
            }
        }
        if(this.valorQuantity){
            this.elemAuxAlerta["eleInput"].value = this.valorQuantity;
        }else{
            this.elemAuxAlerta["eleInput"].value = "";
        }
        this.elemAuxAlerta["contenedor"].appendChild(this.elemAuxAlerta["eleInput"]);
    }

    this.CrearQuantity = function(){
        this.elemAuxAlerta = [];

        this.elemAuxAlerta["contenedor"] = document.createElement("div");
        this.elemAuxAlerta["contenedor"].setAttribute("class",'input-group mt-4 mb-4');
        this.contCard.appendChild(this.elemAuxAlerta["contenedor"]);

        this.elemAuxAlerta["contMenos"] = document.createElement("div");
        this.elemAuxAlerta["contMenos"].setAttribute("class",'input-group-prepend');
        this.elemAuxAlerta["contenedor"].appendChild(this.elemAuxAlerta["contMenos"]);

        this.elemAuxAlerta["elemMenos"] = document.createElement("span");
        this.elemAuxAlerta["elemMenos"].setAttribute("class",'input-group-text');
        this.elemAuxAlerta["elemMenos"].setAttribute("style",'cursor:pointer;');
        this.elemAuxAlerta["elemMenos"].onclick = function(){
            self.AccionBtnQuantity("-");
        }
        this.elemAuxAlerta["elemMenos"].innerHTML = "-";
        this.elemAuxAlerta["contMenos"].appendChild(this.elemAuxAlerta["elemMenos"]);

        this.elemAuxAlerta["eleInput"] = document.createElement("input");
        this.elemAuxAlerta["eleInput"].setAttribute("class",'form-control text-center bg-white');
        this.elemAuxAlerta["eleInput"].setAttribute("type",'number');
        this.elemAuxAlerta["eleInput"].setAttribute("readonly",'readonly');
        this.elemAuxAlerta["eleInput"].setAttribute("style",'text-align: center;');
        if(this.valorQuantity){
            this.elemAuxAlerta["eleInput"].value = this.valorQuantity;
        }else{
            this.elemAuxAlerta["eleInput"].value = 0;
        }
        this.elemAuxAlerta["contenedor"].appendChild(this.elemAuxAlerta["eleInput"]);

        this.elemAuxAlerta["contMas"] = document.createElement("div");
        this.elemAuxAlerta["contMas"].setAttribute("class",'input-group-append');
        this.elemAuxAlerta["contenedor"].appendChild(this.elemAuxAlerta["contMas"]);

        this.elemAuxAlerta["elemMas"] = document.createElement("span");
        this.elemAuxAlerta["elemMas"].setAttribute("class",'input-group-text');
        this.elemAuxAlerta["elemMas"].setAttribute("style",'cursor:pointer;');
        this.elemAuxAlerta["elemMas"].onclick = function(){
            self.AccionBtnQuantity("+");
        }
        this.elemAuxAlerta["elemMas"].innerHTML = "+";
        this.elemAuxAlerta["contMas"].appendChild(this.elemAuxAlerta["elemMas"]);
    }

    this.AccionBtnQuantity = function(accion){
        var cantidad = self.elemAuxAlerta["eleInput"].value;
        if(accion == "+"){
            cantidad++;
            if(typeof self.maxQuantity == "number" && self.maxQuantity < cantidad){
                cantidad = self.maxQuantity;
            }
        }else{
            cantidad--;
            if(typeof self.minQuantity == "number" && self.minQuantity > cantidad){
                cantidad = parseInt(self.minQuantity);
            }
        }
        self.elemAuxAlerta["eleInput"].value = cantidad;
        if(self.OnChange){
            self.OnChange(cantidad);
        }
    }

    this.ColocarBotones = function(){
        if(this.textConfirm || this.textCancel){
            this.contCardFooter = document.createElement("div");
            this.contCardFooter.setAttribute("class",'card-footer');
            this.contCardFooter.setAttribute("style",'background-color: transparent;');
            this.contContenidoCard.appendChild(this.contCardFooter);

            this.buttonConten = document.createElement("div");
            this.buttonConten.setAttribute("class",'row');
            this.contCardFooter.appendChild(this.buttonConten);
        }
        if(this.textCancel){
            this.elemCancel = document.createElement("button");
            this.elemCancel.setAttribute("class","btn btn-danger col-5 mx-auto");
            this.elemCancel.innerHTML = this.textCancel;
            this.elemCancel.onclick = function(){
                if(self.cerrarAlCancelar){
                    self.CerrarAlerta();
                }
                if(self.OnCancel){
                    if(typeof self.OnCancel != "string"){
                        self.OnCancel();
                    }else if(window[self.OnCancel]){
                        window[self.OnCancel]();
                    }
                }
                if(self.OnCancel && typeof self.OnCancel != "string"){
                    self.OnCancel();
                }
            }
            this.buttonConten.appendChild(this.elemCancel);
        }
        if(this.textConfirm){
            this.elemConfirm = document.createElement("button");
            this.elemConfirm.setAttribute("class","btn btn-primary col-5 mx-auto "+this.ccsBtnConfirm);
            this.elemConfirm.innerHTML = this.textConfirm;
            this.elemConfirm.onclick = function(){
                if(self.cerrarAlConfirar){
                    self.CerrarAlerta();
                }
                if(self.OnConfirm){
                    if(typeof self.OnConfirm != "string"){
                        self.OnConfirm();
                    }else if(window[self.OnConfirm]){
                        window[self.OnConfirm]();
                    }
                }
            }
            this.buttonConten.appendChild(this.elemConfirm);
        }
    }

    //Los efectos se ejecutan 10 veces por segundo
    this.AnimarAparicion = function(){
        var elemEffect = this.contAlerta[this.instancias];
        this.altoTotalAlerta = (elemEffect.offsetHeight+5);
        var altoTotalAlerta = (this.altoTotalAlerta*-1);
        if(this.posicion){
            if(this.posicion == "TL" || this.posicion == "TR"){
                this.animarArriba(elemEffect,altoTotalAlerta);
            }
            if(this.posicion == "BL" || this.posicion == "BR"){
                this.animarAbajo(elemEffect,altoTotalAlerta);
            }
            if(this.posicion == "CC"){
                this.animarCentro(elemEffect,0);
            }
        }
    }

    this.animarAbajo = function(elemEffect,altoTotal){
        altoTotal = (altoTotal+((this.posTotalAlerta == 5)? 30:this.posTotalAlerta*0.3));
        if(altoTotal < this.posTotalAlerta){
            elemEffect.setAttribute("style",this.estiloAlerta+" bottom:"+altoTotal+"px;");
            setTimeout(function(){
                self.animarAbajo(elemEffect,altoTotal);
            },50);
        }else{
            elemEffect.setAttribute("style",this.estiloAlerta);
        }
    }

    this.animarArriba = function(elemEffect,altoTotal){
        altoTotal = (altoTotal+((this.posTotalAlerta == 5)? 30:this.posTotalAlerta*0.3));
        if(altoTotal < this.posTotalAlerta){
            elemEffect.setAttribute("style",this.estiloAlerta+" top:"+altoTotal+"px;");
            setTimeout(function(){
                self.animarArriba(elemEffect,altoTotal);
            },50);
        }else{
            elemEffect.setAttribute("style",this.estiloAlerta);
        }
    }

    this.animarCentro = function(elemEffect,opacidadEle){
        opacidadEle = (opacidadEle+20);
        if(opacidadEle < 100){
            elemEffect.setAttribute("style",this.estiloAlerta+" opacity:"+opacidadEle+"%;");
            setTimeout(function(){
                self.animarCentro(elemEffect,opacidadEle);
            },50);
        }else{
            elemEffect.setAttribute("style",this.estiloAlerta);
        }
    }

    this.CerrarAlerta = function(){
        var instanciaAlerta = self.instancias;
        if(this && typeof this == "object" && this.getAttribute && this.getAttribute("alert-id") != ""){
            instanciaAlerta = this.getAttribute("alert-id");
        }
        if(!this.multiplesInstancias){ instanciaAlerta = 0; }
        if(self.contAlerta[instanciaAlerta]){
            self.contAlerta[instanciaAlerta].remove();
        }
        if(self.contBackConten[instanciaAlerta]){
            self.contBackConten[instanciaAlerta].remove();
        }
        self.instancias--;
		clearTimeout(self.autoCerrar);
    }

    this.Show = function(){
        this.CrearContenedorAlerta();
        this.ColocarContenidos();
        this.ColocarBotones();
        if(this.verEfecto){
            this.AnimarAparicion();
        }
        if(this.autoCerrar){
            this.autoCerrarTmr = setTimeout(function(){
                self.CerrarAlerta();
            },this.autoCerrar);
        }
        this.instancias++;
    }

    var self = this;
	Show();
    return this;
};