<?php
    require_once(DIR_model."class.genList.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";
    $post = CleanArray($_POST);

    try{

        // Variables anteriores y actuales
        if (isset($_SESSION['list-users'])){
            $ses = $_SESSION['list-users'];
        }
    
        // Estado utilizandose 
        if (isset($post['sel_state']) AND !empty($post['sel_state'])){
            $estado = $post['sel_state'];
        }else if (isset($ses['state']) AND !empty($ses['state'])){
            $estado = $ses['state'];
        }else{
            $estado = 'HAB';
        }

        $camposorden = array('id','nombre','nro_doc');
        $ordenes = (isset($ses['ord'])) ? $ses['ord'] : array('id' => 'ASC');
    
        //Compruebo el buscar de session
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

        $gen = new cGenList("listadoUsuarios");
        $gen->ItemsPorPagina = $objeto_usuario->opciones->rpp;
        $gen->ordenes = $ordenes;
        $gen->camposorden = $camposorden;
        $gen->PaginaActual = $pag;
        $gen->pag = $pag;
        $gen->limit = $objeto_usuario->opciones->rpp;
        
        $gen->select = array(['nivel','estado'],['id','nombre','apellido','tipo_doc','nro_doc','fecha_nac']);
        $gen->table_main = array(TBL_backend_usuarios,TBL_backend_personas);
        $gen->rename= array('user','people');
        $gen->where = " `people`.`negocio_id` = ".$objeto_usuario->negocio_id." AND `people`.`id`=`user`.`persona_id` AND `user`.`estado` LIKE '$estado'";
        $gen->order = array(
            array(
                "DESC" => "`people`.`id`"
            )
        );
    
        // Busca todos los registro de liquidacion de un prestamo.
        $result = $gen->list();
        if (CanUseArray($result)){
?>
        <div class="card-body px-0">
            <table class="tabla-general table table-striped table-hover mb-0 d-block d-md-table table-responsive" id="listaVendedores">
                <thead class="thead-light">
                    <tr>
                        <th class="<?php //$gen->hlp->Orden(0); ?>" data-field="0" title="Nombre completo">Nombre y Apellido</th>
                        <th class="<?php //$gen->hlp->Orden(1); ?>" data-field="1" title="N° de Documento">Numero de Documento</th>
                        <th class="<?php //$gen->hlp->Orden(3); ?>" data-field="2" title="Nivel del cargo">Cargo</th>
                        <th class="<?php //$gen->hlp->Orden(4); ?>" data-field="2" title="Fecha de nacimiento">Fecha Nacimiento</th>
                        <th class="<?php //$gen->hlp->Orden(5); ?>" data-field="2" title="Estado Cuenta">Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody title="Doble clic para editar">

<?php
            foreach ($result as $key => $value) {
?>
                    <tr ondblclick="IsEdit(<?php echo $value->people_id; ?>)">
                        <td><?php echo $value->people_nombre." ".$value->people_apellido;?></td>
                        <td><?php echo $value->people_tipo_doc.": ".$value->people_nro_doc;?></td>
                        <td><?php echo VALID_TYPE_PERMISOS[$value->user_nivel];?></td>
                        <td><?php echo $value->people_fecha_nac_txtshort;?></td>
                        <td><?php echo ESTADOS_VALIDOS[$value->user_estado];?></td>
                        <td class="text-center"><button type="button" class="btn btn-primary btn-sm" title="Editar Usuario" data-bs-target="#carouselUsuarios" data-bs-slide-to="1" aria-label="Slide 2"  onclick="IsEdit(<?php echo $value->people_id; ?>)"><i class="fas fa-user-edit me-2"></i>Editar</button></td>
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
        }
    }catch(Exception $e){
        cLogging::Write($this_file.$e->getMessage());
    }
	//filtros de busqueda
?>