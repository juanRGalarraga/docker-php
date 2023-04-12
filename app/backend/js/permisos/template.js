/**
 * JS para la la sección plantillas del módulo permisos
 * @author Juan Galarraga
 * @created 2021-11-03
*/

const TEMPLATESLIST = new mgrListadoCreator({
    archivo: "list",
    content: "permisos/templates",
    rulo: SPINNER,
    targetElementIdName: "templatesMainList"
});

function LoadTemplatesList(element = null) {
    TEMPLATESLIST.Get(element);
}

/**
 * Carga un template.
 * @param {int} id - Si se le pasa ID busca una plantilla especifica,
 * sino trae una plantilla nueva.
 */

function GetTemplate(thisID=null) {
	rulo.Show("templatesMainList");
    getAjax({
        archivo: "get",
        content: "permisos/templates",
        templateID : thisID,
    }, function (a, b, c, d){
		rulo.Hide();
        if(a === 200){
            if(c){
                GotoContent("templateWrapperList", "templateMain", c);
            } else {
                TOAST.fire({
                    icon: 'danger',
                    title: 'Error al obtener la plantilla'
                });
            }
        }
    });
}

function CreateTemplate(){
    new rbtFormSend(document.querySelector("#formTemplate"), {
        url: "<?php echo URL_ajax?>",
        extraData: {
            archivo: "create",
            content: "permisos/templates"
        },
        onStart: function(){ SPINNER.Show(); },
        onFinish: function(a, b, c, d) {
            SPINNER.Hide();
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
    }).Send();
}