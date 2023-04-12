console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');

function InitIngresarCBU() {
	getElem('btnNext').addEventListener('click', checkCBU);
}

function checkCBU() {
	let frm = getElem('frmCBU');
	let result = true;
	let ele = frm.cbu;
	
	if (ele.value.trim().length != 22) {
		result = ele.msgerr('Debes indicar CBU. Son 22 números incluyendo los ceros a la izquierda');
	}
	
	ele = frm.checkDeb;
	if (!ele.checked) {
		result = ele.msgerr('Debes autorizarnos a debitar de tu cuenta.');
	}
	if (result) {
		checkCurrentStep(frm);
	}
}