<?php
/*
	Biblioteca de funcionea para claves.
	Key functions library.
*/

const NUMEROS = array('0','1','2','3','4','5','6','7','8','9'); 
const LETRAS = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'); 
const LETRASYNUMEROS = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9');
const LETRASNOAMBIGUOS = array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z');
const NUMEROSNOAMBIGUOS = array('1','2','3','4','5','6','7','8','9','0');
const LETRAYNUMEROSNOAMBIGUOS = array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0');
const CARACTERESESPECIALES =    array('º','\\','ª','!','|','"','@','$','#','%','€','?','ñ','Ñ','{','}',':',';','.','<','>',',','-','_');

function GenerateRandom($cant, $arrayofchars, $norepeat = false) {
	$result = "";
	if ($cant>0) {
		for ($i=1;$i<=$cant;$i++) {
			$len = count($arrayofchars)-1;
			$x = rand(0,$len);
			$result .= $arrayofchars[$x];
			if ($norepeat)  {
				unset($arrayofchars[$x]);
				sort($arrayofchars);
			}
		}
	}
	return $result;
}

function RandomLetters($cant, $norepeat = false) {
	return GenerateRandom($cant, LETRAS, $norepeat);
}

function RandomChars($cant, $norepeat = false) {
	return GenerateRandom($cant, LETRASYNUMEROS, $norepeat);
}

function UnambiguousRandomChars($cant, $norepeat = false) {
	return GenerateRandom($cant, LETRAYNUMEROSNOAMBIGUOS, $norepeat);
}

/*
	Genera una contraseña aleatoria a partir del archivo 'reservoreo.txt'.
	Generate random password from 'reservoreo.txt' file.
*/
function GenerateRandomPassword() {
	$file = dirname(__FILE__).DIRECTORY_SEPARATOR."reservoreo.txt";
	if (!file_exists($file)) { return null; }
	
	$txt = file_get_contents($file);

	$a = explode(" ",$txt);

	$numero = "";
	for ($i=0;$i<3;$i++) {
		$numero .= NUMEROS[rand(0,count(NUMEROS)-1)];
	}

	return $a[rand(0,count($a)-1)].$numero.$a[rand(0,count($a)-1)];
}

/*
	Generar una contraseña aleatoria segura.
*/
function GenerateRandomPasswordSecure() {
	$gen = GenerateRandomPassword();
	$salida = '';
	$len = strlen($gen);
	$lenspl = count(CARACTERESESPECIALES)-1;
	for ($i=0;$i<$len;$i++) {
		if ((rand(1,9) % 3) == 0) {
			if (in_array($gen[$i],LETRAS)) {
				$gen[$i] = strtoupper($gen[$i]);
			}
		}
		if ($i>0 and $i<$len) {
			if (rand(1,4) == 4) {
				$salida .= CARACTERESESPECIALES[rand(0,$lenspl)];
			}
		}
		$salida .= $gen[$i];
	}
	return $salida;
}

