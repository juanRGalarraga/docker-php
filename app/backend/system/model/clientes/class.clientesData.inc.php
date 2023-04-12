<?php
/**
 * Clase para el manejo de los datos asociados a una persona
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */
	class cClientesData extends cWsV2Client {
		function __construct(){
			parent::__construct();
		}

		/**
		 * Summary. Obtiene los datos del tipo indicado de un cliente.
		 * @param int $id El ID del client
		 * @param null|string $tipo El tipo de dato buscado
		 * @return null|array Los resultados de la busqueda
		 */
		public function GetData(int $id, ?string $tipo = null):?array{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El ID no es un número entero válido"); }
				$params = $id;
				if(!empty($tipo)){ $params .= "/".$tipo; }
				$this->GetQuery("personas/data/".$params);
				if(!empty($this->theData)){ return (array)$this->theData; }
			} catch (Exception $e) {
				$this->SetError($e);
			}
			return null;
		}

		
		/**
		 * Summary. Obtiene las cuentas bancarias (cbu/alias/cvu) de un cliente
		 * @param int $id El ID del client
		 * @return null|array Los resultados de la busqueda
		 */
		public function GetCuentasBancarias(int $id):?array{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El ID no es un número entero válido"); }
				$this->GetQuery("personas/cbu/".$id);
				if(!empty($this->theData)){ return (array)$this->theData; }
			} catch (Exception $e) {
				$this->SetError($e);
			}
			return null;
		}
	}