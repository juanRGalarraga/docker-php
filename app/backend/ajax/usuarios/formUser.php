<?php
    /*
    
    
    */
    
    require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

    $post = CleanArray($_POST);
    $action = (empty($post['action'])? "new":$post['action']);

    $usuarios = new stdClass;

    if ($action != "new"){
        $usuarios = new cUsrBackend();
        $usuarios = $usuarios->GetUsuario($action);
    }

?>
<div class="card-header">
    <h4><?php echo ($action == "new")? 'Crear un nuevo usuario':'Editar usuario '.$usuarios->username;?></h4>
</div>
<div class="card-body">
    <form name="formUser" id="formUser">
        <input type="hidden" name="action" id="action" value="<?php echo $action;?>">

        <!-- Conjunto de datos principales -->
        <div class="row justify-content-between">

            <!-- Datos de la Persona -->
            <div class="col-12 col-sm-6 col-lg-7 card">
                <div class="row">
                    <label class="card-title">Datos de usuario</label>
                    <div class="col-12 col-sm-6 form-group">
                        <label for="inpName">Nombre</label>
                        <input type="text" class="form-control" name="inpName" id="inpName" placeholder="Ej: Aleksi" value="<?php echo (isset($usuarios->nombre))? $usuarios->nombre:'';?>">
                    </div>

                    <div class="col-12 col-sm-6 form-group">
                        <label for="inpLastName">Apellido</label>
                        <input type="text" class="form-control" name="inpLastName" id="inpLastName" placeholder="Ej: Krog" value="<?php echo (isset($usuarios->apellido))? $usuarios->apellido:'';?>">
                    </div>
                    
                    <div class="col-12 col-sm-6 form-group">
                        <label for="inpDni">D.N.I</label>
                        <input type="number" class="form-control" name="inpDni" id="inpDni" placeholder="Ej: 10.155.256" value="<?php echo (isset($usuarios->nro_doc))? $usuarios->nro_doc:'';?>">
                    </div>
                    
                    <div class="col-12 col-sm-6 form-group">
                        <label for="DateNacimiento">Fecha Nacimiento</label>
                        <input type="date" class="form-control" name="DateNacimiento" id="DateNacimiento" value="<?php echo  (isset($usuarios->fecha_nac))? $usuarios->fecha_nac: cFechas::Restar(cFechas::Hoy(),6570);?>">
                    </div>
                </div>
            </div>

            <!-- Datos de Usuario -->
            <div class="col-12 col-sm-6 col-lg-4 card">
                <div class="row">
                    <label class="card-title">Datos de cuenta</label>
                    <div class="col-12 col-lg-6 form-group">
                        <label for="inpUserName">Nombre de Usuario</label>
                        <input type="text" class="form-control" name="inpUserName" id="inpUserName" placeholder="Ej: UsuarioDiez" value="<?php echo (isset($usuarios->username))? $usuarios->username:'';?>">
                    </div>
    
                    <div class="col-12 col-lg-6 form-group">
                        <label for="inpPassword">Contraseña</label>
                        <input type="password" class="form-control" name="inpPassword" id="inpPassword" placeholder="Ej: ********">
                    </div>
                    
                    <div class="col-12 col-lg-6 form-group">
                        <label for="intState">Estado</label>
                        <select name="intState" id="intState" class="form-select form-select-lg">
                            <option value="">Seleccionar</option>
                            <?php foreach (ESTADOS_VALIDOS as $key => $value) {   ?>
                                <option value="<?php echo $key;?>" <?php echo (isset($usuarios->estado))? (($usuarios->estado == $key)? 'selected':''):''; ?>> <?php echo $value ?></option>
                            <?php  } ?>
                        </select>
                    </div>

                    <div class="col-12 col-lg-6 form-group">
                        <label for="inpCargo">Cargo</label>
                        <select name="inpCargo" id="inpCargo" class="form-select form-select-lg">
                            <option value="">Seleccionar</option>
                            <?php foreach (VALID_TYPE_PERMISOS as $key => $value) {   ?>
                                <option value="<?php echo $key;?>" <?php echo (isset($usuarios->nivel))? (($usuarios->nivel == $key)? 'selected':''):''; ?>> <?php echo $value ?></option>
                            <?php  } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos de region -->
        <div class="row">

            <!-- Direcciones -->
            <div class="col-12 card" id="direccionPrime">
                <div class="row">
                    <label class="card-title">Datos de Región</label>
                    <div class="col-12 col-sm-6 col-lg-3 form-group">
                        <label for="inpPais">País</label>
                        <input type="hidden" id="hidden_pais" value="<?php echo (isset($usuarios->region->pais))? $usuarios->region->pais:''; ?>">
                        <select class="form-select form-select-lg" name="inpPais" id="inpPais" onchange="getProvincias(this)">
                            <option value="">Seleccionar País</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 form-group">
                        <label for="inpProv">Provincia</label>
                        <input type="hidden" id="hidden_prov" value="<?php echo (isset($usuarios->region->provincia))? $usuarios->region->provincia:''; ?>">
                        <select class="form-select form-select-lg" disabled name="inpProv" id="inpProv" onchange="getCiudad(this)">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 form-group">
                        <label for="inpCity">Ciudad</label>
                        <input type="hidden" id="hidden_city" value="<?php echo (isset($usuarios->region->ciudad))? $usuarios->region->ciudad:''; ?>">
                        <select class="form-select form-select-lg" disabled name="inpCity" id="inpCity" onchange="setCP(this)">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 form-group">
                        <label for="inpCod">Código Postal</label>
                        <input class="form-control" name="inpCod" id="inpCod" placeholder="Ej: 5555" value="<?php echo (isset($usuarios->region->codigo))? $usuarios->region->codigo:''; ?>">
                    </div>
                </div>
            </div>

        </div>

        <!-- Informacion de contacto -->
        <div class="row justify-content-between">

            <!-- Listado de Emails -->
            <div class="col-12 col-sm-5 card" id="emailPrime">
                <div class="row">
                    <label class="card-title">Email/s</label>
                    <div class="col-12 form-group input-group" id="one-email">
                        <input type="email" class="form-control" name="inpEmail[]" id="inpEmail" placeholder="Ej: email@real.com" onblur="clonar('one-email','extraEmail','email')">
                        <div class="input-group-text">
                            <input class="form-check-input radio-emial" type="radio" onclick="MarcarCheck('emial',this)" title="Email Principal" name="DefaulEmail" id="RadioEmails" aria-label="Marcar Email por defecto">
                        </div>
                    </div>
                    <div id="extraEmail" class="row m-0 p-0"></div>
                </div>
            </div>

            <!-- Listado de Telefonos -->
            <div class="col-12 col-sm-5 card" id="telefonoPrime">
                <div class="row" >
                    <label class="card-title">Teléfono/s</label>
                    <div class="col-12 form-group input-group" id="one-phone">
                        <input type="number" class="form-control" name="inpPhone[]" id="inpPhone" placeholder="Ej: 9225-852545" onblur="clonar('one-phone','extraPhone','phone')">
                        <div class="input-group-text">
                            <input class="form-check-input radio-phone" type="radio" onclick="MarcarCheck('phone',this)" title="Telefono Principal" name="DefaulPhon" id="RadioPhone" aria-label="Marcar Telefono por defecto">
                        </div>
                    </div>
                    <div id="extraPhone" class="row m-0 p-0"></div>
                </div>
            </div>
        </div>

        <!-- Datos de direcciones -->
        <div class="row justify-content-between">

            <!-- Direcciones -->
            <div class="col-12 card" id="direccionPrime">
                <label class="card-title">Dirección/es</label>
                <div class="col-12 row" id="one-direccion">
                    <div class="col-12">
                        <div class="float-end">
                            <input class="form-check-input radio-direccion" type="radio" onclick="MarcarCheck('direccion',this)" name="DirecDefault" id="DirecDefault" title="Direccion Principal" aria-label="Marcar Dirección por defecto">
                            <label class="form-check-label mt-2" for="DirecDefault">Dirección Principal</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4 form-group">
                        <label for="inpCalle">Calle</label>
                        <input type="text" class="form-control" name="inpCalle[]" id="inpCalle" placeholder="Ej: Av. Gonzalo" onblur="clonar('one-direccion','extraDireccion','direccion')">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4 form-group">
                        <label for="inpAltura">Altura</label>
                        <input type="text" class="form-control" name="inpAltura[]" id="inpAltura" placeholder="Ej: 1252" onblur="clonar('one-direccion','extraDireccion','direccion')">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4 form-group">
                        <label for="inpPiso">Piso/Deparmento</label>
                        <input type="text" class="form-control" name="inpPiso[]" id="inpPiso" placeholder="Ej: 10-5Y" onblur="clonar('one-direccion','extraDireccion','direccion')">
                    </div>
                </div>
                <div id="extraDireccion" class="row m-0 p-0 col-12"></div>
            </div>

        </div>
    </form>
</div>

