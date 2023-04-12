<?php 
/*
    Chequear la INFORMACION utlizada para dar de alta un usuario y o editar.
*/
    require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");

    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";
    $post = CleanArray($_POST);

    $msgerr = array();
    
    $persona_data= array();
    $usuario_data= array();
    $region = array();
    
    $action = (isset($post['action']))? ((SecureInt($post['action']))? $post['action']:'new'):null;

    if (empty($action)){
        cLogging::Write($this_file." La variable action esta vacía.");
        ResponseOk(["error"=>"Falta información para llevar a cabo la acción."]);
        return;
    }

    $usuario = new cUsuarios;
    $persona = new cPersonas;

    $name = $post["inpName"];
    $lastname = $post["inpLastName"];
    $dni = $post["inpDni"];
    $fnacimiento = $post["DateNacimiento"];

    $user = $post["inpUserName"];
    $password = $post["inpPassword"];

    $state = $post["intState"];
    $cargo = $post["inpCargo"];

    $pais = $post["inpPais"];
    $cod = $post["inpCod"];
    $prov = (isset($post["inpProv"])? $post["inpProv"]:null);
    $city = (isset($post["inpCity"])? $post["inpCity"]:null);

    // Datos de emails
    $inpEmail = (isset($post['inpEmail']))? $post['inpEmail']:array();
    $emails= recorrerElemnt($inpEmail,$post,"DefaulEmail","EMAIL");

    // Si estan vacíos marco el error
    if (!CanUseArray($emails)){
        $msgerr['inpEmail'] = "Es necesario un email de contacto.";
    }

    // Datos de  telefonos
    $inpPhone = (isset($post['inpPhone']))? $post['inpPhone']:array();
    $phones = recorrerElemnt($inpPhone,$post,"DefaulPhon","TEL");

    // Si estan vacíos marco el error
    if (!CanUseArray($phones)){
        $msgerr['inpPhone'] = "Es necasario un telefono de contacto.";
    }

    // Datos de direccion
    $inpCalle = (isset($post['inpCalle']))? $post['inpCalle']:array();
    $inpAltura = (isset($post['inpAltura']))? $post['inpAltura']:array();
    $inpPiso = (isset($post['inpPiso']))? $post['inpPiso']:array();
    $direcciones = recorrerElemnt(["c"=>$inpCalle,"a"=>$inpAltura,"p"=>$inpPiso],$post,"DirecDefault","direccion");

    // Si estan vacíos marco el error
    if (!CanUseArray($direcciones)){
        $msgerr['inpCalle'] = "Es necesaria una Calle.";
        $msgerr['inpAltura'] = "Es necesaria la Altura.";
        $msgerr['inpPiso'] = "Es necesario el Piso/Departamento.";
    }

    // Pasando por control
    cCheckInput::NomApe($name,"inpName");
    $persona_data['nombre'] = $name;
    
    // Pasando por control
    cCheckInput::NomApe($lastname,"inpLastName");
    $persona_data['apellido'] = $lastname;
    
    // Pasando por control
    if (!empty($dni)){
        cCheckInput::DNI($dni,"inpDni");
        $persona_data['nro_doc'] = $dni;

        if ($persona->GetByNumDoc($dni,$action)){
            $msgerr['inpDni'] = "Este documento ya existe en el sistema.";
        }
    }else{
        $msgerr['inpDni'] = "Es necesario un numero de documetno.";
    }

    // control de edad
    $edad = cFechas::CalcularEdad($fnacimiento);
    if ($edad < 20){
        $msgerr['DateNacimiento'] = "Es necesario tener como minimo 20 años.";
    }else if ($edad > 60){
        $msgerr['DateNacimiento'] = "Es necesario tener como maximo 60 años.";
    }
    
    // Controlo si existe el nombre de usuario
    if (!empty($user)){
        if ($usuario->GetByUsername($user)){
            // Si lo encuntro reviso que no se este editando a la persona
            if ($usuario->persona_id != $action){
                $msgerr['inpUserName'] = "Nombre de usuario ya es encuntra ocupado.";
            }
        }
        $usuario_data['username'] = $user;
    }else{
        $msgerr['inpUserName'] = "El nombre de usuario esta vacío.";
    }

    // Pasando por control
    if ($usuario->persona_id != $action){
        cCheckInput::Password($password,"inpPassword","inpPassword");
    }
    if (!empty($password)){
        $usuario_data['password'] = $password;
    }

    // Control que los datos de la region no esten vacíos
    if (empty($prov)){
        $msgerr['inpProv'] = "Tiene que seleccionar una provincia valida.";
    }
    $region['provincia']= $prov;

    if (empty($city)){
        $msgerr['inpCity'] = "Tiene que seleccionar una ciudad valida.";
    }
    $region['ciudad']= $city;

    if (empty($pais)){
        $msgerr['inpPais'] = "Tiene que seleccionar un país valido.";
    }
    $region['pais']= $pais;

    if (empty($cod)){
        $msgerr['inpCod'] = "Tiene que ingresar un código valido.";
    }
    $region['codigo']= $cod;
    
    // Unificamos los datos de la persona
    $persona_data['region'] = json_encode($region);

    // Control de estado valido
    if (!array_key_exists($state,ESTADOS_VALIDOS)){
        $msgerr['intState'] = "El estado seleccionado no es valido..";
    }
    $usuario_data['estado'] = $state;

    // Control de tipo de permisos validos
    if (!array_key_exists($cargo,VALID_TYPE_PERMISOS)){
        $msgerr['inpCargo'] = "El cargo seleccionado no es  valido.";
    }
    $usuario_data['nivel'] = $cargo;
    
    // Emito el mensjae de error
    $msgerr = array_merge(cCheckInput::$msgerr,$msgerr);
    if (CanUseArray($msgerr)) {
        EmitJSON($msgerr);
        return;
    }

    $persona_data['negocio_id'] = $objeto_usuario->negocio_id;
    $persona_data['fecha_nac'] = $fnacimiento;
    $usuario->sys_usuario = $objeto_usuario->id;
  
    $todo = array(
        "persona" => $persona_data,
        "usuario" => $usuario_data,
        "emails" => $emails,
        "telefono" => $phones,
        "direccion" => $direcciones,
    );

    if ($action == "new"){
        if (!$usuario->CreateUsuario($todo)){
            cLogging::Write($this_file." No fue posible crear al usuario");
            ResponseOk(["error"=>"No fue posible dar de alta el usuario."]);
            return;
        }
        ResponseOk(['create'=>"La persona fue creada efectivamente."]);
    }else if ($persona->GetById($action)){
        if (!$usuario->EditarUsuario($todo,$action)){
            cLogging::Write($this_file." No fue posible editar al usuario");
            ResponseOk(["error"=>"No fue posible editar el usuario."]);
            return;
        }
        ResponseOk(['create'=>"La persona fue editada correctamente."]);
    }


    /**
     * Summary. Reccore los elementos para armar los datos.
     * @param array $datos
     * @param obj   $post
     * @param string $default
     * @param string $type
     * @return array $result
     */
    function recorrerElemnt($datos,$post,$default,$type){
        $result = array();

        if ($type != "direccion"){
            foreach ($datos as $key => $value) {
                if (!empty($value)){
                    if ($type == "EMAIL"){
                        cCheckInput::IsEmail($value);
                    }else if ($type == "TEL"){ 
                        cCheckInput::Tel($value);
                    }

                    $dafualt = false;
                    if (isset($post[$default.$key])){
                        $dafualt = true;
                    }

                    $result[] = array(
                        $type=> $value,
                        "default"=> $dafualt
                    );
                }
            }
        }else{
            if (CanUseArray($datos['c']) AND CanUseArray($datos['a']) AND CanUseArray($datos['p'])){
                foreach ($datos as $ident => $arrays) {
                    foreach ($arrays as $key => $value) {
                        if (!empty($value)){

                            switch ($ident) {
                                case 'c':
                                    $calle = $value.$key;
                                    $result[$key-1]["calle"]= $calle;
                                    break;
                                case 'a':
                                    $altura = $value.$key;
                                    $result[$key-1]["altura"]= $altura;
                                    break;
                                case 'p':
                                    $piso = $value.$key;
                                    $result[$key-1]["departamento"]= $piso;
                                    break;
                                }

                            $dafualt = false;
                            if (isset($post[$default.$key])){
                                $dafualt = true;
                            }

                            $result[$key-1]["default"]= $dafualt;
                        }
                    }
                }
            }
        }
        return $result;
    }
?>