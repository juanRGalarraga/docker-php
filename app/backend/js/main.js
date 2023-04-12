var rulo = new rbtRulo,
    evalresult = new rbtEvalResult;

<?php
	if (DEVELOPE) {
?>
var bCtrl = false;
var bAlt = false;
document.addEventListener("DOMContentLoaded", function () {
	document.addEventListener("keydown", function(e){
		if(e.keyCode == 18){
			bAlt = true;
		}
		if(e.keyCode == 17){
			bCtrl = true;
		}else{
			if(bCtrl && e.keyCode == 83){
				e.preventDefault();
			}
		}
	}, false);
	document.addEventListener("keyup", function(e){
		if(e.keyCode == 17){
			bCtrl = false;
		}
		if(e.keyCode == 18){
			bAlt = false;
		}
		if(bCtrl && e.keyCode == 121){ // F10
			new modalBs5Creator({
				archivo: 'watchAll',
				content: 'watch'
			}).Show();
		}
		if(bAlt && e.keyCode == 113){ // F2
			new modalBs5Creator({
				archivo: 'watchAll',
				content: 'watch',
				extraparams: {what: 'unset'}
			}).Show();
		}
	}, false);
});

<?php
	}
	if ($objeto_contenido->alias != 'login') {
?>

var userTL = null;
userTimeLeft();

function userTimeLeft() {
	if (userTL != null) {
		clearTimeout(userTL); userTL = null;
	}
	userTL = setTimeout(function () {
		getAjax({
			archivo: 'userTimeleft',
			extraparams: 'user_id=<?php echo @$objeto_usuario->id; ?>'
		},function (a,b,c,d) {
			if (a == 200) {
				var result = parseJson(c);
				if (result.quit) {
					if (result.quit == 'true') {
						window.location.reload();
						return;
					}
				}
			}else{
				window.location.reload();
				return;
			}
			userTimeLeft();
		});
	},(1000*<?php
	if (isset($objeto_usuario->tsession)) { echo ($objeto_usuario->tsession+5); } else { echo '60*2'; }
	?>));
}

<?php
	}
?>;


	/** COMIENZO CONTADOR */
/**
 * Esto sirve para hacer que los números vayan de 0 a x aumentando su valor constantemente
 * Para usarlo necesitas algún elemento que tenga la clase counter y un atributo llamado data-count donde se almacenara el valor númerico al que quieres aumentar
 */
 document.addEventListener("DOMContentLoaded",()=>{
	agregarContador();
	Contador();
})
function agregarContador(){
    var elements = document.querySelectorAll(".counter[data-count]");
    if(elements.length > 0){
		for(var ele of elements){
			if(typeof ele == 'object'){
				var observer = new MutationObserver(obs => {
					Contador();
                })
                observer.observe(ele,{ attributes: true });
            }
        }
    }
}

function Contador(){
    const options2 = { style: 'decimal' };
    const numberFormat2 = new Intl.NumberFormat('es-AR', options2);
    let counters = document.querySelectorAll('.counter');
	const speed = 400;
	counters.forEach(counter => {
		const animate = () => {
		const value = +counter.getAttribute('data-count');
		console.log()
		const format = counter.getAttribute('data-format');
		var valor = counter.innerText.replace(/,[0-9]+$/g,"");
		const data = +parseInt(valor.replace(/[^0-9]+/g,""));
		
		const time = value / speed;
		if(data < value) {
				var newVal = Math.ceil(data + time);
				if(format){
					newVal = numberFormat2.format(newVal);
					if(value.toString().search(/(?=.*,?\.?[1-9])[,|\.]{1}[0-9]+$/) !== -1){
						newVal += ",00";
					}
				}
				counter.innerText = newVal;
				setTimeout(animate, 1);
			}else{
				if(format){
					var newVal = numberFormat2.format(value)
					counter.innerText = newVal;
				}else{
					counter.innerText = value;
				}
			}
		}
		animate();
	});

	
}
/** FIN CONTADOR */

document.addEventListener("DOMContentLoaded",function(){
    AsignarElementosTipoPrecio();
    const observer = new MutationObserver(list => {
        AsignarElementosTipoPrecio();
    });
    observer.observe(document.body, {attributes: false, childList: true, subtree: true});
});


function ArmarAlias(self,id = "alias_negocio"){
    alias = "";
    if(self && self.value ){
        //Quitar simbolos
        simbolos = {'!':' ','#':' ','$':' ','%':' ','(':' ',')':' ','=':' ','?':' ','¿':' ','Ç':' ','}':' ','{':' ','+':' ','/':' ','*':' ','[':' ',']':' ','^':' ','¡':' ','@':' ','~':' ','€':' ','&':' ',':':' ','.':' ',',':' ','°':'o','ª':'a','ã':'a','à':'a','á':'a','ä':'a','â':'a','è':'e','é':'e','ë':'e','ê':'e','ì':'i','í':'i','ï':'i','î':'i','ò':'o','ó':'o','ö':'o','ô':'o','ù':'u','ú':'u','ü':'u','û':'u','ñ':'n','ç':'c'};
        var alias = self.value.replaceAll(" ","-");
        alias = alias.toLowerCase();
        if(alias){
            //Buscamos y remplazamos los simbolos
            alias = alias.split('').map( letra => simbolos[letra] || letra).join('').toString();
            alias = alias.replaceAll(" ","");
            document.getElementById(id).value = alias;
        }
    }else{
        document.getElementById(id).value = alias;
    }
}

//Filtrador de keys para la busqueda en listados
var prevSearch = "";
function FilterSearch(self,func){
	if(typeof func !== 'object' && typeof func !== 'function'){ return; }
	var value = self.value;
	if(value.search(/^[a-z0-9,\.@-\s]+$/) === -1){
		self.value = value.replace(/[^a-z0-9,\.@-\s]+/,"");
		value = self.value;
	}

	if(value == prevSearch){ return; }
	if(value.trim() == "" && prevSearch == ""){ return; }
	prevSearch = value;
	self.value = value.trim();
	if(typeof func == "function"){
		func();
	}else{
		func.Get(self);
	}
}