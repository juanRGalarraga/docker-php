
var theRulo = new rbtRulo;

var listado = new mgrListadoCreator({
    archivo: 'listado',
    content: 'cobros',
    targetElementIdName: 'listado',
    rulo: theRulo
});

window.addEventListener("DOMContentLoaded",()=>{
    listado.Get();
});


function SetChecked(wuqwhudhq) {
	document.getElementById('comprobante_'+wuqwhudhq).checked = !document.getElementById('comprobante_'+wuqwhudhq).checked;
	UpdateConfirmButton();
}

confirmados = [];
function UpdateConfirmButton(){
    confirmados = [];
    var inputs = document.querySelectorAll("input[type='checkbox'].confirmar-pago:checked");
	if (inputs.length > 0) {
		for (d in inputs){
			if(typeof inputs[d].value != 'undefined'){
				confirmados.push(inputs[d].value);
            }
		}
	}
	var disabled = (confirmados.length == 0);
	document.querySelectorAll('.list-actions-controls').forEach((ele)=>{
        ele.disabled = disabled;
	});
	
}


function ConfirmarCobros(){
	rulo.Show();
    getAjax({
        archivo: 'ConfirmarCobros',
        content: 'cobros',
        elementos: confirmados,
        accion: document.getElementById('accionSobreLosCobros').value.trim()
    },
    function(a,b,c,d){
		rulo.Hide();
        if(evalresult.Eval(c)){
            if(typeof evalresult.TheResult.ok != 'undefinded'){
                listado.Get();
            }
        }
    });
}
