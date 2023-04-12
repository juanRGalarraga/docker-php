<?php 

?>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <p class="text-center fw-bold"><small><?php EchoLang('Nº Solicitud'); ?></small></p>
                <p class="text-center "><?php echo $data_solicitud->id ?></p>		
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Nombre y Apellido'); ?></small></p>
                <p class="text-center "><?php echo (isset($data_solicitud->data) && isset($data_solicitud->data->nombre) && isset($data_solicitud->data->apellido)) ? $data_solicitud->data->nombre." ".$data_solicitud->data->apellido : $incompleto; ?></p>		
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card" style="background-color: <?php echo ESTADOS_COLORES_SOLICITUDES[$data_solicitud->estado_solicitud]; ?>">
            <div class="card-body text-white">
                <p class="text-center fw-bold"><small><?php EchoLang('Estado'); ?></small></p>
                <p class="text-center "><?php echo ESTADOS_SOLICITUDES[$data_solicitud->estado_solicitud]; ?></p>		
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('CBU'); ?></small></p>
                <p class="text-center "><?php echo (isset($data_solicitud->data) && isset($data_solicitud->data->cbu)) ? $data_solicitud->data->cbu : $incompleto; ?></p>		
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Telefono'); ?></small></p>
                <p class="text-center "><?php echo (isset($data_solicitud->data) && isset($data_solicitud->data->tel)) ? $data_solicitud->data->tel : $incompleto; ?></p>		
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Email'); ?></small></p>
                <p class="text-center "><?php echo (isset($data_solicitud->data) && isset($data_solicitud->data->email)) ? $data_solicitud->data->email : $incompleto; ?></p>		
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('DNI'); ?></small></p>
                <p class="text-center "><?php echo (isset($data_solicitud->data) && isset($data_solicitud->data->nro_doc)) ? $data_solicitud->data->nro_doc : $incompleto; ?></p>		
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Domicilio'); ?></small></p>
                <p class="text-center "><?php echo (isset($data_solicitud->data) && isset($data_solicitud->data->domicilio)) ? $data_solicitud->data->domicilio : $incompleto; ?></p>		
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body ">
                <p class="text-center fw-bold"><small><?php EchoLang('Fecha Creación'); ?></small></p>
                <p class="text-center "><?php echo (isset($data_solicitud->sys_fecha_alta_txt)) ? $data_solicitud->sys_fecha_alta_txt : $incompleto; ?></p>		
            </div>
        </div>
    </div>
</div>