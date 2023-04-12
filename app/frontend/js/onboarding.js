<?php
/*
	Código JS del onboarding/solicitud de préstamo.
*/
?>

document.addEventListener('DOMContentLoaded', ()=>{
	collectStep();
	if (typeof HTMLElement.prototype.msgerr != 'function') {
		new jsLoader('rbtMsgerr.2.0');
	}
});

function collectStep(execJS) {
<?php echo (DEVELOPE)?"\tconsole.log('loadNextStep: ',[].slice.call(arguments));":null; ?>
	InitCalculadora();
	let json = getElem('mainOnBoarding').querySelector('json.json');
	if (json) {
		let b = JSON.parse(atob(json.innerText));
		sessionStorage.setItem('token',b.token);
		if (undefined !== b.js && b.js != '') {
			new jsLoader('onboarding/'+b.js,{mode:'c',fncCallBack:b.callback});
		}
		if (undefined !== b.css && b.css != '') {
			new cssLoader('onboarding/'+b.css,{mode:'c'});
		}
	}
	if (getElem('btnBack')) {
		getElem('btnBack').addEventListener('click',()=>{checkCurrentStep({prev:true});});
	}
	if (execJS) {
		let inlinejs = getElem('mainOnBoarding').querySelectorAll('script.inlinejs');
		if (inlinejs) {
			inlinejs.forEach((ele)=>{ eval(ele.innerHTML);});
		}
	}
}

function checkCurrentStep(param) {
<?php echo (DEVELOPE)?"\tconsole.log('checkCurrentStep: ',[].slice.call(arguments));":null; ?>
	rulo.Show('mainOnBoarding');
	let requestParams = {
		archivo: 'onboarding',
		content: 'onboarding',
		token: sessionStorage.getItem('token')
	}
	requestParams.data = sessionStorage.getObject('cotiz');
	if (param instanceof HTMLFormElement) {
		let formData = new FormData(param);
		let entries = {};
		for (var value of formData.entries()) {
			entries[value[0]] = value[1];
		}
		requestParams.data = Object.assign({}, requestParams.data, entries);
	} else {
		requestParams.data = Object.assign({}, requestParams.data, param);
	}
	requestParams.data = JSON.stringify(requestParams.data);
	getAjax(requestParams, function(a,b,c,d) {
		rulo.Hide();
		if (a == 200 && d) {
			if (undefined !== d.ok && d.ok !== null) {
				if (undefined !== d.restart && d.restart) {
					sessionStorage.clear();
				}
				return loadNextStep(c); 
			}
			if (undefined !== d.dataerr && d.dataerr !== null) {
				for (let e in d.dataerr) {
					if (getElem(e)) {
						getElem(e).msgerr(d.dataerr[e]);
						getElem(e).genMessage({msg:d.dataerr[e],class:'generic-message'});
					}
				}
			}
			if (undefined !== d.generr && d.generr !== null && (typeof Alerta == 'function')) {
				Alerta({texto:d.generr,posicion:'CC',iconoAlerta:true});
			}
		}
	});
}

function loadNextStep(content) {
<?php echo (DEVELOPE)?"\tconsole.log('loadNextStep: ',[].slice.call(arguments));":null; ?>
	getElem('mainOnBoarding').innerHTML = content;
	setTimeout(()=>{collectStep(true)},200);
}

function irAMiCuenta() {
	window.location.href = '<?php echo URL_micuenta; ?>';
}