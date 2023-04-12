<?php
/*
	Clase para mostrar avisos estandarizados en el sitio.
	Esta es una evolución sobre el método estático ::ShowWarning que está en cSideKick
	
	Created: 2021-04-24
	Author: DriverOp
	
*/

class cShowWarning {
	
	public $msg = null; // El mensaje a ser mostrado.
	public $plantilla = "default_warning"; // El nombre del archivo de plantilla (sin la extension).
	private $content = null; // El contenido "raw" de la plantilla.
	
/**
* Summary. Muestra efectivamente el aviso.
* @param string $msg optional default null El mensaje que será reemplazado en el placeholder del template. Si es null, se usa el valor de la propiedad ->msg
*/
	public function Show(string $msg = null) {
		$plantilla = DIR_plantillas.$this->plantilla.'.htm';
		if (!empty($msg)) { $this->msg = $msg; }
		if (ExisteArchivo($plantilla)) {
			$this->content = file_get_contents($plantilla);
			echo $this->ProcessMessage();
		} else {
			$content = '<p>'.$this->msg.'</p>';
		}
	}
	
/**
* Summary. Procesa el mensaje para ponerlo en la plantilla.
*/
	private function ProcessMessage() {
		$result = $this->content;
		if (is_array($this->msg)) {
			$this->msg = array_change_key_case($this->msg);
			foreach($this->msg as $key => $value) {
				$result = str_replace('['.$key.']', nl2br($value), $result);
			}
		}
		if (is_string($this->msg)) {
			$result = str_replace('[mensaje]',nl2br($this->msg),$result);
		}
		if (is_numeric($this->msg)) {
			if (is_float($this->msg)) {
				$result = str_replace('[mensaje]', number_format($this->msg, 2, ',', '.'), $result);
			} else {
				$result = str_replace('[mensaje]', number_format($this->msg, 0, ',', '.'), $result);
			}
		}
		$result = $this->ReplaceImgs($result);
		return $result;
	}
/**
* Summary. Busca entre el texto el tag img y reemplaza el atributo src por la referencia correcta a la imagen.
* @param string $texto El texto donde buscar y reemplazar.
*/
	private function ReplaceImgs($texto) {
		$texto = preg_replace('/src\="?/is','src="'.URL_imgs,$texto);
		return $texto;
	}
}

$showWarning = new cShowWarning();