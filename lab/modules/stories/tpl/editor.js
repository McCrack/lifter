var KEYMASK = 0;
const CTRLKEY=1,METAKEY=2,ALTKEY=4,SHIFTKEY=8;

new function(){
	var edt = this;
	this.doc = document;
	this.body = this.node = this.doc.querySelector("#content");
	this.toolbar =  this.doc.querySelector("#toolbar");;
	this.range = this.doc.createRange();
	
	this.setValue = this.doc.setValue = function(value){
		edt.body.innerHTML = value;
	}
	this.getValue = this.doc.getValue = function(){
		return edt.body.innerHTML;
	}
	this.refresh = function(){
		edt.node = edt.doc.getSelection().focusNode.parentNode;
		//edt.node = edt.range.commonAncestorContainer.nodeType==3 ? edt.range.commonAncestorContainer.parentNode : edt.range.commonAncestorContainer;
		edt.range = edt.doc.getSelection().getRangeAt(0);
		setTimeout(function(){ edt.setSelection(); }, 150);
	}
	this.doc.setSelection = this.setSelection = function(){
		edt.body.focus();
		var selection=edt.doc.getSelection();
		selection.removeAllRanges();
		selection.addRange(edt.range);
	}
	var tm,scroll=0;
	edt.body.onscroll = function(){
		var dir = (scroll<edt.body.scrollLeft) ? 1 : -1;
		scroll=edt.body.scrollLeft;
		clearTimeout(tm);
		tm = setTimeout(function(){
			var current = (edt.body.scrollLeft/edt.body.offsetWidth)>>0;
			if(current*edt.body.offsetWidth==edt.body.scrollLeft) return false;
			current += dir;
			if( current < 0 || ( current >= edt.body.querySelectorAll(".card").length)) return false;

			var moveslide,
				offset = (edt.body.offsetWidth * current);
			cancelAnimationFrame(moveslide);
			moveslide = requestAnimationFrame(function scrollSlide(){
				if(((offset - edt.body.scrollLeft) > 16) || ((edt.body.scrollLeft - offset) > 16)){
					edt.body.scrollLeft += (offset - edt.body.scrollLeft)/8;
					moveslide = requestAnimationFrame(scrollSlide);
				}else{
					edt.body.scrollLeft = offset;
				}
			});
		},200);
	}
	this.doc.spellCheck = function(){
		edt.body.spellcheck = edt.body.spellcheck.toggle();
	}
	this.doc.insertTag=function(command, tag){
		if(edt.node.nodeName!=tag){
			edt.doc.execCommand(command, false, tag);
			edt.refresh();
		}
	}
	this.doc.verticalAlign = function(value){
		let node = edt.node;
		while(node && !node.classList.contains("layer")){
			node = node.parentNode;
		}
		if(node){
			["flex-start","flex-end","center","space-between","space-around"].forEach(function(itm){
				node.classList.toggle(itm, (itm==value) );
			});
		}
		edt.refresh();

	}
	this.doc.imgBox=function(){
		var box = new parent.Box(parent.ui.main.path, "xhr/built-in/navigator/imagebox", true);
		box.onsubmit = function(form){
			let src = form.imgpath.value;
			parent.supervisor.drop();

			edt.setSelection();
			edt.doc.execCommand("insertImage", false, src);
			edt.refresh();
		}
	}
	this.doc.albumBox=function(){
		var box = new parent.Box(parent.ui.main.path, "xhr/built-in/navigator/box", true);
		box.onopen = function(){
			box.window.selector(".box-footer>button[type='submit']", false).disabled = false;
		}
		box.onsubmit = function(form){
			let album = [];
			box.window.selector("iframe", false).contentWindow.document.getSelected(true).forEach(function(item){
				if(item){
					album.push("<figure><img src='"+item+"'><figcaption></figcaption></figure>");
				}
			});
			parent.supervisor.drop();
			edt.setSelection();
			edt.doc.execCommand("insertHTML", false, album.join("\n"));
			edt.refresh();
		}
	}
	this.doc.setProperty=function(property, value){
		edt.node.setAttribute(property,value);
		edt.refresh();
	}
	this.checkfont = function(){
		edt.toolbar.fsize.value = edt.node.getCss("font-size");
	}
	this.doc.setFontSize=function(value){
		edt.node.style.fontSize = parseInt(value)+"px";
	}
	this.body.onpaste=function(event){
		event.preventDefault();
		var items=event.clipboardData.getData("text").split(/\n+/);
		var data = [];
		for(var i=0; i<items.length; i++){
			if(items[i].trim()) data.push("<p>"+items[i].trim()+"</p>");
		}
		edt.doc.execCommand("insertHTML", false, data.join("\n"));
		edt.refresh();
	}
	this.doc.createlink = function(){
		if(edt.range.collapsed) return false;
		let box = new parent.Box('{}', "xhr/built-in/editor/linkbox", false);
		box.onsubmit = function(form){
			let target = form.target.value;
			let href = form.url.value.trim();
			parent.supervisor.drop();
			edt.setSelection();
			edt.doc.execCommand("createLink", false, href);
			edt.refresh();
			edt.node.target = target;
		}
	}
	this.doc.breakline=function(){
		edt.doc.execCommand('insertHTML', false, "<br>");
	}
	this.body.onkeydown=function(event){
		KEYMASK = (event.ctrlKey * CTRLKEY)|(event.metaKey * METAKEY)|(event.altKey * ALTKEY)|(event.shiftKey * SHIFTKEY);

		if(KEYMASK & (CTRLKEY|METAKEY)){
			switch(event.keyCode){
				case 83:														// Key "s" - Save
					event.preventDefault();
					document.save();
				break;
				case 66: 														// Key "b" - bold
					event.preventDefault();
					edt.doc.insertTag('bold', 'B');
				break;
				case 73: 														// Key "i" - Open image box
					event.preventDefault();
					edt.doc.imgBox();
					//edt.doc.insertTag('italic','I');
				break;

				case 76: 														// Key "l" - Create link
					event.preventDefault();
					edt.doc.createlink();
				break;

				case 85: 														// Key "u" - underline
					event.preventDefault();
					edt.doc.insertTag('underline','U')
				break;
				case 8: 														// Key "delete" and key "backspace" -
				case 46: 														// drop selected tag
					event.preventDefault();
					edt.doc.drop();
				break;
				case 13: 														// Key "Enter" - paragraph
					event.preventDefault();
					edt.doc.formatblock('p');
				break;
				default:break;
			}
		}else if(KEYMASK & SHIFTKEY){
			
		}else{
			switch(event.keyCode){
				case 13: 														// Key "Enter" - breakline
					event.preventDefault();
					edt.doc.breakline();
				break;
				case 33:
				case 34:
				case 35:
				case 36:
				case 37:
				case 38:
				case 39:
				case 40: edt.body.onkeyup = edt.body.onclick; break;
				default: break;
			}
		}
	}
	this.body.onkeyup=function(event){
		KEYMASK = (event.ctrlKey * CTRLKEY)|(event.metaKey * METAKEY)|(event.altKey * ALTKEY)|(event.shiftKey * SHIFTKEY);
	}
	this.body.onclick = function(event){
		event.preventDefault();
		
		edt.node = edt.doc.getSelection().focusNode;
		if(!edt.node.isContentEditable){
			while(!edt.node.parentNode.isContentEditable){
				edt.node = edt.node.parentNode;
			}
		}
		edt.range = edt.doc.getSelection().getRangeAt(0);
		edt.checkfont();
		edt.body.onkeyup = null;
	}
}