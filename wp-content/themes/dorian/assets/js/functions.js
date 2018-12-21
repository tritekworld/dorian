(function ($) {
  'use strict';

  $(document).ready(function () {

  	/*$("#collectionMenu a").on("click", function (event) {
      event.preventDefault();
      var id  = $(this).attr('href'),
          top = $(id).offset().top;
      $('body,html').animate({scrollTop: top}, 1500);
    });*/
  	
    var myVideo = document.getElementById("videoAbout"); 
    $("#videoPlay").on("click", function(){
      if(myVideo.paused) 
        myVideo.play(); 
      else 
        myVideo.pause(); 
    });

    
    var deleteLog = false;		
		
	function top_menu_change() {
		$('#collectionMenu a').click(function(event){
			event.preventDefault();
			var href = $(this).attr('href');
			href = href.replace("#", "");
			$('html, body').animate({scrollTop:$( "div[data-anchor=" + href + "]" ).offset().top}, 500);
			return this;
		});
	}; 	
		
	//выключаем скролл по блоках при загрузке странички	
	if ($(window).width() < 1200 || $(window).height() < 800) {
		$.fn.fullpage.setAutoScrolling(false);
		top_menu_change();
	} else {
	    $.fn.fullpage.setAutoScrolling(true);
	}
	//выключаем скролл по блоках при ресайзе	
	$( window ).resize(function() {
		if ($(window).width() < 1200 || $(window).height() < 800) {
			$.fn.fullpage.setAutoScrolling(false);
			top_menu_change();
		} else {
			$.fn.fullpage.setAutoScrolling(true);
		}
	});
	


		$(window).scroll(function() {
      if ($(this).scrollTop() > 1){  
        $('#navMenu').addClass("sticky");
      }  else{
        $('#navMenu').removeClass("sticky");
      }
    });

    $('.model-slider').slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      fade: true,
      nextArrow: '<div class="arrow-right fa fa-angle-right"></div>',
      prevArrow: '<div class="arrow-left fa fa-angle-left"></div>'  
    });


  }); //end ready

}(jQuery));