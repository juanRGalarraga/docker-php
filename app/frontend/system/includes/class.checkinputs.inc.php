<?php
/*
	Métodos estáticos para validación de datos proporcionado por los visitantes.
	¡Nunca hay que fiarse de los datos dados por los usuarios!.
	
	Created: 2019-07-31
	Author: DriverOp.
	
	
*/
include_once(DIR_includes."class.logging.inc.php");
include_once(DIR_includes."class.fechas.inc.php");

define("MV_FORMAL",0);
define("MV_COLOQUIAL",1);

class cCheckInput {

	public static $msgerr = array();
	public static $modoverbal = MV_FORMAL;
	public static $checkempty = TRUE;
	public static $strictcuit = TRUE;
	public static $logon = false;
	private static $currentString = NULL;
	private static $rxnomape = "/^[A-Za-zƒŠŒŽšœžŸÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ_\-\s']*$/i";
	private static $rxnick = "/^[A-Za-z0-9_\-\.]*$/i";
	private static $rxdirpos = "/^[0-9A-Za-zºƒŠŒŽšœžŸÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ_\-\s\.\(\),']*$/i";
	private static $rxnumcalle = "/^[0-9KkMmNnSs\/\-\.]*$/i";
	private static $rxtel = "/^[ext\d\-\/\+\(\)\s\.]{6,}$/i";
	private static $rxdni = "/^\d{7,8}$/";
	private static $rxcuit = "/^(\d){11}$/im";
	private static $rxcbu = "/^(\d){22}$/im";
	private static $rxcbu_alias = "/^[0-9a-zA-Z\.-]{6,20}$/im"; // Alias de un CBU
	private static $rxcpa = "/^([a-zA-Z])?\d{4}([a-zA-Z]{3})?$/";
	private static $rxdom_old = "/^[a-zA-Z]{3}\d{3}$/";
	private static
	$rxdom_mercosur = array(
				"/^[A-Z]{2}\d{3}[A-Z]{2}$/i", // Argentina, autos (y Venezuela)
				"/^[A-Z]\d{3}[A-Z]{3}$/i", // Argentina, motos
				"/^[A-Z]{3}\d[A-Z]\d{2}$/i", // Brasil, autos y motos
				"/^[A-Z]{4}\d{3}$/i", // Paraguay, autos.
				"/^\d{3}[A-Z]{4}$/i", // Paraguay, motos.
				"/^[A-Z]{3}\d{4}$/i" // Uruguay autos y motos.
				);
	
