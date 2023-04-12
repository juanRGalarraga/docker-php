<?php
/**
 * Carga el listado de plantillas para el módulo de permisos
 * @author Juan Galarraga
 * @created 2021-11-1
 */
require_once(DIR_model."class.genList.inc.php");
$this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";
$post = CleanArray($_POST);

try{

    // Variables anteriores y actuales
    if (isset($_SESSION['templatesListadoPermisos'])){
        $ses = $_SESSION['templatesListadoPermisos'];
    }

    // Estado utilizandose 
    if (isset($post['sel_state']) AND !empty($post['sel_state'])){
        $estado = $post['sel_state'];
    }else if (isset($ses['state']) AND !empty($ses['state'])){
        $estado = $ses['state'];
    }else{
        $estado = 'HAB';
    }

    $camposorden = array('id','nombre');
    $ordenes = $ses['ord'] ?? array('id' => 'ASC');

    if (isset($post['serch']) AND !empty($post['serch'])){
        $buscar = $post['serch'];
        $ses['serch'] = $buscar;
    }else{
        $buscar ='';
        $ses['serch'] = $buscar;
    }

    //coloco la pagina
    $pag = ((isset($ses['pag']))? $ses['pag']:1);
    if (isset($post['pag'])) {
        $pag = SecureInt(substr(trim($post['pag']), 0, 11), 1);
        $ses['pag'] = $pag;
    }

    if (isset($post['ord'])) {
        $campo = SecureInt(mb_substr(trim($post['ord']), 0, 11), NULL);
        if (!is_null($campo)) {
            if (isset($camposorden[$campo])) {
                $idx = $camposorden[$campo];
                if (array_key_exists($idx, $ordenes)) {
                    $aux = ($ordenes[$idx] == 'DESC') ? 'ASC' : 'DESC';
                    unset($ordenes[$idx]);
                } else {
                    $aux = 'ASC';
                }
                $ordenes = array_reverse($ordenes, true);
                $ordenes[$idx] = $aux;
                $ordenes = array_reverse($ordenes, true);
                $ses['ord'] = $ordenes;
            } else {
                $campo = NULL;
            }
        }
    }

    $gen = new cGenList("templatesList");
    $gen->ItemsPorPagina = $objeto_usuario->opciones->rpp;
    $gen->ordenes = $ordenes;
    $gen->camposorden = $camposorden;
    $gen->PaginaActual = $pag;
    $gen->pag = $pag;
    $gen->limit = $objeto_usuario->opciones->rpp;

    $gen->select = array(['id', 'nombre', 'estado', 'sys_fecha_alta']);
    $gen->table_main = array(TBL_plantillas_permisos, TBL_backend_usuarios,TBL_backend_personas);
    $gen->rename= array('plantillas','user','people');
    $gen->where = " `people`.`negocio_id` = ".$objeto_usuario->negocio_id." AND `plantillas`.`estado` LIKE '$estado'";
    $gen->order = array(
        array(
            "DESC" => "`plantillas`.`id`"
        )
    );

    $result = $gen->list();
    if (CanUseArray($result)){
?>
        <div class="card-body px-0">
            <table class="tabla-general table table-striped table-hover mb-0 d-block d-lg-table table-responsive">
                <thead class="thead-light">
                    <tr>
                        <th data-field="0" title="Nombre de la plantilla">Nombre</th>
                        <th data-field="1" title="Fecha de alta">Fecha de alta</th>
                        <th data-field="2" title="Estado">Estado</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody title="Doble clic para editar la plantilla">

<?php
        foreach ($result as $key => $value) {
?>
                    <tr ondblclick="GetTemplate('<?php echo $value->plantillas_id?>')">
                        <td><?php echo $value->plantillas_nombre?></td>
                        <td><?php echo $value->plantillas_sys_fecha_alta ?? '<i>indefinida</i>'?></td>
                        <td><?php echo ESTADOS_VALIDOS[$value->plantillas_estado];?></td>
                        <td class="text-center" colspan="2">
							<button onclick="GetTemplate('<?php echo $value->plantillas_id?>')" class="btn btn-secondary btn-sm" title="Editar plantilla" data-bs-target="#carouselExampleControls" data-bs-slide-to="1" aria-label="Slide 2"><i class="fas fa-tasks-alt"></i> Editar plantilla</button>
						</td>
                    </tr>
<?php
        }
?>  
            </tbody>
		</table>
	</div>
    <div class="card-footer row">
		 <div class="col-11">
			<?php if ($gen->hlp->Paginar()) { $gen->hlp->MostrarPaginacion(); } ?>
		</div>
		<div class="col-1 text-right">
			<p><small><?php echo ((($pag - 1) * $objeto_usuario->opciones->rpp) + 1) . " - " . (((($pag - 1) * $objeto_usuario->opciones->rpp)) + $gen->numrows); ?> / <span title="Total de registros en esta consulta"><?php echo $gen->hlp->ItemsTotales; ?></span></small></p>
		</div>
	</div>
<?php       
    } else {
        cSidekick::ShowEmptySearchMessage();
    }
}catch(Exception $e){
    cLogging::Write($this_file.$e->getMessage());
}
?>