<?php if (DEVELOPE): ?>
console.log('Step <?php echo pathinfo(__FILE__, PATHINFO_FILENAME); ?> loaded.');
<?php endif; ?>

function InitDatosPersonales() {
	getElem('btnNext').addEventListener('click', ()=>{ checkCurrentStep(); });
}