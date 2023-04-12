<?php
/*
	Controla el proceso de onBoarding.
	Created: 2021-09-02
	Author: DriverOp
*/

require_once(DIR_model."class.jwtbase.inc.php");
require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");
require_once(DIR_model."solicitudes".DS."class.solictemp.inc.php");

defined("SES_onboarding") || define("SES_onboarding", "onboarding");
defined("ONBOARDING_TOKEN_SECRET_KEY") || define("ONBOARDING_TOKEN_SECRET_KEY", DEVELOPE_NAME);
defined("ONBOARDING_TOKEN_SESSION_TIME") || define("ONBOARDING_TOKEN_SESSION_TIME", 300);

const onBoardingViews = DIR_site."onboarding".DS;

class cOnBoarding extends cWsV2Client {

	private $sesname = SES_onboarding;
	private $jwt = null;
	public $secretKey = ONBOARDING_TOKEN_SECRET_KEY;
	public $sessionTime = ONBOARDING_TOKEN_SESSION_TIME;
	public $expireTime = 0;
	public $plan = null;
	public $solicitud = null;
	public $alias = null;
	public $descripcion = null;
	public $steps = null;
	public $solic_id = null;
	public $solicTemp_id = null;
	public $solictemp = null;
	public $crearSolicitud = true;
	public $currentStep = null;

	public function __construct() {
		$this->SetLogEx(__METHOD__, func_get_args());
		parent::__construct();
		
		
		$this->jwt = new cJWT_base($this->secretKey);
		
		$this->solictemp = new cSolicTemp();
		

		if (!isset($_SESSION[$this->sesname])) {
			$_SESSION[$this->sesname] = array();
		}

		if (!empty($_SESSION[$this->sesname]['alias'])) {
			$this->alias = $_SESSION[$this->sesname]['alias'];
		}
		$this->setSolicTemp($_SESSION[$this->sesname]['solicTemp_id']??null);
		$this->solic_id = $_SESSION[$this->sesname]['solic_id']??null;

		$this->plan = $_SESSION[$this->sesname]['cotizacion']->plan??null;
		$this->solicitud = $_SESSION[$this->sesname]['solicitud']??null;
		if (empty($_SESSION[$this->sesname]['transId'])) {
			$_SESSION[$this->sesname]['transId'] = $this->GetTransId();
		} else {
			$this->transId = $_SESSION[$this->sesname]['transId'];
		}
		$this->AddHTTPHeader('Transaction-ID', $this->transId);
		$this->currentStep = new stdClass;
		$this->LoadScript();
	}

