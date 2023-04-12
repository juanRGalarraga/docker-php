console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');

function InitConfirmar() {
	getElem('btnNext').addEventListener('click', checkCodigo);
	
	getElem('codigo_aceptacion').addEventListener('keyup', (ele)=>{
		getElem('btnNext').disabled = (ele.target.value.trim().length == 0);
	});
	getElem('codigo_aceptacion').addEventListener('input', (ele)=>{
		getElem('btnNext').disabled = (ele.target.value.trim().length == 0);
	});
}

function checkCodigo() {
	let frm = getElem('frmAceptacion');
	let result = true;
	let ele = frm.codigo_aceptacion;
	
	if (ele.value.trim().length == 0) {
		result = ele.msgerr('Por favor escribe el código');
	}
	if (result) {
		checkCurrentStep(frm);
	}
}