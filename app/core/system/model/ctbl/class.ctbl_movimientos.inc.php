<?php
/**
 * Asentamiento de movimientos contables
 * Created: 2021-02-08
 * Author: DriverOp
 * 
 * Modif: 2021-08-20
 * Author: Gastón Fernandez
 * Desc:
 *      Agrego asentamiento de mora e IVA mora
 */

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_model."class.prestamos.inc.php");
require_once(DIR_model."class.cobros.inc.php");
require_once(DIR_model."class.planes.inc.php");
require_once(DIR_model."ctbl".DS."class.ctbl_param_asientos.inc.php");

const CTBL_CAMPOS_ASENTAMIENTO_PRESTAMO = array('total','desembolso','total_intereses','total_iva_interes');
const CTBL_TIPO_MOVIMIENTO_PRESTAMO_ID = 4; // Este es el id del registro en la tabla TBL_ctbl_tipos_movimientos que señala el alta de préstamos.
const CTBL_TIPO_MOVIMIENTO_ASENTAR_COBRO = 16; // Idem asentar cobro ('cobranzas')
const CTBL_TIPO_MOVIMIENTO_ASENTAR_RECALCULO_MORA = 14; // Idem asentar recalculo de la mora ('cobranzas')
const CTBL_TIPO_MOVIMIENTO_ASENTAR_RECALCULO_IVA_MORA = 15; // Idem asentar recalculo del iva de la mora ('cobranzas')

class cCtblMovimiento extends cParamAsientos {
	
	private $tabla_asientos = TBL_ctbl_asientos;
	private $tabla_cuentas = TBL_ctbl_cuentas;
	private $tabla_subcuentas = TBL_ctbl_subcuentas;
	private $tabla_lineas = TBL_ctbl_linea_contable;
	private $tabla_params = TBL_ctbl_param_asientos;
	private $tabla_tipos_importes = TBL_ctbl_tipos_importe;
	private $tabla_tipos_movimientos = TBL_ctbl_tipos_movimientos;
	
	public $lista_cuentas = array();
	public $trace = false;
	
	public $prestamo = null;
	public $linea_contable = null;
	public $esta_fecha_hora = null;

	function __construct() {
		parent::__construct();
		$this->tabla_principal = $this->tabla_asientos;
		$this->linea_contable = new stdClass();
		$this->linea_contable->existe = false;
	}


