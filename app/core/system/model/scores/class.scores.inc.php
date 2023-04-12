<?php
/**
	Clase para el manejo de los scores crediticios de las personas.
	Created: 2021-10-14
	Author: DriverOp
*/
require_once(DIR_model."class.fundation.inc.php");

class cScores extends cModels {
	
	const tabla_scored = TBL_scores;

	public $qMainTable = '';

	function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_scored;
		$this->qMainTable = SQLQuote(self::tabla_scored);
		$this->ResetInstance();
	}
	
	/**
	 * Summary. Obtiene un registro dado su ID
	 * @param int $id El ID del registro
	*/
	public function Get(int $id = null):?object {
		$result = null;
		try {
			if (is_null(SecureInt($id))) { throw new Exception("No se indicó ID."); }
			$this->sql = "SELECT `scores`.* FROM ".$this->qMainTable." AS `scores` WHERE `scores`.`id` = ".$id;
			$result = parent::Get();
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
* Summary. Devuelve el score actual (más reciente) de la persona que se pasa como parámetro.
* @param int $persona_id El ID de la persona buscada.
* @return int/null El score crediticio o null en caso que no se encuentra.
*/
	public function GetScore(int $persona_id = null) {
		$this->sql = "SELECT `id`, `score` FROM ".$this->qMainTable." WHERE `persona_id` = '".$persona_id."' ORDER BY `fecha_request` DESC, `sys_fecha_modif` DESC, `sys_fecha_alta` DESC LIMIT 1;";
		return $this->GetThisScore();
	}

/**
* Summary. Devuelve el score actual (más reciente) de la persona que se pasa como parámetro discriminado por buró.
* @param int $persona_id El ID de la persona buscada.
* @return int/null El score crediticio o null en caso que no se encuentra.
*/
	public function GetScoreByBuro(int $persona_id = null, int $buro_id = null) {
		$this->sql = "SELECT `id`, `score` FROM ".$this->qMainTable." WHERE `persona_id` = '".$persona_id."' AND `buro_id` ".(is_null($buro_id)?"IS NULL":"= '".$buro_id."'")."
		ORDER BY `fecha_request` DESC, `sys_fecha_modif` DESC, `sys_fecha_alta` DESC LIMIT 1;";
		return $this->GetThisScore();
	}
/**
* Summary. Ejecuta la SQL para obtener el score y actualizar la instancia con el registro encontrado
*/
	private function GetThisScore() {
		if ($result = $this->FirstQuery($this->sql)) {
			$this->Get($result->id);
			return $result->score;
		} else {
			return null;
		}
	}
/**
* Summary. Agregar un nuevo score a una persona.
* @param array $data, los datos. Principalmente 'score' y 'data'.
* @return null/object con el registro recién creado.
*/
	public function Create($data) {
		if (empty($data['persona_id'])) {
			$data['persona_id'] = $this->persona_id??null;
		}
		if (empty($data['persona_id'])) { throw new Exception('No se indió la persona.'); }
		if (!isset($data['fecha_request'])) {
			$data['fecha_request'] = cFechas::Hoy();
		}
		$data['sys_fecha_alta'] = cFechas::Ahora();
		$data['sys_fecha_modif'] = $data['sys_fecha_alta'];
		$data['sys_usuario_id'] = 1;
		unset($data['id']);
		if ($this->Insert($this->mainTable, $data)) {
			return $this->Get($this->last_id);
		}
		return null;
	}
} // class