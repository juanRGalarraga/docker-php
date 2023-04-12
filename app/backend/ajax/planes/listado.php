<?php
/**
 * Obtiene el listado de planes
 * Created: 2021-09-24
 * Author: Gastón Fernandez
*/
require_once(DIR_model."planes".DS."class.planes.inc.php");
require_once(DIR_includes."class.listhelper.inc.php");
$planes = new cPlanes;
$params = [];

if (FindParam('novacias')) {
	$params['novacias'] = '1';
}

$lid = FindParam("lid");
if($lid){
	$params["listid"] = $lid;
	
	//Intentamos porcesar el ordenamiento de la tabla...
	$orden = SecureInt(FindParam("ord"));
		
	if(!is_null($orden)){
		if($ordenamiento = $planes->SetOrden($lid,$orden)){
			$params["orden"] = $ordenamiento;
		}
	}
}

$planes->GetListado($params);
if (SecureInt($planes->header->cant??null,0) == 0) {
?>
	<p class="text-center">La consulta no devolvió resultados.</p>
<?php
	return;
}

$hlp = new ListHelper;
$hlp->SetPropeties($planes);
?>
	<span class="lid" id="lid" hidden><?php echo $planes->listid; ?></span>
    <table class="tabla-general table table-striped table-hover mb-0 d-block d-xxl-table table-responsive" id="tabla_planes">
        <thead>
            <tr>
                <th class="text-end <?php echo $hlp->Orden(0) ?>" data-field="0">ID</th>
                <th>Tipo</th>
                <th>Nombre</th>
                <th class="<?php echo $hlp->Orden(1) ?>" data-field="1">Alias</th>
                <th>Estado</th>
                <th>Devengamiento</th>
                <th>Moneda</th>
                <th class="text-center <?php echo $hlp->Orden(2) ?>" data-field="2">Fecha Hora</th>
            </tr>
        </thead>
        <tbody >
<?php
    foreach($planes->listado as $linea){ 
?>
            <tr title="Doble clic para ver/editar plan" data-id="<?php echo $linea->id; ?>">
                <td class="text-end"><?php echo $linea->id; ?></td>
                <td><?php echo $linea->tipo; ?></td>
                <td><?php echo $linea->nombre_comercial; ?></td>
                <td><?php echo $linea->alias; ?></td>
                <td><?php echo $linea->estado; ?></td>
                <td><?php echo $linea->devengamiento; ?></td>
                <td><?php echo $linea->tipo_moneda; ?></td>
                <td class="text-center"><?php echo $linea->sys_fecha_alta_txtshort; ?></td>
            </tr>
<?php
    }
?>
        </tbody>
    </table>
<?php
$hlp->ListadoMgr = "listado";
$hlp->ListadoMgrExtraParam = "";
$hlp->Footer();


