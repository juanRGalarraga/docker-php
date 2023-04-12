
<?php
require_once(DIR_js."rbtAutocomplete.js");
?>
;

var autocompleter = new rbtAutocomplete(null, {
		source: '<?php echo URL_ajax; ?>',
		archivo: 'getCiudades',
		content: 'tools',
		onFetch: function () {
			autocompleter.ExtraData({
				region_id: getElem('region_id').value.trim()
			});
		},
		onSelect: function (li, data) {
			console.log(data);
		}
	});


document.addEventListener('DOMContentLoaded',()=>{
	autocompleter.setTarget(getElem('inputTarget'));
});