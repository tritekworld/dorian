(function ($) {
  'use strict';

  $(document).ready(function () {


		$(".tabs-year li:first").addClass("active");
		$(".tab-ral li:first").addClass("active");
		$(".list-models li:first").addClass("active");
		$(".content-year .tab-pane:first").addClass("in active");
    $(".model-wrap .tab-pane:first").addClass("in active");
		$(".models-content .tab-pane:first").addClass("in active");
		$('.tabs-year a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      $('.content-year .tab-pane').find('.tab-ral li:first').find('a').trigger('click');
    });
    $('.tab-ral a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      $('.model-wrap .tab-pane').find('.list-models li:first').find('a').trigger('click');
    });
	
	 
        
   
var $easyzoom = $('.easyzoom').easyZoom();
	
	
	
  }); //end ready

}(jQuery));