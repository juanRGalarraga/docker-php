<?php
/*
	Clase para tratamiento de contraseñas.
	Created: 2021-09-08
	Author: Gaston

*/
    class cPasswords{
        const normalPassword = ["[A-Z]+","[a-z]+","[0-9]+","[º\\ª!|\"@·#\$~%€&¬\/\(\)=\?'`´¡¿^\[\]\+\*ñÑ¨{}<>;,:\.\-_]"];
        const simplifiedPassword = ["[A-Za-z]+","[0-9]+","[º\\ª!|\"@·#\$~%€&¬\/\(\)=\?'`´¡¿^\[\]\+\*ñÑ¨{}<>;,:\.\-_]*"];
		const alfaChars = "abcdefghijklmnopqrstuvwxyz";
		const numberChars = "01234566789";
		const symbols = "º\\ª!|\"@·#\$~%€&¬\/\(\)=\?'`´¡¿^\[\]\+\*ñÑ¨{}<>;,:\.\-_";

        /**
         * Summary. Comprueba que una contraseña sea válida, de 8 a 128 caracteres con al menos, 1 letra minúscula, 1 mayúscula, 1 número y 1 caracter
         * @param string $password La contraseña a comprobar
         * @return bool $result True en caso de que pase la prueba o false en caso contrario
         */
        public static function CheckNormalPassword($password):?bool {
			if(empty($password)) { return false; }
			 //Entre 8 y 128 caracteres
            if (strlen($password) < 8) { return false; }
			if (strlen($password) > 128) { return false; }
			
            $result = true;
			foreach(self::normalPassword as $value){
				if (!preg_match("/".$value."/",$password)) { $result = false; break; }
			}
			return $result;
        }
		/**
		* Summary. Generar una contraseña que valide con CheckNormalPassword.
		* @param bool $symbols Incluir símbolos.
		* @return string
		*/
		public function GenPass(int $length = 8, bool $symbols = true):string {
			$pool = self::alfaChars. strtoupper(self::alfaChars) . self::numberChars . (($symbols)?self::symbols:null);
			do {
				$result = substr(str_shuffle($pool), 0, $length+1);
			} while(!$this->CheckNormalPassword($result));
			return $result;
		}

        /**
         * Summary. Comprueba que una contraseña sea válida, de 8 a 128 caracteres con al menos, 1 letra (sin importar caps) y al menos un número (caracteres especiales opcionales)
         * @param string $password La contraseña a comprobar
         * @return bool $result True en caso de que pase la prueba o false en caso contrario
         */
        public static function CheckSimplifiedPassword($password):?bool {
			if(empty($password)) { return false; }
			 //Entre 8 y 128 caracteres
            if (strlen($password) < 8) { return false; }
			if (strlen($password) > 128) { return false; }
			
            $result = true;
			foreach(self::simplifiedPassword as $value){
				if (!preg_match("/".$value."/",$password)) { $result = false; break; }
			}
			return $result;
        }
		
    }