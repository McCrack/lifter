(function(form){
	var scroll_timeout;
	var slideshow = form.querySelector(".slideshow");
	var slides = slideshow.querySelectorAll("img");
	form.setSlide=function(current){
		var offset = slides[current].offsetLeft - slideshow.offsetLeft;
		requestAnimationFrame(function scrollSlide(){
			var delta = (offset - slideshow.scrollLeft)/4;
			if(((offset - slideshow.scrollLeft) > 20) || ((slideshow.scrollLeft - offset) > 20)){
				slideshow.scrollLeft += delta;
			}else slideshow.scrollLeft = offset;
			if(slideshow.scrollLeft != offset) requestAnimationFrame(scrollSlide);
		});
	}
	form.querySelector(".leftpoint").onclick = form.querySelector(".rightpoint").onclick = function(){
		var current = Number(slideshow.dataset.current) + Number(this.dataset.dir);
		if(current >= 0 && current < slides.length) form.setSlide(current);
	}
	form.querySelector(".imagelist").onclick = function(event){
		if(event.target.dataset.slide) form.setSlide(event.target.dataset.slide);
	}
	slideshow.onscroll=function(){
		clearTimeout(scroll_timeout);
		scroll_timeout = setTimeout(function(){
			slideshow.dataset.current=(slideshow.scrollLeft/slideshow.offsetWidth)^0;
		}, 300);
	}
})(doc.scripts[doc.scripts.length-1].parentNode);