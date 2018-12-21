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
		var placeHolder = $('#header');

    $('#fullpage').fullpage({
			anchors: ['photogallery', 'configurator_', 'video_', 'frames_', 'advantages_'],
			menu: '#collectionMenu',
			lazyLoad: true,
			slidesNavigation: true,
			onleave: function(index, nextIndex, direction) {
		    if (deleteLog) {
		      placeHolder.html('');
		    }
		    placeHolder.append('<p>onLeave - index:' + index + ' nextIndex:' + nextIndex + ' direction:' + direction + '</p>');
		  }
		});





  }); //end ready

}(jQuery));