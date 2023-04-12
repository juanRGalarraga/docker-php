<?php 

$post = CleanArray($_POST);

require_once(DIR_model."simular".DS."class.simular.inc.php");
require_once(DIR_model."planes".DS."class.planes.inc.php");
$simulador = new cSimular();
$planes = new cPlanes;

$capital = $data_solicitud->data->capital ?? 0;
$plazo = $data_solicitud->data->plazo ?? 0;
$plan = $data_solicitud->data->plan ?? 0;

if(isset($post['capital']) && SecureFloat($post['capital'])){
    $capital = $post['capital']; 
}

if(isset($post['plazo']) && SecureFloat($post['plazo'])){
    $plazo = $post['plazo']; 
}

if(isset($post['plan']) && SecureFloat($post['plan'])){
    $plan = $post['plan']; 
}

$filters = array();
$filters['capital'] = $capital;
$filters['plazo'] = $plazo;
$filters['plan'] = $plan;

$listado = $planes->GetListado([]);
$plan_filters = array();
if(CanUseArray($listado)){
	foreach($listado as $key => $value){
		$plan_filters[] = array(
			'id' => $value->id,
			'nombre' => $value->nombre_comercial
		);
	}
}


if(!$date_simulate = $simulador->Simular($filters)){
    echo '<p class="text-center"> No se pudo simular el resultado </p>';
    return;
}

if(!isset($post['capital']) && !isset($post['plazo']) && !isset($post['plan'])){ ?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-4">
                <label class="fw-bold" for=""> Capital </label>
                <input class="form-control" type="number" id="capital" name="capital" step="100"   value="<?php echo $capital; ?>">
            </div>
            <div class="col-4">
                <label class="fw-bold" for=""> Plazo </label>
                <input class="form-control" type="number" id="plazo" name="plazo" value="<?php echo $plazo; ?>">
            </div>
            <div class="col-2">
				<label for="plan">Planes</label>
				<select id="plan" name="plan" class="form-select" onchange="refreshList();">
					<?php 
						foreach($plan_filters as $value) { ?>
							<option value="<?php echo $value['id']; ?>"><?php echo $value['nombre']; ?></option>
					<?php } ?>
				</select>
            </div>
            <div class="col-2 align-self-end">
                <button class="btn btn-primary float-end" onclick="Simular(); return false;" > Simular Préstamo </button>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="row" id="body_simulate">
    <div class="col-3">
        <div class="card" >
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Total'); ?></small></p>
                <p class="text-center ">$ <?php echo F($date_simulate->Total ?? 0); ?></p>		
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card" >
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Intereses'); ?></small></p>
                <p class="text-center ">$ <?php echo F($date_simulate->Intereses ?? 0); ?></p>		
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card" >
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Gastos Administrativos'); ?></small></p>
                <p class="text-center "> $ <?php echo F($date_simulate->Gastos_Administrativos ?? 0); ?></p>		
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card" >
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('TNA'); ?></small></p>
                <p class="text-center ">%<?php echo $date_simulate->TNA ?? 0; ?></p>		
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card" >
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Tipo'); ?></small></p>
                <p class="text-center "> <?php echo $date_simulate->Tipo ?? 0; ?></p>		
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card" >
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Fecha Pago'); ?></small></p>
                <p class="text-center "><?php echo $date_simulate->Fecha_Pago_Display ?? 0; ?></p>		
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card" >
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Tipo Moneda'); ?></small></p>
                <p class="text-center "><?php echo $date_simulate->Tipo_Moneda ?? "-"; ?></p>		
            </div>
        </div>
    </div>

    <div class="row">
        <?php if(isset($date_simulate->cuotas) && is_object($date_simulate->cuotas)){  ?>
            <table class="table table-striped ">
                <thead>
                    <tr>
                        <th class="text-center"> Nº Cuota </th>
                        <th class="text-end"> Saldo Inicio del Periodo </th>
                        <th class="text-center"> Fecha Vencimiento </th>
                        <th class="text-end"> Capital </th>
                        <th class="text-end"> Interes </th>
                        <th class="text-end"> IVA Interes </th>
                        <th class="text-end"> Monto Cuota </th>
                        <th class="text-end"> Saldo Final del Periodo </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($date_simulate->cuotas as $key => $value) { ?>  
                        <tr>
                            <td class="text-center"> <?php echo (isset($value->cuota_nro)) ? $value->cuota_nro : "-"; ?></td>
                            <td class="text-end"> $ <?php echo (isset($value->saldo_inicio_periodo)) ? F($value->saldo_inicio_periodo) : "-"; ?></td>
                            <td class="text-center"> <?php echo (isset($value->fecha_venc)) ? cFechas::SQLDate2Str($value->fecha_venc) : "-"; ?></td>
                            <td class="text-end"> $ <?php echo (isset($value->capital)) ? F($value->capital) : "-"; ?></td>
                            <td class="text-end"> $ <?php echo (isset($value->interes_cuota)) ? F($value->interes_cuota) : "-"; ?></td>
                            <td class="text-end"> $ <?php echo (isset($value->iva_interes_cuota)) ? F($value->iva_interes_cuota) : "-"; ?></td>
                            <td class="text-end"> $ <?php echo (isset($value->monto_cuota)) ? F($value->monto_cuota) : "-"; ?></td>
                            <td class="text-end"> $ <?php echo (isset($value->saldo_final_periodo)) ? F($value->saldo_final_periodo) : "-"; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } ?>
    </div>
</div>

