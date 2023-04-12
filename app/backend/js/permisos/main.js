/**
 * JS principal para el módulo de permisos
 * @author Juan Galarraga
 * @created 2021-10-29
 */

document.addEventListener("DOMContentLoaded", ()=> { 
    LoadUsersList(); 
});

const SPINNER = new rbtRulo();

const TOAST = Swal.mixin({
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

/**
 * Se encarga de hacer que funcione el collapse de boostrap
 * en la tabla de la plantilla
 * @param {int} parentKey - ID del elemento padre que tendrá el collapse
 */
function ToggleCollapse(parentKey){
    if(!parentKey){ return console.error("Falta ID del button"); }
    let button = document.querySelector(`#toggle-${parentKey}`);
    let icon = document.querySelector(`#toggle-icon-${parentKey}`);
    if(button === undefined){ return; }
    button.click();
    if(icon.classList.contains("fa-chevron-down")){
        icon.classList.replace("fa-chevron-down", "fa-chevron-up");
    } else {
        icon.classList.replace("fa-chevron-up", "fa-chevron-down");
    }
}

/**
 * Verifica que, al deshabilitarse el permiso de lectura en cualquier contenido,
 * se deshabiliten el resto de los permisos
 * @param {*} id
 */

function ControlCheckedPermisos(id){
    let readChecked = document.querySelector(`#checkR-${id}`),
        createChecked = document.querySelector(`#checkC-${id}`),
        updateChecked = document.querySelector(`#checkU-${id}`),
        deleteChecked = document.querySelector(`#checkD-${id}`),
        childsChecked = document.querySelectorAll(`.checkToParent-${id}`);

    if(!readChecked.checked){
        createChecked.setAttribute("disabled", "disabled");
        updateChecked.setAttribute("disabled", "disabled");
        deleteChecked.setAttribute("disabled", "disabled");
        if(childsChecked.length > 0) {
            childsChecked.forEach((child) => {
                child.setAttribute("disabled", "disabled");
            });
        }
    } else {
        createChecked.removeAttribute("disabled");
        updateChecked.removeAttribute("disabled");
        deleteChecked.removeAttribute("disabled");
        if(childsChecked.length > 0) {
            childsChecked.forEach((child) => {
                child.removeAttribute("disabled");
            });
        }
    }
}

/**
 * Toma el ID de dos contenedores, ocultando uno y mostrando el otro.
 * De forma opcional, recibe un cuerpo que es insertado en el contenedor destino.
 * @param {String} from - ID del contenedor de origen
 * @param {String} to - ID del contenedor de destino
 */

 function GotoContent(fromId, toId, insertBody=null){
    let fromContent = document.querySelector(`#${fromId}`),
        toContent = document.querySelector(`#${toId}`);

    if(!fromContent || !toContent){
        return console.error(`No se encontró uno de los objetos: ${fromId}, ${toId}`);
    }
    fromContent.setAttribute("hidden", "hidden");
    toContent.removeAttribute("hidden");
    if(insertBody !== null){
        toContent.innerHTML = insertBody;
    }
}