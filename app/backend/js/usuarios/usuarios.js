window.addEventListener('load', ()=>{
	let car = document.querySelector('#carouselUsuarios');
	SliderUsuarios = bootstrap.Carousel.getInstance(car);
});

var rulo = new rbtRulo,
    evalresult = new rbtEvalResult,
    checkForm = new rbtFormSend(null,{url: '<?php echo URL_ajax; ?>'}),
    usuarios = "usuarios",
    action = null,
    puntero = 1,
    sumP = 0,
    sumE = 0,
    sumD = 0,
    SliderUsuarios;

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

document.addEventListener("DOMContentLoaded", function () {
    listadoUsuarios.Get();
})

// Declaraciones de MODAL/LISTADOS
const formUser = new mgrListadoCreator({
    archivo: "formUser",
    content: usuarios,
    targetElementIdName: "formulario-user",
    onFinish: function (tabla) {
        getPais();
        getDataUser();
    }
})

const listadoUsuarios = new mgrListadoCreator({
    archivo: "listadoUsuarios",
    content: usuarios,
    targetElementIdName: "list-users"
})

// Funciones 

// 
function IsNew(){
    rulo.Show();
    action = "new";
    formUser.Get(null,{action: null});
}
// 
function IsEdit(ele){
    rulo.Show();
    action = ele;
	var edit = document.getElementById("formulario-user");
	edit.innerHTML = "";
    formUser.Get(null,{action: ele});
	interval = setInterval(checkContent,100);
}

function checkContent(){
	var edit = document.getElementById("formulario-user");
	if(edit.innerHTML.trim().length > 0){
		clearInterval(interval);
		SliderUsuarios.to("1");
	}
}