	private static $rxpass = "/^[0-9A-Za-z]*$/";
	private static $rxstrongpass = "/^[0-9A-Za-zº\\ª!|\"@·#\$~%€&¬\/\(\)=\?'`´¡¿^\[\]\+\*ñÑ¨{}<>;,:\.\-_]*$/";
	private static $rxfecha = "/^[0-3]?[0-9](-|\/)[0-1]?[0-9](-|\/)[0-9]{4}$/";
	private static $rxfechaiso = "/^[0-9]{4}-[0-1]?[0-9]{1}-[0-3]?[0-9]{1}$/";
	private static $rxrfc = "/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/";
	private static $rxemail = "/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-ñÑ]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/iD"; // As per RFC 5322
	

/*
	$string es la cadena a evaluar.
	$index es el índice en el array $msgerr a devolver si hay error.
	$label es la cadena que se usará en el mensaje de error.
*/
	static function NomApe($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'nomape';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('nomape', 0, $index, $label);
		} else {
			if (strlen($string) > 0) {
				if (strlen($string) < 2) {
					self::putMsgErr('nomape',1, $index, $label);
				} else {
					if (strlen($string) > 50) {
						self::putMsgErr('nomape',2, $index, $label);
					} else {
						if (!preg_match(self::$rxnomape,$string)) {
							self::putMsgErr('nomape',3, $index, $label);
						} else {
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	} // NomApe

	static function DNI($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'dni';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('dni',0, $index, $label);
		} else {
			if (strlen($string) > 0) {
				if (preg_match("/^0+/",$string)) {
					self::putMsgErr('dni',5, $index, $label);
				} else {
					$string = preg_replace("/^0+/", "", $string);
					if (strlen($string) < 7) {
						self::putMsgErr('dni',3, $index, $label);
					} else {
						if (strlen($string) > 8) {
							self::putMsgErr('dni',4, $index, $label);
						} else {
							if (!preg_match(self::$rxdni,$string)) {
								self::putMsgErr('dni',2, $index, $label);
							} else {
								$result = true;
							}
						}
					}
				}
			}
		}
		return $result;
	} // DNI

	static function CUIT($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'cuit';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('cuit',0, $index, $label);
		} else {
			if (strlen($string) > 0) {
				if (preg_match("/^0+/",$string)) {
					self::putMsgErr('cuit',5, $index, $label);
				} else {
					if (strlen($string) != 11) {
						self::putMsgErr('cuit',1, $index, $label);
					} else {
						if (!preg_match(self::$rxcuit,$string)) {
							self::putMsgErr('cuit',2, $index, $label);
						} else {
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	} // CUIT

	static function ValidarCUIT($string, $label = 'CUIT/CUIL') {
		$result = false;
		$string = trim($string);
		$mult = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
		$string = str_replace(array("-"," "), null, $string);
		if (strlen($string) == 11) {
			$total = 0;
			for ($i = 0; $i < count($mult); $i++) {
				$total = $total + ((int)$string[$i] * $mult[$i]);
			}
			$mod = $total % 11;
			$digito = ($mod == 0) ? 0 : (($mod == 1) ? 9 : (11 - $mod));
			$result = $digito == (int)$string[10];
			if (!$result) {
				self::putMsgErr('cuit',3, 'cuit', $label);
			}
		} else {
			self::putMsgErr('cuit',1, 'cuit', $label);
		}
		return $result;
	}

	static function nro_doc($string, $index = null, $label = null) { // Esto controla que $string sea un DNI, o un CUIT, o un Passport válido. Cualquiera de los tres.
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'nro_doc';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('nro_doc',0, $index, $label);
		} else {
			if (self::DNI($string, $index = null, $label = null)) {
				$result = true;
			}
			if (($result == false) and (self::CUIT($string, $index = null, $label = null))) {
				unset(self::$msgerr['dni']);
				$result = true;	
			}
		}
		return $result;
	} // nro_doc

	static function RFC($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'RFC';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('RFC',0, $index, $label);
		} else {
			if (strlen($string) > 0) {
				if (preg_match("/^0+/",$string)) {
					self::putMsgErr('RFC',5, $index, $label);
				} else {
					$string = preg_replace("/^0+/", "", $string);
					if (strlen($string) < 12) {
						self::putMsgErr('RFC',3, $index, $label);
					} else {
						if (strlen($string) > 13) {
							self::putMsgErr('RFC',4, $index, $label);
						} else {
							if (!preg_match(self::$rxrfc,$string)) {
								self::putMsgErr('RFC',2, $index, $label);
							} else {
								$result = true;
							}
						}
					}
				}
			}
		}
		return $result;
	} // RFC
/*
	$tipo puede ser:
	'LATIN': verifica que $string es una fecha latina, todo lo demás falla.
	'ISO': verifica que $string es una fecha ISO, todo lo demás falla.
	'BOTH': verifica que $string sea cualquiera de las dos, cualquier otra cosa, falla.
*/
	static function Fecha($string, $index = null, $label = null, $tipo = 'BOTH') {
		$result = false;
		self::$currentString = $string;
		$index = (!empty($index))?$index:'fecha';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('fecha',0, $index, $label);
		} else {
			if (strtoupper($tipo) == 'LATIN') {
				if (cFechas::LooksLikeDate($string) == false) {
					self::putMsgErr('fecha',1, $index, ' '.$label.' Debe ser DD/MM/AAAA');
					return false;
				}
			}
			if (strtoupper($tipo) == 'ISO') {
				if (cFechas::LooksLikeISODate($string) == false) {
					self::putMsgErr('fecha',1, $index, ' '.$label.' Debe ser AAAA-MM-DD');
					return false;
				}
			}
			$tipo = cFechas::LooksLikeADate($string);
			if ($tipo == false) {
				self::putMsgErr('fecha',1, $index, $label);
				return false;
			}
			if (strtoupper($tipo) == 'LATIN') {
				if (cFechas::IsValidDate($string) == false) {
					self::putMsgErr('fecha',2, $index, $label);
				} else {
					$result = true;
				}
			} else {
				if (cFechas::IsValidISODate($string) == false) {
					self::putMsgErr('fecha',2, $index, $label);
				} else {
					$result = true;
				}
			}
		}
		return $result;
	} // Fecha

	static function Dominio($string, $index = null, $label = null) {
		$result = false;
		self::$currentString = $string;
		$index = (!empty($index))?$index:'dominio';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('dominio',0,$index, $label);
		} else {
			$string = trim($string);
			$len = mb_strlen($string);
			if ($len == 0) {
				self::putMsgErr('dominio',0,$index, $label);
				return true;
			}
			if (($len < 6) or ($len > 7)) {
				self::putMsgErr('dominio',1,$index, $label);
				return false;
			}
			if ($len == 6) {
				if (!preg_match(self::$rxdom_old,$string)) {
					self::putMsgErr('dominio',2,$index, $label);
					return false;
				} else {
					return true;
				}
			}
			foreach(self::$rxdom_mercosur as $pattern) {
				if (preg_match($pattern,$string) != false) {
					$result = true;
					break;
				}
			}
			if (!$result) {
				self::putMsgErr('dominio',2,$index, $label);
			}
		}
		return $result;
	} // Dominio

	static function Email($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'email';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('email',0,$index, $label);
		} else {
			if (!preg_match(self::$rxemail, $string)) {
				self::putMsgErr('email',1, $index, $label);
			} else {
				$result = true;
			}
		}
		return $result;
	} // Email
/*
	Rápidamente determinar si una cadena es una dirección de correo electrónico.
*/
	static function IsEmail($string) {
		$res = preg_match(self::$rxemail, $string);
		if (($res === 0) or ($res === false)) {
			return false;
		} else {
			return true;
		}
	}

/* Esto tanto sirve para validar nombres de usuarios como contraseñas */
	static function Nick($string, $index = null, $label = null) {
		$result = false;
		self::$currentString = $string;
		$index = (!empty($index))?$index:'nick';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('nick',0,$index, $label);
		} else {
			if (!preg_match(self::$rxnick, $string)) {
				self::putMsgErr('nick',1, $index, $label);
			} else {
				$result = true;
			}
		}
		return $result;
	} // nick
	
	static function Password($string, $index, $label) {
		$result = false; // Soy pesimista
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('nomape',0,($label!=null)?$label:$index);
		} else {
			if (strlen($string) > 0) {
				if (strlen($string) < 2) {
					self::putMsgErr('nomape',1,($label!=null)?$label:$index);
				} else {
					if (strlen($string) > 32) {
						self::putMsgErr('nomape',2,($label!=null)?$label:$index);
					} else {
						$result = true;
					}
				}
			}
		}
		return $result;
	} // Password o Contraseña

