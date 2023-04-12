<?php
/*
	Clase básica para el envío de mail.
	No uses esta clase directamente, derivá una clase nueva específica.
	
	Created: 2021-10-19
	Author: DriverOp
*/

require_once(__DIR__.DS."class.phpmailer.php");
require_once(__DIR__.DS."class.smtp.php");
require_once(__DIR__.DS."class.makemessages.inc.php");


class cSendMail extends PHPMailer {
	
	private $templateFile = null;
	private $templateTags = [];
	
	public $LogFunction = null;
	public $log_file_name = 'sendmail';
	public $test_mode = false;
	public $test_mode_bcc = false;

	function __construct($sysParams = null, $ExternalLogFunction = null) {
		parent::__construct();
		$this->LogFunction = function($mensaje, $nivel) {
			$mensaje = trim($nivel." ".$mensaje);
			cLogging::LogToFile(__FILE__ ." ".$mensaje, $this->log_file_name);
		};
		
		
		$this->CharSet = "utf-8";
		$this->IsSMTP(); // Se envia por SMTP
		$this->IsHTML();
		$this->ClearAddresses();
		$this->ClearReplyTos();
		$this->ClearAttachments();
		$this->FromName = DEVELOPE_NAME;
		$this->AuthType = 'PLAIN';
		$this->WordWrap = 70;
		$this->Subject = '';
		$this->SMTPDebug = DEVELOPE;
		// Establecer el nivel de debug según el ambiente en el que se ejecuta.
		switch(DEPLOY) {
			case 'uat': $this->SMTPDebug = 2; break; // DEBUG_SERVER;
			case 'prod': $this->SMTPDebug = 1; break; // DEBUG_CLIENT;
			default: $this->SMTPDebug = 4; // DEBUG_LOWLEVEL;
		}

		if (!is_null($sysParams) and is_object($sysParams) and is_a($sysParams, "cSysParams")) {
			$this->Host = $sysParams->Get('smtp_host','localhost');
			$this->Port = $sysParams->Get('smtp_port',22);
			$this->SMTPSecure = $sysParams->Get('smtp_secure',false);

			$this->SMTPAuth = $sysParams->Get('smtp_auth',false);
			$this->Username = $sysParams->Get('smtp_username',null);
			$this->Password = $sysParams->Get('smtp_password',null);

			$this->From = $sysParams->Get('smtp_sender_address','nobody@localhost');
			if ($sysParams->Get('email_modo_test', false)) {
				$testing_recipient = $sysParams->Get('test_email_recipient','nobody@localhost');
				$this->test_mode = true;
				if ($sysParams->Get('email_bcc_test', false)) {
					$this->test_mode_bcc = true;
					$this->AddBCC($testing_recipient);
				} else {
					parent::addAddress($testing_recipient);
				}
			}
			
		}
		if ($this->SMTPDebug) {
			if (is_null($ExternalLogFunction)) {
				$this->Debugoutput = $this->LogFunction;
			} else {
				$this->Debugoutput = $ExternalLogFunction;
			}
		}
	} // __construct
/**
* Summary. Realiza acciones pre y post envío del mensaje de correo.
* @return bool.
*/
	public function Send() {
		cLogging::Write(__FILE__ ." ".__LINE__ ." Enviando correo a: ".print_r($this->getAllRecipientAddresses(),true));
		if (parent::Send()) {
			cLogging::Write(__FILE__ ." ".__LINE__ ." Correo Enviado.");
			return true;
		} else {
			cLogging::Write(__FILE__ ." ".__LINE__ ." No se envió el correo!");
			return false;
		}
	}
/**
* Summary. Antes de agregar una dirección de correo, verifica que el modo no sea test ya que en caso de serlo, se deben hacer cambios.
*/
	public function addAddress($address, $name = '') {
		if (!$this->test_mode) {
			// SI NO está en modo test, business as usual...
			return parent::addAddress($address, $name);
		}
		if ($this->test_mode_bcc) {
			// SI el modo del test es con copia oculta, entonces sí agregar la dirección.
			return parent::addAddress($address, $name);
		}
		// Si está en modo test no agregar la dirección, en su lugar el mensaje de enviará al receptor de testing.
		return true;
	}
/**
* Summary. Establece el lugar donde está el archivo de plantilla.
* @param string $value El path completo al archivo.
*/
	public function SetTemplate(string $value):bool {
		if (!ExisteArchivo($value)) { return false; }
		$this->templateFile = $value;
		return true;
	}

/**
* Summary. Limpia el path al archivo de plantilla
* @return true
*/
	public function ClearTemplate():bool {
		$this->templateFile = null;
		return true;
	}

/**
* Summary. Devuelve el path al archivo de la plantilla actual.
* @return string/null
*/
	public function GetTemplate():?string {
		return $this->templateFile;
	}
	
/**
* Summary. Establece el array con los tags y los valores a reemplazar en esos tags que luego serán usados en la plantilla.
* @param string $value El path completo al archivo.
*/
	public function SetTags(array $value):bool {
		$this->templateTags = $this->templateTags+$value;
		return true;
	}

/**
* Summary. Limpia los tags
* @return true
*/
	public function ClearTags(array $value):bool {
		$this->templateTags = [];
		return true;
	}

/**
* Summary. Devuelve el array de tags actualmente en uso.
* @return array
*/
	public function GetTags():array {
		return $this->templateTags;
	}
/**
* Summary. Agrega uno o más tags a los ya existentes.
* @param array $tags.
*/
	public function AddTags(array $tags):bool {
		$this->templateTags = array_merge($this->templateTags, $tags);
		return true;
	}

/**
* Summary. Limpia todos los receptores de correos.
*/	
	public function ClearRecipients() {
		$this->ClearAddresses();
		$this->ClearReplyTos();
		$this->ClearAttachments();
	}

/**
* Summary. Arma el mensaje del correo cargando la plantilla y reemplazando los tags por sus valores.
* @return object/null
*/
	public function UseTemplate($filename = null, $tags = null):?object {
		if (empty($filename)) {
			$filename = $this->templateFile;
		}
		if (empty($tags)) {
			$tags = $this->templateTags;
		}
		$composer = new cMakeMessage;
		if (!$composer->LoadTemplate($filename)) { throw new Exception("No se pudo cargar plantilla '$filename'"); }
		$composer->SetValues($tags);
		$this->Body = $composer->GetContent();
		$this->AltBody = strip_tags($this->Body);
		return $this;
	} // UseTemplate
}