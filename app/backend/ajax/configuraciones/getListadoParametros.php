<?php
require_once(DIR_model."configuraciones".DS."class.parametros.inc.php");
require_once(DIR_includes."class.listhelper.inc.php");
$parametros = new cParametros;
$params = array();


//Buscamos los filtros...
$pag = FindParam("pag");
$pag = ($pag > 0)? $pag:1;
$params["pag"] = $pag;

$alias = substr($_POST['alias']??null,0,20);
$_SESSION['params']['alias'] = $alias;

$grupo = SecureInt(FindParam('grupo'));
if(!is_null($grupo)){ $params['grupo'] = $grupo; }
$grupo = empty($grupo)? "0":$grupo;

$buscar = $_POST['buscar'] ?? null;
if(!is_null($buscar)){ $params['buscar'] = $buscar; }

$lid = FindParam("lid");
if($lid){
	$params["listid"] = $lid;
	
	//Intentamos porcesar el ordenamiento de la tabla...
	$orden = SecureInt(FindParam("ord"));
		
	if(!is_null($orden)){
		if($ordenamiento = $parametros->SetOrden($lid,$orden)){
			$params["orden"] = $ordenamiento;
		}
	}
}

$parametros->GetParams($params);

if (SecureInt($parametros->header->cant??null,0) == 0) {
?>
	<p class="text-center">La consulta no devolvió resultados.</p>
<?php
	return;
}
$hlp = new ListHelper;
$hlp->SetPropeties($parametros);
?>
<span class="lid" id="lid" hidden><?php echo $prestamos->parametros ?? null; ?></span>
<table class="tabla-general table table-striped table-hover mb-0 d-block d-lg-table table-responsive">
	<thead>
		<tr>
			<th>Nombre</th>
			<th>Valor</th>
			<th>Tipo</th>
			<th>Estado</th>
			<th>Descripción</th>
			<th>Ult. Modificación</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
	foreach($parametros->listado as $linea) {
		$ofuscado = $linea->ofuscado ?? false;
		$valor = CortarElipse($linea->valor,20,null,true);
		$min = floor(strlen($valor)/2);
		$hidden = str_pad("",random_int($min,strlen($valor)),"*");
		$hiddenVal = ($ofuscado)? $hidden:$valor;
		$title = ($ofuscado)? $hidden:$linea->valor;
?>
		<tr title="Doble clic para editar" style="cursor: pointer;" onDblClick="Editar('<?php echo $linea->nombre; ?>');">
			<td><?php echo $hlp->replace($linea->nombre); ?></td>
			<td title="<?php echo NeutralizeStr($title); ?>">
				<span>
					<?php echo $hiddenVal; ?>
				</span>
				<?php if($ofuscado){ ?>
					<button class="btn" onclick="toggleValue(this);" data-value="<?php echo $valor; ?>" data-title="<?php echo NeutralizeStr($linea->valor); ?>"><i class="far fa-eye"></i></button>
				<?php } ?>
			</td>
			<td><?php echo $linea->tipo; ?></td>
			<td><?php echo $linea->estado; ?></td>
			<td title="<?php echo NeutralizeStr($linea->descripcion); ?>"><?php echo CortarElipse($linea->descripcion,30); ?></td>
			<td><?php echo $linea->sys_fecha_modif_txtshort; ?></td>
			<td class="text-center"> <button class="btn btn-sm btn-primary" onclick = "Editar('<?php echo $linea->nombre; ?>');"> <i class="fas fa-arrow-right"></i></button> </td>
		</tr>
<?php
	}
?>
	</tbody>
</table>
<?php
$hlp->ListadoMgr = "elListador.SetTargetElement(targetDiv); elListador";
$hlp->ListadoMgrExtraParam = "{'grupo':$grupo,'alias':'".$alias."'}";
$hlp->Footer();
