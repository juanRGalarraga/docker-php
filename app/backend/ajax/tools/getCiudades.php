<?php

require_once(DIR_model."geo".DS."class.geo.inc.php");

try {
	
	$geo = new cGeo();

	$busqueda = (isset($_GET['term']))?$_GET['term']:@$_POST['term'];
	$provincia_id = (isset($_GET['region_id']))? SecureInt($_GET['region_id']) : SecureInt(@$_POST['region_id']);

	$ciudades = $geo->GetCiudades($busqueda, $provincia_id);

	//return $ciudades;
	if (CanUseArray($ciudades)) {
		echo ''.json_encode($ciudades).'';
	} else {
		echo '[]';
	}
} catch(Exception $e) {
	EchoLog($e->getMessage());
	cLogging::Write(__FILE__ ." ".__LINE__ ." ".$e->getMessage());
}
?>