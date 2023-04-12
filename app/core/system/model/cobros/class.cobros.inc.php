<?php
/*
	Clase ejemplo práctico de cómo derivar una clase desde cModels.
	Created: 2021-08-16
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
require_once(DIR_model."prestamos".DS."class.prestamos_hist.inc.php");
require_once(DIR_model."cuotas".DS."class.cuotas.inc.php");
require_once(DIR_model."cuotas".DS."class.cuotas_hist.inc.php");


class cCobros extends cModels {
	
	const tabla_cobros = TBL_cobros;
	public $qMainTable = TBL_cobros;
    public $usuario_id = null;
    public $simulacion = false;
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_cobros;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
		$this->calculos = new cCalculo;
	}
	
	public function Get($id = null):?object {
		if (is_null($id)) { return null; }

        $this->sql = "SELECT * FROM ".$this->qMainTable;
        if(CanUseArray($id)){
            $this->sql .= " AND `id` IN (".implode(",",$id).") ";
        }else{
            $this->sql .= " WHERE `id` = ".$id;
        }
		return parent::Get();
	}
	
	public function GetListByPrestamo($prestamo_id):?array {
		$result = null;
		if(!SecureInt($prestamo_id)){ throw new Exception("No se indico el prestamo por el cual se van a buscar las cuotas"); }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `prestamo_id` = ".$prestamo_id;
		if($fila = $this->FirstQuery()){
			do {
				$result[] = $fila;
			} while ($fila = $this->Next());
		}
		return $result;
	}

    public function GetCobrado() {
		$result = null;
		$this->sql = "SELECT SUM(`monto`) as 'monto_cobrado' , `tipo_moneda` FROM ".$this->qMainTable;
		if($fila = $this->FirstQuery()){
            $result = number_format($fila->monto_cobrado,2,".","");
            if(isset($fila->money_converted) && !empty($fila->money_converted) && $fila->money_converted != $fila->tipo_moneda){
                $result = number_format($fila->converted_monto_cobrado,2,".","");
            }
            // $result = $fila->monto_cobrado;
		}
		return $result;
	}



    public function GetListByCuota($cuota_id):?array {
		$result = null;
		if(!SecureInt($cuota_id)){ throw new Exception("No se indico el prestamo por el cual se van a buscar las cuotas"); }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `cuota_id` = ".$cuota_id;
		if($fila = $this->FirstQuery()){
			do {
				$result[] = $fila;
			} while ($fila = $this->Next());
		}
		return $result;
	}


	public function Create($data){
		$result = false;
		try {
			if(!CanUseArray($data)){ throw new Exception(" No hay datos para crear el cobro "); }
            $campos = $this->GetColumnsNames($this->mainTable);
            foreach ($data as $key => $value) {
                if (isset($campos[$key])) {
                    $key = $this->RealEscape($key);
                    if(CanUsearray($value)){
                        $value = $this->RealEscapeArray($value);
                    }else{
                        $value = $this->RealEscape($value);
                    }
                    if ($key != 'id') { // Just a precaution.
                        $reg[$key] = $value;
                    }
                }
            }
            $reg['estado'] = "PEND";
            $reg['sys_fecha_alta'] = cFechas::Ahora();
            $reg['sys_fecha_modif'] = cFechas::Ahora();
            $reg['sys_usuario_id'] = $this->usuario_id;
			if(!$result = $this->NewRecord($reg)){ 
                throw new Exception("No se pudo crear el cobro indicado");
            }
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

    /**
	 * Summary. Confirma un cobro apuntado por $id, se controla que pertenezca al mismo negocio
	 * @param int $id el id del cobro a confirmar
	 * @return bool-array $result bool false en caso de fallo
    */

    public function ConfirmarCobro() {
        $result = false;
		try {
			if (!$this->existe){ throw new Exception("No hay registro activo, se debe usar ->Get antes de este método.");	}
			$reg = array();
            $this->estado = 'ACRE';
            $this->sys_fecha_modif = cFechas::Ahora();
            $this->sys_usuario_id = $this->usuario_id;
            $this->Set();
			$result = true;
		} catch (Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
    }

    
	/**
	 * Summary. Confirma los cobros listados en $data
	 * @param array $data un array con la lista de cobros a confirmar y el id del negocio al que pertenecen
	 * @return bool-array $result bool false en caso de fallo
    */
	public function RechazarCobro($id,$rechazo = null){
		$result = false;
		try {
			if (!$this->existe){
				if(SecureInt($id)){
					$this->Get($id);
				}
			}
			if(!$this->existe){ throw new Exception("No hay registro activo, se debe usar ->Get antes de este método."); }
			$result = $this->Update($this->tabla_cobros, [
				'estado'=>'RECH',
				'sys_fecha_modif'=>cFechas::Ahora(),
				'sys_usuario_id'=>$this->usuario_id,
				'motivo_rechazo' => $rechazo['motivo']??null,
				'fecha_rechazo' => $rechazo['fecha']??null
				], "`id` = ".$this->id);
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;

	}



    /**
* Summary. Dado un id de cobro y un id de préstamo, realiza todas las tareas, usando las clases necesarias, para acreditar un pago total o parcial sobre ese préstamo.
* @note: Esto es solo para préstamos simples. Los mensajes de error se ponen en la propiedad ->msgerr. Debe de haber un cobro activo leído con ->Get() que es el cobro que se quiere acreditar.
* @return bool False en caso de problemas.
*/

	public function AcreditarCobro() {
		$result = false;
		$a_facturar = 0;
        $nuevosMontos = array();
		try {
			$prestamo = new cPrestamos();
			$prestamoHist = new cPrestamosHist();
			// $movimientos = new cCtblMovimiento;
			if (!$this->existe) { throw new Exception('No hay cobro activo. Se debe llamar a ->Get() antes que a este método.'); }
			if ($this->estado != 'PEND') { throw new Exception('El cobro actual no está pendiente de aprobación.'); }
			if (!$prestamo->Get($this->prestamo_id)) {	throw new Exception('Préstamo '.$this->prestamo_id.' no encontrado'); }
			
			if($prestamo->estado == "CANC" or $prestamo->estado == "REFIN" or $prestamo->estado == "ANUL"){throw new Exception('No se puede hacer un cobro sobre un prestamo que esta en un estado invalido para operaciones de cobro.');}
			// - ¿A cuánto asciende la deuda?
            
            $cobro_id = $this->id;

            if($prestamo->tipo_prestamo == "CUOTAS"){
                $cuotas = new cCuotas();
			    $cuotasHist = new cCuotasHist();
                $sobrantes = 0;
                $restantes = 0;
                if (!$cuotas->Get($this->cuota_id)) {	throw new Exception('Cuota '.$this->cuota_id.' no encontrada'); }
                if (!$cuotasHist->GetByCuota($this->cuota_id)) {	throw new Exception('Cuota '.$this->cuota_id.' no encontrada'); }
                if ($cuotas->estado == "CANC" or $cuotas->estado == "REFIN" or $cuotas->estado == "ANUL"){throw new Exception('No se puede hacer un cobro sobre un prestamo que esta en un estado invalido para operaciones de cobro.');}
                
                
                $total_adeudado = $cuotasHist->monto_cuota + $cuotas->monto_mora + $cuotas->total_iva_mora;
                
                $monto = $this->monto;


                $monto_orig = $monto;

                
                $seguir = true; // Bandera para seguir calculando.
                if ($seguir) {
                    $nuevosMontos['total_iva_mora'] = RNM($cuotas->total_iva_mora, $monto, $seguir);
                }

                if ($seguir) {
                    $nuevosMontos['monto_mora'] = RNM($cuotas->monto_mora, $monto, $seguir);
                }
                
                if ($seguir) {
                    $nuevosMontos['iva_interes_cuota'] = RNM($cuotasHist->iva_interes_cuota, $monto, $seguir);
                }
                
                if ($seguir) {
                    $nuevosMontos['interes_cuota'] = RNM($cuotasHist->interes_cuota, $monto, $seguir);
                }
                
                
                // Si $monto es negativo significa que no alcanzó para cubrir todos los sobrecostos. El monto a facturar es el original:
                    if ($monto < 0) {
                    $a_facturar = $monto_orig;
                } else {
                    // La diferencia entre el monto original y lo que quedó después de pagar los sobrecostos, es el monto de la factura que se debe emitir.
                    $a_facturar = $monto_orig - $monto;
                }
                
                
                if ($seguir) {
                    $monto = $monto - $cuotasHist->capital;
                    
                    $nuevosMontos['capital'] = $monto;
                    if($nuevosMontos['capital'] < 0){
                        $nuevosMontos['capital'] = ($nuevosMontos['capital']*-1);
                    }

                    if($nuevosMontos['capital'] <= 1 && $nuevosMontos['capital'] > 0){
                        $nuevosMontos['capital'] = 0;
                    }

                    

                    if ($monto >= 0) {
                        $nuevosMontos['monto_cuota'] = 0; // Esto significa que el préstamo está cancelado!.
                        // Pero si en el monto queda algo... el cliente acreditó de más... ¿qué se hace con ese resto?.
                        if ($monto > 0) {
                            $nuevosMontos['sobrante'] = $monto;
                            $nuevosMontos['observaciones'] = 'El cliente pagó de más quedando un remanente de '.number_format($monto,2,',','.');
                        }
                        else { $nuevosMontos['resto'] = 0; }
                    } else {
                        if($monto <= 1){
                            $nuevosMontos['monto_cuota'] = $monto*-1;
                            $nuevosMontos['restante'] = $monto*-1;
                        }else{
                            $nuevosMontos['monto_cuota'] = 0;
                            $nuevosMontos['restante'] = 0;
                        }
                    }
                }
                if (!$this->simulacion) {
                    if (!$this->ConfirmarCobro()) { throw new Exception('No se pudo actualizar el estado del cobro.'); }
                }

                // Armar el registro para el histórico del préstamo.
                $nuevosMontos['monto_cobro'] = $monto_orig;
                $nuevosMontos['cobro_id'] = $cobro_id;
                $nuevosMontos['cuota_id'] = $this->cuota_id;
                $nuevosMontos['operacion'] = "Pago";
                $nuevosMontos['usuario_id'] = 1;
                $nuevosMontos['total_a_facturar'] = $a_facturar;
                $nuevosMontos['total_pagado'] = $monto_orig;
                $nuevosMontos['fecha_cobro'] = cFechas::Ahora();
                // Registrar el histórico del préstamo. También determina si el préstamo ha sido efectivamente cancelado (pagado).
                if (!$this->simulacion) {
                    if ($cuotas->estado == 'HOLD') { $cuotas->RevertirEstado(); }// Esto es para sacar al préstamo de 'HOLD'.
                    $nuevosMontos['estado'] = $cuotas->estado;
                    $nuevosMontos['entidad'] = "COBRO";
                    if (!$cuotasHist->AddHistorial($nuevosMontos)) { throw new Exception('No se pudo actualizar histórico de la cuota  '.$this->cuota_id); }
                }
                
                // Devuelvo los resultados
                $result = $nuevosMontos;

            }else{
                $prestMontos = $prestamoHist->SetPrestamoAndGet($prestamo);
			    
                if (!$prestMontos) { throw new Exception('No se pudo establecer histórico del préstamo '.$this->prestamo_id); }

                $total_adeudado = $prestMontos->total_imponible;
                
                 // Me guardo el ID por las dudas
                $monto = $this->monto; // Copio el monto desde la propiedad para no afectar la integridad de esa propiedad en la instancia del objeto.
    
    
                // - Ahora al monto del cobro se le tiene que ir restándo los montos de los totales del préstamo comenzando con la mora. El orden importa y por lo tanto esto no se puede "automatizar". 
                
                
                $monto_orig = $monto; // Hagamos una copia por las dudas.
                $seguir = true; // Bandera para seguir calculando.
                
                // Mora.
                if ($seguir) {
                    $nuevosMontos['iva_mora'] = RNM($prestMontos->iva_mora, $monto, $seguir);
                }
    
                // IVA mora.
                if ($seguir) {
                    $nuevosMontos['total_mora'] = RNM($prestMontos->total_mora, $monto, $seguir);
                }
    
                // Cargo administrativo.
                if ($seguir) {
                    $nuevosMontos['iva_cargos_administrativos'] = RNM($prestMontos->iva_cargos_administrativos, $monto, $seguir);
                }
                if ($seguir) {
                    $nuevosMontos['cargos_administrativos'] = RNM($prestMontos->cargos_administrativos, $monto, $seguir);
                }
    
                // Cargo cobranza.
                if ($seguir) {
                    $nuevosMontos['iva_cargos_cobranza'] = RNM($prestMontos->iva_cargos_cobranza, $monto, $seguir);
                }
                if ($seguir) {
                    $nuevosMontos['cargos_cobranza'] = RNM($prestMontos->cargos_cobranza, $monto, $seguir);
                }
    
                // Interés.
                if ($seguir) {
                    $nuevosMontos['iva_interes_capital'] = RNM($prestMontos->iva_interes_capital, $monto, $seguir);
                }
                if ($seguir) {
                    $nuevosMontos['interes_capital'] = RNM($prestMontos->interes_capital, $monto, $seguir);
                }
                // Impuestos varios
                if ($seguir) {
                    $nuevosMontos['total_impuestos_1'] = RNM($prestMontos->total_impuestos_1, $monto, $seguir);
                }
                if ($seguir) {
                    $nuevosMontos['total_impuestos_2'] = RNM($prestMontos->total_impuestos_2, $monto, $seguir);
                }
    
                // - A facturar.
                // Si $monto es negativo significa que no alcanzó para cubrir todos los sobrecostos. El monto a facturar es el original:
                if ($monto < 0) {
                    $a_facturar = $monto_orig;
                } else {
                    // La diferencia entre el monto original y lo que quedó después de pagar los sobrecostos, es el monto de la factura que se debe emitir.
                    $a_facturar = $monto_orig - $monto;
                }
    
                // - Si queda algo del monto del cobro ($seguir es true), entonces cancelar capital.
                // El capital no es el original, sino el total imponible que quedó de los cobros anteriores. Si no hay cobros, total_imponible es = a capital.
                if ($seguir) {
                    $monto = $monto - $prestMontos->capital;
                    if ($monto >= 0) {
                        $nuevosMontos['total_imponible'] = 0; // Esto significa que el préstamo está cancelado!.
                        // Pero si en el monto queda algo... el cliente acreditó de más... ¿qué se hace con ese resto?.
                        if ($monto > 0) {
                            $nuevosMontos['observaciones'] = 'El cliente pagó de más quedando un remanente de '.number_format($monto,2,',','.');
                        }
                        else { $nuevosMontos['resto'] = 0; }
                    } else {
                        $nuevosMontos['total_imponible'] = $monto*-1;
                    }
                }
                // - Aprobar el cobro.
                if (!$this->simulacion) {
                    if (!$this->ConfirmarCobro()) { throw new Exception('No se pudo actualizar el estado del cobro.'); }
                }
                
                // Armar el registro para el histórico del préstamo.
                $nuevosMontos['monto_cobro'] = $monto_orig;
                $nuevosMontos['cobro_id'] = $cobro_id;
                $nuevosMontos['usuario_id'] = 1;
                $nuevosMontos['total_a_facturar'] = $a_facturar;
                $nuevosMontos['total_pagado'] = $monto_orig+$prestMontos->total_pagado;
                // Registrar el histórico del préstamo. También determina si el préstamo ha sido efectivamente cancelado (pagado).
                if (!$this->simulacion) {
                    if ($prestamo->estado == 'HOLD') { $prestamo->RevertirEstado(); }// Esto es para sacar al préstamo de 'HOLD'.
                    $nuevosMontos['estado'] = $prestamo->estado;
                    $nuevosMontos['entidad'] = "COBRO";
                    if (!$prestamoHist->AddHistorial($nuevosMontos,$prestMontos->id)) { throw new Exception('No se pudo actualizar histórico del préstamo '.$this->prestamo_id); }
                }
                
                // Devuelvo los resultados
                $result = $nuevosMontos;
                
                
                // Asentar el cobro en la contabilidad.
                // $movimientos->SetPrestamo($prestamo);
                // if(!$movimientos->AsentarCobro($this)){
                //     cLogging::Write(__FILE__." ".__LINE__." No se pudo asentar el cobro en la contabilidad para el cobro ID ".$this->id);
                // }
                $prestamo = null; // Just in case.

            }
		} catch(Exception $e) {
			$this->SetError($e);
		} finally {
			$prestamo = null;
			$prestamoHist = null;
		}
		return $result;
	}
}

/**
* Summary. Restar No Menos. Dado un total y un monto, modifica el monto restándole el total. Y si el resultado es igual o menor a cero, pone en falso la bandera de seguir.
* @param float $sust. El total a restar del monto.
* @param float $monto ref. El monto a ser restado.
* @param bool $seguir ref. Bandera que indica si el monto está en cero o menos.
* @return float El resultado de la resta.
* @note Se multiplica el monto por -1 para que sea siempre positivo.
*/
function RNM($sust, &$monto, &$seguir) {
	$result = 0;
	$monto = ($monto-$sust);
	$result = ($monto < 0)?($monto*-1):0;
	$seguir = $result == 0;
	return $result;
}