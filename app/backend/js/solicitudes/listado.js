;
var theRulo = new rbtRulo;
var evalResult = new rbtEvalResult;
let slider = null;
var interval = null;

var listado = new mgrListadoCreator({
    archivo: 'listado',
    content: 'solicitudes',
    targetElementIdName: 'listado_solicitudes',
    rulo: theRulo
});

window.addEventListener("load",()=>{
    listado.Get();
    var car = document.querySelector("#carrListado");
	if(car){
        slider = bootstrap.Carousel.getInstance(car);
	}
});



function VerSolicitud(id){
	var ver = document.getElementById("visualizacion");
	if(!ver) return;
	rulo.Show();
	getAjax({
		archivo: 'ver',
		content: 'solicitudes',
		id:id
	},function (a,b,c,d){
		rulo.Hide();
		evalResult.Eval(c);
		if(evalResult.TheResult.ok){
			ver.innerHTML = c;
			slider.to("1");
		}
	});
}

function GoBack(){
    slider.to("0");
}

function Simular(){
	capital = document.getElementById("capital").value;
	plazo = document.getElementById("plazo").value;
	plan = document.getElementById("plan").value;
	getAjax({
		archivo: 'simular',
		content: 'solicitudes/ver',
		capital:capital,
		plazo:plazo,
		plan:plan,
	},function (a,b,c,d){        
		if(evalResult.TheResult.ok){
			document.getElementById("body_simulate").innerHTML = c;
		}
	});
	
}