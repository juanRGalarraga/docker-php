window.addEventListener('load', ()=>{
	let car = document.querySelector('#carouselImpuestos');
	SliderUsuarios = bootstrap.Carousel.getInstance(car);
});

var impuestos= "impuestos",
    vistaActual = "list-impuestos",
    checkForm = new rbtFormSend(null,{url: '<?php echo URL_ajax; ?>'}),
    SliderUsuarios;

const listadoImpuestos = new mgrListadoCreator({
    archivo: "listadoImpuestos",
    content: impuestos,
    targetElementIdName: "list-impuestos"
})

const listadoCargos = new mgrListadoCreator({
    archivo: "listadoCargos",
    content: impuestos,
    targetElementIdName: "list-cargos"
})

const formEdit = new mgrListadoCreator({
    archivo: "formEdit",
    content: impuestos,
    targetElementIdName: "formulario-edit",
	onFinish: ()=>{
		rulo.Hide();
	}
})

const modalImpCargo = new modalBs5Creator({
    archivo: "modalImpCargo",
    content: impuestos
})

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

// Funcion para evitar el refrescar la vista si se preciona en el Nav que esta ACTIVE.
function CargarVista(vist){
    if (vist != vistaActual){
        listadoCargos.Get();
    }else if (vist != vistaActual){
        listadoImpuestos.Get();
    }
    vistaActual = vist;
}

// 
function IsNew(idfrm) {
    rulo.Show();
    checkForm.Send(
        document.getElementById(idfrm),
      {
          extraData: {
              archivo: 'checkImpuesto',
              content: impuestos
          },
          onFinish: function (a,b,c,d) {
            rulo.Hide(null);
            if (d.okok){
                alert.fire({
                    icon: 'success',
                    title: d.okok
                });
                modalImpCargo.Hide();
                listadoImpuestos.Get();
                listadoCargos.Get();

              }else if (d.error){
                alert.fire({
                    icon: 'error',
                    title: d.error
                })
              }else{
                alert.fire({
                    icon: 'warning',
                    title: "Revise los datos del formulario."
                })
              }
          }
      }
  );
}

var interval = null;
function IsEdit(tipe,ele){
	rulo.Show();
	var edit = document.getElementById("formulario-edit");
	edit.innerHTML = "";
    formEdit.Get(null,{tipe: tipe,id: ele});
	interval = setInterval(checkContent,100);
}

function checkContent(){
	var edit = document.getElementById("formulario-edit");
	if(edit.innerHTML.trim().length > 0){
		clearInterval(interval);
		SliderUsuarios.to("1");
	}
}

document.addEventListener("DOMContentLoaded", function () {
    listadoImpuestos.Get();
})

/*
    Elimina el formulario para que quede todo en blanco
*/
function removeForm(){
    setTimeout(() => {
        var ele = document.getElementById('formulario-edit');
        
        while (ele.firstChild) {
            ele.removeChild(ele.firstChild);
        }
    }, 650);
}

function CheckData(idfrm) {
    rulo.Show();
    checkForm.Send(
        document.getElementById(idfrm),
      {
          extraData: {
              archivo: 'checkImpuesto',
              content: impuestos
          },
          onFinish: function (a,b,c,d) {
            rulo.Hide(null);
            if (d.okok){
                alert.fire({
                    icon: 'success',
                    title: d.okok
                });
                listadoImpuestos.Get();
                listadoCargos.Get();
                SliderUsuarios.to('0');
                removeForm();

              }else if (d.error){
                alert.fire({
                    icon: 'error',
                    title: d.error
                })
              }else{
                alert.fire({
                    icon: 'warning',
                    title: "Revise los datos del formulario."
                })
              }
          }
      }
  );
}