	public function GenToken($payload, array $data = null) {
		$this->SetLogEx(__METHOD__, func_get_args());
		$this->expireTime = time()+$this->sessionTime;
		$message = [];
		$message['expire'] = $this->expireTime;
		$message['alias'] = $payload;
		if (empty($this->solicTemp_id) or !is_numeric($this->solicTemp_id)) {
			$this->solicTemp_id = $this->CreateSolic($message['alias']);
			$_SESSION[$this->sesname]['solicTemp_id'] = $this->solicTemp_id;
		}
		$message['solic_id'] = $this->solic_id;
		$message['solictemp_id'] = $this->solicTemp_id;
		if (!empty($data)) {
			$message['data'] = $data;
		}
		return $this->jwt->GenerateToken($message);
	}

/**
* Summary. Verificar que un token es válido, no ha expirado y contiene un mensaje.
* @param string $theToken El token en cuestión
* @return bool
*/
	public function ValidToken(string $theToken):bool {
		$this->SetLogEx(__METHOD__, func_get_args());
		if ($mensaje = $this->jwt->GetMessage($theToken)) {
			if (($mensaje->expire - $mensaje->iat) <= $this->sessionTime) {
				$this->setStep($mensaje->alias, $this->steps->{$mensaje->alias});
				$this->setSolicTemp($mensaje->solictemp_id??null);
				$this->solic_id = $mensaje->solic_id??null;
				$_SESSION[$this->sesname]['solic_id'] = $this->solic_id;
				return true;
			} else {
				cLogging::Write(__FILE__ ." ".__LINE__ ." El token expiró.");
			}
		}
		return false;
	}
/**
* Summary. Decodificar un token.
*/
	public function DecodeToken(string $theToken) {
		$this->SetLogEx(__METHOD__, func_get_args());
		return $this->jwt->GetMessage($theToken);
	}
/**
* Summary. Vuelve a foja cero toda la sesión del onboarding.
*/
	public function Clear() {
		$this->SetLogEx(__METHOD__, func_get_args());
		$_SESSION[$this->sesname] = null;
		$this->setSolicTemp(null);
		$this->solic_id = null;
		$this->plan = null;
		$this->transId = null;
		$this->alias = null;
		$this->descripcion = null;
		$this->solicitud = null;
		$this->LoadScript();
	}
/**
* Summary. Encuentra y devuelve el primer paso según el script.
* @return object/null
*/
	public function FirstStep():?object {
		$this->SetLogEx(__METHOD__, func_get_args());
		$result = null;
		try {
			if (is_null($this->steps)) { throw new Exception('Pasos no cargados.'); }
			foreach($this->steps as $key => $value) {
				$result = $value;
				if (!is_null($value->next) and isset($this->steps->{$value->next})) {
					if (empty($value->prev)) {
						$this->setStep($key, $result);
						break;
					}
				}
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Pasa al siguiente paso del actual paso, o al paso siguiente del paso apuntado por el parámetro. Mueve el punterio 'alias' a ese paso. Si no hay siguiente, devuelve null.
* @param string $alias default null El alias del cual se quiere buscar su siguiente.
* @return object/null
*/
	public function NextStep(string $alias = null):?object {
		$this->SetLogEx(__METHOD__, func_get_args());
		$result = null;
		try {
			if (is_null($this->steps)) { throw new Exception('Pasos no cargados.'); }
			if (empty($alias)) { $alias = $this->alias; }
			if (empty($alias)) { return $this->FirstStep(); }
			if (!isset($this->steps->{$alias})) { throw new Exception("No existe un paso con alias '".$alias."'"); }
			$next = $this->steps->{$alias}->next??null;
			if (!is_null($next)) {
				if (isset($this->steps->{$next})) {
					$result = $this->steps->{$next};
					$this->setStep($next, $result);
					$this->descripcion = $result->descripcion??null;
				}
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Pasa al paso anterior del paso actual, o al paso anterior del paso apuntado por el parámetro. Mueve el punterio 'alias' a ese paso. Si no hay anterior, devuelve null.
* @param string $alias default null El alias del cual se quiere buscar su previo.
* @return object/null
*/
	public function PrevStep(string $alias = null):?object {
		$this->SetLogEx(__METHOD__, func_get_args());
		$result = null;
		try {
			if (is_null($this->steps)) { throw new Exception('Pasos no cargados.'); }
			if (empty($alias)) { $alias = $this->alias; }
			if (empty($alias)) { return $this->FirstStep(); }
			if (!isset($this->steps->{$alias})) { throw new Exception("No existe un paso con alias '".$alias."'"); }
			$prev = $this->steps->{$alias}->prev??null;
			if (is_null($prev)) { return $this->FirstStep(); }
			if (isset($this->steps->{$prev})) {
				$result = $this->steps->{$prev};
				$this->setStep($prev, $result);
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Va al paso apuntado por el alias o relee el paso actual si no se indica el paso a donde ir.
* @param string $alias default null El alias al cual se debe ir. Si no lo encuentra, devuelve null.
* @return object/null
*/
	public function GoStep(string $alias = null):?object {
		$this->SetLogEx(__METHOD__, func_get_args());
		$result = null;
		try {
			if (is_null($this->steps)) { throw new Exception('Pasos no cargados.'); }
			if (empty($alias)) { $alias = $this->alias; }
			if (isset($this->steps->{$alias})) {
				$result = $this->steps->{$alias};
				$this->setStep($alias, $result);
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Va al paso indicado como fallo en el paso actual. Si no se encuentra, solamente recarga el paso actual.
* @param string $alias default null El alias del paso del cual extraer el fallo.
* @return object/null
*/
	public function FailStep(string $alias = null):?object {
		$this->SetLogEx(__METHOD__, func_get_args());
		$result = null;
		try {
			if (is_null($this->steps)) { throw new Exception('Pasos no cargados.'); }
			if (empty($alias)) { $alias = $this->alias; }
			if (isset($this->steps->{$alias})) {
				if (isset($this->steps->{$alias}->fail)) {
					$result = $this->steps->{$alias}->fail;
					$this->setStep($this->steps->{$alias}->fail, $result);
				} else {
					$result = $this->steps->{$alias};
					$this->setStep($alias, $result);
					
				}
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Establecer las propiedades del paso actual como propias.
* @param string $alias El alias del paso actual (es el índice de la lista de pasos)
* @param object $step El objeto que represetna el paso.
*/
	public function setStep(string $alias, object $step = null) {
		$this->SetLogEx(__METHOD__, func_get_args());
		$this->currentStep = new stdClass;
		$this->alias = $alias??null;
		$this->descripcion = null;
		$this->vista = null;
		$this->next = null;
		$this->prev = null;
		$this->fail = null;
		$this->crearSolicitud = true;
		if ($step and is_object($step)) {
			$this->currentStep = $step;
			$this->currentStep->alias = $alias;
			$this->descripcion = $step->descripcion??null;
			$this->vista = $step->vista??null;
			$this->next = $step->next??null;
			$this->prev = $step->prev??null;
			$this->fail = $step->fail??null;
			$this->crearSolicitud = $step->solicitud??true;
		}
		$_SESSION[$this->sesname]['alias'] = $alias;
	}

/**
* Summary. Establecer o crear la sesión temporal.
* @return int El ID de la nueva solicitud.
*/
	public function setSolicTemp(string $solicTemp_id = null):?int {
		$this->SetLogEx(__METHOD__, func_get_args());
		if (!empty($solicTemp_id) and is_numeric($solicTemp_id)) {
			$this->solictemp->id = $solicTemp_id;
			$this->solictemp->GetSolic();
			if (in_array($this->solictemp->estado, estados_sesion_temporal_end )) {
				$this->solictemp->data = null;
				$this->solicTemp_id = $this->CreateSolic();
			} else {
				$this->solicTemp_id = $this->solictemp->id;
			}
		} else {
			$this->solictemp->data = null;
			$this->solicTemp_id = $this->CreateSolic();
		}
		$_SESSION[$this->sesname]['solicTemp_id'] = $this->solicTemp_id;
		
		return $this->solicTemp_id;
	}
/**
* Summary. Establecer el id de solicitud del core.
* @param int $id.
*/
	public function setSolic_id(int $id = null) {
		$this->SetLogEx(__METHOD__, func_get_args());
		$this->solic_id = $id;
		$_SESSION[$this->sesname]['solic_id'] = $this->solic_id;
		$this->SetSolic();
	}
/**
* Summary. Crear una solicitud temporal nueva.
* @param string $alias El alias con el cual crear la solicitud
* @return int El ID de la nueva solicitud.
*/
	public function CreateSolic(string $alias = null):?int {
		$this->SetLogEx(__METHOD__, func_get_args());
		if (empty($alias)) {
			$alias = $this->alias??null;
		}
		$this->solictemp->alias_paso = $alias;
		if (!empty($alias)) {
			$this->solictemp->Create();
		}
		return $this->solictemp->id??null;
	}
/**
* Summary. Agregar datos a los datos de la sesión temporal.
*/
	public function SetData($data) {
		$this->SetLogEx(__METHOD__, func_get_args());
		if (is_object($data)) {
			$data = (array)$data;
		}
		$this->solictemp->solic_id = $this->solic_id??null;
		$data = array_merge((array)$this->solictemp->data, $data);
		$this->solictemp->data = json_decode(json_encode($data, JSON_FORCE_OBJECT + JSON_BIGINT_AS_STRING + JSON_PRESERVE_ZERO_FRACTION + JSON_UNESCAPED_UNICODE), false, 512, JSON_BIGINT_AS_STRING );
		$this->SetSolic();
	}
/**
* Summary. Actualizar la sesión temporal.
*/
	public function SetSolic() {
		$this->SetLogEx(__METHOD__, func_get_args());
		$this->solictemp->alias_paso = $this->alias;
		$this->solictemp->solic_id = $this->solic_id??null;
		$this->solictemp->SetSolic();
	}
/**
* Summary. Termina la sesión con fallo (si existe)
*/
	public function EndFail() {
		$this->SetEstado('ENDFAIL');
	}
/**
* Summary. Termina la sesión con fallo (si existe)
*/
	public function EndOk() {
		$this->SetEstado('ENDOK');
	}
/**
* Summary. Cambiar estado a la sesión temporal.
* @param string $estado El estado al cual cambiar la sesión temporal.
*/
	private function SetEstado(string $estado = 'INIT') {
		if (!empty($this->solicTemp_id)) {
			$this->solictemp->id = $this->solicTemp_id;
			$this->solictemp->SetEstado($estado);
		}
	}
/**
* Summary. Carga, interpreta e integra el JSON con los pasos a seguir.
*/
	private function LoadScript() {
		$this->SetLogEx(__METHOD__, func_get_args());
		$this->steps = new stdClass();
		try {
			$script = onBoardingViews."onboarding.json";
			if (!ExisteArchivo(onBoardingViews."onboarding.json")) { throw new Exception('Script no encontrado: '.$script); }
			$this->steps = json_decode(file_get_contents($script));
			if (json_last_error() !== JSON_ERROR_NONE) { throw new Exception($script.' contiene un json no válido: '.JSON_ERROR_MSG_ESP[json_last_error()]); }
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
	}
/**
* Summary. Genera un número de transacción para enviársela al core.
*/
	private function GetTransId() {
		$this->SetLogEx(__METHOD__, func_get_args());
		$aux = GetIp().DEVELOPE_NAME.parse_url(BASE_URL,PHP_URL_HOST).time().session_id();
		$this->transId =hash('sha256',$aux);
		return $this->transId;
	}


	public function SetLogEx($method, $args) {
		cLogging::SetPostfix("onboarding");
		cLogging::Write($method. ": ".print_r($args, true));
	}
}