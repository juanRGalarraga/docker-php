/**
* Inicio Calculadora simulador.
*/

document.addEventListener('DOMContentLoaded', ()=>{
	InitCalculadora();
});

var ttl = null;

function InitCalculadora() {
	if (getElem('calculadoraSimulador') == null) { return; }
	var numberMonto = getElem('numberMonto'),
		 rangeMonto = getElem('rangeMonto'),
		numberPlazo = getElem('numberPlazo'),
		 rangePlazo = getElem('rangePlazo');
	[numberMonto,rangeMonto,numberPlazo,rangePlazo].forEach((ele)=> {
		ele.addEventListener('change', function () {
			getElem(this.dataset.target).value = normalizeValue(this);
			restartRequest(this);
		});
	});
	[numberMonto,numberPlazo].forEach((ele)=>{
		ele.addEventListener('keyup', function () { restartRequest(this) } );
	});
	;
	sessionStorage.setObject('cotiz',getCotizValues());
}

function getCotizValues() {
	return {numberMonto:numberMonto.value,rangeMonto:rangeMonto.value,numberPlazo:numberPlazo.value,rangePlazo:rangePlazo.value}
}

function restartRequest(ele) {
	if (ttl != null) {
		clearTimeout(ttl);
	}
	ttl = setTimeout(() => {updateCotiz(ele);}, 500);
}

function updateCotiz(ele) {
	var cotiz = sessionStorage.getObject('cotiz');
	if (cotiz[ele.id] != ele.value) {
		if (ele.hasAttribute('min') && (ele.value < parseInt(ele.getAttribute('min')))) { ele.value = ele.getAttribute('min'); }
		if (ele.hasAttribute('max') && (ele.value > parseInt(ele.getAttribute('max')))) { ele.value = ele.getAttribute('max'); }
		cotiz = getCotizValues();
		sessionStorage.setObject('cotiz',cotiz);
		getAjax({archivo:'getCotiz',content:'cotiz','cotiz':JSON.stringify(cotiz)},
		function(a,b,c,d,e){
			if (d.ok !== undefined && d.respuesta !== undefined) {
				getElem('res-total').innerHTML = d.respuesta.Total??'--';
				getElem('res-capital').innerHTML = d.respuesta.Capital??'--';
				getElem('res-periodo').innerHTML = d.respuesta.Dias??'--';
				getElem('res-dias').innerHTML = d.respuesta.Dias??'--';
				getElem('res-fecha_pago_txt').innerHTML = d.respuesta.Fecha_Pago_Display??'--';
				getElem('res-intereses').innerHTML = d.respuesta.Intereses??'--';
				getElem('res-gastos_administrativos').innerHTML = d.respuesta.Gastos_Administrativos??'--';
			}
		});
	}
}

function normalizeValue(ele) {
	let value = parseInt(ele.value);
	if (isNaN(value)) {
		if (ele.hasAttribute('min') && ele.hasAttribute('max')) {
			min = parseInt(ele.getAttribute('min'));
			max = parseInt(ele.getAttribute('max'));
			ele.value = parseInt(min + ((max-min)/2));
			
		}
	}
	return value;
}


/**
* Fin Calculadora simulador.
*/

