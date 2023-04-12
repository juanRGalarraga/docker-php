console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');

var tmnIdVerif;

function InitIdentidadVerificada() {
	getElem('btnNext').addEventListener('click', ()=>{ justGoToNextStep() });
	tmnIdVerif = setTimeout(()=>{
		checkCurrentStep();
	}, 5000);
}

function justGoToNextStep() {
	clearTimeout(tmnIdVerif);
	checkCurrentStep();
}
