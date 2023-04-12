<?php
	chdir(__DIR__);
	$data = $prestamo_data;
?>
<div class="row">
	<div class="col-12">
		<h3 class="text-center">Datos del cliente</h3>
		<?php include("datos_cliente.php"); ?>
	</div>

	<div class="col-12">
		<h3 class="text-center">Datos del préstamo</h3>
		<?php include("..".DS."detallesPrestamo.php"); ?>
	</div>
</div>