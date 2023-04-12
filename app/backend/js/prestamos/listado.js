;<?php
	require_once(DIR_js."rbtFileViewer.js");
?>;
;var theRulo = new rbtRulo;
var evalResult = new rbtEvalResult;
let slider = null;
var interval = null;
var viewer = new rbtView;
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

var listado = new mgrListadoCreator({
    archivo: 'listado',
    content: 'prestamos',
    targetElementIdName: 'listado_prestamos',
    rulo: theRulo
});

var historialPrestamo = new mgrListadoCreator({
    archivo: 'historial',
    content: 'prestamos/ver',
    targetElementIdName: 'historial',
    rulo: theRulo
});

window.addEventListener('load', ()=>{
    listado.Get();
	var car = document.querySelector("#carrListado");
	if(car){
		slider = bootstrap.Carousel.getInstance(car);
	}

	multiSelect({
		selector:"select#estado[multiple]",
		onOptionSelected: refreshList
	});
});

function refreshList() {
	var buscar = "";
	var estado = "";
	var mora = -1;
	var plan = -1;
	var filters = document.querySelector("#filters");
	if(!filters) return;
	var buscarFilter = filters.querySelector("#buscar");
	if(buscarFilter) {
		buscar = buscarFilter.value;
	}

	var estadoFilter = filters.querySelector("#estado");
	if(estadoFilter) {
		estado = GetValueSelect(estadoFilter);
	}

	var moraFilter = filters.querySelector("#mora");
	if(moraFilter) {
		mora = moraFilter.value;
	}

	var planFilter = filters.querySelector("#plan");
	if(planFilter) {
		plan = planFilter.value;
	}

	var filterObject = {
		buscar: buscar,
		estado: estado,
		mora: mora,
		plan: plan,
	}
	listado.SetFixedParam(filterObject);
	listado.Get();
}

function VerPrestamo(id){
	var ver = document.getElementById("visualizacion");
	if(!ver) return;
	historialPrestamo.SetFixedParam({"prestamo_id":id});
	getAjax({
		archivo: 'ver',
		content: 'prestamos',
		id:id
	},function (a,b,c,d){
		evalResult.Eval(c);
		if(evalResult.TheResult.ok){
			ver.innerHTML = c;
			historialPrestamo.Get();
			slider.to("1");
		}
	});
}

function VerComprobante(id){
	if(isNaN(parseInt(id))){ return; }
	getAjax({
		archivo: 'GetFile',
		content: 'biblioteca',
		id: id
	}, (a,b,c)=>{
		evalResult.Eval(c);
		if(evalResult.TheResult.ok){
			if(evalResult.TheResult.name && evalResult.TheResult.mime && evalResult.TheResult.data){
				viewer.Show({
					base: evalResult.TheResult.data,
					nombre: evalResult.TheResult.name,
					tipo: evalResult.TheResult.mime,
					title: 'Comprobante de pago'
				});
			}
		}

		if(evalResult.TheResult.dataerr && evalResult.TheResult.dataerr.swerr){
			alert.fire({
				title: evalResult.TheResult.dataerr.swerr,
				icon: "error"
			});
		}
	});
}
