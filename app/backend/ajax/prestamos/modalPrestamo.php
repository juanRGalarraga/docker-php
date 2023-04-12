<?php
/**
 * Modal para visualización del préstamo desde varios lugares de la plataforma
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */
	$prestamo_id = SecureInt(FindParam("id,pid,prestamo_id"));
	$formated_pid = str_pad($prestamo_id,8,"0",STR_PAD_LEFT);
?>
<div class="modal-header">
	<h5 class="modal-title" id="my-modal-title">Préstamo N°: <?php echo $formated_pid; ?></h5>
	<button class="close" data-bs-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<div class="modal-body">
	<?php include ("detallesPrestamo.php"); ?>
</div>
<div class="modal-footer">
	<div class="row">
		<div class="col-12 text-center text-sm-end">
			<a href="<?php echo BASE_URL."cobranzas/gestion/".$prestamo_id; ?>" target="_blank" rel="noopener noreferrer">
				<button class="btn btn-success bg-gradient">Gestionar</button>
			</a>
		</div>
	</div>
</div>