<?php
/*
	Clase para armar y enviar el mail de aceptación del préstamo previo al otorgamiento.
*/

defined("DIR_templates_email") || define("DIR_templates_email",DIR_plantillas."emails".DS);

require_once(__DIR__.DS."class.sendmail_base.inc.php");

class cSendAcceptance extends cSendMail {
	
	public $nombrePlantilla = "acceptance.htm";
	
	function __construct($sysParams = null, $ExternalLogFunction = null) {
		parent::__construct($sysParams, $ExternalLogFunction);
	}
	
	public function Send(array $tags = null):bool {
		$result = false;
		try {
			if (!$this->SetTemplate(DIR_templates_email.$this->nombrePlantilla)) {
				throw new Exception("No se cargó la plantilla '$this->nombrePlantilla'");
			}
			
			$this->AddTags($tags??[]);
			if (empty($this->GetTags()['codigo_aceptacion'])) { throw new Exception('No se indicó código de aceptación.'); }
			if (!CanUseArray($this->getToAddresses())) { throw new Exception('No se indicó ningún receptor de correo.'); }
			if (empty($this->Subject)) {
				$this->Subject = "Aceptación de préstamo ".DEVELOPE_NAME;
			}
			$this->UseTemplate();
			$result = parent::Send();
		} catch(Exception $e) {
			call_user_func($this->LogFunction, $e->GetMessage(), LGEV_ERROR);
		}
		return $result;
	}
	
}