window.addEventListener('load', ()=>{
	loadListado();
	let s = document.querySelector('#seccionParametros');
	console.log(s);
	theBigSlider = bootstrap.Carousel.getInstance(s);
	console.log(theBigSlider);
});
var theRulo = new rbtRulo();
var theRulo2 = new rbtRulo();

var elListador = new mgrListadoCreator({
	archivo: 'getListadoParametros',
	content: 'configuraciones',
	rulo: theRulo,
	onFinish: ()=>{
		theBigSlider.to('0');
	}
});

var targetDiv;
var targetGrupo;
var theBigSlider;
var evalResult = new rbtEvalResult;

var frm = new rbtFormSend(null, {
	extraData: {
		archivo: 'guardarEdicion',
		content: "configuraciones"
	},
	url: "ajx/"
});

function loadListado(content, grupo) {
	if (content == null) { content = 'todos'; }
	targetDiv = getElem('content-'+content);
	targetGrupo = grupo;
	if (targetDiv.innerHTML == '') {
		elListador.SetTargetElement(targetDiv);
		elListador.Get(null,{'grupo':targetGrupo,'alias':content});
	}
}

document.addEventListener('DOMContentLoaded',()=> {
	let lista = document.querySelectorAll('div.nav-pills a.nav-link:not(.active)');
	lista.forEach((ele)=>{
		ele.addEventListener('click',(ele)=>{
			loadListado(ele.target.dataset.targetContent, ele.target.dataset.targetGroupId);
		})
	});
});

function Editar(nombre) {
	rulo.Show();
	getElem('formularioEdicion').innerHTML = '';
	getAjax({
		archivo: 'editParametro',
		content: 'configuraciones',
		nombre: nombre
	},function (a,b,c,d) {
		rulo.Hide();
		theBigSlider.to('1');
		if (a == 200 && !d) {
			getElem('formularioEdicion').innerHTML = c;
		}
	});
}

function GuardarEdicion(self){
	var form = document.getElementById("formEditParam");
	if(!form){ return false; }
	frm.Send(form,{
		onStart: ()=>{
			self.setAttribute("disabled",true);
			theRulo2.Show(form);
		},
		onFinish: function(a,b,c){
			theRulo2.Hide();
			evalResult.Eval(c);
			if(evalResult.TheResult.ok){
				elListador.Get();
				return;
			}
			self.removeAttribute("disabled");
		}
	});
}

function toggleValue(self){
	var span = self.parentNode.querySelector("span");
	if(span){
		var oldValue = span.innerHTML;
		var newValue = self.getAttribute("data-value");
		var title = self.getAttribute("data-title");
		var oldTitle = self.parentNode.getAttribute("title");
		span.innerHTML = newValue.trim();
		self.parentNode.setAttribute("title",title.trim());
		self.setAttribute("data-title",oldTitle.trim());
		self.setAttribute("data-value",oldValue.trim());
	}
}
