<?php
include(DIR_js."rbtMicroAjax.1.0.js");
include(DIR_js."rbtMsgerr.1.0.js");
include(DIR_js."rbtEvalResult.2.1.js");
include(DIR_js."alerta.js");
?>
$(function(){$('[data-toggle="tooltip"]').tooltip();});

/*
var evalResultLogin = new mgrEvalResult({
	silent: true,
	onError: function () {
		setTimeout(function () {
			document.getElementById('msg-error').classList.add('d-none');
		},3000);
		document.getElementById('msg-error').classList.remove('d-none');
	}
});
*/
//var rulo = new fmdRulo();
var evalResult = new rbtEvalResult();


document.addEventListener("DOMContentLoaded", function () {
	let frm = document.getElementById('loginform');
	frm.addEventListener('keydown', function (e) {
		var code = e.keyCode || e.which;
		if(code == 13) {
			CheckLogin();
		}	
	});
});


function CheckLogin() {
	var result = true,
		frm = document.getElementById('loginform'),
		ele = frm.username;
	
	if (ele.value.trim().length == 0) {
		result = ele.msgerr('Ingresá tu nombre de usuario.');
	}
	ele = frm.password;
	if (ele.value.trim().length == 0) {
		result = ele.msgerr('Ingresá tu contraseña.');
	}

	if (result) {
		getAjax({
			archivo: 'checkLogin',
			username: frm.username.value,
			password: frm.password.value
		}, function (a,b,c,d,e) {
			Debug(c);
			if (!e) {
				window.location.href = '<?php echo BASE_URL; ?>';
			}
			
		});
	}
}