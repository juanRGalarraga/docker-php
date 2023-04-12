<?php
require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php");
$this_file = substr(__FILE__,strlen(DIR_BASE))." ";

$usr = new cUsrBackend();

$user_id = SecureInt(substr(trim(@$_REQUEST['user_id']),0,11),NULL);
if (is_null($user_id)) {
	if (!$usr->CheckLogin()) {
		echo '<json>{"quit":"false","msg":"No user selected"}</json>';
		return;
	}
} else {
	if (!$usr->Get($user_id)) {
		echo '<json>{"quit":"false","msg":"User not found"}</json>';
		return;
	}
}

$sql = "SELECT * FROM ".SQLQuote(TBL_backend_sesiones)." WHERE `usuario_id` = ".$usr->id." AND `estado` = 1 ORDER BY `sys_fecha_alta` DESC LIMIT 1;";

$objeto_db->Query($sql);

if ($fila = $objeto_db->First()) {
	$time_left = (Date('U') - $fila['idle']);
	if ($time_left > $usr->opciones->tsession) {
		echo '<json>{"quit":"true","msg":"User has no time left"}</json>';
		return;
	} else {
		echo '<json>{"quit":"false","time_left":'.($usr->opciones->tsession-$time_left).'}</json>';
		return;
	}
} else {
	echo '<json>{"quit":"false","msg":"User is not logged in"}</json>';
	return;
}
?>