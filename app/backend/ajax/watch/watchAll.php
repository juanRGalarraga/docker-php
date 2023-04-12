<?php
defined("USR_SESSION_NAME") || define("USR_SESSION_NAME", "usr_backend");
?>
<!-- <json>{"ok":"ok"}</json> -->
<div class="modal-header">
	<h4 class="modal-title" id="Label%winid%">Watch</h4>
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php EchoLang("Cerrar", "Cerrar"); ?></span></button>
</div>
<div class="modal-body">
<?php
if (isset($_POST['what'])) {
	if (strtolower($_POST['what']) == 'user') {
		ShowVar($objeto_usuario);
	}
	if (strtolower($_POST['what']) == 'unset') {
		unset($_SESSION[USR_SESSION_NAME]);
	}
}
ShowVar($_SESSION);
ShowVar($_COOKIE);
?>
</div>
<div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
</div>
