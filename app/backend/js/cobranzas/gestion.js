;<?php 
    require_once(DIR_js."modalBs5.js");
	require_once(DIR_js."rbtFileViewer.js");
?>;

rulo = new rbtRulo;
evalresult = new rbtEvalResult;
var viewer = new rbtView;

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});



var formSend = new rbtFormSend(null, {});
/** Ventana para informar un pago */
var ventanaInformePago = new modalBs5Creator({
    archivo: 'modalInformePago',
    content: 'cobranzas',
    onShow: function () {
        formSend.SetOptions({
            dropZoneId : "adjunto",
            inputFileId : "adjunto_input",
            inputName : "adjunto_input",
            maxFileCount: 1, 
            maxFileSize: 25242880,
            onFinish: checkCompleted,
            onFileLimit : function(limit, value,file){
                if(limit == "fileCount"){
                    if(file){
                        rbtsend.addInput(file);
                    }
                }
                if(limit == "fileSize"){
                    Toast.fire({
                        icon: 'error',
                        title: 'El archivo supera el maximo permitido'
                    });
                }
            },
            extraData: {
                archivo: 'confirmarCobroManual',
                content: 'cobranzas'
            }
        });
        SetPago(document.getElementById("cuotas_selected"));
    }
});

/**
 * BUSCAR UN PRÉSTAMO POR SU ID
 **/

function BuscarPorId() {
    var result = true;
    var ele_prestamo = document.getElementById('nro_prestamo');
    var ele_documento = document.getElementById('nro_documento');
    var nro_prestamo = ele_prestamo.value.trim();
    var nro_documento = ele_documento.value.trim();

    if ((nro_prestamo.length == 0) && (nro_documento.length == 0)) {
        ele_prestamo.msgerr('<?php EchoLang("Al menos uno de estos datos debe ser completado"); ?>');
        ele_documento.msgerr('<?php EchoLang("Al menos uno de estos datos debe ser completado"); ?>');
        result = false;
    }
    if (nro_documento.length > 0) {
        if (!CheckDNI(ele_documento)) {
            result = false;
            nro_documento.msgerr('<?php EchoLang("Es inválido"); ?>');
        }
    }

    if (result) {

        rulo.Show('areaPremensaje');
        getAjax({
            archivo: 'buscarPrestamo',
            content : 'cobranzas',
            'nro_prestamo':nro_prestamo, 
            'nro_documento':nro_documento
        }, function(a, b, c, d) {
            rulo.Hide();
            if (a == 200) {
                if (evalresult.Eval(c)) {
                    document.getElementById('areaPremensaje').style.display = 'none';
                    document.getElementById('areaTabs').style.display = 'block';
                    document.getElementById('areaDatas').innerHTML = c;
                    // Seguimiento();
                    Pagos();
                    // if(CheckNumber(ele_prestamo)) {
                    // 	listadoComprobantes.Get(null,{"prestamo_id":nro_prestamo});
                    // }
                }
            } else {
                document.getElementById('mainlist').innerHTML = '<?php EchoLang("No se pudo recuperar el resultado de la búsqueda"); ?>: ' + a;
            }
        });
    }
}


function Pagos(){
    rulo.Show("pagos");
    getAjax({
        archivo: 'getPagos',
        content : 'cobranzas',
        prestamo_id: document.getElementById('prestamo_id').value.trim()
    },
    function(a,b,c,d){
        rulo.Hide();
        if(evalresult.Eval(c)){
            document.getElementById("pagos").innerHTML = c;
        }
    });
}

document.addEventListener('DOMContentLoaded',()=> {
	if(document.getElementById("nro_prestamo").value){
        BuscarPorId();
    }
});



function verCreditosDNI() {
    var result = true;
    var nro_documento = document.getElementById('nro_documento');
    var ele_prestamo = document.getElementById('nro_prestamo');

    if ((nro_documento.value.trim().length == 0)) {
        $(nro_documento).msgerr('<?php EchoLang("No puede estar vacío"); ?>');
        result = false;
    }
    if (nro_documento.value.trim().length > 0) {
        if (!CheckDNICOL(nro_documento)) {
            $(nro_documento).msgerr('<?php EchoLang("El documento ingresado es invalido."); ?>');
            result = false;
        }
    }
    if (result) {
        ventanaListadoPrestamos.Show({
            nro_documento: nro_documento.value.trim()
        });
        if(CheckNumber(ele_prestamo)) {
            var nro_prestamo = ele_prestamo.value.trim();
            listadoComprobantes.Get(null,{"prestamo_id":nro_prestamo});
        }
    }
    return false;
}

