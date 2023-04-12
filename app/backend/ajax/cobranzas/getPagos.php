<?php
/**
 * Obtiene los pagos de un prestamo dado su Id
 * Created: 2021-03-01
 * Author: Gastón Fernandez
 */
    require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
    require_once(DIR_model."cobros".DS."class.cobros.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

    $post = CleanArray($_POST);

    $prestamo_id = SecureInt($post['prestamo_id'], null);

    if(is_null($prestamo_id)){
        cLogging::Write($this_file." ".__LINE__." No se especifico id del préstamo.");
        EmitJSON("No se especifico id del préstamo.");
        return;
    }

    $prestamos = new cPrestamos();
    if(!$prestamos->Get($prestamo_id)){
        cLogging::Write($this_file." ".__LINE__." El préstamo no existe.");
        EmitJSON("El préstamo no existe.");
        return;
    }

    $cobros = new cCobros();
    $listado = $cobros->GetByPrestamo($prestamo_id);
    ?>
<h4><?php EchoLang("Pagos")?></h4>
<table class="tabla-general table table-striped table-hover mb-0 d-block d-md-table table-responsive">
	<thead class="thead-light">
		<tr>
			<th class="text-center"><?php EchoLang("Nº Comprobante")?></th>
			<th class="text-center"><?php EchoLang("Fecha")?></th>
			<th class="text-right"><?php EchoLang("Monto")?></th>
			<th><?php EchoLang("Comprobante")?></th>
			<th class="text-center"><?php EchoLang("Estado")?></th>
		</tr>
	</thead>
	<tbody>
<?php
if(CanUseArray($listado)){
	$pagado = 0;
	$sin_confirmar = 0;
	foreach($listado as $value){

		if($value->estado == "ACRE"){
			$pagado += $value->monto;
		}
		if($value->estado == "PEND"){
			$sin_confirmar += $value->monto;
		}
		$tipo = $value->tipo_moneda;
		$fecha_hora = cFechas::ISOToLatin($value->fecha_de_cobro);
		$estado = $value->estado ?? null;
		$estado_color = ESTADOS_COLORES[$estado] ?? ESTADOS_COLORES['ANUL']; 
		$estado = ESTADOS_COBROS[$estado] ?? "Desconocido";
		?>
			<tr title="ID: <?php echo $value->id; ?>">
				<td class="text-center"><p class="mt-2"><?php echo $value->nro_comprobante; ?></p></td>
				<td class="text-center"><p class="mt-2"><?php echo $fecha_hora; ?></p></td>
				<td class="text-right"><p class="mt-2"><?php echo $value->tipo_moneda." ".$value->monto; ?></p></td>
				<td>
					<?php if(isset($value->data->nombre_archivo) && !empty($value->data->nombre_archivo)){ 
						$archivo_id = $value->data->archivo_id; ?>
						<span class="pr-3 mt-1" title="<?php echo htmlentities($value->data->nombre_archivo); ?>"><?php echo CortarElipse($value->data->nombre_archivo,35,"&hellip;",true); ?></span>
						<button class="btn bg-gradient-primary btn-sm" onclick="VerComprobante('<?php echo $archivo_id;  ?>');" title="Ver comprobante">
							<i class="fas fa-eye"></i>
						</button>
					<?php }else{ ?>
							<p class="mt-2"><?php EchoLang("Sin comprobante."); ?></p> 
						<?php } ?>
				</td>
				<td class="text-center">
					<div class="rounded-3 d-flex justify-content-center align-items-center" style="background-color:<?php echo $estado_color; ?>">
						<span class="text-white fw-bold"><?php echo $estado; ?></span>
					</div>
				</td>
			</tr>
<?php   }//for

	$restante =  ($prestamos->theData->total_mora +  $prestamos->theData->total_imponible + $prestamos->theData->iva_mora) - $pagado;
}
if(!CanUseArray($listado)){ ?>

	<tr>
		<td colspan="5" class="text-center"><?php EchoLog("No se encontraron pagos."); ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>
<?php if(CanUseArray($listado)){ ?>
	<hr>
	<div class="row">
	<?php if($sin_confirmar > 0){ ?>
		<div class="col-4">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">Monto sin confirmar</h4>
					<p class="card-text"><?php echo "$ ".$tipo." ".F($sin_confirmar); ?></p>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if($pagado > 0){ ?>
		<div class="col-4">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">Monto pagado</h4>
					<p class="card-text"><?php echo "$ ".$tipo." ".F($pagado); ?></p>
				</div>
			</div>
		</div>
	<?php } ?>
		<div class="col-4">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">Restante a pagar</h4>
					<p class="card-text"><?php echo "$ ".$tipo." ".F($restante); ?></p>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<hr>
<div class="d-flex justify-content-between">
	<?php
	if (($prestamos->theData->estado != 'CANC' OR $prestamos->theData->estado != 'HOLD')) {
		
	?>
		<div class="col text-center">
			<button type="button" class=" btn bg-primary text-white mb-3" onClick="informarPago();" title="Informar un pago de forma manual">Informar Pago</button>
		</div>
	<?php
	} ?>
</div>  