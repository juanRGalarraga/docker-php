<?php
/**
* Summary. Devolver un listado simple apto para <select>
* Created: 2021-10-30
* Author: DriverOp
*/

require_once(DIR_model."negocios".DS."class.negocios.inc.php");

$selected = FindParam('id');
$placeholder = '<option value="%s"%s>%s</option>';

$negocios = new cNegocios;
$lista = $negocios->GetListSimple();

if (CanUseArray($lista)) {
	foreach($lista as $id => $nombre) {
		printf($placeholder, $id, ($id == $selected)?" selected":"", $nombre);
	}
}

ResponseOk();