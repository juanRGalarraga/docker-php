<?php

$this_file = substr(__FILE__,strlen(DIR_BASE))." ";

$cuotas = new cCuotas();
$cuotas = $cuotas->GetByPrestamo($prestamos->theData->id);

?>
<div class="card">
	<div class="card-body">
	<?php 
		if (CanUseArray($cuotas)) {
	?>
		<div class="row">
			<div class="col-12">
				<div class="card-body">
					<h3>Cuotas</h3>
					<table class="table table-striped tabla-general text-bold">
					<thead>
						<tr>
							<th>Nº Cuota</th>
							<th>Fecha Vencimiento</th>
							<th class="text-right">Capital</th>
							<th class="text-right">Interes</th>
							<th class="text-right">Total mora</th>
							<th class="text-right">IVA mora</th>
							<th class="text-right">Cuota total</th>
							<th class="text-right">Estado</th>
							<th class="text-right">Saldo capital</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($cuotas as $cuota) { 
						?>
						<tr>
							<td class="text-center"><?php echo($cuota->cuota_nro); ?></td>
							<td><?php echo cFechas::SQLDate2Str($cuota->fecha_venc, CDATE_SHORT); ?></td>
							<td class="text-right"><?php echo $cuota->tipo_moneda." ".F($cuota->capital); ?></td>
							<td class="text-right"><?php echo $cuota->tipo_moneda." ".F($cuota->interes_cuota); ?></td>
							<td class="text-right"><?php echo $cuota->tipo_moneda." ".F($cuota->monto_mora); ?></td>
							<td class="text-right"><?php echo $cuota->tipo_moneda." ".F($cuota->total_iva_mora); ?></td>
							<td class="text-right"><?php echo $cuota->tipo_moneda." ".F($cuota->monto_cuota); ?></td>
							
							<td class="">
								<div class="rounded-3 d-flex justify-content-center align-items-center" style="background-color:<?php echo ESTADOS_COLORES[$cuota->estado]; ?>">
									<span class="text-white fw-bold"><?php echo ESTADOS_PRESTAMOS[$cuota->estado]; ?></span>
								</div>
								
							</td>
							<td class="text-right"><?php echo $cuota->tipo_moneda." ".F($cuota->saldo_final_periodo); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php } ?>
</div>

