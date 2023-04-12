<?php
require_once(DIR_model."prestamos".DS. "class.prestamos.inc.php");
require_once(DIR_model."cobros".DS. "class.cobros.inc.php");
require_once(DIR_model."cuotas".DS. "class.cuotas.inc.php");
$this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";
$post = CleanArray($_POST);

$prestamo_id = SecureInt($post['prestamo_id']);
$persona_id = SecureInt($post['persona_id']);
$prestamo = new cPrestamos();
$hoy = cFechas::Hoy();
$prestamo->Get($prestamo_id);

if ($prestamo->theData->persona_id != $persona_id) {
	EmitJson('Cliente no coincide con el préstamo.');
	return;
}

$colores_estado = array(
	'PEND' => 'bg-gradient-info',
	'SOLIC' =>  'bg-gradient-primary',
	'CANC' => 'bg-gradient-success',
	'MORA' => 'bg-gradient-danger',
	'REFIN' => 'bg-gradient-warning',
	'ANUL' => 'bg-gradient-warning',
	'HOLD' => 'bg-gradient-warning'
);
// $estados = cSidekick::GetEstadosPrestamosPosibles(1);
$estado = NULL;
$estado = ESTADOS_PRESTAMOS[$prestamo->theData->estado];

$dias_mora_segun_pago = cFechas::DiasEntreFechas($prestamo->theData->fecha_vencimiento, $hoy);

$cobros = new cCobros();

$listado = $cobros->GetByPrestamo($prestamo_id);

$pagado = 0;
if(CanUseArray($listado)){
	foreach($listado as $value){
		if($value->estado == "ACRE" OR $value->estado == "PEND"){
			$pagado += $value->monto;
		}
	}
}
$total_a_pagar = 0;
if($prestamo->theData->tipo_prestamo != "UNICO"){
	$cuotas = new cCuotas();
	$estados = array('PEND','MORA','PAGP','DIFCAP','PROR','PAGCAP','PAGINT');
	$cuotas = $cuotas->GetHistByPrestamo($prestamo->theData->id,array("estados"=>$estados));
	
	if(isset($cuotas[0])){
		$total_a_pagar = $cuotas[0]->monto_cuota;
	}
}else{
	$total_a_pagar =  ($prestamo->theData->total_mora +  $prestamo->theData->total_imponible + $prestamo->theData->iva_mora) - $pagado;

}

// $monto_mora = $prestamo->theData->orig_total * ((($mora_tasa_anual / 100) / 360) * $dias_mora_segun_pago);

?>
<div class="modal-header" id="modalInformePago">
	<h4 class="modal-title" id="listado_prestamos"><?php EchoLang("Informar un pago"); ?></h4>
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php EchoLang("Cerrar"); ?></span></button>
</div>
<div class="modal-body">
<?php

?>
	<form name="frmDatosPago" id="frmDatosPago" class="formulario">
		<h3><?php EchoLang('Datos del préstamo'); ?> Nº <?php echo $prestamo->theData->id; ?></h3>
		<div class="d-flex flex-row justify-content-between align-items-end mb-3">
			<div class=""><strong>Fecha de vencimiento:</strong> <?php echo cFechas::SQLDate2Str($prestamo->theData->fecha_vencimiento, CDATE_SHORT+CDATE_IGNORETIME); ?></div>
			<div class="btn <?php echo $colores_estado[$prestamo->theData->estado]; ?>"><?php echo $estado; ?></div>
		</div>
		<div class="border-bottom mb-3"></div>
		<div class="row align-items-end">
			<input type="hidden" name="prestamo_id" value="<?php echo $prestamo_id; ?>" />
			<input type="hidden" name="persona_id" value="<?php echo $persona_id; ?>" />
		<?php if($prestamo->theData->tipo_prestamo != "UNICO"){ ?>
		
			<div class="col-12 col-sm-4 col-md form-group">
				<label for="cuotas_selected">Cuotas Disponbiles</label>
				<select name="cuotas_selected" id="cuotas_selected" class="form-select" onchange = "SetPago(this);">
					<?php foreach ($cuotas as $key => $value) { 
						if($value->estado != "CANC"){
						?> 
						<option value="<?php echo $value->id ?>" data-monto_cuota="<?php echo number_format($value->monto_cuota+$value->monto_mora,2,'.',''); ?>"> <?php echo "Cuota Nro : ".$value->cuota_nro." Monto : ".F($value->monto_cuota); ?></option>
					<?php }
					} ?>
				</select>
			</div>
			<?php } ?>
			<div class="col form-group">
				<label for="monto_a_pagar">Monto a Pagar</label>
				<input type="number" id="monto_a_pagar" name="monto_a_pagar" class="form-control" readonly class="form-control" title="El monto a pagar" value="<?php echo number_format(($total_a_pagar), 2, '.', ''); ?>">
			</div>
			<div class="col form-group">
				<label for="monto_pago">Monto Pagado</label>
				<input type="number" id="monto_pago" name="monto_pago" class="form-control" class="form-control" title="Ingrese el monto pagado" value="<?php echo number_format(($total_a_pagar), 2, '.', ''); ?>">
			</div>

			<div class="col form-group">
				<label for="fecha_cobro">Fecha de pago</label>
				<input type="date" id="fecha_cobro" name="fecha_cobro" value="<?php echo $hoy; ?>" class="form-control" onchange="recalcultarTotalPrestamo(this.value, <?php echo $prestamo->theData->id; ?>);">
			</div>
		</div>
		<div id="test"></div>
		<div class="row align-items-start">

			<div class="col form-group">
				<label for="nro_comprobante"><?php EchoLang('Número de comprobante'); ?></php></label>
				<input type="text" id="nro_comprobante" name="nro_comprobante" class="form-control" title="Ingresar el número de comprobante del pago">
			</div>
			<div class="col">
				<label for="comprobante" class="w-100" id="contenedor_input">
					<?php EchoLang('Suba el comprobante'); ?></php>
					<div class="dragable w-100" id="zona_comprobante">
						<div id="bordeado" style="border-width: 2px; border-style: dashed; border-color: #ddd;">
							<div id="adjunto">
								<div class="d-flex justify-content-center mb-4 mt-2">
									<i class="mt-2 fad fa-file-upload" style="font-size: 50px;"></i>
								</div>
								<div class="d-flex justify-content-center ">
									<input type="hidden" class="" id="adjunto_input" name="adjunto_input">
									<h5 class="text-center" id="soltar">
										Arrastre y suelte el archivo</br>
										o</br>
										click para subirlo.
									</h5>

								</div>

							</div>
						</div>
					</div>
				</label>
				<div class="row" id="admite_adjunto">
					<div class="files w-100" id="zonaComprobante">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-success" onclick="confirmarCobroManual();">Confirmar</button>
</div>