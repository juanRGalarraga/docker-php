<?php
/*
	Clase para el manejo de fechas.
	Author: DriverOp
	Created: A long time ago
	Modif: Agregado, nuevo método para traducir fechas ISO a String.
	Modif: 2019-02-28
	Desc:
		- Agregado método InterleaveSep. Agregadas constantes CDATE_ISISO y CDATE_ISLATIN.
		- Modificada la RegEx en LooksLikeDate para que los separadores sean opcionales.
	Modif: 2019-03-01
		- Agregado método SplitDate.
	
	Métodos:
	- SQLDate2Str
	- RestarFechas
	
	MOdif: 2019-11-31
		- Agregados constantes para indicar el tipo de salida deseada.
		- Modificado SQLDate2Str() para tomar en cuenta estas constantes.
*/
define("CDATE_LONG",1);
define("CDATE_SHORT",2);
define("CDATE_NOLEADINGZERO",4);
define("CDATE_NOWEEKDAY",8);
define("CDATE_NOYEAR",16);
define("CDATE_IGNORETIME",32);
define("CDATE_IGNORE_TIME",32);
define("CDATE_NOSEC",64);
define("CDATE_NOLEADINGZEROINTIME",128);
define("CDATE_MONTHYEAR",256);

define("CDATE_ISISO",1);
define("CDATE_ISLATIN",2);

define("CDATE_ISOOUT",1);
define("CDATE_LATINOUT",2);
define("CDATE_ANGLOOUT",3);
/*
define("CDATE_TODAY",'');
define("CDATE_NOW",'');
*/


class cFechas{

