<?php
/**
 * Cliente para enviar sms mediante infobip
 * Created: 2021-02-23
 * Author: Gastón Fernandez
 */
    require_once("class.sms_base.inc.php");

    class cInfobipSms extends IBBase{
        private $default_pin = 12345;
        function __construct(){
            parent::__construct();
        }

        /**
         * Summary. Envia el pin al numero de teléfono indicado
         * @param string $tel Número de telefono al que se le va a enviar el mensaje incluyendo el +(codigo de país)
         * @param string $from(opcional) Indica quien envia el mensaje
         * @param string $templateID(opcional) ID que indica el template a utilizar para el mensaje
         * @return Bool $result Devuelve un bool indicando si el mensaje fue enviado o no
         */
        public function EnviarPin($tel, $from = null, $templateID = null){
            $result = false;
            try {
                if(empty($tel)){
                    throw new Exception("No se indico un número de teléfono.");
                }

                if(is_null($templateID)){
                    $templateID = $this->templateid;
                    if(empty($templateID)){
                        $template = $this->obtenerTemplate($this->appid);
                        if($template !== false AND isset($template->messageId)){
                            $templateID = $template->messageId;
                        }else{
                            throw new Exception("No se pudo obtener ningún template.");   
                        }
                    }
                }

                $data = [
                    "applicationId" => $this->appid,
                    "messageId" => $templateID,
                    "from" => $from,
                    "to" => $tel
                  ];
                  
                $url = "/2fa/1/pin?ncNeeded=true";

                if($this->modo_test){
                    $result = json_decode(json_encode(["pinId" => $this->default_pin]));
                }else{
                    $result = $this->Enviar("POST", $data, $url);
                }
                $result = [
                    "http_code" => $this->http_code,
                    "method" => "POST",
                    "url" => $this->base_url.$url, 
                    "request" => $data, 
                    "response" => $result,
                    "curl_error" => $this->err
                    ];
            } catch (Exception $e) {
                throw new Exception(__FILE__ ." ".__LINE__ ." ".$e->getMessage());
            }
            return $result;
        }

         /**
         * Summary. Comprueba si el pin ingresado es correcto o no
         * @param int $pin Número que indica el pin ingresado
         * @param int $pinID Indica el ID del pin
         * @return Bool-Json $result Devuelve un json indicando si el pin fue verificado o un bool false en caso de error
         */
        public function VerificarPin($pin, $pinID){
            $result = false;
            try {
                if (empty($pin) or empty($pinID)) {
                    throw new IBNotificadorExcepcion("pin o pinID vacio:");
                }
                $data = ['pin' => $pin];
                $url = "/2fa/1/pin/".$pinID."/verify";
                if($this->modo_test){
                    if($pin == $this->default_pin){
                        $result = json_decode(json_encode(["pinId" => $pinID, "pin" => $pin, "verified" => true]));
                    }else{
                        $result = json_decode(json_encode(["pinId" => $pinID, "pin" => $pin, "verified" => false]));
                    }
                }else{
                    $result = $this->Enviar("POST", $data, $url);
                }

                $result = [
                    "http_code" => $this->http_code,
                    "method" => "POST",
                    "url" => $this->base_url.$url, 
                    "request" => $data, 
                    "response" => $result,
                    "curl_error" => $this->err
                    ]; 
            } catch (Exception $e) {
                throw new Exception(__FILE__ .":".__LINE__ ." ".$e->getMessage());
            }
            return $result;
        }

        /**
         * Summary. Re-Envia el pin al pinID indicado
         * @param int $pinID Indica el ID del pin
         * @return Bool-Json $result Devuelve un json indicando el id del pin enviado o un bool false en caso de error
         */
        public function ReEnviarPin($pinID){
            $result = false;
            try {
                if (empty($pinID)) {
                    throw new Exception("pin o pinID vacio:");
                }
                $url = "/2fa/1/pin/".$pinID."/resend";
                if($this->modo_test){
                    $result = json_decode(json_encode(["pinId" => $this->default_pin]));
                }else{
                    $result = $this->Enviar("POST", null, $url);
                }
                $result = [
                    "http_code" => $this->http_code,
                    "method" => "POST",
                    "url" => $this->base_url.$url, 
                    "request" => '{"resend_pinID":"'.$pinID.'"}',
                    "response" => $result,
                    "curl_error" => $this->err
                    ]; 
            } catch (Exception $e) {
                throw new Exception(__FILE__.":".__LINE__." ".$e->getMessage());
            }
            return $result;
        }

        public function EnviarMensaje($nroTel, $text = "", $from = "Tenela")
        {
            $result = false;
            try {
                if (empty($nroTel)) {
                    throw new Exception(__LINE__." Número de telefono vacio");
                }
                
                if (empty($text)) {
                    throw new Exception(__LINE__." No hay texto que enviar");
                }
                $url = "/sms/1/text/query?";

                $data = [
                    "username" => $this->username,
                    "password" => $this->password,
                    "from" => $from,
                    "to" => $nroTel,
                    "text" => $text
                ];

                $result = $this->Enviar("GET", $data, $url);
                
                $result = [
                    "http_code" => $this->http_code,
                    "method" => "GET",
                    "url" => $this->base_url.$url, 
                    "request" => $data, 
                    "response" => $result,
                    "curl_error" => $this->err
                    ];
            } catch (Exception $e) {
                throw new Exception(__FILE__.":".__LINE__." ".$e->getMessage());
            }
            return $result;
        }
    }

?>