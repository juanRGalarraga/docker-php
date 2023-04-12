<?php
/*
	URL parser. Clase para interpretar la URL para el Web Service. La usa cApis (class.wsv2Apis.inc.php).
	Created: 2021-05-04
	Author: DriverOp
	
	
*/

const PREDEFINED_PATTERNS = [
        '*' => '(.*)', // todo
        '?' => '([^/]+)', // opcional
        'int' => '([0-9]+)', // solo números enteros positivos
        'id' => '((?=[^0])[0-9]+)', // solo números enteros positivos que no son el cero o comienzan con cero
        'num' => '([0-9,\.]+)', // numero decimal.
        'str' => '((?=[^\d].*?)[a-z0-9\-\_]+)', // cadena tipo alias que no empieza con número
        'string' => '((?=[^\d].*?)[a-z0-9\-\_]+)', // cadena tipo alias que no empieza con número
        'pass' => '([a-z0-9_!@#$%&:{}]+)', // cadena tipo password segura
        'user' => '([a-z0-9_-]+)', // nombre de usuario
		'dni' => '([a-z]?[0-9]{7,9}[a-z]?)' // dni
    ];


class cURIParser {

	private $patterns = PREDEFINED_PATTERNS;

/**
* Summary. Parsea el route de una API para extraer parámetros y patrones.
* @param str $route El route candidato en cuestión.
* @return array $result [parameters => array, pattern => array, result => str]
* @note Es decir, entra algo como sarasa/{id}:int y sale como sarasa/([0-9]+) que es la expresión regular que se usará para determinar si la URL existe como API y qué parte es parámetro.
* El método devuelve:
* 	parameters: La lista (array) de los nombres de los parámetros, lo que está entre { } en el route. Vacío si el route no indica ningún parámetro con nombre.
* 	patterns: La lista (array) de las subexpresiones regulares que deben matchear con cada parámetro listado en parameters.
* 	result: La expresión (string) regular completa que debe matchear contra la URI candidata a ser API.
*/

	public function Parse(string $route): array {
        $parameterList = [];
        $patternList = [];
        $result = preg_replace_callback('~/{([a-z-0-9@_]+)\}\??((:\(?[^/]+\)?)?)~i', // Esto indica qué caracteres están permitidos en el route.
			function ($match) use (&$parameterList, &$patternList) {
					$rawMatch = $match[0];
				   $parameter = $match[1];
				$namedPattern = $match[2];
				$pattern = '/' . $this->GetRegex('?');
				if (!empty($namedPattern)) {
					$replace = substr($namedPattern, 1);

					if ($this->RegexExists($replace)) {
						$pattern = '/' . $this->GetRegex($replace);
					} elseif (substr($replace, 0, 1) == '(' && substr($replace, -1, 1) == ')') {
						$pattern = '/' . $replace;
					}
				} elseif ($this->RegexExists($parameter)) {
					$pattern = '/' . $this->GetRegex($parameter);
				}

				// Check whether parameter is optional.
				if (strpos($rawMatch, '?') !== false) {
					$pattern = str_replace(['/(', '|'], ['(/', '|/'], $pattern) . '?'; //Esto hace que la barra inclinada (separador de directorios virtuales) esté incluida en la parte "opcional" de la regex resultante.
				}
				$parameterList[] = $parameter;
				$patternList[] = $pattern;

				return $pattern;
			},
		trim($route));

        return ['parameters' => $parameterList, 'patterns' => $patternList, 'result' => $result];
	}
/**
* Summary. Devuelve la expresión regular apuntada por $pattern
* @param str $pattern el índice.
* @return str la expresión regular o null en caso de no existir.
*/
	public function GetRegex(string $pattern) {
		return $this->patterns[$pattern]??null;
	}

/**
* Summary. Determina si el patrón buscado existe en la lista de patrones de expresiones regulares predefinidas.
* @param str $pattern el índice candidato.
* @return bool true si existe, false si no.
*/
	public function RegexExists(string $pattern): bool {
		return isset($this->patterns[$pattern]);
	}

} // class cURIParser
