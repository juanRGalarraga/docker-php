<?php 
    $post = cleanArray($_POST);
    $id = SecureInt($post['id']);

    if (!empty($id)){
        require_once(DIR_model."personas".DS."class.personasData.inc.php");
        $persona = new cPersonasData($id);
        $data = $persona->GetAllData();
        if (!empty($data)) {
            $emails = array();
            $phones = array();
            $directs = array();

            foreach ($data AS $value){
                switch ($value->type) {
                    case 'EMAIL':
                            $emails[] = array(
                                "data"=> $value->data,
                                "default" => $value->default
                            );
                        break;
                    case 'TEL':
                            $phones[] = array(
                                "data"=> $value->data,
                                "default" => $value->default
                            );
                        break;
                    case 'DIREC':
                        $directs[] = array(
                            "calle"=> $value->data->calle,
                            "altura"=> $value->data->altura,
                            "departamento"=> $value->data->departamento,
                            "default" => $value->default
                        );
                        break;
                }
            }

            ResponseOk(["error"=>false,"emails"=>$emails,"phones"=>$phones,"directs"=>$directs]);
        }else{
            ResponseOk(["error"=>true]);
        }
    }else{
        ResponseOk(["error"=>true]);
    }
?>