<?php
/*
	Clase para ayudar con los listados del sistema.
	
	Update: 2020-02-10
	Author: DriverOp
	Desc: Agregada propiedad ListadoMgr para poner el nombre (identificador) de la instancia del objeto JavaScript mgrListadoCreator que maneja el listado (se usa al armar el paginador).

	Update: 2020-03-10
	Author: DriverOp
	Desc: Agregado método MakeOrderBy para armar la lista de campos para la cláusula ORDER BY.

	Update: 2020-04-27
	Author: DriverOp
	Desc: Agregada propiedad ListadoMgrExtraParam para agregar segundo parámetro al Get del onClick y reformada esa parte para escribir el HTML teniendo en cuenta esta propiedad.
	
	Update: 2020-05-15
	Author: DriverOp
	Desc:
		Método ->Replace ahora resalta la cadena buscada en varias partes de la cadena objetivo aún cuando la cadena buscada sea más de una palabra.
	
	Update: 2021-10-27
	Author: DriverOp
	Desc:
		Ahora el array del paginador es una propiedad pública del objeto ->Paginador. Esto es para que se pueda asignar desde afuera de la clase.
	
*/

class ListHelper {
	public $ordenes = null;
	public $camposorden = null;
	public $ordenClass = 'col-order';
	public $OrdenwithTitle = false;
	public $ItemsPorPagina = 25; // cuántos registros hay en la consulta.
	public $PaginaActual = 1; // el índice de la página actual.
	public $Rango = 3; // cuántos items hay que mostrar en cada página.
	public $CantExtremos = 5; // cuántas páginas mostrar al inicio y al final.
	public $ItemsTotales = 0;
	public $ListadoMgr = 'listado'; // Cómo se llama la instancia de mgrListadoCreator (JS)
	public $ListadoMgrExtraParam = null; // Qué parámetro extra pasarle a ListadoMgr
	public $flechaorder = true; //Para seleccionar en que listado mostramos la flecha de orden o no --Puede romper el diseño--
	public $buscar = null;
	public $ReplaceTag = array('<b class="red">', '</b>');
    public $ItemsActuales = 0;//Para poder armar el footer del listado utilizamos esta propiedad
	public $Paginador = [];
	
	

	function SetOrden($ord) {
		if (empty($ord)) { return $this->ordenes; }
		$campo = SecureInt(mb_substr(trim($ord),0,11),NULL);
		// ShowVar($campo,true);
		if (!is_null($campo)) {
			if (isset($this->camposorden[$campo])) {
				
				$idx = explode('>',$this->camposorden[$campo]);
				$idx = (isset($idx[1])) ? $idx[1] : $idx[0];
				if (array_key_exists($idx, $this->ordenes)) {
					$aux = ($this->ordenes[$idx] == 'DESC')?'ASC':'DESC';
					unset($this->ordenes[$idx]);
				} else {
					$aux = 'ASC';
				}
				$this->ordenes = array_reverse($this->ordenes,true);
				$this->ordenes[$idx] = $aux;
				$this->ordenes = array_reverse($this->ordenes,true);
				return $this->ordenes;
			} else {
				return NULL;
			}
		}
	}


