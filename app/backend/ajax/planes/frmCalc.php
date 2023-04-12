<?php
/*
	Contenido de la calculadora para probar un plan.
	Author: DriverOp
*/
require_once(DIR_model."planes".DS."class.planes.inc.php");

$id = SecureInt($_POST['id']??null,null);

$p = new cPlanes();
if (!$p->GetAll($id)) {
	return cSideKick::ShowEmptySearchMessage('Plan no encontrado.');
}
$plan = $p->theData;
?>
<form id="frmCalc" name="frmCalc" class="form-group">
	<input type="hidden" name="id" class="form-control" value="<?php echo $id;?>" />
	<div class="row">
		<div class="col-md-6 col-12">
			<div class="form-group">
				<label for="testMonto">Monto</label>
				<input type="number" id="testMonto" class="form-control" placeholder="Monto del préstamo" name="testMonto" value="<?php echo I($plan->monto_minimo); ?>" min="<?php echo I($plan->monto_minimo); ?>" max="<?php echo I($plan->monto_maximo); ?>" step="100">
			</div>
		</div>
		<div class="col-md-6 col-12">
			<div class="form-group">
				<label for="testPeriodo">Período</label>
				<select class="form-select" id="testPeriodo" name="testPeriodo">
<?php
for ($i = $plan->plazo_minimo; $i<=$plan->plazo_maximo; $i++) {
?>
					<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
<?php
}
?>
				</select>
			</div>
		</div>
		<div class="col-12 text-end">
			<button type="button" class="btn btn-primary" onclick="calcular()" title="Calcular"><i class="fas fa-square-root-alt"></i> Calcular</button>
		</div>
	</div>
</form>
<div id="mostrarResultados"></div>
<?php
?>