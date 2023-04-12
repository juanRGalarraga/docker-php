<?php
	@session_start();
	$objeto_usuario->Logout();
	unset($_SESSION);
	session_destroy();
	header('Location: '.BASE_URL)
?>