	function Orden($indice, $title = null) {
		echo $this->ordenClass;
		$camposaux = $this->camposorden;
		foreach ($camposaux as $key => $value) {
			if(strpos($value,'>')>-1){
				$aux = explode('>',$value);
				$camposaux[$key] = $aux[1];
			}
		}

		if (isset($camposaux[$indice])) {
			$field_name = $camposaux[$indice];
			if (array_key_exists($field_name,$this->ordenes)) {
				if($this->flechaorder == true){
					if($this->ordenes[$field_name] == "ASC"){
						echo ' '.strtolower($this->ordenes[$field_name]);
					}else{
						if($this->ordenes[$field_name] == "DESC"){

							echo ' '.strtolower($this->ordenes[$field_name]);	
						}
					}

				}else{
					echo ' '.strtolower($this->ordenes[$field_name]);
				}
			}	
		}
		if ($this->OrdenwithTitle) {
			echo '" title="';
			if (!empty($title)) { echo $title.' - '; }
			echo 'Ordenar por esta columna';
		} else {
			if (!empty($title)) { echo '" title="'.$title; }
		}
	}
/*
	Esto arma el componente de paginación de los listados.
*/
	function Paginar() {
		if ($this->ItemsTotales == 0) { return false; }
		$this->Paginador = array();
		// $MedioRango = ($this->Rango-1)/2;
		$MedioRango = $this->Rango;
		$TotalDePagina = ceil($this->ItemsTotales / $this->ItemsPorPagina);
		$ContPuntos = false;
		for ($p = 1; $p <= $TotalDePagina; $p++) {
			// do we display a link for this page or not?
			if ($p == 1 and ($TotalDePagina > 1)) {
				if (($this->PaginaActual-1) > 1) {
					$this->Paginador[] = array("p"=>$this->PaginaActual-1,"act"=>false,"arr"=>"prev");
				}
			}
			if (
				($p <= $this->CantExtremos) or
				($p > ($TotalDePagina - $this->CantExtremos)) or
				(($p >= $this->PaginaActual - $MedioRango) and ($p <= $this->PaginaActual + $MedioRango)) or
				($p == $this->CantExtremos + 1 and $p == $this->PaginaActual - $MedioRango - 1) or
				($p == $TotalDePagina - $this->CantExtremos and $p == $this->PaginaActual + $MedioRango + 1 )
				)
				{
				$ContPuntos = false;
				if ($p == $this->PaginaActual) {
						$this->Paginador[] = array("p"=>$p, "act"=>true);
				} else {
					$this->Paginador[] = array("p"=>$p, "act"=>false);
				}
				echo "\n";
			// if not, have we already shown the elipses? 
			} elseif ($ContPuntos == false) { 
				$this->Paginador[] = array("p"=>null, "act"=>false);
				$ContPuntos=true; // make sure we only show it once
			}
			if ($p == $TotalDePagina and ($TotalDePagina > 1)) {
				if (($this->PaginaActual+1) < $TotalDePagina) {
					$this->Paginador[] = array("p"=>$this->PaginaActual+1,"act"=>false,"arr"=>"next");
				}
			}
		}
		return $this->Paginador;
	}
/**
* Summary. Escribe los tags de paginación directamente.
*/
	public function MostrarPaginacion() {
		if (!CanUseArray($this->Paginador)) { return; }
		?><nav aria-label="Page navigation"><ul class="pagination"><?php
		
			$onClick = '';
			foreach($this->Paginador as $value) {
				if (!empty($this->ListadoMgr)) {
					$onClick = sprintf('%s.Get(%s%s);return false;',$this->ListadoMgr,"{name:'pag',value:".$value['p']."}",((!empty($this->ListadoMgrExtraParam))?",".$this->ListadoMgrExtraParam:""));
				}				
			?><li class="p-1 page-item<?php echo ($value['act'])?' active':'';?>"><?php
				if (empty($value['p'])) {
				?><span class="page-link">&hellip;</span><?php
				} else {
					if ($value['act']) {
						?><span class="page-link active"><?php echo $value['p']; ?></span><?php
					} else {
						if (isset($value['arr'])) {
							if ($value['arr'] == 'prev') {
								?><a class="page-link active-link" href="#!" aria-label="Anterior" onClick="<?php echo $onClick; ?>" data-page="<?php echo $value['p']; ?>"><span aria-hidden="true">&laquo;</span><span class="sr-only">Anterior</span></a><?php
							} else {
								?><a class="page-link active-link" href="#!" aria-label="Siguiente" onClick="<?php echo $onClick; ?>" data-page="<?php echo $value['p']; ?>"><span aria-hidden="true">&raquo;</span><span class="sr-only">Siguiente</span></a><?php
							}
						} else {
							?><a class="page-link active-link" href="#!" title="Ir a página <?php echo $value['p']; ?>" onClick="<?php echo $onClick; ?>" data-page="<?php echo $value['p']; ?>"><?php echo $value['p']; ?></a><?php
						}
					}
				}
				?></li><?php
			}
		?></ul></nav><?php
	}  // MostrarPaginacion
/**
* Summary. Resalta una cadena encerrándo la parte coincidente con el tag indicado.
* @param string $string La cadena objetivo.
* @param string $buscar optional null La cadena buscada para resaltar dentro de $str. Si es null o no se pasa, se usa la propiedad ->buscar de la clase.
*/
	public function Replace($string, $buscar = null) {
		$salida = $string;
		$buscar = (is_null($buscar))?$this->buscar:$buscar;
		if (!empty($buscar)) {
			$buscar = mb_strtolower($buscar);
			// Convertir en array y escapar los caracteres que son significativos en una expresión regular.
			$buscar = array_map("filter_preg", array_filter(explode(' ',$buscar)));
			$work = array_filter(explode(' ',$string));
			$salida = array();
			foreach ($work as $str) {
				reset($buscar);
				foreach($buscar as $b) {
					$str = mb_ereg_replace('('.$b.')','[tag]\\1[/tag]',$str,'i');
					if (!isset($this->ReplaceTag)) {
						$str = str_replace(array('[tag]','[/tag]'), array('<b>','</b>'),$str);
					} else {
						$str = str_replace(array('[tag]','[/tag]'), $this->ReplaceTag,$str);
					}
				}
				$salida[] = $str;
			}
			$salida = implode(' ',$salida);
		}
		return $salida;
	} // Replace

/**
* Summary. Devuelve la lista de campos para ser usuados en la cláusula ORDER BY.
* @param array $ordenes es el array que tiene el nombre de los campos que deben incluirse en la cláusula ORDER BY. Tiene en cuenta que cada campo puede estar en una tabla diferente.
*/
	public function MakeOrderBy($ordenes) {
		$result = '';
		foreach ($ordenes as $campo => $orden) {
			$p = explode('>',$campo);
			if (count($p) > 1) {
				$result .= SQLQuote($p[0]).".".SQLQuote($p[1])." ".$orden.",";
			} else {
				$result .= SQLQuote($campo)." ".$orden.",";
			}
		}


		$result[strlen($result)-1] = " ";
		return $result;
	}