	static function StrongPassword($string, $index = null, $label = null) {
		$result = false; // Soy pesimista
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('pass',0,$index, $label);
		} else {
			if (mb_strlen($string) > 0) {
				if (mb_strlen($string) < 2) {
					self::putMsgErr('pass',1,$index, $label);
				} else {
					if (mb_strlen($string) > 256) {
						self::putMsgErr('pass',2,$index, $label);
					} else {
						if (preg_match(self::$rxstrongpass, $string)) {
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	} // Password o Contraseña

	static function Tel($string, $index = null, $label = null) {
		$result = false;
		self::$currentString = $string;
		$index = (!empty($index))?$index:'tel';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('tel',0,$index, $label);
		} else {
			if (strlen($string) > 0) {
				if (strlen($string) > 25) {
					self::putMsgErr('tel', 1, $index, $label);
				} else {
					if (!preg_match(self::$rxtel, $string)) {
						self::putMsgErr('tel', 2, $index, $label);
					} else {
						$result = true;
					}
				}
			}
		}
		return $result;
	} // Tel

	static function DirPos($string, $index = null, $label = null) {
		$result = false;
		self::$currentString = $string;
		$index = (!empty($index))?$index:'dirpos';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('dirpos',0, $index, $label);
		} else {
			if (strlen($string) > 0) {
				if (strlen($string) < 3) {
					self::putMsgErr('dirpos',1, $index, $label);
				} else {
					if (strlen($string) > 100) {
						self::putMsgErr('dirpos',2, $index, $label);
					} else {
						if (!preg_match(self::$rxdirpos,$string)) {
							self::putMsgErr('dirpos',3, $index, $label);
						} else {
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	} // DirPos

	/* Valida la altura de la calle */
	static function NumCalle($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'tel';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('numcalle',0,$index, $label);
		} else {
			if (strlen($string) > 0) {
				if (!preg_match(self::$rxnumcalle,$string)) {
					self::putMsgErr('numcalle',1, $index, $label);
				} else {
					$result = true;
				}
			}
		}
		return $result;
	} // NumCalle

	/* Valida el Código Bancario Único */
	static function CBU($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'cbu';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('cbu',0,$index, $label);
		} else {
			if (strlen($string) > 0) {
				if (strlen($string) != 22) {
					self::putMsgErr('cbu',2, $index, $label);
				} else {
					if (!preg_match(self::$rxcbu,$string)) {
						self::putMsgErr('cbu',1, $index, $label);
					} else {
						$result = true;
					}
				}
			}
		}
		return $result;
	} // CBU
	
	/* Dado un CBU, determina si es válido internamente */
	static function ValidarCBU($string) {
		$string = trim($string);
		$string = str_replace(array("-"," "), null, $string);
		if (!preg_match('/[0-9]{22}/', $string)) { return false; } //Si no son 22 números, chau...
		$string = str_split($string);
		if (self::CalcularVerif(array_slice($string,0,7)) != $string[7]) { return false; }
		if (self::CalcularVerif(array_slice($string,8,13)) != $string[21]) { return false; }
		return true;
	}
	/* Calcula un dígito verificador de un CBU */
	static function CalcularVerif($parte) {
		$array_arbitrario = array(3,1,7,9);
		$sum = 0;
		$parte = array_reverse($parte);
		foreach($parte as $key => $value) {
			$rem = ($key % 4);
			$sum = $sum + ((int)$value * $array_arbitrario[$rem]);
		}
		$result = (10-$sum % 10);
		$result = $result % 10;
		return $result;
	}

	/* Valida el Alias de un Código Bancario Único */
	static function CBU_alias($string, $index = null, $label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		$index = (!empty($index))?$index:'cbu_alias';
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('cbu_alias',0,$index, $label);
		} else {
			if (strlen($string) > 0) {
				if (strlen($string) < 6) {
					self::putMsgErr('cbu_alias',2, $index, $label);
				} else {
					if (strlen($string) > 20) {
						self::putMsgErr('cbu_alias',3, $index, $label);
					} else {
						if (!preg_match(self::$rxcbu_alias,$string)) {
							self::putMsgErr('cbu_alias',1, $index, $label);
						} else {
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	} // CBU
	

	static function CPA($string,$index = null,$label = null) {
		$result = false;
		$string = trim($string);
		self::$currentString = $string;
		if ((strlen($string) == 0) and (self::$checkempty)) {
			self::putMsgErr('cpa',0,($label!=null)?$label:$index);
		} else {
			if (strlen($string) > 0) {
				if (strlen($string) == 4) {
					if (!preg_match("/^\d{4}$/im",$string)) {
						self::putMsgErr('cpa',1,$index, $label);
					} else {
						$result = true;
					}
				} else {
					if (strlen($string) == 8) {
						if (!preg_match(self::$rxcpa,$string)) {
							self::putMsgErr('cpa',2,$index, $label);
						} else {
							$result = true;
						}
					} else {
						self::putMsgErr('cpa',3,$index, $label);
					}
				}
			}
		}
		return $result;
	}


	private static function putMsgErr($msgindex, $shift, $index, $label = null) {
		self::$msgerr[$index] = mb_ucfirst(sprintf(self::$mensajes[$msgindex][self::$modoverbal][$shift],trim($label)));
		if (self::$logon) {
			self::SetError(__METHOD__,self::$currentString.' -> '.self::$msgerr[$index]);
		}
	}

	static function SetError($method, $msg) {
		$line = basename(__FILE__)." -> ".$method.". ".$msg;
		if (DEVELOPE) { EchoLogP(htmlentities($line)); }
		cLogging::Write($line);
	}
	
	private static $mensajes = array(
		"nomape" => array(
				0=>array(
					0=>"Debe escribir %s",
					1=>"%s es demasiado corto",
					2=>"%s es muy largo",
					3=>"%s escrito no es válido"
				),
				1=>array(
					0=>"Debes escribir tu %s",
					1=>"%s es demasiado corto",
					2=>"%s es muy largo",
					3=>"%s que has escrito no es válido"
				)
			),
		"dirpos" => array(
				0=>array(
					0=>"Debe escribir %s",
					1=>"%s es demasiado corto",
					2=>"%s es muy largo",
					3=>"%s no es válido"
				),
				1=>array(
					0=>"Debes escribir %s",
					1=>"%s es demasiado corto",
					2=>"%s es muy largo",
					3=>"%s que has escrito no es válido"
				)
			),
		"numcalle" => array(
				0=>array(
					0=>"Debe escribir la altura de la calle",
					1=>"%s no es válido"
				),
				1=>array(
					0=>"Debes escribir la altura de la calle",
					1=>"%s no es válido"
				)
			),
		"email" =>  array(
				0=>array(
					0=>"Debe escribir la dirección de %s",
					1=>"La dirección de %s no es válida"
				),
				1=>array(
					0=>"Debes escribir tu dirección de %s",
					1=>"La dirección de %s no es válida"
				)
			),
		"pass" => array(
				0=>array(
					0=>"Debe escribir %s",
					1=>"%s es demasiado corta",
					2=>"%s es muy larga",
					3=>"%s escrita no es válida"
				),
				1=>array(
					0=>"Debes escribir tu %s",
					1=>"%s es demasiado corta",
					2=>"%s es muy larga",
					3=>"%s que has escrito no es válida"
				)
			),
		"tel" => array(
				0=>array(
					0=>"Debe ingresar el número de %s",
					1=>"El número de %s demasiado largo",
					2=>"%s no válido. Formato incorrecto"
				),
				1=>array(
					0=>"Debes ingresar tu número de %s",
					1=>"El número de %s es demasiado largo",
					2=>"%s no válido. Formato incorrecto"
				)
		),
		"dni" => array(
				0=>array(
					0=>"Debe escribir el número de %s",
					1=>"El número de %s no es válido",
					2=>"El número de %s no es válido. Formato incorrecto",
					3=>"El %s no debe ser menor a 7 cifras",
					4=>"El %s no debe ser mayor a 8 cifras",
					5=>"El %s no debe comenzar con ceros"
				),
				1=>array(
					0=>"Debes ingresar tu número de %s",
					1=>"El número de %s no es válido",
					2=>"El número de %s no es válido. Formato incorrecto",
					3=>"El %s no debe ser menor a 7 cifras",
					4=>"El %s no debe ser mayor a 8 cifras",
					5=>"El %s no debe comenzar con ceros"
				)
		),
		"cuit" => array(
				0=>array(
					0=>"Debe escribir el número de %s",
					1=>"El número de %s deben ser 11 cifras",
					2=>"El número de %s no es válido. Formato incorrecto",
					3=>"El número de %s no es válido",
					5=>"El %s no debe comenzar con ceros"
				),
				1=>array(
					0=>"Debes ingresar tu número de %s",
					1=>"El número de %s deben ser 11 cifras",
					2=>"El número de %s no es válido. Formato incorrecto",
					3=>"El número de %s no es válido",
					5=>"El %s no debe comenzar con ceros"
				)
		),
		"RFC" => array(
				0=>array(
					0=>"Debe escribir el número de %s",
					1=>"El número de %s no es válido",
					2=>"El número de %s no es válido. Formato incorrecto",
					3=>"El %s no debe ser menor a 12 cifras",
					4=>"El %s no debe ser mayor a 13 cifras",
					5=>"El %s no debe comenzar con ceros"
				),
				1=>array(
					0=>"Debes ingresar tu número de %s",
					1=>"El número de %s no es válido",
					2=>"El número de %s no es válido. Formato incorrecto",
					3=>"El %s no debe ser menor a 12 cifras",
					4=>"El %s no debe ser mayor a 13 cifras",
					5=>"El %s no debe comenzar con ceros"
				)
		),
		"cpa" => array(
				0=>array(
					0=>"Debe ingresar el %s",
					1=>"%s deben ser cuatro números",
					2=>"%s debe ser una letra, cuatro números y tres letras",
					3=>"Formato de %s incorrecto"
				),
				1=>array(
					0=>"Debes ingresar el %s",
					1=>"%s deben ser cuatro números",
					2=>"%s debe ser una letra, cuatro números y tres letras",
					3=>"El formato de %s es incorrecto"
				)
		),
		"dominio"=>array(
				0=>array(
					0=>"Debe ingresar %s",
					1=>"%s deben ser 6 o 7 caracteres",
					2=>"%s el formato no es válido"
				),
				1=>array(
					0=>"Debes ingresar el %s",
					1=>"%s deben ser 6 o 7 caracteres",
					2=>"%s el formato no es válido"
				)
		),
		"pasaporte"=>array(
				0=>array(
					0=>"Debe ingresar el %s",
					1=>"% deben ser al menos 8 caracteres y no más de 11",
					2=>"Nro de %s no válido"
				),
				1=>array(
					0=>"Debes ingresar el %s",
					1=>"% deben ser al menos 8 caracteres y no más de 11",
					2=>"Nro de %s no válido"
				)
		),
		"fecha"=>array(
				0=>array(
					0=>"Debes indicar la %s",
					1=>"Formato de fecha no válido.%s",
					2=>"%s no es válida."
				),
				1=>array(
					0=>"Debe indicar la %s",
					1=>"El dato está en un formato de fecha no válido.%s",
					2=>"%s no es válida."
				)
		),
		"nro_doc"=>array(
				0=>array(
					"Debes indicar un número"
				),
				1=>array(
					"Debe indicar un número"
				)
		),
		"nick"=>array(
				0=>array(
					"Debes indicar %s",
					"%s contiene caracteres no válidos"
				),
				1=>array(
					"Debe indicar su %s",
					"%s contiene caracteres no válidos"
				)
		),
		"cbu"=>array(
				0=>array(
					"Debes indicar %s",
					"%s deben ser solo números",
					"%s debe ser exactamente 22 números",
					"%s no es válido"
				),
				1=>array(
					"Debe indicar su %s",
					"%s deben ser solo números",
					"%s debe ser exactamente 22 números",
					"%s no es válido"
				)
		),
		"cbu_alias"=>array(
				0=>array(
					"Debes indicar %s",
					"%s no puede contener signos, acentos o espacio en blanco",
					"%s debe ser 6 caracteres o más",
					"%s debe ser hasta 20 caracteres"
				),
				1=>array(
					"Debe indicar su %s",
					"%s no puede contener signos, acentos o espacio en blanco",
					"%s debe ser 6 caracteres o más",
					"%s debe ser hasta 20 caracteres"
				)
		)
	);

} // Fin de la clase
?>