var theRulo = new rbtRulo;
var evalResult = new rbtEvalResult;
let slider = null;

var listado = new mgrListadoCreator({
    archivo: 'listado',
    content: 'clientes',
    targetElementIdName: 'listado_clientes',
    rulo: theRulo
});

window.addEventListener('load', ()=>{
    listado.Get();
	var car = document.querySelector("#carrListado");
	if(car){
		slider = bootstrap.Carousel.getInstance(car);
	}
});

function Editar(id){
	rulo.Show();
	getAjax({
		archivo: 'editarCliente',
		content: 'clientes',
		id: id
	},(a,b,c)=>{
		rulo.Hide();
		evalResult.Eval(c);
		if(evalResult.TheResult.ok){
			var edit = document.getElementById("edicion");
			if(edit){
				edit.innerHTML = c;
				ViewFolder(id);
				slider.to("1");
			}
		}
	});
}
