<?php
/**
 * Datos bancarios del cliente
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."clientes".DS."class.clientesData.inc.php");
	$clientesData = new cClientesData;

	$cbu_list = $clientesData->GetCuentasBancarias($persona_id);
?>

<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-12">
				<table class="tabla-general table table-striped table-hover mb-0 d-block d-md-table table-responsive">
					<thead>
						<tr>
							<th>Tipo</th>
							<th class="text-end">Número</th>
							<th>Alias</th>
							<th>Banco</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php
						if(!CanUseArray($cbu_list)){ ?>
								<tr>
									<td colspan="5" class="text-center">Sin cuentas bancarias</td>
								</tr>
					<?php }else{ 
							foreach($cbu_list as $value){ ?>
								<tr>
									<td><?php echo $value->tipo; ?></td>
									<td><?php echo $value->valor; ?></td>
									<td><?php echo $value->extras->alias ?? $value->extras->ALIAS ?? ""; ?></td>
									<td><?php echo $value->banco ?? ""; ?></td>
									<td>
										<div class="rounded-3 justify-content-center <?php echo ($value->default)? "bg-success":"bg-danger"; ?> d-flex align-items-center">
											<span class="text-center text-white fw-bold"><?php echo ($value->default)? "Activo":"No activo"; ?></span>
										</div>
									</td>
								</tr>
						<?php } ?>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>	
	</div>
</div>