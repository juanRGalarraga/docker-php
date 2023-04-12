<?php
/**
*	Maneja los filtros de mora de los préstamos
*	Created: 2021-11-05
*	Author: Gastón Fernandez
*/

	class cFiltrosMoras extends cModels{
		private $tabla = TBL_filtros_mora;
		public $id = null;
		function __construct(){
			parent::__construct();
			$this->mainTable = $this->tabla;
		}
		
		/**
		*	Summary. Obtiene todos los filtros de mora
		*	return null|object
		*/
		public function GetAllFilters():?array{
			$sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE `estado`='HAB'";
			$this->Query($sql);
			$record = $this->GetAllRecords();
			return (empty($record))? null:$record;
		}
		
		/**
         * Summary. Obtiene un filtro dado su ID
         * @param int $id El ID del filtro a obtener
         * @return object
        */
        public function Get(int $id = null):?object {
			try {
				if (empty($id)) { $id = $this->id??null; }
				if (empty($id)) { throw new Exception("No se indicó ID."); }
				$this->sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE `id` = ".$id;
				return parent::Get();
			} catch(Exception $e) {
				$this->SetError($e);
			}
			return null;
        }
		
		/**
		*	Summary. Obtiene la consulta a utilizar para el filtro
		*	@param null|string $table El nombre de la tabla a utilizar en los campos
		*	@return null|string
		*/
		public function GetFilterQuery($table):?string{
			$cond = $this->data ?? null;
			$moraCond = "";
			if(!CanUseArray($cond)){ return null; }
			foreach($cond as $value){
				if(!isset($value->field) OR !isset($value->cond) OR !isset($value->value)){ continue; }
				$valor = trim($value->value);
				if($valor[0] != "$" AND $valor[0] != "("){
					$tmp = SecureInt($valor);
					if(is_null($tmp)){
						$tmp = $valor;
						$tmp = ($tmp[0] == "'")? $tmp:"'".$tmp;
						$tmp = ($tmp[strlen($tmp)-1] == "'")? $tmp:$tmp."'";
					}
					$valor = $tmp;
				}else if($valor[0] != "("){
					$valor = mb_substr($valor,1);
				}
				
				$campo = trim($value->field);
				if($campo[0] != "$"){
					$campo = SQLQuote($campo);
				}else{
					$campo = mb_substr($campo,1);
				}
				
				$moraCond .= (empty($moraCond))? "":" AND ";
				$moraCond .= $campo." ".$value->cond." ".$valor;
				$moraCond = str_replace("`dias_mora`","DATEDIFF(NOW(),`fecha_vencimiento`)",$moraCond);
			}
			
			if(!empty($table)){
				$moraCond = preg_replace("/(`[\S]+`)+/i", SQLQuote($table).".$1",$moraCond);
			}
			return $moraCond;
		}
	}