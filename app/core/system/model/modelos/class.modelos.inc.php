<?php
/**
 * Clase para el manejo de modelos
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."class.fundation.inc.php");

	class cModelos extends cModels {
		const tabla_modelos = TBL_modelos;

		private $qMainTable = null;
		function __construct(){
			parent::__construct();
			$this->mainTable = self::tabla_modelos;
			$this->qMainTable = SQLQuote(self::tabla_modelos);
		}

		/**
		 * Summary. Obtiene un registro dado su id
		 * @param int $id El ID del registro a obtener
		 * @return null|object
		 */
		public function Get(int $id = null):?object{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El array dado no se puede utilizar"); }
				$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id`=".$id;
				return parent::Get();
			} catch (Exception $e) {
				$this->SetError($e);
			}
			return null;
		}

		/**
		 * Summary. Crea un nuevo modelo en base al array dado
		 * @param array $data Los datos con los que se creara el modelo
		 * @return null|int
		 */
		public function Crear(array $data):?int{
			try {
				if(!CanUseArray($data)){ throw new Exception("El array dado no se puede utilizar"); }
				return $this->NewRecord($data);
			} catch (Exception $e) {
				$this->SetError($e);
			}
			return null;
		}

		/**
		 * Summary. Edita un modelo existente dado un array con modificaciones
		 * @param array $data Los datos con los que se editara el modelo
		 * @return null|int
		 */
		public function Editar(array $data):?bool{
			try {
				if(!CanUseArray($data)){ throw new Exception("El array dado no se puede utilizar"); }
				foreach($data as $key => $val){
					$this->$key = $val;
				}
				return $this->Set();
			} catch (Exception $e) {
				$this->SetError($e);
			}
			return null;
		}
		
		/**
		*	Summary. Obtiene un listado de caraterísticas disponibles para utilizar
		*	@return null|object Listado de caractrísticas encontradas
		*/
		public function GetCaracs():?array{
			try{
				$sql = "SELECT DISTINCT `claves` FROM ".$this->qMainTable.", JSON_TABLE(JSON_KEYS(`data`->'$.caracteristicas'),'$[*]' COLUMNS(`claves` JSON PATH '$')) AS `temp`";
				if($fila = $this->FirstQuery($sql)){
					$result = array();
					do{
						$clave = $fila->claves;
						$clave = str_replace("\"","",$clave);
						$clave = trim($clave);
						if(empty($clave)){ continue; }
						if(!in_array($clave,$result)){
							$result[] = $clave;
						}
					}while($fila = $this->Next());
					return $result;
				}
			} catch (Exception $e) {
				$this->SetError($e);
			}
			return null;
		}
		
		/**
		*	Summary. Dado el ID de un modelo intenta obtener su imagen asociada
		*	@return null|array
		*/
		public function GetImage(?int $id = null):?array{
			try{
				if(!$this->existe){
					if(is_null(SecureInt($id))){ throw new Exception("No se indicó el ID del registro a obtener"); }
				}
				if(!is_null(SecureInt($id))){
					if(!$this->Get($id)){ throw new Exception("No se pudo obtener el modelo con id ".$id); }
				}
				
				$data = $this->data;
				return $this->GetImageByData($data,$this->id);
			}catch (Exception $e) {
				$this->SetError($e);
			}
			return null;
		}
		
		/**
		*	Summary. Dado un campo $data intenta obtener la imagen de dicho campo si es que la tiene
		*	@param array|object $data El campo a inspeccionar
		*	@param int $id El ID al cual se aplicara algún cambio en caso de encontrar una URL de imagen que no exista físicamente
		*/
		public function GetImageByData($data,$id):?array{
			if(!is_object($data) AND !is_array($data)){ return null; }
			$data = (object)$data;
			$imagen = $data->imagen ?? null;
			if(!empty($imagen) AND !is_object($imagen)){ 
				if(cCheckInput::URL($imagen)){
					if($imgData = file_get_contents($imagen)){
						$name = pathinfo($imagen)['basename'];
						if($imgFile = SaveImg($imgData,"modelos/".$id,$name)){
							$dataToSave = $data->data;
							unset($dataToSave->imagen);
							$dataToSave->imagen_url = $imagen;
							$dataToSave->imagen_file = $imgFile;
							$dataToSave->imagen_nombre = $name;
							
							$this->Editar(["data"=>$dataToSave]);
							$data = $dataToSave;
						}
					}
				}
			}
			
			if(isset($data->imagen_file)){
				if(ExisteArchivo(DIR_biblioteca.$data->imagen_file)){
					if($imgData = file_get_contents(DIR_biblioteca.$data->imagen_file)){
						$nombre = $data->imagen_nombre ?? pathinfo(DIR_biblioteca.$data->imagen_file)['basename'] ?? "";
						$imagen = array(
							'data' => "data:".mime_content_type(DIR_biblioteca.$data->imagen_file).";base64,".base64_encode($imgData),
							'name' => $nombre
						);
						return $imagen;
					}			
				}
			}
			return null;
		}
	}