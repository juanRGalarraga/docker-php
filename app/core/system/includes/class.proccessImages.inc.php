<?php
/**
*	Clase estatica para el procesamiento de imagenes
*	Created: 2021-11-26
*	Author: Gastón Fernandez
*/
	require_once(DIR_includes."class.sidekick.inc.php");
	require_once(DIR_includes."core_constants.inc.php");
	require_once(DIR_includes."common.inc.php");
	class cImages{
		public static $msgErr = null;
		public static $dataErr = null;
		public static $maxImageName = 32;//Cantidad máxima de caracteres permitidos en el nombre de una imagen
		public static $rootDirectory = DIR_biblioteca;//Directorio en el que se guardaran las imagenes, además del directorio dado en las funciones
		
		/**
		*	Summary. Dado el binario de una imagen la guarda en la carpeta de library con su correspondiente nombre dado en $name. Usado para productos, marcas y modelos
		*	@param binary $data El binary de la imagen a guardar
		*	@param string $dir El directorio donde seguardara dentro de la carpeta library
		*	@param string $name El nombre de la imagen
		*	@return bool|string La ruta dentro de la carpeta library donde se guardo la imagen
		*/
		public static function SaveImg($data,$dir, $name = "imagen.jpeg"):?string{
			try{
				if(empty($data) or empty($name)){ return false; }
				if(!$ext = strtolower(ExtraerExtension($name))){ throw new Exception("Nombre de imagen no es un archivo"); }
				if(!in_array($ext,EXTENSIONES_IMAGENES)){ throw new Exception("La extension ".$ext." no se encuentra dentro de las permitidas para las imagenes"); }
				$dir = str_replace(["/","\\"],DS,$dir);
				$targetDir = EnsureTailingSlash(self::$rootDirectory.$dir);
				
				//No se pudo crear el directorio destino...
				if(!cSidekick::EnsureDirExists($targetDir)){ throw new Exception("No se pudo comprobar la exitencia del directorio: ".$targetDir); }
				$targetDir = $targetDir.$name;
				
				if(!$tmpName = self::ConvertToWebp($targetDir,$data)){
					//Si falla pues, guardamos la imagen como sin convertir nada...
					if(!file_put_contents($targetDir,$data)){ throw new Exception("No se pudo guardar la imagen"); }
				}
				if($tmpName AND $tmpName != $targetDir){
					$targetDir = $tmpName;
				}
				return str_replace(self::$rootDirectory,"",$targetDir);
			}catch(Exception $e){
				cLogging::Write(__FILE__." ".__LINE__." ".$e->getMessage());
				$msgErr = $e->getMessage();
			}
			return null;
		}

		/**
		*	Summary. Dado el Binario de una imagen la convierte a webp y la guarda en la carpeta indicada
		*	@param string $dir El directorio donde se guardara(incluyendo el nombre de la imagen, no es necesario que el nombre finalice con .webp)
		*	@param binary $data
		*	@return null|string Directorio de guardado de la imagen
		*/
		private static function ConvertToWebp($dir,$data){
			try{
				if(!$imagen = imagecreatefromstring($data)){ throw new Exception("No se pudo crear una imagen en base al binario dado"); }
				if(!imagepalettetotruecolor($imagen)){ throw new Exception("No se pudo obtener el color verdadero de la imagen"); }
				if(!imagealphablending($imagen, true)){ throw new Exception("No se pudo usar el alpha en la imagen"); }
				if(!imagesavealpha($imagen, true)){ throw new Exception("No se pudo activar el guardado de alpha en la imagen"); }
				$tmpName = preg_replace("/\..+$/",".webp",$dir);
				if(!imagewebp($imagen,$tmpName)){ throw new Exception("No se pudo guardar la imagen final en webp"); }
				return $tmpName;
			}catch(Exception $e){
				cLogging::Write(__FILE__." ".__LINE__." ".$e->getMessage());
				$msgErr = $e->getMessage();
			}
			return null;
		}
		
		/**
		*	Summary. Dado un nombre y un base64 de una imagen intenta crearla y guardarla en un directorio, devuelve un array con los datos de la imagen creada (nombre y ruta)
		*	@param string $nombre El nombre de la imagen
		*	@param string|binary $data Base64 de la imagen o el binario
		*	@param string $dir El nombre del directorio donde se creara la imagen
		*	@return null|array
		*/
		public static function CreateImage(string $nombre,$data,string $dirName):?array{
			try{
				if (empty($nombre)){ throw new Exception("El nombre de la imagen esta vacío"); }
				if (empty($data)){ throw new Exception("Datos con los que crear la imagen vacíos"); }
				if (empty($dirName)){ throw new Exception("El nombre del directorio esta vacío"); }
				if (!$nombre = ProccessFileName($nombre,self::$maxImageName)){ throw new Exception("No se pudo procesar el nombre de la imagen"); }
				if (is_base64($data)){ $data = base64_decode($data,true); }
				if ($imgFile = self::SaveImg($data,$dirName,$nombre)) { return array('archivo'=>$imgFile,'nombre'=>pathinfo($imgFile)['basename'] ?? $nombre); }
			}catch(Exception $e){
				cLogging::Write(__FILE__." ".__LINE__." ".$e->getMessage());
				$msgErr = $e->getMessage();
			}
			return null;
			
		}
	}