	public function AsentarPrestamo($prestamo = null) {
		$result = false;
		$lista_id_tipos_importe = array();
		if (is_null($prestamo)) { $this->Trace(__METHOD__ .' prestamo está vacío.', LGEV_WARN); return false; }
		try {
			if (!is_a($prestamo, "cPrestamo")) { throw new Exception("Parámetro no es un préstamo."); }
			$this->prestamo = $prestamo;
			if (!$this->GetPlan()) { $this->Trace(__METHOD__ .' GetPlan() devolvió false.', LGEV_WARN); return false; }
			if (!$this->GetLineaContable()) { $this->Trace(__METHOD__ .' GetLineaContable() devolvió false.', LGEV_WARN); return false; }

			$sql = "SELECT `id` FROM ".SQLQuote($this->tabla_tipos_importes)." WHERE LOWER(`campo`) IN ('".implode("','",CTBL_CAMPOS_ASENTAMIENTO_PRESTAMO)."')";
			//$this->Trace(__METHOD__ ." SQL: ".$sql);
			$this->Query($sql);
			if ($fila = $this->First()) {
				do {
					$lista_id_tipos_importe[] = $fila['id'];
				} while($fila = $this->Next());
			} else {
				 $this->Trace(__METHOD__ .' No se encontró ninguno de los tipos de movimientos', LGEV_WARN); 
			}
			
			$sql ="SELECT `params`.`linea_contable_id`, `params`.`tipo_mov_id`,`params`.`tipo_importe_id`,`params`.`cuenta_id`,`params`.`subcuenta_id`,`movs_importes`.`campo`,`params`.`tipo_asiento`
				FROM ".SQLQuote($this->tabla_params)." AS `params`,(
					SELECT `movs`.`id` AS `movimiento_id`, `importes`.`id` AS `importe_id`, `importes`.`campo`
					FROM ".SQLQuote($this->tabla_tipos_movimientos)." AS `movs`, ".SQLQuote($this->tabla_tipos_importes)." AS `importes`
					WHERE `movs`.`id` = ".CTBL_TIPO_MOVIMIENTO_PRESTAMO_ID."
					AND `importes`.`id` IN (".implode(",",$lista_id_tipos_importe).")
				) AS `movs_importes` WHERE `params`.`linea_contable_id` = ".$this->linea_credito_id." AND `params`.`tipo_mov_id` = `movs_importes`.`movimiento_id` AND `params`.`tipo_importe_id` = `movs_importes`.`importe_id`";

			$this->Query($sql);
			if ($fila = $this->First()) {
				do {
					$p = trim($fila['campo']);
					if (property_exists($this->prestamo, $p)) {
						$this->SetMovimiento($fila, $this->prestamo->$p);
					}
				} while($fila = $this->Next());
				$result = true;
			} else {
				$this->Trace(__METHOD__ ." Esta consulta: ".$sql.PHP_EOL."Falló miserablemente...", LGEV_WARN); 
			}

		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
	
	/**
	*	Deja asentado en la contabilidad un cobro realizado a un préstamo
	*	@param object $cobro Instancia a la clase cobros
	*/
	public function AsentarCobro(cCobros $cobro) {
		$result = false;
		try {
			if(!is_object($cobro)){ throw new Exception("Se esperaba recibir un objeto"); }
			$monto = SecureFloat($cobro->monto ?? null);
			$estado = $cobro->estado ?? null;
			
			if(is_null($monto)){ throw new Exception("El monto del cobro no es un número válido"); }
			//if($estado != 'ACRE'){ throw new Exception("El cobro debe haber sido confirmado con anterioridad"); }
			
			//No se pudo obtener el asiento para reflejar los cambios al momento de realizar el cobro
            if(!$data = $this->Get(CTBL_TIPO_MOVIMIENTO_ASENTAR_COBRO)){ throw new Exception(" No se pudo obtener el parametro de asientos para reflejar el cobro en la contabilidad"); }
			$data['campo'] = "Cobro detectado";
			$result = $this->SetMovimiento($data,$monto);
			
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Este es el señor que pone el registro de asiento donde debe... es la panza de la bestia.
*/
	private function SetMovimiento($theReg, $theMonto) {
		$result = false;
		try {
			$reg = array(
				"cuenta_id" => $theReg['cuenta_id'] ?? null,
				"subcuenta_id" => $theReg['subcuenta_id'] ?? null,
				//"fechahora" => cFechas::Ahora(),
				"fechahora" => (!empty($this->esta_fecha_hora))?$this->esta_fecha_hora:cFechas::Ahora(),
				"linea_contable_id" => $theReg["linea_contable_id"] ?? null,
				"leyenda" => titleCase(str_replace("_"," ",$theReg['campo']))." del préstamo ".$this->prestamo->id,
				"importe" => $theMonto,
				"tipo_moneda" => $this->prestamo->tipo_moneda,
                "tipo_asiento" => $theReg['tipo_asiento'] ?? "DEBE",
				"prestamo_id" => $this->prestamo->id,
				"fechahora_alta" => (!empty($this->esta_fecha_hora))?$this->esta_fecha_hora:cFechas::Ahora(),
				"fechahora_modif" => (!empty($this->esta_fecha_hora))?$this->esta_fecha_hora:cFechas::Ahora(),
			);
			$reg = $this->RealEscapeArray($reg);
			$result = $this->Insert($this->tabla_asientos, $reg);
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

    public function SetPrestamo(cPrestamo $prestamo){
        $result = false;
		try {
			if(!is_object($prestamo)){ throw new Exception("Se esperaba un objeto con la clase préstamos"); }
            $this->prestamo = $prestamo;
            $result = true;
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
    }

/**
* Summary. Devuelve la línea contable del plan del préstamo. Estableciendo la propiedad $linea_credito_id
*/
	private function GetPlan() {
		$result = false;
		try {
			$plan = new cPlan();
			if ($plan->Get($this->prestamo->plan_id)) {
				$result = $plan->linea_credito_id;
				$this->linea_credito_id = $result;
			} else {
				throw new Exception(__LINE__." Plan ".$this->prestamo->plan_id." no tiene una línea contable asignada.");
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Devuelve la línea contable.
*/
	private function GetLineaContable() {
		$result = false;
		$this->linea_contable->existe = false;
		try {
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_lineas)." WHERE `id` = ".$this->linea_credito_id." ";
			if ($fila = $this->FirstQuery($sql)) {
				$this->linea_contable = json_decode(json_encode($fila));
				$this->linea_contable->existe = true;
				$result = true;
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Asentar movimiento BIND.
*/
	public function AsentarTransferenciaBind($bind) {
		
	}
/**
* Summary. Logea para debug.
*/
	private function Trace($linea, $lgev_level = LGEV_DEBUG) {
		if ($this->trace)
		cLogging::Write(__FILE__." ".$linea, $lgev_level);
	}

    /**
     * Summary. Deja asentado el recalculo de la mora por un pago fecha-valor
     * @param float $monto El monto a reflejar
     * @param string $campo La leyenda que se mostrara a modo de descrpción del asiento
     */
    public function AsentarRecalculoMora($monto,$campo = ""){
        $result = false;
        try {
            if(!SecureFloat($monto) AND $monto != 0){ throw new Exception("El monto debe ser un número"); }

            //No se pudo obtener el asiento para reflejar los cambios en la mora
            if(!$data = $this->Get(CTBL_TIPO_MOVIMIENTO_ASENTAR_RECALCULO_MORA)){
                throw new Exception(" No se pudo obtener el parametro de asientos para reflejar el cambio en la mora");
            }

            $data['campo'] = $campo;
            
            $result = $this->SetMovimiento($data,$monto);
        } catch(Exception $e) {
			$this->SetErrorEx($e);
		}
        return $result;
    }

    /**
     * Summary. Deja asentado el recalculo de la mora por un pago fecha-valor
     * @param float $monto El monto a reflejar
     * @param string $campo La leyenda que se mostrara a modo de descrpción del asiento
     */
    public function AsentarRecalculoIVAMora($monto,$campo = ""){
        $result = false;
        try {
            if(!SecureFloat($monto) AND $monto != 0){ throw new Exception("El monto debe ser un número"); }

            //No se pudo obtener el asiento para reflejar los cambios en la mora
            if(!$data = $this->Get(CTBL_TIPO_MOVIMIENTO_ASENTAR_RECALCULO_IVA_MORA)){
                throw new Exception(" No se pudo obtener el parametro de asientos para reflejar el cambio en la mora");
            }

            $data['campo'] = $campo;
            
            $result = $this->SetMovimiento($data,$monto);
        } catch(Exception $e) {
			$this->SetErrorEx($e);
		}
        return $result;
    }
} // class


/*
		$result = false;
		try {
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;


*/
?>