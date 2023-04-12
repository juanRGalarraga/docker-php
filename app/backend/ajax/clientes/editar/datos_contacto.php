<?php
/**
 * Datos de contacto del cliente
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."clientes".DS."class.clientesData.inc.php");
	$clientesData = new cClientesData;

	$tel = $data->TelefMovil ?? "-";
	$email = $data->Email ?? "-";

	$email_list = $clientesData->GetData($persona_id,"email");
	$tel_list = $clientesData->GetData($persona_id,"tel");

	//Lo que hace es remover el email/tel que sea igual al principal
	if(CanUseArray($email_list)){
		foreach($email_list as $key => $value){
			if($value->valor == $email){ unset($email_list[$key]); break; }
		}
	}

	if(CanUseArray($tel_list)){
		foreach($tel_list as $key => $value){
			if($value->valor == $tel){ unset($tel_list[$key]); break; }
		}
	}
?>

<div class="card">
	<div class="card-body">
		<form name="frmDatosContacto" id="frmDatosContacto">
			<div class="row">
				<!-- Dirección -->
				<div class="col-12 col-md-6 col-xl-4">
					<div class="card gap-3">
						<div class="card-header bg-info">
							<h3>Dirección</h3>
						</div>
						<div class="card-body">
							<!-- Calle -->
							<label for="calle" class="form-label">Calle</label>
							<input class="form-control" type="text" name="calle" id="calle">

							<!-- Número -->
							<label for="dirNro" class="form-label">Número</label>
							<input class="form-control" type="text" name="dirNro" id="dirNro">

							<!-- Piso -->
							<label for="dirPiso" class="form-label">Piso</label>
							<input class="form-control" type="text" name="dirPiso" id="dirPiso">

							<!-- Departamento -->
							<label for="dirDepa" class="form-label">Departamento</label>
							<input class="form-control" type="text" name="dirDepa" id="dirDepa">

							<!-- Código postal -->
							<label for="dirPostal" class="form-label">Código postal</label>
							<input class="form-control" type="text" name="dirPostal" id="dirPostal">

							<!-- Ciudad -->
							<label for="dirCiudad" class="form-label">Ciudad</label>
							<input class="form-control" type="text" name="dirCiudad" id="dirCiudad">

							<!-- Región -->
							<label for="dirRegion" class="form-label">Región</label>
							<input class="form-control" type="text" name="dirRegion" id="dirRegion">
						</div>
					</div>
				</div>

				<div class="col-12 col-md-6 col-xl-8">
					<div class="row row-cols-1 row-cols-xl-2">
						<!-- Número teléfono -->
						<div class="col">
							<div class="card gap-3">
								<div class="card-header bg-info">
									<h3>Números de teléfono</h3>
								</div>
								<div class="card-body">
									<!-- Principal -->
									<label for="tel" class="form-label">Principal</label>
									<input class="form-control" type="text" name="tel" id="tel" value="<?php echo $tel ?>" placeholder="tel" disabled>
									
									<div class="table-responsive">
										<table class="table table-hover table-striped mt-3">
											<thead>
												<tr>
													<th>Número</th>
													<th>Verificado</th>
												</tr>
											</thead>
											<tbody>
												<?php
													if(!CanUseArray($tel_list)){ ?>
															<tr>
																<td colspan="2" class="text-center">Sin teléfonos extra</td>
															</tr>
												<?php }else{ 
														foreach($tel_list as $value){ ?>
															<tr>
																<td><?php echo $value->valor; ?></td>
																<td><?php echo ($value->validado)? "Si":"No"; ?></td>
															</tr>
													<?php } ?>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>

						<!-- Emails -->
						<div class="col">
							<div class="card gap-3">
								<div class="card-header bg-info">
									<h3>Direcciones de correo electrónico</h3>
								</div>
								<div class="card-body">
									<!-- Principal -->
									<label for="email" class="form-label">Principal</label>
									<input class="form-control" type="text" name="email" id="email" value="<?php echo $email ?>" placeholder="email" disabled>
									
									<div class="table-responsive">
										<table class="table table-hover table-striped mt-3">
											<thead>
												<tr>
													<th>Número</th>
													<th>Verificado</th>
												</tr>
											</thead>
											<tbody>
												<?php
													if(!CanUseArray($email_list)){ ?>
															<tr>
																<td colspan="2" class="text-center">Sin direcciones extra</td>
															</tr>
												<?php }else{ 
														foreach($email_list as $value){ ?>
															<tr>
																<td><?php echo $value->valor; ?></td>
																<td><?php echo ($value->validado)? "Si":"No"; ?></td>
															</tr>
													<?php } ?>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>	
		</form>
	</div>
	<?php /*
	<div class="card-footer">
		<div class="row">
			<div class="col-12 text-center text-sm-end">
				<button class="btn btn-success"><i class="mx-3 fas fa-check-double" aria-hidden="true"></i> Guardar</button>
			</div>
		</div>
	</div>
	*/?>
</div>