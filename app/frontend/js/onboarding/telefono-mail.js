console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');


function InitTelMail() {
	getElem('btnNext').addEventListener('click', checkTelMail);
}

function checkTelMail() {
	let frm = getElem('frmTelefonoMail');
	let result = true;
	frm.telcod.value = frm.telcod.value.trim();
	frm.telnum.value = frm.telnum.value.trim();
	frm.email.value = frm.email.value.trim();
	let ele = frm.telcod;
	if (ele.value.length == 0) {
		result = ele.msgerr('Ingresa código de área (sin el cero)');
	}
	ele = frm.telnum;
	if (ele.value.length == 0) {
		result = ele.msgerr('Ingresa número (sin el 15)');
	}
	if (result && (frm.telcod.value.length > 0) && (frm.telnum.value.length > 0)) {
		let telcod = frm.telcod;
		let telnum = frm.telnum;
		if (telcod.value.length == 1) { result = telcod.msgerr('Deben ser al menos dos números'); }
		if (telcod.value.length > 4) { result = telcod.msgerr('No más de cuatro números'); }
		if (telnum.value.length < 6) { result = telnum.msgerr('Deben ser al menos seis números'); }
		if (telnum.value.length > 8) { result = telnum.msgerr('No más de ocho números'); }
		if (result) {
			switch (telcod.value.length) {
				case 2: result = (telnum.value.length != 8)?telnum.msgerr('Deben ser ocho números'):result; break;
				case 3: result = (telnum.value.length != 7)?telnum.msgerr('Deben ser siete números'):result; break;
				case 4: result = (telnum.value.length != 6)?telnum.msgerr('Deben ser seis números'):result; break;
			}
		}
	}
	ele = frm.email;
	if (ele.value.length == 0) {
		result = ele.msgerr('Debes indicar dirección de correo electrónico');
	}
	if (result) {
		checkCurrentStep(frm);
	}
}