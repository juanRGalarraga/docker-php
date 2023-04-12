;<?php 
	require_once(DIR_js."rbtMicroAjax.1.0.js");
?>;

const alert = Swal.mixin ({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

var formSend = new rbtFormSend(null, {
	extraData: {
		archivo: 'SubirArchivo',
		content: 'biblioteca'
	},
	onFinish: evaluarResultado
});

function ViewFolder(id,folder=""){
	var target = document.querySelector("#archivosAsociados");
	getAjax({
		archivo: 'archivos_clientes',
		content: 'biblioteca',
		folder: folder,
		id: id
	},(a,b,c)=>{
		if(target){
			target.innerHTML = c;
		}
	});
}

function toggleSelection(self){
	var check = self.parentNode.querySelector("input[type=checkbox]");
	if(check){
		check.checked = !check.checked;
		UpdateSelections();
	}
}
function GetCantSelected(){
	let cantidad = 0;
	var checks = document.querySelectorAll("div.file input[type=checkbox]");
	for(var d of checks) {
		if(d.checked) {  cantidad++; }
	}
	return cantidad;
}
function UpdateSelections(){
	var checks = document.querySelectorAll("div.file input[type=checkbox]");
	var enable = false;
	for(var d of checks) {
		if(d.checked) { enable = true; break; }
	}
	SetDownloadButton(enable);
}

function SetDownloadButton(status){
	var button = document.querySelector("button.file-download");
	if(!button){ return; }

	if(status){
		button.classList.remove("d-none");
	}else{
		button.classList.add("d-none");
	}
	
	if(document.getElementById("cant_selected")){
		document.getElementById("cant_selected").innerText = GetCantSelected();
	}
	button.addEventListener("click",DownloadFiles);
}

function DownloadFiles(){
	var checks = document.querySelectorAll("div.file input[type=checkbox]");
	var pid = null;
	var files = [];
	for(var d of checks) {
		if(!d.checked) { continue; }
		var value = d.getAttribute("value");
		if(!value){ continue; }
		files.push(value);
		if(!pid){ 
			pid = d.getAttribute("data-id");
		}
	}
	
	if(files.length == 0){ return; }
	getAjax({
		archivo: 'GetFileByName',
		content: 'biblioteca',
		archivos: files,
		pid: pid
	},async (a,b,c,d)=>{
		if(d.files && d.files.length > 0) {
			var archivos = d.files;
			for(var i in archivos){
				var name = archivos[i].name;
				var mime = archivos[i].mime;
				var data = archivos[i].data;

				var a = document.createElement("a");
				a.setAttribute("href","data:"+mime+";base64,"+data);
				a.setAttribute("download",name);
				a.click();
				a.remove();
				var currentFile = parseInt(i)+1;
				await fireAlert(currentFile,archivos.length);
			}

			alert.fire({
				title: "Todos los archivos han sido puestos en la cola de descarga...",
				icon: "success"
			});

			var checks = document.querySelectorAll("div.file input[type=checkbox]");
			for(var d of checks) {
				d.checked = false;
			}
			UpdateSelections();
		}
	});
}

function fireAlert(current,total){
	return new Promise((res)=>{
		alert.fire({
			title: "Comenzo la descarga del archivo "+current+" de "+total,
			icon: "info",
			didOpen: null
		}).then((stats)=>{
			res(true);
		});
	});
}

function NewFolder(id, folder){
	alert.fire({
		title: 'Nueva carpeta',
		input: 'text',
		inputAttributes: {
			autocapitalize: 'off'
		},
		toast: false,
		timer: false,
		position: 'center',
		reverseButtons: true,
		showCancelButton: true,
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Crear carpeta',
		showConfirmButton: true,
		showLoaderOnConfirm: true,
		preConfirm: async (input)=>{
			var result = await EvalFolderName(id,folder,input);
			if(result !== true) {
				alert.showValidationMessage(result);
			}
		}
	}).then((result)=>{
		if(result.isConfirmed){ 
			alert.fire({
				title: "¡Carpeta creada con exito!",
				icon: "success"
			});
			ViewFolder(id,folder);
		}
	});
}

function EvalFolderName(id,where,name){
	return new Promise((res)=>{
		getAjax({
			archivo: 'NuevaCarpeta',
			content: 'biblioteca',
			id: id,
			folder: where,
			name: name
		},(a,b,c,d)=>{
			if(d.dataerr && d.dataerr.swerr){
				console.log(d.dataerr.swerr);
				res(d.dataerr.swerr);
				return;
			}
			res(true);
		});
	});
}

function UploadFile(self){
	var input = self.parentNode.querySelector("input[hidden][type=file]");
	if(!input){ return; }
	input.click();
	input.addEventListener("change", finishUpload);
}

function finishUpload(){
	var frm = document.querySelector("form#formFile");
	if(!frm){ return; }

	var files = this.files;
	if(files.length == 0){ return; }
	alert.fire({
		title: 'Subir archivo',
		text: '¿Cofirmas la subida del archivo "'+files[0].name+'"?',
		toast: false,
		timer: false,
		position: 'center',
		reverseButtons: true,
		showCancelButton: true,
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Confirmar',
		showConfirmButton: true
	}).then((result)=>{
		if(result.isConfirmed){ 
			formSend.Send(frm);
		}
		this.value = "";
	});
}

function evaluarResultado(a,b,c,d){
	if(d.dataerr && d.dataerr.swerr){
		alert.fire({
			title: d.dataerr.swerr,
			icon: "error",
			toast: false,
			timer: false,
			position: 'center',
		});
		return;
	}
	if(d.id){
		alert.fire({
			title: "Archivo subido exitosamente",
			icon: "success"
		});
		var folder = d.folder ?? "";
		ViewFolder(d.id,folder);
	}
}
