/**
 * JS para la la sección Roles del módulo permisos
 * @author Juan Galarraga
 * @created 2021-11-03
 */

const ROLESLIST = new mgrListadoCreator({
    archivo: "list",
    content: "permisos/roles",
    rulo: SPINNER,
    targetElementIdName: "rolesMainList"
});

function LoadRolesList(element = null){
    ROLESLIST.Get(element);
}
/**
 * Obtiene el formulario para editar un rol
 */
function FormNewRol(){
    new modalBs5Creator({
        archivo: "form",
        content: "permisos/roles",
        extraparams: {
            action: "new"
        }
    }).Show();
}

/**
 *  Obtiene el formulario para crear un rol
 * @param {int} rolID ID del rol
 */
function FormEditRol(rolID){
    new modalBs5Creator({
        archivo: "form",
        content: "permisos/roles",
        extraparams: {
            action: null,
            id: rolID
        }
    }).Show();
}

/**
 * Envía los datos del rol para guardar o crear.
 * @returns {void}
 */
function SendRolForm(){
    let formExtraData = {},
        formElement = document.querySelector("#formRol"),
        thisRolID = formElement.rolID.value;

    if( empty(formElement.rolName.value) ){
        formElement.rolName.msgerr("El nombre del rol no puede estar vacío.");
        return;
    }

    if(thisRolID){
        formExtraData = {
            archivo: "update",
            content: "permisos/roles",
            rolID: thisRolID,
        };
    } else {
        formExtraData = {
            archivo: "create",
            content: "permisos/roles"
        };
    }

    new rbtFormSend(formElement, {
        url: "<?php echo URL_ajax?>",
        extraData: formExtraData,
        onStart: function() { SPINNER.Show(); },
        onFinish: function(a, b, c, d){
            SPINNER.Hide();
            if(a === 200){
                if(d.ok){
                    TOAST.fire({
                        icon: 'success',
                        title: d.msgok
                    });
                    LoadRolesList();
                } else if(d.generr) {
                    TOAST.fire({
                        icon: 'danger',
                        title: d.msgerr
                    });
                }
            }
        }
    }).Send();
}

function GetRolTemplate(theRolId) {
	rulo.Show("rolesMainList");
    getAjax({
        archivo: "getTemplate",
        content: "permisos/roles",
        rolID : theRolId,
    }, function (a, b, c, d){
		rulo.Hide();
        if(a === 200){
            if(c){
                GotoContent("rolesWrapperList", "rolTemplateMain", c);
            } else {
                TOAST.fire({
                    icon: 'danger',
                    title: 'Error al obtener la plantilla'
                });
            }
        }
    });
}

function CreateRolTemplate() {
    new rbtFormSend(document.querySelector("#formRolesTemplate"), {
        url: "<?php echo URL_ajax?>",
        extraData: {
            archivo: "createTemplate",
            content: "permisos/roles"
        },
        onStart: function(){ SPINNER.Show(); },
        onFinish: function(a, b, c, d) {
            if(a === 200){
                if(d.msgok){
                    TOAST.fire( {
                        icon: 'success',
                        title: d.msgok
                    });
                } else if (d.msgerr) {
                    TOAST.fire( {
                        icon: 'danger',
                        title: d.msgerr
                    });
                } else {
                    TOAST.fire( {
                        icon: 'danger',
                        title: 'No se pudo crear la plantilla'
                    });
                }
            }
            SPINNER.Hide();
        }
    }).Send();
}