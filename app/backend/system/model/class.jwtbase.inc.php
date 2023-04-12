<?php
/**
	Implementación del protocolo JWT (JSON Web Token)
	Created: 2020-09-30
	Author: DriverOp
*/
/**
*	Implementación del protocolo JWT (JSON Web Token) como se indica en el RFC 7519 (https://tools.ietf.org/html/rfc7519)
* @author DriverOp (driverop@gmail.com)
*/

class cJWT_base {
	
	public $algo = 'HS256';
	public $DebugLevel = 1;
	public $msgerr = '';
	public $message = null;
	public $secretKey = '';
	public $verified = false;

/**
* Summary. Constructor de la clase.
* @param str La clave secreta a usar para firmar el mensaje.
*/
	public function __construct($secretKey = '') {
		$this->secretKey = $secretKey;
	}

/**
* Summary. Genera el token para ser enviado al cliente.
* @param array $payload Un array con los datos a codificar en el token. El campo 'iat' se agrega automáticamente.
* @return bool/str Regresa el token listo para ser enviado o false en caso de algún error.
*/
	public function GenerateToken(array $payload) {
		$the_token = false;
		try {
			$header = '{"alg":"%s","typ":"JWT"}';
			$header = sprintf($header,(empty($algo))?'HS256':$algo);
			$header = $this->base64Encode($header);
			
			
			if (is_array($payload)) {
				if (count($payload) > 0) {
					$payload['iat'] = time();
				} else {
					throw new Exception(__LINE__." I'm affraid you pass me an empty array, can't continue.");
				}
			}
			$payload = json_encode($payload, JSON_UNESCAPED_UNICODE+JSON_FORCE_OBJECT);
			$payload = $this->base64Encode($payload);
			
			
			$sign = hash_hmac('sha256', $header.".".$payload, $this->secretKey, true);
			$sign = $this->base64Encode($sign);
			//$sign = base64_encode($sign);
			
			$the_token = $header.".".$payload.".".$sign;
		} catch(Exception $e) {
			$this->msgerr = $e->getMessage();
			if ($this->DebugLevel > 0) {
				echo $this->msgerr;
			}
		}
		return $the_token;
	}

/**
* Summary. Verifica la corrección del token usando la firma en él. Establece las propiedades ->verified y ->message.
* @param str $token. El token conformando el estandar JWT.
* @return bool True si la firma es correcta, false en caso contrario.
*/
	public function VerifyToken($token) {
		$result = false;
		$this->verified = false;
		try {
			if (preg_match("/^.+\..+\..+$/im", $token)) {
				list($header, $payload, $sign) = explode('.',$token);
				$message = $header.".".$payload;
				$sign = $this->base64Decode($sign);
				$hash = hash_hmac('sha256',$message,$this->secretKey,true);
				$result = hash_equals($hash, $sign);
				$this->message = $this->base64Decode($payload);
				$this->verified = true;
			}
		} catch(Exception $e) {
			$this->msgerr = $e->GetMessage();
			if ($this->DebugLevel > 0) {
				echo $this->msgerr;
			}
		}
		return $result;
	}

/**
* Summary. Decodifica y devuelve el mensaje contenido en el token. Si el token no ha sido verificado, lo verifica antes de decodificar el mensaje.
* @param str $token El token conformando el estandar JWT.
* @param str $type default 'object'. El tipo de dato al que se quiere transformar el mensaje.
* @return bool/object/str. Dependiendo de si el mensaje pudo ser decodificado o no, regresa el mensaje con el tipo indicado.
*/
	public function GetMessage($token, $type = 'object') {
		$result = false;
		try {
			if (!$this->verified) {
				if (!$this->VerifyToken($token)) {
					return false;
				}
			}
			if ($type == 'object') {
				$result = json_decode($this->message);
				$this->message = $result;
			} else {
				$result = $this->message;
			}
		} catch(Exception $e) {
			$this->msgerr = $e->getMessage();
			if ($this->DebugLevel > 0) {
				echo $this->msgerr;
			}
		}
		return $result;
	}

/**
* Summary. Codifica una cadena a Base64 reemplazando los caracteres que no son seguros en una URL, tal como se pide en el estandar JWT.
*/
	private function base64Encode($string) {
		return str_replace(['+','/','='], ['-','_',''], base64_encode($string));
	}
	
	private function base64Decode($string) {
		return base64_decode(str_replace(['-','_'], ['+','/'], $string));
	}
}


?>