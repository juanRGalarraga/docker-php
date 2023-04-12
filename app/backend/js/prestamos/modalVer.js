var modalPrestamo = new modalBs5Creator({
	archivo: 'modalPrestamo',
	content: 'prestamos',
	centered: true
});

function VerPrestamo(id){
	modalPrestamo.Show({id:id});
}