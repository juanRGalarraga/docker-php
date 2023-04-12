var theRulo = new rbtRulo;
var theCarousel;

var listado = new mgrListadoCreator({
    archivo: 'listado',
    content: 'planes',
    targetElementIdName: 'listado_planes',
    rulo: theRulo,
	onFinish: mifuncion
});

var formSend = new rbtFormSend(null, {
	extraData: {
		archivo: 'postEditar',
		content: 'planes'
	}
});

var formCalc = new rbtFormSend(null, {
	extraData: {
		archivo: 'calcular',
		content: 'planes'
	}
});

const alert = Swal.mixin ({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
})

/**
* Summary. Callback del guardado de cambios del formulario de edición.
*/
var Finish = function(a,b,c,d) {
	if (d.ok) {
		alert.fire({
			icon: 'success',
			title: 'Plan guardado con éxito.'
		})
		listado.Get();
		if (d.leave) {
			getElem('btnBack').click();
		}
		return
	}
	alert.fire({
		icon: 'warning',
		title: 'Hay datos erróneos en este formulario.'
	})
}


document.addEventListener("DOMContentLoaded",()=>{
    listado.Get();
	theCarousel = document.querySelector('#carouselExampleControls');
	theCarousel = new bootstrap.Carousel(theCarousel);
	// getElem('btnConfirm').addEventListener('click',()=>{
	// 	formSend.Send(getElem('frm_plan'),Finish);
	// });
	getElem('btnBack').addEventListener('click',()=>{
		getAjax({archivo:'frmNew',content:'planes'},function(a,b,c,d){getElem('contFormPlan').innerHTML=c;});
	});
	InitEditar(null);
	getElem('btnNewPlan').addEventListener('click', ()=>{ InitEditar() });
});

function mifuncion(theTable) {
	var tr = theTable.querySelector('tbody').querySelectorAll('tr');
	if (tr) {
		tr.forEach((ele)=>{
			ele.addEventListener('dblclick', ()=>{
				rulo.Show();
				getAjax({
					archivo: 'frmEditar',
					content: 'planes',
					id: ele.dataset.id
				}, function (a,b,c,d) {
					rulo.Hide();
					if (a == 200 && !d) {
						getElem('contFormPlan').innerHTML = c;
						theCarousel.to("1");
						InitEditar(ele.dataset.id);
					}
				});
			});
		});

/**
* Summary. Establece la lógica de la interfaz para el tipo de pago.
* @param select ele El select con los tipos de pagos disponibles.
*/
var setTipoPagoLogic = function(ele) {
	addClasses('p.desc-pago','d-none');
	getElem('desc-pago-'+ele.value.toLowerCase()).removeClass('d-none');
	var opt = ele.options[ele.selectedIndex];
	addClasses('.cuotas','d-none');
	if (opt.dataset.cuotas == true) {
		removeClasses('.con-cuotas','d-none');
	} else {
		removeClasses('.sin-cuotas','d-none');
	}
	if (opt.dataset.gracias == true) {
		removeClasses('.gracias','d-none');
	} else {
		addClasses('.gracias','d-none');
	}
	if (opt.dataset.caida == true) {
		removeClasses('.caida','d-none');
	} else {
		addClasses('.caida','d-none');

	}
}
/**
* Summary. Establece los eventos del formulario de edición de planes
* @param int elid El ID del plan.
*/
var InitEditar = function(elid) {
	if (elid) {
		document.querySelectorAll('table.tabla-general tbody tr').forEach((elem)=>{elem.style.color = 'inherit'; });
		document.querySelector('table.tabla-general tbody tr[data-id="'+elid+'"]').style.color = 'blue';
	}
	var tipo_plan = getElem('tipo_plan');
	if (tipo_plan) {
		getElem('tipo_plan').addEventListener('change', (ev)=>{
			var ele = ev.target;
			addClasses('p.desc-tipo','d-none');
			getElem('desc-tipo-'+ele.value.toLowerCase()).removeClass('d-none');
		});
	}
	var tipo_pago = getElem('tipo_pagos');
	if (tipo_pago) {
		getElem('desc-pago-'+tipo_pago.value.toLowerCase()).removeClass('d-none');
		tipo_pago.addEventListener('change', (ev)=>{
			setTipoPagoLogic(ev.target)
		});
		setTipoPagoLogic(tipo_pago);
	}
	getElem('btnConfirm').addEventListener('click',()=>{
		formSend.Send(getElem('frm_plan'),Finish);
	});
	getElem('btnBack').addEventListener('click',()=>{
		window.history.replaceState(null,null,window.location.origin+window.location.pathname);
		getAjax({archivo:'frmNew',content:'planes'},function(a,b,c,d){getElem('contFormPlan').innerHTML=c;});
	});
	window.history.replaceState(null,null,window.location.origin+window.location.pathname+'#'+elid);
}

/**
* Summary. Carga mediante ajax un plan en el formulario de edición.
* @param int elid El ID del plan.
* @param int elid El ID del plan.
*/
var frmLoad = function (elid) {
	getAjax({
		archivo: 'frmEditar',
		content: 'planes',
		id: elid
	}, function (a,b,c,d) {
		if (a == 200 && !d) {
			getElem('contFormPlan').innerHTML = c;
			theCarousel.to("1");
			InitEditar(elid);
		}
	});
}

document.addEventListener("DOMContentLoaded",()=>{
    //listado.Get();
	mifuncion(getElem('tabla_planes'));
	theCarousel = document.querySelector('#carouselExampleControls');
	theCarousel = new bootstrap.Carousel(theCarousel);
	// getElem('btnConfirm').addEventListener('click',()=>{
	// 	formSend.Send(getElem('frm_plan'),Finish);
	// });
	getElem('btnBack').addEventListener('click',()=>{
		getAjax({archivo:'frmNew',content:'planes'},function(a,b,c,d){getElem('contFormPlan').innerHTML=c;});
	});
	
	getElem('btnNewPlan').addEventListener('click', ()=>{ InitEditar() });
	if (window.location.hash && window.location.hash != '') {
		let hash = window.location.hash.slice(1);
		if (isNumeric(hash)) {
			frmLoad(hash);
			theCarousel.to("1");
		}
	} else {
		InitEditar(null);
	}
});

function mifuncion(theTable) {
	var tr = theTable.querySelector('tbody').querySelectorAll('tr');
	if (tr) {
		tr.forEach((ele)=>{
			ele.addEventListener('dblclick', ()=>{
				frmLoad(ele.dataset.id);
			});
		});
	}
}

function showTest(elid) {
	getAjax({
		archivo: 'frmCalc',
		content: 'planes',
		id: elid
	}, function (a,b,c,d) {
		getElem('contenidoCalculadora').innerHTML = c;
	})
}

function calcular() {
	formCalc.Send(getElem('frmCalc'), function (a,b,c,d) {
		getElem('mostrarResultados').innerHTML = c;
	});
}