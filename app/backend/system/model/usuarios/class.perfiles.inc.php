<?php
/*
	Crea o edita perfiles de acceso para los usuarios
	Created: 2021-03-04
	Author: Gastón Fernandez
	Modif: 2021-03-24
	Desc: Agregado método GetList/GetListado
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_includes."common.inc.php");
class cPerfilesUsuarios extends cModels{
	
	const tabla_perfiles = TBL_backend_perfiles;

	function __construct()
		{
		    parent::__construct();
		    $this->actual_file = substr(__FILE__, strlen(DIR_BASE)) . " ";
		}

		public function Get($id){
		    $result = false;
		    try {
				if(is_null(SecureInt($id,null))){
				    throw new Exception("ID no es un número o esta vacío.");
				}

				$sql = "SELECT `alias`,`nombre`,`data`,`estado` FROM ".SQLQuote(self::tabla_perfiles). " WHERE `id`=".$id;

				$this->Query($sql);
				if($fila = $this->First()){
				    $this->raw_record = $this->ParseTextFields($fila);
				    $this->ParseRecord();
				    $result = $fila;
				}

		    } catch (Exception $e) {
				$this->SetErrorEx($e);
		    }
		    return $result;
		}

		public function GetByAlias($alias){
		    $result = false;
		    try {
				if(empty($alias)){
				    throw new Exception("El alias esta vacío.");
				}
				$sql = "SELECT `alias`,`nombre`,`data`,`estado` FROM ".SQLQuote(self::tabla_perfiles). " WHERE UPPER(`alias`)=UPPER('".$this->RealEscape($alias)."')";

				$this->Query($sql);
				if($fila = $this->First()){
				    $this->raw_record = $this->ParseTextFields($fila);
				    $this->ParseRecord();
				    $result = $fila;
				}

		    } catch (Exception $e) {
				$this->SetErrorEx($e);
		    }
		    return $result;
		}

		public function Create($data){
		    global $objeto_usuario;
		    $result = false;
		    $campos = ["alias","nombre","data","estado"];
		    try {
				if(!CanUseArray($data)){
				    throw new Exception("DATA no es un array o esta vacío.");
				}

				$insert = array();
				foreach($data as $key => $value){
				    if(in_array($key, $campos)){
						$insert[$key] = $value;
				    }
				}

				if(CanUseArray($insert)){
				    $insert['sys_fecha_alta'] = cFechas::Ahora();
				    $insert['sys_fecha_modif'] = cFechas::Ahora();
				    $insert['sys_usuario_id'] = @$objeto_usuario->id;
				    $result = $this->Insert(self::tabla_perfiles, $insert);
				}

		    } catch (Exception $e) {
				$this->SetErrorEx($e);
		    }
		    return $result;
		}

		public function Editar($id, $data){
		    $result = false;
		    $campos = ["alias","nombre","data","estado"];
		    try {
				if(is_null(SecureInt($id,null))){
				    throw new Exception("ID no es un número o esta vacío.");
				}

				if(!CanUseArray($data)){
				    throw new Exception("DATA no es un array o esta vacío.");
				}

				$update = array();
				foreach($data as $key => $value){
				    if(in_array($key, $campos)){
						$update[$key] = $value;
				    }
				}

				$where = '`id`='.$id;

				if(CanUseArray($update)){
				    $update['sys_fecha_modif'] = cFechas::Ahora();
				    $result = $this->Update(self::tabla_perfiles, $update, $where);
				}

		    } catch (Exception $e) {
				$this->SetErrorEx($e);
		    }
		    return $result;
		}
/**
* Summary. Devuelve la lista de perfiles de usuario, como array.
* @param string/array $estado Un estado o un array de estados para incluir en los resultados. O Null para todos.
* @param bool $id_as_index Incluir o no el ID como índice del array resultante.
* @return array.
*/
		public function GetList($estado = null, $id_as_index = true):array {
			$result = [];
			try {
				$sql = "SELECT * FROM " . SQLQuote(self::tabla_perfiles) . " WHERE 1=1 ";
				if (!is_null($estado)) {
					$sql .= cSideKick::BuildEstadoCond($this, $estado);
				}
				$sql .= " ORDER BY `nombre`;";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$result = array();
					do {
						$fila['data'] = json_decode($fila['data']);
						if ($id_as_index) {
							$result[$fila['id']] = $fila;
						} else {
							$result[] = $fila;
						}
					} while ($fila = $this->Next());
				}
			} catch(Exception $e) {
				$this->SetErrorEx($e);
			}
			return $result;
		}
/**
* Summary. Alias de GetList
*/
	public function GetListado($estado = null, $id_as_index = true):array {
		return $this->GetList($estado, $id_as_index);
	}
}
?>