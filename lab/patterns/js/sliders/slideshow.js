(function(emd){
	var slideshow=emd.querySelector(".slideshow");
	var tmr,slides=slideshow.querySelectorAll(".slide");
	emd.setSlide=function(sld){
		var speed,offset=slides[sld].offsetLeft-slideshow.offsetLeft;
		tmr=setInterval(function(){
			speed=(offset-slideshow.scrollLeft)/4;
			if((offset-slideshow.scrollLeft)>4){
				slideshow.scrollLeft+=speed;
			}else if((slideshow.scrollLeft - offset) > 4){
				slideshow.scrollLeft+=speed;
			}else{
				slideshow.scrollLeft=offset;
				clearInterval(tmr);
			}
		},20);
	}
	emd.querySelector(".leftpoint").onclick=emd.querySelector(".rightpoint").onclick=function(){
		var sld = Number(slideshow.dataset.current)+Number(this.dataset.dir);
		if(sld<0){
			emd.setSlide(0);
		}else if(sld>(slides.length-1)){
			emd.setSlide(slides.length-1);
		}else emd.setSlide(sld);
	}
	emd.querySelector(".imagelist").onclick=function(event){
		if(event.target.dataset.slide) emd.setSlide(event.target.dataset.slide);
	}
	slideshow.onscroll=function(){
		slideshow.dataset.current=((slideshow.scrollLeft+10)/slideshow.offsetWidth)^0;
	}
})(doc.scripts[doc.scripts.length-1].parentNode);