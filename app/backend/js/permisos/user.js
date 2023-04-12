/**
 * JS para la la sección usuarios del módulo permisos
 * @author Juan Galarraga
 * @created 2021-11-03
*/

const USERSLIST = new mgrListadoCreator({
    archivo: "list",
    content: "permisos/users",
    rulo: SPINNER,
    targetElementIdName: "usersMainList"
});

function LoadUsersList(element = null) {
    //element es un objeto HTML requerido para los filtros del listado.
    USERSLIST.Get(element);
}

/**
 * Carga el template de un usuario
 * @param {int} rolID - ID del usuario
*/
function GetUserTemplate(theUserId){
	rulo.Show("usersMainList");
    getAjax({
        archivo: "getTemplate",
        content: "permisos/users",
        userID : theUserId,
    }, function (a, b, c, d){
		rulo.Hide();
        if(a === 200){
            if(c){
                GotoContent("usersWrapperList", "userTemplateMain", c);
            } else {
                TOAST.fire({
                    icon: 'danger',
                    title: 'Error al obtener la plantilla'
                });
            }
        }
    });
}

function CreateUserTemplate(){
    new rbtFormSend(document.querySelector("#formUserTemplate"), {
        url: "<?php echo URL_ajax?>",
        extraData: {
            archivo: "createTemplate",
            content: "permisos/users"
        },
        onStart: function() { SPINNER.Show(); },
        onFinish: function(a, b, c, d) {
            SPINNER.Hide();
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
        }
    }).Send();
}