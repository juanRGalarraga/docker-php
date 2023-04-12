console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');
getElem('mainOnBoarding').querySelectorAll('.obBtnCallToAction').forEach(
	(ele)=>{
		ele.addEventListener('click',
			()=>{
				sendSolicitaloAhora();
			}
	)}
);

function sendSolicitaloAhora() {
	/*
		Poner las validaciones del paso, acá.
	*/
	checkCurrentStep();
}