	public static $strmeses_es = Array(1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre");
	public static $strmeses_en = Array(1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December");
	public static $diassem_es = Array(1=>"Lunes",2=>"Martes",3=>"Miércoles",4=>"Jueves",5=>"Viernes",6=>"Sábado",7=>"Domingo");
	public static $diassem_en = Array(1=>"Monday",2=>"Tuesday",3=>"Wednesday",4=>"Thursday",5=>"Friday",6=>"Saturday",7=>"Sunday");

	static function Hoy($formato = CDATE_ISOOUT) {
		$date = 'Y-m-d';
		if (defined('CDATE_TODAY') and !empty(CDATE_TODAY)) {
			return CDATE_TODAY;
		} else {
			switch ($formato) {
				case CDATE_ISOOUT: $date = 'Y-m-d'; break;
				case CDATE_LATINOUT: $date = 'd/m/Y'; break;
				case CDATE_ANGLOOUT: $date = 'm/d/Y'; break;
			}
		}
		return Date($date);
	}
	static function Ahora($formato = CDATE_ISOOUT) {
		$date = 'Y-m-d H:i:s';
		if (defined('CDATE_NOW') and !empty(CDATE_NOW)) {
			return CDATE_NOW;
		} else {
			switch ($formato) {
				case CDATE_ISOOUT: $date = 'Y-m-d H:i:s'; break;
				case CDATE_LATINOUT: $date = 'd/m/Y H:i:s'; break;
				case CDATE_ANGLOOUT: $date = 'm/d/Y H:i:s'; break;
			}
		}
		return Date($date);
	}
/**
* Summary. Devuelve la fecha y la hora con precisión de milisegundos.
* @param string $formato CDATE_ISOOUT fecha ISO, CDATE_LATINOUT latino, CDATE_ANGLOOUT anglosajón.
* @return string.
*/	
	static function AhoraMicro($formato = CDATE_ISOOUT) {
		$date = 'Y-m-d H:i:s.';
		switch ($formato) {
			case CDATE_ISOOUT: $date = 'Y-m-d H:i:s.'; break;
			case CDATE_LATINOUT: $date = 'd/m/Y H:i:s.'; break;
			case CDATE_ANGLOOUT: $date = 'm/d/Y H:i:s.'; break;
		}
		return date($date.sprintf('%06d', (microtime(true)-floor(microtime(true)))*1000000));
	}
/**
* Summary. Dada una fecha ISO YYYY-MM-DD, devuelve su literal en formato $formato.
* @param string $entrada La fecha en formato YYYY-DD-MM HH:II:SS (ISO)
* @param int $opciones Las opciones para formatear la salida.
* @param int $formato El formato de la fecha de salida.
* @return string
*/

	static function SQLDate2Str($entrada, $opciones = CDATE_LONG, $formato = CDATE_LATINOUT) {
		if (empty($entrada)) { return ''; }
		$salida = NULL;
		$work = array();
		
		$strmeses = ($formato == CDATE_LATINOUT)?self::$strmeses_es:self::$strmeses_en;
		$diassem = ($formato == CDATE_LATINOUT)?self::$diassem_es:self::$diassem_en;
		$prep1 = ($formato == CDATE_LATINOUT)?' de ':' of ';
		$prep2 = ($formato == CDATE_LATINOUT)?' a las ':' at ';
		$nunca = ($formato == CDATE_LATINOUT)?'nunca':'never';
		$fecha_invalida = ($formato == CDATE_LATINOUT)?'la fecha no es válida':'invalid date format';
		
		$aux = explode(' ',$entrada);
		if (!empty($aux) and (is_array($aux)) and (count($aux) > 0)) {
			foreach ($aux as $key => $value) {
				if (!empty($value)) {
					$work[] = trim($value);
				}
			}
			if (!empty($work[0])) {
				if ($work[0] == '0000-00-00') {
					$salida = '<i>'.$nunca.'</i>';
				} else {
					if (self::LooksLikeISODate($work[0])) {
						$fecha = self::SQLDateToArrDate($work[0]);
						if (self::IsAValidDate($fecha)) {
							foreach ($fecha as $key => $value) {
								$fecha[$key] = $value*1;
							}
							if (!($opciones & CDATE_NOLEADINGZERO)) {
								foreach ($fecha as $key => $value) {
									$fecha[$key] = sprintf('%02d',$value);
								}
							}
							if ($opciones & CDATE_SHORT) {
								if ($opciones & CDATE_MONTHYEAR) {
									$salida = sprintf("%s/%s", $fecha['mes'], $fecha['ano']);
								} else {
									if (!($opciones & CDATE_NOYEAR)) {
										if ($formato == CDATE_ISLATIN) {
											$salida = sprintf("%s/%s/%s", $fecha['dia'], $fecha['mes'], $fecha['ano']);
										} else {
											$salida = sprintf("%s/%s/%s", $fecha['mes'], $fecha['dia'], $fecha['ano']);
										}
									} else {
										$salida = sprintf("%s/%s", $fecha['dia'], $fecha['mes']);
									}
								}
							} else {
								if (!($opciones & CDATE_NOWEEKDAY)) {
									$salida .= $diassem[(int) Date("N",mktime(0,0,0,(int) $fecha['mes'],(int) $fecha['dia'],(int) $fecha['ano']))].", ";
								}
								if ($formato == CDATE_ISLATIN) {
									if (!($opciones & CDATE_MONTHYEAR)) {
										$salida .= $fecha['dia'].$prep1;
									}
									$salida .= $strmeses[(int)$fecha['mes']];
									if (!($opciones & CDATE_NOYEAR)) {
										$salida .= $prep1.$fecha['ano'];
									}
								} else {
									$salida .= $strmeses[(int)$fecha['mes']].' ';
									if (!($opciones & CDATE_MONTHYEAR)) {
										$salida .= $fecha['dia'];
									}
									if (!($opciones & CDATE_NOYEAR)) {
										$salida .= $prep1.$fecha['ano'];
									}
								}
							}
						} else {
							if (DEVELOPE) {
								$salida = '<i>'.$fecha_invalida.'</i>';
							}
						}
					} else {
						if (DEVELOPE) {
							$salida = '<i>'.$fecha_invalida.'</i>';
						}
					}
				}
			}
			if (!empty($work[1]) and (!($opciones & CDATE_IGNORETIME))) {
				if (self::LooksLikeSQLTime($work[1])) {
					$tiempo = self::SQLTimeToArrTime($work[1]);
					if (!empty($tiempo)) {
						if (!($opciones & CDATE_SHORT)) {
							$salida .= $prep2;
						} else {
							$salida .= " ";
						}
						if (!($opciones & CDATE_NOLEADINGZEROINTIME)) {
							foreach ($tiempo as $key => $value) {
								$tiempo[$key] = sprintf('%02d',$value);
							}
						}
						if (isset($tiempo['hora'])) {
							$salida .= $tiempo['hora'];
						}
						if (isset($tiempo['min'])) {
							if (isset($tiempo['hora'])) {
								$salida .= ':';
							}
							$salida .= $tiempo['min'];
						}
						if (isset($tiempo['seg']) and (!($opciones & CDATE_NOSEC))) {
							if (isset($tiempo['min'])) {
								$salida .= ':';
							}
							$salida .= $tiempo['seg'];
						}
					}
				}
			}
		}
		if (empty($salida) and DEVELOPE) {
			$salida = NULL; //'<i>(sin fecha)</i>';
		}
		return $salida;
	}
	
	static function SQLTimeStampToStr($entrada, $opciones = CDATE_LONG) {
		$entrada = Date('Y-m-d H:i:s', $entrada);
		return self::SQLDate2Str($entrada, $opciones);
	}

/*
	Resta una fecha a otra, el formato de las fechas debe ser "YYYY-MM-DD" ó "YYYY/MM/DD"
*/
	static function RestarFechas($fechamayor, $fechamenor) {
		if (strpos($fechamayor,"/") !== false) {
			$max = explode("/",$fechamayor); }
		else {
			$max = explode("-",$fechamayor);
		}
		if (strpos($fechamenor,"/") !== false) {
			$min = explode("/",$fechamenor); }
		else {
			$min = explode("-",$fechamenor);
		}
		
		return gregoriantojd($max[1],$max[2],$max[0]) - gregoriantojd($min[1],$min[2],$min[0]); 
	} // function RestarFechas


/*
	Dada una fecha SQL (ISO) la devuelve formateada para input de tipo "date" o "datetime".
	$inctime: incluir la hora
*/
	static function SQLDateTimeToValue($datetime, $inctime = false) {
		$time = "";
		$work = explode(" ",$datetime);
		$date = $work[0];
		if ($inctime) {
			if (isset($work[1])) {
				$time = "T".trim($work[1])."Z";
			}
		}
		return $date.$time;
	} // function SQLDateTimeToValue

/*
	Extrae la parte 'date' de una fecha ISO.
*/
	static function ExtractSQLDate($fecha) {
		$work = explode(" ",$fecha);
		return @$work[0];
	}

/*
	Extrae la parte 'time' de una fecha ISO.
*/
	static function ExtractSQLTime($fecha) {
		$work = explode(" ",$fecha);
		return @$work[1];
	}
/*
	Dada una fecha y hora SQL (ISO) devuelve la fecha y la hora por separado en un array tal como 
	['fecha'] => YYYY-MM-DD, ['hora'] => HH:MM:SS
*/
	static function SplitDateTime($fechahora) {
		$result = array('fecha'=>'0000-00-00','hora'=>'0:0:0');
		if (strpos($fechahora," ") !== false) {
			$aux = explode(" ",$fechahora);
			if (isset($aux[0])) {
				$result['fecha'] = $aux[0];
			}
			if (isset($aux[1])) {
				$result['hora'] = $aux[1];
			}
		}
		return $result;
	} // function SplitDateTime
/*
	Dada una fecha y hora SQL (ISO) devuelve un array con cada dato por separado.
*/
	static function ExtractSQLDateTime($datetime) {
		$work = explode(' ',$datetime);
		$date = $work[0];
		$time = $work[1];
		return array_merge(self::SQLDateToArrDate($date), self::SQLTimeToArrTime($time));
		
	}
/*
	Determina si el año es biciesto
*/
	static function isLeapYear($year) { 
	    # Check for valid parameters # 
	    if ($year < 0){ 
	        printf('A&ntilde;o debe ser un entero positivo.'); 
	        return false; 
	    } 
	    # In the Gregorian calendar there is a leap year every year divisible by four 
	    # except for years which are both divisible by 100 and not divisible by 400. 
	    if ($year % 4 != 0) { 
	        return false; 
	    }else{ 
	        if ($year % 100 != 0){ 
	            return true;    # Leap year 
	        }else{ 
	            if ($year % 400 != 0){ 
	                return false; 
	            }else{ 
	                return true;    # Leap year 
	            } 
	        } 
	    } 
	} // function isLeapYear

/*
	Fecha debe ser un array tal como array('dia'=>numero,'mes'=>numero,'ano'=>numero); 
*/
	static function IsAValidDate($fecha) {
		$result = true;
		if (!CheckInt($fecha['dia'])) { $result = false; }
		if (!CheckInt($fecha['mes'])) { $result = false; }
		if (!CheckInt($fecha['ano'])) { $result = false; }
		if ($result) {
			$result = checkdate($fecha['mes'],$fecha['dia'],$fecha['ano']); // php function
		}
		return $result;
	} // function IsAValidDate

/*
	Devuelve una cadena con una fecha compatible con SQL (una fecha en formato ISO).
	Fecha debe ser un array tal como array('dia'=>numero,'mes'=>numero,'ano'=>numero); 
*/
	static function ArrDateToSQLDate($fecha) {
		return sprintf("%04d-%02d-%02d",$fecha['ano'],$fecha['mes'],$fecha['dia']);
	} // function ArrDateToSQLDate

/*
	La inversa de la anterior.
*/
	static function SQLDateToArrDate($fecha) {
		$result = NULL;
		if (strpos($fecha,"/") !== false) {
			$max = explode("/",$fecha); }
		else {
			$max = explode("-",$fecha);
		}
		if (count($max) == 3) {
			$result = array("dia"=>$max[2],"mes"=>$max[1],"ano"=>$max[0]);
		}
		return $result;
	} // function SQLDateToArrDate

/*
	Dada una hora en formato HH:AA:SS devuelve un array tal como:
	array('hora'=>número, 'min'=>número, 'seg'=>número);
*/
	static function SQLTimeToArrTime($hora) {
		$result = NULL;
		$work = explode(':',$hora);
		if (!empty($work) and is_array($work)) {
			$result = array();
			if (isset($work[0])) {
				$result['hora'] = (int)$work[0];
			}
			if (isset($work[1])) {
				$result['min'] = (int)$work[1];
			}
			if (isset($work[2])) {
				$result['seg'] = (int)$work[2];
			}
		}
		return $result;
	}
/*
	Devuelve la cantidad de días que hay en el mes del año indicado
*/
	static function DiasDelMes($month,$year){
		return cal_days_in_month(CAL_GREGORIAN, $month, $year);
	} // function DiasDelMes

/*
	Formatea una fecha latina en ISO (SQL). Entra como DD/MM/YYYY y sale como YYYY-MM-DD. Completa ceros faltantes.
	NO valida la fecha y NO valida el formato.
*/
	static function FechaToISO($fecha) {
		$result = null;
		$aux = str_replace('/','-',$fecha);
		$aux = explode('-',$aux);
		$result = sprintf('%04d-%02d-%02d',$aux[2],$aux[1],$aux[0]);
		return $result;
	}
/*
	La inversa de la anterior.
*/
	static function ISOToLatin($iso) {
		return self::ISOToFecha($iso);
	}
	static function ISOToFecha($iso) {
		$result = null;
		$aux = str_replace('/','-',$iso);
		$aux = explode('-',$aux);
		$result = sprintf('%02d/%02d/%04d',$aux[2],$aux[1],$aux[0]);
		return $result;
	}
/*
	Suma x días a una fecha
*/
	static function Sumar($fecha, $dias){
		$aux = strtotime('+'.$dias.' day', strtotime($fecha));
		return date('Y-m-d', $aux);
	} // function Sumar

/*
	Resta x días a una fecha
*/
	static function Restar($fecha, $dias){
		$aux = strtotime('-'.$dias.' day', strtotime($fecha));
		return date('Y-m-d', $aux);
	} // function Restar

/*
	Devuelve la cantidad de días entre 2 fechas
*/
	static function DiasEntreFechas($inicio, $fin, $suma = 0){
		return ((strtotime($fin) - strtotime($inicio)) / (60 * 60 * 24)+$suma);
	} // function DiasEntreFechas

/*
	Devuelve la diferencia entre dos fechas, se puede especificar el período a comparar (dias, meses, años).
*/
	static function DiferenciaEntreFechas($inicio, $fin, $tipo = 'dias'){
		$result = null;

		$finicio = new datetime($inicio);
		$ffin = new datetime($fin);
		
		$diferencia = $finicio->diff($ffin);
		
		switch ($tipo) {
			case 'dias':
				$result = $diferencia->days;
				break;
			
			case 'meses':
				$result = ($diferencia->y * 12) + $diferencia->m;
				break;

			case 'anos':
				$result = $diferencia->m/12;
				break;
		}
		return $result;
	} // function DiferenciaEntreFechas
/*
	Devuelve los segundos transcurridos entre una fecha hora y otra fecha hora.
*/
	static function SegundosEntreFechas($inicio, $fin) {
		$date1 = strtotime($inicio);
		$date2 = strtotime($fin);
		return $date2 - $date1;
	}

/*
	Dadas dos fechas o fechas horas, devuelve el literal del tiempo transcurrido entre las dos.
	$prec: incluir los segundos.
	$as_array: devolver un array en vez de un string.
*/
	static function TiempoTranscurrido($inicio, $fin, $prec = true, $as_array = false) {
		
		if ($fin < $inicio) {
			$aux = $fin;
			$fin = $inicio;
			$inicio = $aux;
		}
		
		$finicio = new datetime($inicio);
		$ffin = new datetime($fin);
			
		$diferencia = $finicio->diff($ffin);
		
		$salida = array();
		if ($diferencia->y > 0) {
			$aux = $diferencia->y." año";
			$aux .= ($diferencia->y > 1)?"s":"";
			$salida[] = $aux;
		}
		if ($diferencia->m > 0) {
			$aux = $diferencia->m." mes";
			$aux .= ($diferencia->m > 1)?"es":"";
			$salida[] = $aux;
		}

		if ($diferencia->d > 0) {
			$aux = $diferencia->d." dia";
			$aux .= ($diferencia->d > 1)?"s":"";
			$salida[] = $aux;
		}

		if ($diferencia->h > 0) {
			$aux = $diferencia->h." hora";
			$aux .= ($diferencia->h > 1)?"s":"";
			$salida[] = $aux;
		}

		if ($diferencia->i > 0) {
			$aux = $diferencia->i." min";
			$aux .= ($diferencia->i > 1)?"s":"";
			$aux .= ".";
			$salida[] = $aux;
		}
		if (($diferencia->s > 0) and $prec) {
			$aux = $diferencia->s." seg";
			$aux .= ($diferencia->s > 1)?"s":"";
			$aux .= ".";
			$salida[] = $aux;
		}
		if ($as_array) {
			return $salida;
		} else {
			return implode(", ",$salida);
		}
	}
/*
	Dado un período de fecha, lo corrige para que quede de lunes a domingo.
*/
	static function SetRangeWeek($desde, $hasta){
		$result['desde'] = $desde;
		$result['hasta'] = $hasta;
		$desde_dia = date('w', strtotime($desde));
		$hasta_dia = date('w', strtotime($hasta));
		if ($desde_dia > 0) { $result['desde'] = self::Restar($desde,$desde_dia); }
		if ($hasta_dia < 6) { $result['hasta'] = self::Sumar($hasta,(6-$hasta_dia)); }
		return $result;
	} // function SetRangeWeek

/*
	Dadas dos fechas, devuelve el primer dia del mes en el que comienza y el ultimo dia del mes en que termina.
*/
	static function SetRangeMonth($inicio, $fin){
		$result['desde'] = date('Y-m',strtotime($inicio)).'-01';
		$result['hasta'] = date('Y-m-d', strtotime('-1 day',strtotime(date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m',strtotime($fin)).'-01'))))));
		return $result;
	} // function SetRangeMonth

/*
	Dadas dos fechas, devuelve el primer y el ultimo dia de los años que abarca.
*/
	static function SetRangeYear($inicio, $fin){
		$result['desde'] = date('Y',strtotime($inicio)).'-01-01';
		$result['hasta'] = date('Y',strtotime($fin)).'-12-31';
		return $result;
	} // function SetRangeYear

/*
	Data una fecha en formato YYYY-MM-DD, devuelve la edad en años:
*/
	static function CalcularEdad($fechanac) {
		$fecha = explode('-',$fechanac);
		$hoy = explode('-',Date('Y-m-d'));
		
		$r = gregoriantojd($hoy[1],$hoy[2],$hoy[0]) - gregoriantojd($fecha[1],$fecha[2],$fecha[0]);
		return round($r/365);
	}


/*
	Verifica si una cadena de texto, se parece a una fecha ISO.
*/
	static function LooksLikeISODate($string) {
		return preg_match("/^[0-9]{4}(-|\/)?[0-1]?[0-9]?(-|\/)?[0-3]?[0-9]$/",$string);
	}

/*
	Verifica si una cadena de texto, se parece a una fecha Latina.
*/
	static function LooksLikeDate($string) {
		return preg_match("/^[0-3]?[0-9](-|\/)?[0-1]?[0-9](-|\/)?[0-9]{4}$/",$string);
	}
/*
	Verifica si la cadena se parece a una fecha cualquiera. Usa los dos métodos anteriores para determinar en qué formato está la fecha.
	Devuelve 'ISO', 'LATIN' o false.
*/
	static function LooksLikeADate($string) {
		$result = false;
		if (self::LooksLikeDate($string)) {
			$result = 'LATIN';
		} else {
			if (self::LooksLikeISODate($string)) {
				$result = 'ISO';
			}
		}
		return $result;
	}
/*
	Verifica que una fecha Latina es una fecha válida.
*/
	static function IsValidDate($fecha) {
		$fecha = self::FechaToISO($fecha);
		return self::IsValidISODate($fecha);
	}
/*
	Verifica que una fecha ISO es una fecha válida.
*/
	static function IsValidISODate($fechaISO) {
		$salida = Date('Y-m-d', strtotime($fechaISO));
		return ($fechaISO == $salida);
	}
/*
	Verifica que una cadena se parece a una hora ISO HH:AA:SS
*/
	static function LooksLikeISOTime($string) {
		return preg_match("/^(0|1|2)??\d:(0|1|2|3|4|5)??\d(:(0|1|2|3|4|5)??\d)?$/",$string);
	}
	static function LooksLikeSQLTime($string) {
		return self::LooksLikeISOTime($string);
	}
/**
* Summary. Verifica si una cadena se parece a una fecha y hora ISO con segundos opcionales YYYY-DD-MM HH:AA[:SS]
* @param str $string. La fecha y hora candidata.
* @return bool True en caso que se parezca, false en caso contrario
*/
	static function LooksLikeISODateTime($string)
	{
		return preg_match('/^[0-9]{4}(-|\/)?[0-1]?[0-9]?(-|\/)?[0-3]?[0-9]\s(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/', $string);
	}

/*
	Dada una fecha en el formato YYYYMMDD (o DDMMYYYY), devuelve la misma con los separadores correctos (YYYY-MM-DD).
*/
	static function InterleaveSep($string, $sep = '-', $input_format = CDATE_ISISO) {
		$result = null;
		$work = array();
		if ($input_format == CDATE_ISISO) {
			if (self::LooksLikeISODate($string)) {
				$work['ano'] = substr($string,0,4);
				$work['mes'] = substr($string,4,2);
				$work['dia'] = substr($string,6,2);
				$result = implode($sep,$work);
				if (empty($work['dia'])) {
					$result = $work['ano'].$sep.$work['mes'];
				}
			}
		}
		if ($input_format == CDATE_ISLATIN) {
			if (self::LooksLikeDate($string)) {
				if (strlen($string) == 6) {
					$work['dia'] = null;
					$work['mes'] = substr($string,0,2);
					$work['ano'] = substr($string,2,4);
				} else {
					$work['dia'] = substr($string,0,2);
					$work['mes'] = substr($string,2,2);
					$work['ano'] = substr($string,4,4);
				}
				$result = implode($sep,$work);
				if (empty($work['dia'])) {
					$result = $work['mes'].$sep.$work['ano'];
				}
			}
		}
		return $result;
	}
/*
	Dada una fecha en formato YYYYDDMM o YYYY-DD-MM (CDATE_ISISO) o DDMMYYYY o DD/MM/YYYY (CDATE_ISLATIN), devuelve un array tal como array('ano'=>YYYY, 'mes'=>MM, 'dia'=>DD)
*/
	static function SplitDate($string, $input_format = CDATE_ISISO) {
		$salida = array();
		$string = str_replace(array('-','/'),'',$string);
		if ($input_format == CDATE_ISISO) {
			if (strlen($string) == 8) {
				$salida['ano'] = substr($string,0,4);
				$salida['mes'] = substr($string,4,2);
				$salida['dia'] = substr($string,6,2);
			}
			if (strlen($string) == 6) {
				$salida['ano'] = substr($string,0,4);
				$salida['mes'] = substr($string,4,2);
			}
			if (strlen($string) == 4) {
				$salida['mes'] = substr($string,0,2);
				$salida['dia'] = substr($string,2,4);
			}
		}
		if ($input_format == CDATE_ISLATIN) {
			if (strlen($string) == 8) {
				$salida['ano'] = substr($string,4,4);
				$salida['mes'] = substr($string,2,2);
				$salida['dia'] = substr($string,0,2);
			}
			if (strlen($string) == 6) {
				$salida['ano'] = substr($string,2,4);
				$salida['mes'] = substr($string,0,2);
			}
			if (strlen($string) == 4) {
				$salida['mes'] = substr($string,2,4);
				$salida['dia'] = substr($string,0,2);
			}
		}
		return $salida;
	}
/*
	Devuelve un array con todas las fechas intermedias entre dos fechas ($inicio, $fin). Si alguna de las fechas dadas no es válida, devuelve null. Si las fechas están invertidas, devuelve el array invertido.
*/
	static function DatesRange($inicio, $fin, $in_format = CDATE_ISISO, $out_format = CDATE_ISISO) {
		$invertir = false;
		$salida = null;
		if ($in_format == CDATE_ISLATIN) {
			$inicio = self::FechaToISO($inicio);
			$fin = self::FechaToISO($fin);
		}
		if (self::IsValidISODate($inicio) === false) {
			return null;
		}
		if (self::IsValidISODate($fin) === false) {
			return null;
		}
		if ($inicio > $fin) {
			$aux = $inicio;
			$inicio = $fin;
			$fin = $aux;
			$invertir = true;
		}
		$arr_inicio = self::SQLDateToArrDate($inicio);
		foreach ($arr_inicio as $key => $value) {
			$arr_inicio[$key] = (int)$value;
		}
		$arr_fin = self::SQLDateToArrDate($fin);
		foreach ($arr_fin as $key => $value) {
			$arr_fin[$key] = (int)$value;
		}

		if (($arr_inicio['ano'] == $arr_fin['ano']) and ($arr_inicio['mes'] == $arr_fin['mes'])) { // El rango está en el mismo mes del mismo año?.
			if (self::DiasEntreFechas($inicio, $fin) > 0) {
				$salida = array();
				$primer_dia = $arr_inicio['dia'];
				$ultimo_dia = $arr_fin['dia'];
				for($i = $primer_dia; $i <= $ultimo_dia; $i++) {
					$arr_inicio['dia'] = $i;
					$salida[] = self::ArrDateToSQLDate($arr_inicio);
				}
			} else {
				$salida = array($inicio);
			}
		} else {
			$salida = array();
			$actual['ano'] = $arr_inicio['ano'];
			$actual['mes'] = $arr_inicio['mes'];
			$actual['dia'] = $arr_inicio['dia'];
			$dias_del_mes = (int)Date('t',strtotime($actual['ano'].'-'.$actual['mes']));
			$end = false;
			$i = 1;
			do {
				$salida[] = self::ArrDateToSQLDate($actual);
				if (($actual['ano'] == $arr_fin['ano']) and ($actual['mes'] == $arr_fin['mes']) and ($actual['dia'] == $arr_fin['dia'])) {
					$end = true;
				}
				$actual['dia']++;
				if ($actual['dia'] > $dias_del_mes) {
					$actual['dia'] = 1;
					$actual['mes']++;
					if ($actual['mes'] > 12) {
						$actual['mes'] = 1;
						$actual['ano']++;
						if ($actual['ano'] > $arr_fin['ano']) {
							$actual['ano'] = $arr_fin['ano'];
						}
					}
					$dias_del_mes = (int)Date('t',strtotime($actual['ano'].'-'.$actual['mes']));
				}
				$i++;
				if ($i > 1000) {
					$end = true;
				}
			} while(!$end);
			
		}

		if ((!empty($salida)) and (is_array($salida)) and (count($salida)>0)) {
			if ($invertir) {
				$salida = array_reverse($salida);
			}

			if ($out_format == CDATE_ISLATIN) {
				foreach ($salida as $key => $value) {
					$salida[$key] = self::ISOToFecha($value);
				}
			}
		}
		return $salida;
	}

	static function SetDayInitProxMes($fecha,$dia_inicio = "07",$delimiter = "-"){
		
		if(!self::IsValidISODate($fecha)){
			//cLogging::Write(__FILE__ ." ".__LINE__ ." La fecha que se indico , fue invalida");
			return null;
		}
		$fecha_explode = explode($delimiter,$fecha);
		
		if(!CanUseArray($fecha_explode)){
			// cLogging::Write(__FILE__ ." ".__LINE__ ." la fecha enviada no se pudo separar ");
			return null;
		}


		$dia_mes = end($fecha_explode);
		$mes = (int)$fecha_explode[1];
		$año = (int)$fecha_explode[0];
		if($dia_mes > 10){
			$mes++;
			if($mes > 12){
				$mes = 1;
				$año++;
			}
		}
		$fecha = sprintf('%04d-%02d-%02d', $año, $mes, $dia_inicio);
		return $fecha;
	}
/***********************************************************************
							FIN DE LA CLASE
***********************************************************************/
} // class cFechas

?>