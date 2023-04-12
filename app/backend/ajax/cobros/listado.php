<?php
/**
    * Listado 
    * Created: 
    * Author: 
 */
	require_once(DIR_model."cobros".DS."class.cobros.inc.php");
	require_once(DIR_includes."class.listhelper.inc.php");

	$post = CleanArray($_POST);
    
    $params = array();
    
    $cobros = new cCobros;

    $lid = FindParam("lid");
    if($lid){
        $params["listid"] = $lid;
        
        //Intentamos porcesar el ordenamiento de la tabla...
        $orden = SecureInt(FindParam("ord"));
        
        if(!is_null($orden)){
            if($ordenamiento = $cobros->SetOrden($lid,$orden)){
                $params["orden"] = $ordenamiento;
            }
        }

        $buscar = FindParam("buscar");
        if(!is_null($buscar)){
            $params["buscar"] = $buscar;
        }
    }
    $estado = "PEND";
    $params['estado'] = $estado;
	$cobros->GetListado($params);
    
	if (SecureInt($cobros->header->cant??null,0) == 0) {
	?>
        <p class="text-center">No hay Cobros a Confirmar.</p>
	<?php
		return;
	}
        $hlp = new ListHelper;
	    $hlp->SetPropeties(clone $cobros);
        $hlp->ListadoMgr = "listado";
	    $hlp->ListadoMgrExtraParam = "";
	?>
    <span class="lid" id="lid" hidden><?php echo $cobros->listid; ?></span>
	<table class="tabla-general table table-striped table-hover mb-0 d-block d-xxl-table table-responsive">
		<thead>
			<tr>
				<th class="text-end <?php echo $hlp->Orden(0); ?>" data-field="0"> Nº Pago</th>
				<th>Nº Comprobante</th>
				<th>Nº Prestamo</th>
				<th class="">N° Cuota</th>
				<th>Monto</th>
				<th class="text-center">Tipo Cobro</th>
				<th class="text-center" >Fecha Cobro</th>
				<th class="text-center">Fecha alta</th>
				<th > Pagar </th>
			</tr>
		</thead>
		<tbody>
	<?php
		foreach($cobros->listado as $linea) {
	?>
			<tr>
				<td class="text-center"><?php echo $linea->id; ?></td>
				<td><?php echo $linea->nro_comprobante ?? "-"; ?></td>
				<td><?php echo $hlp->Replace($linea->prestamo_id) ?? "-"; ?></td>
				<td ><?php echo $linea->cuota_id ?? "-"; ?></td>
				<td>$ <?php echo F($linea->monto) ?? "-"; ?></td>
				<td class="text-center"><?php echo $linea->nombre_broker ?? "-"; ?></td>
				<td class="text-center"><?php echo $linea->fecha_de_cobro_txtshort ?? "-"; ?></td>
				<td class="text-center"><?php echo $linea->sys_fecha_alta_txtshort ?? "-"; ?></td>
				<td class="text-center" onclick="SetChecked(<?php echo $linea->id; ?>);">
                    <div class="form-check">
                        <input class="form-check-input confirmar-pago" type="checkbox" value="<?php echo $linea->id; ?>" id="comprobante_<?php echo $linea->id; ?>" <?php echo ($linea->estado == "ACRE") ? 'checked disabled':''; ?> onchange="UpdateConfirmButton();" onclick="SetChecked(<?php echo $linea->id; ?>);">
                        <label class="form-check-label" for="comprobante_<?php echo $linea->id; ?>"></label>
                    </div>
				</td>
			</tr>
	<?php
		}
	?>
		</tbody>
	</table>
    <div class="row">
        <div class="col-md-8"></div>
        <div class="col-md-2 justify-content-end py-2" title="Para los cobros seleccionados...">
            <select name="accionSobreLosCobros" id="accionSobreLosCobros" class="list-actions-controls form-select form-select-sm float-end" disabled>
                <option value="confirm">Confirmar cobros</option>
                <option value="decline">Rechazar cobros</option>
            </select>&nbsp;
        </div>
        <div class="col-md-2 justify-content-end py-2" title="Para los cobros seleccionados...">
            <button type="button" id="" class="list-actions-controls btn btn-primary btn-sm float-end" onClick="ConfirmarCobros()" title="Ejecutar la acción seleccionada" disabled><i class="fa fa-bolt"></i> Hacer...</button>
        </div>
    </div>
	<?php
	
	$hlp->Footer();
    
    // $hlp->Paginador = $cobros->Paginador;
	// $hlp->ItemsPorPagina = $cobros->ItemsPorPagina;
	// $hlp->PaginaActual = $cobros->PaginaActual;
	// $hlp->ItemsTotales = $cobros->ItemsTotales;
	// $hlp->ItemsActuales = $cobros->ItemsActuales;
	

