<?php 

	/**
	*	Clase para el manejo de datos de las peticiones DEBINES.
	*	Created: 2020-12-17
	*	Author: Juan Galarraga
	*/


	class cDebin extends cModels {
		private $tabla_bind_debin = TBL_bind_debin;
		private $ws_usuario_id = null;

		function __construct($ws_usuario_id = null){
			parent::__construct();
			$this->ws_usuario_id = $ws_usuario_id;
			$this->tabla_principal = $this->tabla_bind_debin;
			$this->actual_file = __FILE__;
		}

		public function Get($id){
			$result = false;
			try {
				if(!SecureInt($id)) { throw new Exception(__LINE__." ID debe ser un número"); }
				$sql = "SELECT * FROM ".SQLQuote($this->tabla_principal)." WHERE `id`=".$id." AND `estado` !='ELI'";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$this->ParseRecord();
					$result = $fila;
				}
			} catch (Exception $e) {
				$this->SetError(__METHOD__, $e->getMessage());
			}

			return $result;

		}

		public function GetAll() {
            $result = false;
            try {
                $sql = "SELECT * FROM ".SQLQuote($this->tabla_principal)." WHERE `estado`!='ELI'";
                $this->Query($sql, true);
                if ($this->cantidad > 0) {        
                    if ($fila = $this->First()) {    
                        do {
                            $result[$fila['id']] = $fila;
                        } while ($fila = $this->Next());
                    }
                }

            } catch(Exception $e){
                $this->SetError(__METHOD__, $e->getMessage());
            }

            return $result;
        }


		public function Set($data){
			$result = false;
			try {
				if(!CanUseArray($data)){ throw new Exception(__LINE__." Data debe ser un array "); }
				$data['ws_usuario_id'] = $this->ws_usuario_id;
				$data['sys_fecha_alta'] = cFechas::Ahora();
				$data['sys_fecha_modif'] = cFechas::Ahora();
				if ($this->Insert($this->tabla_principal, $data)) {
					$result = true;
				}

			} catch (Exception $e) {
				$this->SetError(__METHOD__, $e->getMessage());
			}
			
			return $result;
		}

	} //Fin de la clase
 ?>