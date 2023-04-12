<?php
require_once(DIR_model."planes".DS."class.planes.inc.php");


$id = FindParam('id');
$p = new cPlanes();
$plan = $p->GetAll($id);

if (!$plan) {
	EmitJSON('Plan no encontrado.'); return;
}
$tipos_pagos = $p->GetTiposPagos();
require_once(DIR_site."planes".DS."newPlanes.htm");
// ShowVar($tipos_pagos);
// ShowVar($plan);