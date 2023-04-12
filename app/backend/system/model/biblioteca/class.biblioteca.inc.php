<?php
/**
 * Clase para el manejo de archivos de la biblioteca de clientes
 * Created: 2021-11-09
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."wsv2".DS."client".DS."class.wsv2Client.inc.php");

	class cBiblioteca extends cWsV2Client{
		function __construct(){
			parent::__construct();
		}

		/**
		 * Summary. Obtiene un archivo dado su ID
		 * @param int $id El ID del archivo
		 * @return null|object Objeto conteniendo el base64 y datos del archivo, o nulo en caso de no encontrar el archivo
		 */
		public function GetFile(int $id):?object{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El ID del archivo debe ser un número entero válido"); }
				$this->GetQuery("biblioteca/".$id);
				if(!empty($this->theData) AND $this->http_nroerr == 200){ return $this->theData; }
			} catch (Exception $e) {
				$this->SetLog($e);
			}
			return null;
		}

		/**
		 * Summary. Obtiene un archivo dado su ID
		 * @param int $id El ID del archivo
		 * @param array $data Los datos a obtener del archivo, nombre y carpeta donde buscar
		 * @return null|array Array conteniendo el base64 y datos del archivo, o nulo en caso de no encontrar el archivo
		 */
		public function GetFileByName(int $id,array $data):?array{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El ID del archivo debe ser un número entero válido"); }
				if(!CanUseArray($data)) { throw new exception("El array para buscar el archivo esta vacío"); }
				$this->GetQuery("biblioteca/byname/".$id,$data);
				if(!empty($this->theData) AND $this->http_nroerr == 200){ return $this->theData; }
			} catch (Exception $e) {
				$this->SetLog($e);
			}
			return null;
		}

		/**
		 * Summary. Obtiene la biblioteca de archivos del cliente
		 * @param int $id EL ID del cliente
		 * @param string $route La carpeta actual
		 */
		public function ListFiles(int $id, ?string $route = null):?object{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El ID de la persona debe ser un número entero válido"); }
				$this->GetQuery("biblioteca/list/".$id,["route"=>$route]);
				if(!empty($this->theData) AND $this->http_nroerr == 200){ return $this->theData; }
			} catch (Exception $e) {
				$this->SetLog($e);
			}
			return null;
		}

		/**
		 * Summary. Crea una carpeta a una persona
		 * @param int $id El Id de la persona al cual se le creara la carpeta
		 * @param array $folder Array con la carpeta a crear y el lugar donde se creara
		 * @return bool
		 */
		public function CreateFolder(int $id, array $folder):?object{
			try{
				if(is_null(SecureInt($id))){ throw new Exception("El ID de la persona debe ser un número entero válido"); }
				$this->PostQuery("biblioteca/folder/".$id,$folder);
				if(!empty($this->theData) AND $this->http_nroerr == 200){ return $this->theData; }
			} catch (Exception $e) {
				$this->SetLog($e);
			}
			return null;
		}
		/**
		 * Summary. Realiza la subida de un archivo
		 * @param int $id El ID de la persona a la cual se subira el archivo
		 * @param array $data Los datos del archivo a subir
		 * @return bool
		 */
		public function UploadFile(int $id, array $data):bool{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El ID de la persona debe ser un número entero válido"); }
				if(!CanUseArray($data)){ throw new Exception("Debes indicar un array con archivos que se pueda utilizar"); }
				$this->PostQuery("biblioteca/".$id,$data);
				if($this->http_nroerr == 201){ return true; }
			} catch (Exception $e) {
				$this->SetLog($e);
			}
			return false;
		}
	}