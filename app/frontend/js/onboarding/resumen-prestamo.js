console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');

function InitResumenPrestamo() {
	if (calculadora && (calculadora instanceof objCalculadora)) {
		calculadora.addResponseListener(function (respuesta, conError) {
			if (!conError) {
				getElem('conf-total').innerHTML = respuesta.Total??'--';
				getElem('conf-capital').innerHTML = respuesta.Capital??'--';
				getElem('conf-periodo').innerHTML = respuesta.Dias??'--';
				getElem('conf-fecha_pago_txt').innerHTML = respuesta.Fecha_Pago_Display??'--';
				getElem('conf-intereses').innerHTML = respuesta.Intereses??'--';
				getElem('conf-gastos_administrativos').innerHTML = respuesta.Gastos_Administrativos??'--';
			}
		})
	}
	getElem('btnNext').addEventListener('click', checkCurrentStep);
}