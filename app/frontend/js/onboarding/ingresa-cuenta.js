<?php

?>
console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');

getElem('btnIrMiCuenta').addEventListener('click', irMiCuenta);
getElem('btnNext').addEventListener('click', ()=>{ checkCurrentStep(); });

function InitIngresaCuenta() {
	
}

function irMiCuenta() {
	let username = getElem('username');
	let password = getElem('password');
	let result = true;
	if (username.value.trim().length == 0) {
		result = username.msgerr('Debes ingresar tu DNI');
	}
	if (password.value.trim().length == 0) {
		result = password.msgerr('Debes ingresar la contraseña');
	}
}