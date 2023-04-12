<?php
/**
 * biblioteca de archivos del cliente
 * Created: 2021-11-09
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$biblioteca = new cBiblioteca;
	$archivos = array();
	$carpetas = array();

	$folder = "";
	if(!isset($persona_id)){ 
		$persona_id = SecureInt(FindParam("id"));
		$folder = FindParam("folder");
	}
	if(is_null(SecureInt($persona_id))){ 
		cLogging::Write(__FILE__." ".__LINE__." El ID de la persona no es un número entero válido");
		return cSidekick::ShowWarning("No se pueden listar los archivos");
	}
	$files = $biblioteca->ListFiles($persona_id,$folder);

	if($files){
		$files = (is_object($files) AND !is_array($files))? json_decode(json_encode($files),true):$files;
	
		$archivos = $files['archivos'] ?? array();
		unset($files['archivos']);
	
		$carpetas = array_keys($files);
	}
	$ruta = explode("/",$folder);
	$ruta = array_filter($ruta,function($value){
		$value = trim($value);
		if(!empty($value)){ return $value; }
	});
	$root = "";
?>
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-12 col-md-3">
				<div class="ibox float-e-margins">
					<div class="ibox-content">
						<div class="file-manager">
							<h5>Directorio actual:</h5>
							<nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a class="fst-underline" onclick="ViewFolder(<?php echo $persona_id; ?>); return false;">Inicio</a></li>
								<?php foreach($ruta as $value){
									if(empty($value)){ continue; }
									$root .= "/".$value
								?>
									<li class="breadcrumb-item"><a class="fst-underline" onclick="ViewFolder(<?php echo $persona_id; ?>,'<?php echo $root; ?>'); return false;"><?php echo $value; ?></a></li>
								<?php } ?>
								</ol>
							</nav>

							<div class="hr-line-dashed"></div>
							<button class="btn btn-primary btn-sm float-end" title="Crear nueva carpeta"onclick="NewFolder(<?php echo $persona_id; ?>,'<?php echo $folder; ?>');">
								<i class="fas fa-folder-plus"></i>
							</button>
							<div class="hr-line-dashed"></div>

							<h5>Carpetas</h5>
							<ul class="folder-list" style="padding: 0">
								<?php 
									if(CanUseArray($ruta)){
										array_pop($ruta);
										$atras = implode("/",$ruta);
										?>
										<li onclick="ViewFolder(<?php echo $persona_id; ?>,'<?php echo $atras; ?>')" title="Regresar">
											<a href="" onclick="return false;">
												<i class="fa fa-folder"></i>..
											</a>
										</li>
									<?php
									}
									if(CanUseArray($carpetas)){
										foreach($carpetas as $value){ 
											$current = 	$folder."/".$value;
										?>
										<li onclick="ViewFolder(<?php echo $persona_id; ?>,'<?php echo $current; ?>')" title="<?php echo $value; ?>">
											<a href="" onclick="return false;">
												<i class="fa fa-folder"></i> <?php echo $value; ?>
											</a>
										</li>

									<?php }
									}else{ ?>
											<span>No hay carpetas</span>
								<?php } ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<!-- End folders -->

			<div class="fixed-bottom mb-5">
				<?php if(CanUseArray($archivos)){ ?>
					<button class="btn btn-info float-end file-download ms-2 me-2 d-none " > <i class="far fa-file-download"></i> Descargar (<span id="cant_selected"> 1 </span>) </button>
					
				<?php } ?>
			
				<button class="btn btn-primary float-end " onclick="UploadFile(this);"> <i class="far fa-file-upload" ></i> Subir Archivo </button>
				
				<form name="formFile" id="formFile">
					<input type="text" id="pid" name="pid" hidden value="<?php echo $persona_id; ?>">
					<input type="text" id="ruta" name="ruta" hidden value="<?php echo $folder; ?>">
					<input type="file" hidden name="uploadFile" id="uploadFile">
				</form>
				

			</div>
			<div class="col-12 col-md-9">
				<div class="file-box">
					<!-- <div class="file mb-0">
						<a onclick="UploadFile(this);" >
							<span class="corner"></span>

							<div class="icon">
								<i class="far fa-file-upload" style="color: aquamarine;"></i>
							</div>
							<span class="d-flex justify-content-center">Subir archivo</span>
						</a>
					</div> -->

					<?php if(CanUseArray($archivos)){ ?>
						<!-- <div class="file-box file-download" title="Descargar seleccionados">
							<div class="file">
								<a>
									<span class="corner"></span>
		
									<div class="icon">
										<i class="far fa-file-download"></i>
									</div>
									<span class="d-flex justify-content-center">Descargar archivos</span>
								</a>
							</div>
						</div> -->
					<?php } ?>
				</div>
				<?php
				if(CanUseArray($archivos)){ ?>
				<?php foreach($archivos as $value){
							$nombre = $value['nombre'];
							$agregado = $value['sys_fecha_modif'] ?? null;
							$agregado = (cFechas::LooksLikeISODate($agregado) OR cFechas::LooksLikeISODateTime($agregado))? cFechas::SQLDate2Str($agregado,CDATE_SHORT):$agregado;
						?>
						<div class="file-box">
							<div class="file" title="<?php echo $nombre; ?>">
								<input class="form-check-input" type="checkbox" data-id="<?php echo $persona_id; ?>" value="<?php echo $folder."/".$nombre; ?>" aria-label="Download File" onchange="UpdateSelections();">
								<a onclick="toggleSelection(this);">
									<span class="corner"></span>

									<div class="icon">
										<i class="fa fa-file"></i>
									</div>
									<div class="file-name">
										<?php echo CortarElipse($nombre,30); ?>
										<br>
										<small>Agregado: <?php echo $agregado; ?></small>
									</div>

								</a>
							</div>
						</div>
			<?php	} ?>
			<?php } ?>
			</div>
			<!-- End files -->
		</div>
		<!-- End row -->
	</div>
</div>