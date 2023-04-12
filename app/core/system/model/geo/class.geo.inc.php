<?php
/*
	Manejo de las entidades geográficas.
	Esta clase devuelve objetos stdClass con los datos del registro leído.
	
	Modif: 2020-11-15
	Desc:
		Eliminado método MakeObject sustituido por ParseRecord del ancestro.

	Modif: 2021-02-18
	Desc:
		Reparado bug al convertir array en object usando json_decode(json_encode()).

	Modif: 2022-10-22
	Desc: 
		Refactorizada toda la clase y adaptada a la versión 2.0 del framework
	Author: Juan Galarraga
*/

require_once(DIR_model."class.fundation.inc.php");

class cGeo extends cModels {
	public $tabla_ciudades = "";
	public $tabla_regiones = "";
	public $tabla_paises = "";

	function __construct(){
		parent::__construct();
		$this->tabla_ciudades = SQLQuote(TBL_ciudades);
		$this->tabla_regiones = SQLQuote(TBL_regiones);
		$this->tabla_paises = SQLQuote(TBL_paises);
	}

	/**
	 * Summary.
	 * Busca una ciudad junto con su región y país mediante el ID de la ciudad
	 * @param int $id ID de la ciudad
	 * @return object El registro obtenido
	 */
	
	public function GetCiudad(int $id) :? object {
		$this->sql = "SELECT
		`ciudades`.`id`,
		`regiones`.`id` AS `region_id`,
		`paises`.`id` AS `pais_id`,
		`ciudades`.`cp`,
		`ciudades`.`nombre` AS `nombre_ciudad`,
		`regiones`.`nombre` AS `nombre_region`,
		`paises`.`nombre_pais`,
		`regiones`.`orden` AS `orden_region`,
		`regiones`.`mostrar` AS `mostrar_region`,
		`regiones`.`deptos`,
		`regiones`.`denomdeptos` AS `denominacion_departamentos`,
		`regiones`.`codigo_interno`,
		`paises`.`isonum`,
		`paises`.`iso2`,
		`paises`.`iso3`,
		`paises`.`orden` AS `orden_pais`,
		`paises`.`mostrar` AS `mostrar_pais`,
		`paises`.`denomreg` AS `denominacion_regiones`
		FROM $this->tabla_ciudades AS `ciudades`, $this->tabla_regiones AS `regiones`, $this->tabla_paises AS `paises`
		WHERE `ciudades`.`id` = $id AND  `regiones`.`id` = `ciudades`.`region_id` AND `paises`.`id` = `regiones`.`pais_id`";
		return parent::Get();
	} // GetCiudad

	/**
	 * Summary.
	 * Obtiene una región junto con su país mediante el ID de la región
	 * @param int $id ID de la región
	 * @return object El registro obtenido
	 */
	
	public function GetRegion(int $id) :? object {
		$this->sql = "SELECT `regiones`.`id`,
		`paises`.`id` AS `pais_id`,
		`regiones`.`nombre` AS `nombre_region`,
		`paises`.`nombre_pais`,
		`regiones`.`orden` AS `orden_region`,
		`regiones`.`mostrar` AS `mostrar_region`,
		`regiones`.`deptos`,
		`regiones`.`denomdeptos` AS `denominacion_departamentos`,
		`paises`.`isonum`,
		`paises`.`iso2`,
		`paises`.`iso3`,
		`paises`.`orden` AS `orden_pais`,
		`paises`.`mostrar` AS `mostrar_pais`,
		`paises`.`denomreg` AS `denominacion_regiones`
		FROM $this->tabla_regiones AS `regiones`, $this->tabla_paises AS `paises`
		WHERE `regiones`.`id` = $id AND `paises`.`id` = `regiones`.`pais_id`";

		return parent::Get();
	} // GetRegion

	/**
	 * Summary.
	 * Obtiene un país mediante su ID
	 * @param int $id - ID del país
	 * @return object|null El registro obtenido o null en caso contrario
	 */

	public function GetPais(int $id) :? object {
		$this->sql = "SELECT
		`paises`.`id`,
		`paises`.`nombre_pais`,
		`paises`.`isonum`,
		`paises`.`iso2`,
		`paises`.`iso3`,
		`paises`.`orden` AS `orden_pais`,
		`paises`.`mostrar` AS `mostrar_pais`,
		`paises`.`denomreg` AS `denominacion_regiones`
		FROM $this->tabla_paises AS `paises`
		WHERE `paises`.`id` = $id";
		return parent::Get();
	} // GetPais

	/**
	* Summary. Devuelve una lista con las ciudades que contienen un lexema.
	* @param string $busqueda El lexema buscado.
	* @param int $region_id El id de la región a filtrar.
	* @return array of object.
	*/
	public function GetCiudades(string $busqueda, int $region_id = NULL) {
		$result = null;
			try {
				
				$busqueda = $this->RealEscape($busqueda);
				$sql = "SELECT `ciudad`.`id`, `ciudad`.`nombre`, `regiones`.`nombre` AS `nombre_region` FROM $this->tabla_ciudades AS `ciudad` LEFT JOIN $this->tabla_regiones AS `regiones` ON `regiones`.`id` = `ciudad`.`region_id` WHERE `ciudad`.`nombre` LIKE '%".$busqueda."%' " ;
				if (!is_null($region_id)) {
					$region_id = $this->RealEscape($region_id);
					$sql .= "AND `ciudad`.`region_id` = ".$region_id." ";
				}
				$sql .= " ORDER BY CASE WHEN ciudad.nombre LIKE '".$busqueda."%' THEN CONCAT(' ',ciudad.nombre) ELSE ciudad.nombre END ASC ";
				$sql .= "LIMIT 50";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$result = array();
					do {
						$aux = new stdClass;
						$aux->id = $fila->id;
						$aux->label = $fila->nombre.' - '.$fila->nombre_region;
						$aux->value = $fila->nombre;
						array_push($result,$aux);
					} while($fila = $this->Next());
				}
			} catch(Exception $e) {
				$this->SetError(__METHOD__,$e->getMessage());
			}		
		return $result;
	}

} // cGeo
?>