/**
 * RBTMultiSelect 1.0
 * Agrega soporte a los select multiples
 */

var multiSelect = function(params){
	this.defaultOptions = {
		selector: '',
		title: '',
		onOptionSelected: null
	}
	this.targetElement = null;
	this.dropId = null;
	this.dropdown = null;
	this.selectedOptions = [];
	this.customOptions = {};//Used for title
	this.options = Object.assign({}, this.defaultOptions,params);
	var select = this;

	this.Init = function(selector){
		if(!selector){ console.log("Selector no indicadó"); return; }
		var element = document.querySelector(selector);
		if(!element){ console.log("No encontre el elemento"); return; }
		if(element.tagName.toLowerCase() != 'select'){ console.log("No es un select"); return; }
		if(!element.hasChildNodes()){ return; }//No tiene opciones...
		//Colocamos opciones del dropdown
		var id = element.getAttribute("id");
		if(!id){ id = selector; }
		select.dropId = "dprSelectMultiple"+id;
		//Creamos el dropdown
		select.targetElement = element;
		var title = element.getAttribute("aria-label");
		select.CreateDropwDown(title);
		element.setAttribute("hidden",true);
		//Recorremos los hijos del select
		for(var i = 0; i < element.childElementCount; i++){
			var currentEle = element.children[i];
			if(!currentEle){ continue; }
			var tagName = currentEle.tagName.toLowerCase();
			if(tagName != 'option' && tagName != 'optgroup'){ continue; }

			var value = currentEle.value;
			var text = currentEle.textContent;
			if(tagName == 'option'){
				var selected = currentEle.selected;
				select.addOption({ value: value, text: text, selected: selected});
			}else{
				select.addGroup(currentEle);
			}
		}
	}

	this.CreateDropwDown = function(title){
		if(!select.targetElement){ console.log("asd"); return; }
		var drop = document.createElement("div");
		drop.classList.add("dropdown");
		drop.addEventListener("click",select.handleDismiss);

		var btn = document.createElement("button");
		btn.classList.add("btn","dropdown-toggle","mw-100","overflow-hidden");
		btn.setAttribute("type", "button");
		btn.setAttribute("id", select.dropId);
		btn.setAttribute("data-bs-toggle", "dropdown");
		btn.style.border = "1px solid #DCE7F2";
		btn.textContent = (title)? title:"Selecciona opciones...";
		select.options.title = btn.textContent;

		var ul = document.createElement("ul");
		ul.classList.add("dropdown-menu");
		ul.setAttribute("aria-labelledby", select.dropId);

		drop.appendChild(btn);
		drop.appendChild(ul);

		select.dropdown = drop;

		var parent = select.targetElement.parentNode;
		parent.insertBefore(drop,select.targetElement);
	}

	this.addOption = function(option, target = null){
		if(typeof option != "object"){ return; }
		if(!target){ target = select.dropdown; }
		if(!target){ return; }
		var ul = target.querySelector("ul");
		if(!ul){ return; }

		var value = option.value ?? "";
		var text = option.text ?? "";
		var selected = option.selected ?? false
		var li = document.createElement("li");
		var a = document.createElement("a");
		a.classList.add("dropdown-item","d-flex","justify-content-between");
		a.setAttribute("data-value", value);
		a.innerHTML = text;
		a.addEventListener("click",select.toggleOption);
		li.appendChild(a);
		ul.appendChild(li);
		if(selected){ a.click(); }
	}

	this.addGroup = function(group){
		if(group.childElementCount == 0){ console.log("Grupo sin opciones en select multiple"); return; }
		var mainUl = select.dropdown.querySelector("ul");
		if(!mainUl){ console.log("No se encontro el UL padre");return; }

		var text = group.getAttribute("aria-label") ?? "-";
		var ul = document.createElement("ul");
		ul.classList.add("list-group","list-group-flush","text-center","list-unstyled");

		var container = document.createElement("li");
		var span = document.createElement("span");
		span.classList.add("text-center","w-100","text-secondary");
		span.textContent = text;
		container.appendChild(span);
		container.appendChild(ul);

		var putDiv = true;
		if(mainUl.lastChild && mainUl.lastChild.classList && mainUl.lastChild.classList.contains("dropdown-divider")){
			putDiv = false;
		}
		if(putDiv){
			var divider = document.createElement("li");
			divider.classList.add("dropdown-divider");
			mainUl.appendChild(divider);
		}
		mainUl.appendChild(container);
		//Recorremos los hijos del select
		for(var i = 0; i < group.childElementCount; i++){
			var currentEle = group.children[i];
			if(!currentEle){ continue; }
			var tagName = currentEle.tagName.toLowerCase();
			if(tagName != 'option' && tagName != 'optgroup'){ continue; }

			var value = currentEle.value;
			var text = currentEle.textContent;
			text = "<span class='ms-5'>"+text+"</span>";
			if(tagName == 'option'){
				var selected = currentEle.selected;
				select.addOption({ value: value, text: text, selected: selected},container);
			}else{
				select.addGroup(currentEle);
			}
		}
		var divider = document.createElement("li");
		divider.classList.add("dropdown-divider");
		mainUl.appendChild(divider);
	}

	this.toggleOption = function(){
		if(!select.dropdown){ return; }

		var value = this.getAttribute("data-value");
		var option = select.targetElement.querySelector("[value="+value+"]");
		if(this.lastChild && this.lastChild.tagName == 'I'){
			// this.classList.remove("active");
			this.lastChild.remove();
			var newOptions = select.selectedOptions.filter(function(valor,indice){
				return valor != value;
			});
			delete select.customOptions[value];
			select.selectedOptions = newOptions;
			option.selected = false;
		}else{
			// this.classList.add("active");
			var i = document.createElement("i");
			i.classList.add("fas", "fa-check","float-end");
			this.appendChild(i);
			select.selectedOptions.push(value);
			select.customOptions[value] = this.textContent;
			option.selected = true;
		}
		select.setTitle();
		
		if(typeof select.options.onOptionSelected == 'function'){ select.options.onOptionSelected(GetValueSelect(select.targetElement)); }
	}

	this.setTitle = function() {
		if(!select.dropdown){ return; }
		var btn = select.dropdown.querySelector("button");
		var title = select.options.title;
		if(Object.values(select.customOptions).length > 0){
			title = Object.values(select.customOptions);
			title.sort();
			title = title.join(",");
		}
		btn.textContent = title;
	}

	this.handleDismiss = function(event){
		if(!event.delegateTarget){ 
			event.stopPropagation();
		}
	}

	if(select.options.selector.length > 0){
		select.Init(select.options.selector);
	}
}
