<?php
	if (DEVELOPE) {
?>
var bCtrl = false;
var bAlt = false;
document.addEventListener("DOMContentLoaded", function () {
	document.addEventListener("keydown", function(e){
		if(e.keyCode == 18){
			bAlt = true;
		}
		if(e.keyCode == 17){
			bCtrl = true;
		}else{
			if(bCtrl && e.keyCode == 83){
				e.preventDefault();
			}
		}
	}, false);
	document.addEventListener("keyup", function(e){
		if(e.keyCode == 17){
			bCtrl = false;
		}
		if(e.keyCode == 18){
			bAlt = false;
		}
		if(bCtrl && e.keyCode == 121){ // F10
			new modalBsLTECreator({
				archivo: 'watchAll',
				content: 'watch'
			}).Show();
		}
		if(bAlt && e.keyCode == 113){ // F2
			new modalBsLTECreator({
				archivo: 'watchAll',
				content: 'watch',
				extraparams: {what: 'unset'}
			}).Show();
		}
	}, false);
});

<?php
	}
?>
