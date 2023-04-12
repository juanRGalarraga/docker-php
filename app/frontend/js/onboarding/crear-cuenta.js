<?php

?>
console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');


function InitCrearCuenta() {
	getElem('btnNext').addEventListener('click', checkCredentials);
}

function checkCredentials() {
	let frm = getElem('frmCrearCuenta');
	let result = true;
	let ele = frm.nro_doc;

	if (ele.value.trim().length == 0) {
		result = ele.msgerr('Ingresa tu DNI');
	}
	ele = frm.password;
	if (ele.value.trim().length == 0) {
		result = ele.msgerr('Debes indicar una contraseña');
	}
	ele = frm.checkTyc;
	if (!ele.checked) {
		result = ele.msgerr('Debes aceptar los términos y condiciones');
	}
	ele = frm.checkPdP;
	if (!ele.checked) {
		result = ele.msgerr('Debes aceptar la política de privacidad');
	}
	ele = frm.checkAuth;
	if (!ele.checked) {
		result = ele.msgerr('Debes autorizarnos a transferirte el dinero');
	}

	if (result) {
		checkCurrentStep(frm);
	}
}