// 
function CheckUser(idfrm) {
    rulo.Show();
    checkForm.Send(
        document.getElementById(idfrm),
      {
          extraData: {
              archivo: 'checkUsuario',
              content: usuarios
          },
          onFinish: function (a,b,c,d) {
              rulo.Hide(null);
              if (d.create){
                alert.fire({
                    icon: 'success',
                    title: d.create
                });
                listadoUsuarios.Get();
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

/*


*/
function clonar(idOrigen,newLocal,identificador){
    var div = document.getElementById(idOrigen),
        ele = null,
        origen,
        clon,
        sumN = 0;
    if (identificador == "direccion"){
        if (document.getElementById("inpCalle").value == "" || document.getElementById("inpAltura").value == "" || document.getElementById("inpPiso").value == "")
        {
            return
        }
    }
        
    // Se realiza el control para realizar un return si esta vacío el elemento
    switch (identificador) {
        case "phone":
            if (div.firstElementChild.value == ''){
                return;
            }
            sumN = parseInt(sumP) + 1;
            sumP = sumN;
            ele = "phone";
        break;
        case "email":
            if (div.firstElementChild.value == ''){
                return;
            }
            sumN = parseInt(sumE) + 1;
            sumE = sumN;
            ele = "emial";
        break;
        case "direccion":
            if (div.children[1].children[1].value == ''){
                return;
            }
            sumN = parseInt(sumD) + 1;
            sumD = sumN;
            ele = "direccion";
        break;
        default:
            console.log("El ejecutador no es valido");
            return;
        break;
    }

    // Buscamos donde se va a colocar el clon y lo creamos
    origen = document.getElementById(newLocal);
    clon = div.cloneNode(true);
    origen.appendChild(clon);
    
    // Cambiamos los id para que no alla conflictos y limpiamos el blur a los imputs
    if (identificador != "direccion"){

        div.firstElementChild.value = '';

        clon.firstElementChild.onblur = null;
        clon.firstElementChild.id = clon.firstElementChild.id+sumN;
        
        clon.children[1].firstElementChild.setAttribute('checked',null);
        clon.children[1].firstElementChild.name = clon.children[1].firstElementChild.name+sumN;
        clon.children[1].firstElementChild.id = clon.children[1].firstElementChild.id+sumN;
        MarcarCheck(ele,clon.children[1].firstElementChild);
        clon.id = clon.id+sumN;

        AgregarButtonX(clon.firstElementChild.id,clon.id);
    }else{
        clon.classList.add("p-0");
        div.children[1].children[1].value = '';

        // Check para marcar la direccion por defecto
        clon.children[0].firstElementChild.children[0].name = clon.children[0].firstElementChild.children[0].name+sumN
        clon.children[0].firstElementChild.children[0].id = clon.children[0].firstElementChild.children[0].id+sumN
        clon.children[0].firstElementChild.children[0].setAttribute('checked',null);
        
        // Input de la calle
        clon.children[1].children[0].setAttribute('for',clon.children[1].children[0].getAttribute('for') + sumN);
        clon.children[1].children[1].id = clon.children[1].children[1].id + sumN;
        clon.children[1].children[1].onblur = null;

        // Input de la altura
        clon.children[2].children[0].setAttribute('for',clon.children[2].children[0].getAttribute('for') + sumN);
        clon.children[2].children[1].id = clon.children[2].children[1].id + sumN;
        clon.children[2].children[1].onblur = null;
        div.children[2].children[1].value = '';

        // Input de la piso/Departamento
        clon.children[3].children[0].setAttribute('for',clon.children[3].children[0].getAttribute('for') + sumN);
        clon.children[3].children[1].id = clon.children[3].children[1].id + sumN;
        clon.children[3].children[1].onblur = null;
        div.children[3].children[1].value = '';

        MarcarCheck(ele,clon.children[0].firstElementChild.children[0]);
        clon.id = clon.id+sumN;
        
        AgregarButtonXDireccion(clon.children[1].children[1].id,clon.children[2].children[1].id,clon.children[3].children[1].id,clon.id);
    }
}

function MarcarCheck(reference,ele){
    var newEle = ".radio-"+reference,
        elements = document.querySelectorAll(newEle);

    elements.forEach(e => {
        if (e.checked) {
            if (e.id != ele.id){
                e.checked = null;
            }
        }
    });
}

/*
    
*/
function getPais() {
    var inpPais = document.getElementById("inpPais"),
        hidden_pais = document.getElementById("hidden_pais").value;
    getAjax({
        archivo: 'listPaises',
        content: 'geo',
        pais: hidden_pais
    }, function (a,b,c,d) {
        if (evalresult.Eval(c)){            
            inpPais.innerHTML = c;
            if (inpPais.value){
                getProvincias(document.getElementById("inpPais"));
            }
        }
    })
}

/*

*/
function getProvincias(ele) {
    console.log();
    var inpProv = document.getElementById("inpProv"),
        hidden_prov = document.getElementById("hidden_prov").value;
    getAjax({
        archivo: 'listProvincias',
        content: 'geo',
        id: ele.value,
        prov: hidden_prov
    }, function (a,b,c,d) {
        if (evalresult.Eval(c)){
            inpProv.removeAttribute("disabled");
            inpProv.innerHTML = c;
            if (inpProv.value){
                getCiudad(document.getElementById("inpProv"));
            }
        }
    })
}

/*

*/
function getCiudad(ele) {
    var inpProv = document.getElementById("inpCity"),
        hidden_city = document.getElementById("hidden_city").value;
    getAjax({
        archivo: 'listCiudades',
        content: 'geo',
        id: ele.value,
        city: hidden_city
    }, function (a,b,c,d) {
        if (evalresult.Eval(c)){
            inpProv.removeAttribute("disabled");        
            inpProv.innerHTML = c;
        }
    })   
}

/*

*/
function getDataUser(){
    var id = document.getElementById("action").value;
    getAjax({
        archivo: 'dataUser',
        content: usuarios,
        id: id
    },function (a,b,c,d){
        rulo.Hide(null);
        if (d.error == false){
            d.emails.forEach(e => {
                document.getElementById("inpEmail").value = e.data
                clonar('one-email','extraEmail','email')
            });
            d.phones.forEach(e => {
                document.getElementById("inpPhone").value = e.data
                clonar('one-phone','extraPhone','phone')
            });
            d.directs.forEach(e => {
                document.getElementById("inpCalle").value = e.calle
                document.getElementById("inpAltura").value = e.altura
                document.getElementById("inpPiso").value = e.departamento
                clonar('one-direccion','extraDireccion','direccion')
            });
        }
    })

}


function setCP(ele) {
    document.getElementById('inpCod').value = ele.options[ele.selectedIndex].getAttribute('data-set');
}

/*
    Elimina el formulario para que quede todo en blanco
*/
function removeForm(){
    setTimeout(() => {
        var ele = document.getElementById('formulario-user');
        
        while (ele.firstChild) {
            ele.removeChild(ele.firstChild);
        }
    }, 650);
}

/*
    Crea el botton de eliminacion de elemento
*/
function AgregarButtonX(ele,cont){
    var button = '<button type="button" title="Elimar Información" class="btn d-block float-left text-danger p-0 w-auto" style="margin-left: -8px; position: absolute; z-index: 10;" onclick="DeleteInfo(\''+ele+'\',\''+cont+'\')"><i class="fas fa-times-circle"></i></button>';
    ele = document.getElementById(ele);

    ele.insertAdjacentHTML('beforebegin', button);
}

/*
    Crea el botton de eliminacion de elemento para las direcciones
*/
function AgregarButtonXDireccion(c,a,p,cont){
    var button = '<button type="button" title="Elimar Información" class="btn d-block float-left text-danger p-0 w-auto" style="margin-left: 10px; position: absolute; z-index: 10;" onclick="DeleteInfoDirecc(\''+c+'\',\''+a+'\',\''+p+'\',\''+cont+'\')"><i class="fas fa-times-circle"></i></button>';
    cont = document.getElementById(cont);

    cont.insertAdjacentHTML('afterbegin', button);
}

/*
    Elimina el elemtno tanto de TEL como de EMAIL
*/
function DeleteInfo(id,cont){
    var ele = document.getElementById(id),
        contenedor = document.getElementById(cont);
    getAjax(
        {
            archivo: 'deletData',
            content: usuarios,
            eli: ele.value,
            elem: id,
            action: document.getElementById("action").value
        },function (a,b,c,d){
            contenedor.remove();
        }
    )
}

/*
    Elimina el elemtno tanto de TEL como de EMAIL
*/
function DeleteInfoDirecc(calle,altura,piso,cont){
    var calle = document.getElementById(calle).value,
        altura = document.getElementById(altura).value,
        piso = document.getElementById(piso).value,
        contenedor = document.getElementById(cont);
    getAjax(
        {
            archivo: 'deletData',
            content: usuarios,
            calle: calle,
            altura: altura,
            piso: piso,
            action: document.getElementById("action").value
        },function (a,b,c,d){
            contenedor.remove();
        }
    )
}