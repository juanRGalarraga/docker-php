

var modal = new modalBs5Creator({
	archivo: 'modaldeprueba',
	content: 'tests',
	size: 'auto',
	onClose: function (win) {
		console.log(win.id);
	}
});

function PonerModal() {
	modal.Show();
}