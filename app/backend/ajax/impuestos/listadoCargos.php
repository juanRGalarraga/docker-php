<?php 
    require_once(DIR_model."impuestos".DS."class.impuestos.inc.php");
    require_once(DIR_includes."class.listhelper.inc.php");
	$impuesto = new cImpuestos();
    $params = array();
	//Buscamos los filtros...
	$pag = FindParam("pag");
	$pag = ($pag > 0)? $pag:1;
	$params["pag"] = $pag;

	$buscar = FindParam("buscar");
	if(!is_null($buscar)){
		$params["buscar"] = $buscar;
	}

	$lid = FindParam("lid");
	if($lid){
		$params["listid"] = $lid;
		
		//Intentamos porcesar el ordenamiento de la tabla...
		$orden = SecureInt(FindParam("ord"));
			
		if(!is_null($orden)){
			if($ordenamiento = $impuesto->SetOrden($lid,$orden)){
				$params["orden"] = $ordenamiento;
			}
		}

	}

	$result = $impuesto->List("CARGO",$params);
	$hlp = new ListHelper;
	$hlp->SetPropeties($impuesto);
	$hlp->ListadoMgr = 'listadoCargos';
?>
<span class="lid" id="lid" hidden><?php echo $impuesto->listid ?? null; ?></span>
<table class="tabla-general table table-striped table-hover mb-0 d-block d-lg-table table-responsive" id="listaCargo">
	<thead class="thead-light">
		<tr>
			<th class="<?php $hlp->Orden(1); ?>" data-field="1" title="">Nombre</th>
			<th class="<?php $hlp->Orden(2); ?>" data-field="2" title="">Alias</th>
			<th class="<?php $hlp->Orden(3); ?>" data-field="3" title="">Valor</th>
			<th>Aplicacion</th>
			<th>Con IVA</th>
			<th>Estado</th>
			<th></th>
		</tr>
	</thead>
	<tbody title="Doble clic para editar">
		<?php
	if(CanUseArray($result)){
		foreach ($result as $key => $value) { ?>
		<tr ondblclick="IsEdit('CARGO',<?php echo $value->id; ?>)">
			<td><?php echo $value->nombre;?></td>
			<td><?php echo $value->alias;?></td>
			<td><?php echo $value->valor;?></td>
			<td><?php echo $value->aplicar;?></td>
			<td><?php echo $value->aplica_iva;?></td>
			<td><?php echo ESTADOS_VALIDOS[$value->estado];?></td>
			<td class="text-center"><button type="button" class="btn btn-primary btn-sm"  title="Editar Cargo" data-bs-target="#carouselImpuestos" data-bs-slide-to="1" aria-label="Slide 2"  onclick="IsEdit('CARGO',<?php echo $value->id; ?>)"><i class="fas fa-user-edit me-2"></i>Editar</button></td>
		</tr>
		<?php
		}
	}else{ ?>
		<tr>
			<td colspan="7">No se encontraron elementos</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<?php echo $hlp->footer(); ?>  