	/**
	 * Summary. Realiza el footer de un listado
	 */
	public function Footer(){
		?>
		<div class="row">
			<div class="col-12 col-sm-10 d-flex justify-content-sm-start justify-content-center">
				<?php
					if(!CanUseArray($this->Paginador)){
						$this->Paginar();
					}
					$this->MostrarPaginacion();
				?>
			</div>
			<div class="col-12 text-center col-sm-2 text-sm-end">
				<p><small><?php echo ((($this->PaginaActual - 1) * $this->ItemsPorPagina) + 1) . " - " . (((($this->PaginaActual - 1) * $this->ItemsPorPagina)) + $this->ItemsActuales); ?> / <span title="Total de registros en esta consulta"><?php echo $this->ItemsTotales; ?></span></small></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Summary. Dado un resultado provisto por el core asigna los elementos del resultado a propiedades del objeto para ser utilizados
	 * @param array|object $data El objeto-array del cual se extraeran las propiedades
	 */
	public function SetPropeties($data):void{
		$data = (is_array($data))? (object)$data:$data;
		$orden = $data->header->fields ?? array();
		$currentOrder = $data->header->currentOrd ?? array();
		$buscar = $data->header->currentSearch ?? "";
		$this->camposorden = (array)$orden;
		$this->ordenes = (array)$currentOrder;
		$this->Paginador = $data->Paginador ?? $this->Paginador;
		$this->ItemsPorPagina = $data->ItemsPorPagina ?? $this->ItemsPorPagina;
		$this->PaginaActual = $data->PaginaActual ?? $this->PaginaActual;
		$this->ItemsTotales = $data->ItemsTotales ?? $this->ItemsTotales;
		$this->ItemsActuales = $data->ItemsActuales ?? $this->ItemsActuales;
		$this->buscar = $buscar;
	}
} // class