/**
    * Summary. Abre el modal que permite informar pago
*/
 function informarPago() {
    var prestamo_id = document.getElementById('prestamo_id').value.trim();
    var persona_id = document.getElementById('persona_id').value.trim();
    ventanaInformePago.Show({
        prestamo_id: prestamo_id,
        persona_id: persona_id
    });
}

function checkCompleted(a,b,c,d) {
    rulo.Hide();
    if(d && evalresult.Eval(c)){
        ventanaInformePago.Hide();
        Toast.fire({
            icon: 'success',
            title: 'Pago Ingresado'
        });
        Pagos();
    }
}


	/**
	 * Summary. Confirmar un pago de forma manual (cobro manual).
	 * Description. Lleva al script php que valida y guarda todos los datos.
	 */
	
     function confirmarCobroManual() {
        result = true;
        var frm = document.getElementById("frmDatosPago");
        var monto_a_pagar = document.getElementById("monto_a_pagar");
    
        // MONTO DE PAGO
        var ele = frm.monto_pago;
        if (ele.value.trim().length == 0) {
            result = $(ele).msgerr('Todos los campos son obligatorios.');
        }
    
        if (parseFloat(ele.value.trim()) > parseFloat(monto_a_pagar.value.trim())) {
            result = $(ele).msgerr('No puedes pagar más que lo adeudado.');
        }
    
        // FECHA DE PAGO
        var ele = frm.fecha_cobro;
        if (ele.value.trim().length == 0) {
            result = $(ele).msgerr('Todos los campos son obligatorios.');
        }
        // if (!CheckFecha(ele)) {
        //     result = $(ele).msgerr('Fecha inválida.');
        // };
    
        // NRO DE COMPROBANTE
        var ele = frm.nro_comprobante;
        if (ele.value.trim().length == 0) {
            result = $(ele).msgerr('Todos los campos son obligatorios.');
        }
    
    
        // ARCHIVO
        
    
        if (result) {
            var continuar = false;
            var monto_pago = document.getElementById("monto_pago");
            var diferencia = parseFloat(monto_pago.value.trim()) - parseFloat(monto_a_pagar.value.trim());
            console.log(monto_a_pagar.value);
            if (diferencia > 5) {
                swal({
                    title: "¿Registrar pago excedente?",
                    text: "¿Estás seguro?",
                    icon: "warning",
                    dangerMode: true,
                    buttons: {
                        cancelar: {
                            text: "Cancelar"
                        },
                        confirm: {
                            text: "Confirmar"
                        }
                    }
                }).then(respuesta => {
                    if (respuesta == "cancelar") { // Rechaza el monto ingresado
                        monto_pago.value = monto_a_pagar.value;
                        swal("Se cambió al monto máximo posible. Edítelo si es necesario y reenvíe el informe de pago.")
                    } else { // Confirma el monto
                        swal({ timer: 2000, buttons: false, text: "Se mantuvo el monto de pago ingresado" }).then(respuesta => confirmarDatos())
                    }
                    
                })
            } else {
                continuar = true;
            }
            if (continuar) {
                confirmarDatos();
            }
        }
    }

    function SetPago(self){
		if(self){
			rulo.Hide();
			monto_cuota = parseFloat(self.options[self.selectedIndex].dataset["monto_cuota"]);
			document.getElementById("monto_a_pagar").value = monto_cuota;
			document.getElementById("monto_pago").value = monto_cuota;
			// recalcultarTotalPrestamo();
		}
	}


    function confirmarDatos() {
        rulo.Show('datosPago');
        formSend.Send(document.getElementById("frmDatosPago"));
    }

    function checkInputEnter(e){
        console.log(e);
        console.log("e");
        var code = e.keyCode || e.which;
        if(code == 13) {
            BuscarPorId();
        }
    }
    document.addEventListener("DOMContentLoaded", function () {
        let btn = document.getElementById('button-prestamo_id');
    });

	function VerComprobante(id){
		if(isNaN(parseInt(id))){ return; }
		getAjax({
			archivo: 'GetFile',
			content: 'biblioteca',
			id: id
		}, (a,b,c)=>{
			evalresult.Eval(c);
			if(evalresult.TheResult.ok){
				if(evalresult.TheResult.name && evalresult.TheResult.mime && evalresult.TheResult.data){
					viewer.Show({
						base: evalresult.TheResult.data,
						nombre: evalresult.TheResult.name,
						tipo: evalresult.TheResult.mime,
						title: 'Comprobante de pago'
					});
				}
			}
	
			if(evalresult.TheResult.dataerr && evalresult.TheResult.dataerr.swerr){
				Toast.fire({
					title: evalresult.TheResult.dataerr.swerr,
					icon: "error"
				});
			}
		});
	}