/**
* Inicio Calculadora simulador.
*/

var calculadora = new objCalculadora({archivo:'getCotiz',content:'cotiz'});

document.addEventListener('DOMContentLoaded', ()=>{
	InitCalculadora();
});

var ttl = null;

function InitCalculadora() {
	if (getElem('calculadoraSimulador') == null) { return; }
	
	calculadora.addResponseListener(actualizarInterfaz);
	
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
		calculadora.Get({form: getElem('frmCalculadora')});
	}
}

function actualizarInterfaz(respuesta, conError) {
	if (!conError) {
		getElem('res-total').innerHTML = respuesta.Total??'--';
		getElem('res-capital').innerHTML = respuesta.Capital??'--';
		getElem('res-periodo').innerHTML = respuesta.Dias??'--';
		getElem('res-dias').innerHTML = respuesta.Dias??'--';
		getElem('res-fecha_pago_txt').innerHTML = respuesta.Fecha_Pago_Display??'--';
		getElem('res-intereses').innerHTML = respuesta.Intereses??'--';
		getElem('res-gastos_administrativos').innerHTML = respuesta.Gastos_Administrativos??'--';
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

