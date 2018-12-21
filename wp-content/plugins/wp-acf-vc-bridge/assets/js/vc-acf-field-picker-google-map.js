(function($) {

// Detect Element Resize
(function(){var attachEvent=document.attachEvent,stylesCreated=false;if(!attachEvent){var requestFrame=function(){var raf=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||function(fn){return window.setTimeout(fn,20)};return function(fn){return raf(fn)}}();var cancelFrame=function(){var cancel=window.cancelAnimationFrame||window.mozCancelAnimationFrame||window.webkitCancelAnimationFrame||window.clearTimeout;return function(id){return cancel(id)}}();function resetTriggers(element){var triggers=element.__resizeTriggers__,expand=triggers.firstElementChild,contract=triggers.lastElementChild,expandChild=expand.firstElementChild;contract.scrollLeft=contract.scrollWidth;contract.scrollTop=contract.scrollHeight;expandChild.style.width=expand.offsetWidth+1+"px";expandChild.style.height=expand.offsetHeight+1+"px";expand.scrollLeft=expand.scrollWidth;expand.scrollTop=expand.scrollHeight}function checkTriggers(element){return element.offsetWidth!=element.__resizeLast__.width||element.offsetHeight!=element.__resizeLast__.height}function scrollListener(e){var element=this;resetTriggers(this);if(this.__resizeRAF__)cancelFrame(this.__resizeRAF__);this.__resizeRAF__=requestFrame(function(){if(checkTriggers(element)){element.__resizeLast__.width=element.offsetWidth;element.__resizeLast__.height=element.offsetHeight;element.__resizeListeners__.forEach(function(fn){fn.call(element,e)})}})}var animation=false,animationstring="animation",keyframeprefix="",animationstartevent="animationstart",domPrefixes="Webkit Moz O ms".split(" "),startEvents="webkitAnimationStart animationstart oAnimationStart MSAnimationStart".split(" "),pfx="";{var elm=document.createElement("fakeelement");if(elm.style.animationName!==undefined){animation=true}if(animation===false){for(var i=0;i<domPrefixes.length;i++){if(elm.style[domPrefixes[i]+"AnimationName"]!==undefined){pfx=domPrefixes[i];animationstring=pfx+"Animation";keyframeprefix="-"+pfx.toLowerCase()+"-";animationstartevent=startEvents[i];animation=true;break}}}}var animationName="resizeanim";var animationKeyframes="@"+keyframeprefix+"keyframes "+animationName+" { from { opacity: 0; } to { opacity: 0; } } ";var animationStyle=keyframeprefix+"animation: 1ms "+animationName+"; "}function createStyles(){if(!stylesCreated){var css=(animationKeyframes?animationKeyframes:"")+".resize-triggers { "+(animationStyle?animationStyle:"")+"visibility: hidden; opacity: 0; } "+'.resize-triggers, .resize-triggers > div, .contract-trigger:before { content: " "; display: block; position: absolute; top: 0; left: 0; height: 100%; width: 100%; overflow: hidden; } .resize-triggers > div { background: #eee; overflow: auto; } .contract-trigger:before { width: 200%; height: 200%; }',head=document.head||document.getElementsByTagName("head")[0],style=document.createElement("style");style.type="text/css";if(style.styleSheet){style.styleSheet.cssText=css}else{style.appendChild(document.createTextNode(css))}head.appendChild(style);stylesCreated=true}}window.wavbAddResizeListener=function(element,fn){if(attachEvent)element.attachEvent("onresize",fn);else{if(!element.__resizeTriggers__){if(getComputedStyle(element).position=="static")element.style.position="relative";createStyles();element.__resizeLast__={};element.__resizeListeners__=[];(element.__resizeTriggers__=document.createElement("div")).className="resize-triggers";element.__resizeTriggers__.innerHTML='<div class="expand-trigger"><div></div></div>'+'<div class="contract-trigger"></div>';element.appendChild(element.__resizeTriggers__);resetTriggers(element);element.addEventListener("scroll",scrollListener,true);animationstartevent&&element.__resizeTriggers__.addEventListener(animationstartevent,function(e){if(e.animationName==animationName)resetTriggers(element)})}element.__resizeListeners__.push(fn)}};window.wavbRemoveResizeListener=function(element,fn){if(attachEvent)element.detachEvent("onresize",fn);else{element.__resizeListeners__.splice(element.__resizeListeners__.indexOf(fn),1);if(!element.__resizeListeners__.length){element.removeEventListener("scroll",scrollListener);element.__resizeTriggers__=!element.removeChild(element.__resizeTriggers__)}}}})();

/*
*  new_map
*
*  This function will render a Google Map onto the selected jQuery element
*
*  @type  function
*  @date  8/11/2013
*  @since 4.3.0
*
*  @param $el (jQuery element)
*  @return  n/a
*/

function new_map( $el ) {
  
  // var
  var $markers = $el.find('.marker');
  
  
  // vars
  var args = {
    zoom    : 16,
    center    : new google.maps.LatLng(0, 0),
    // mapTypeControl    : true,
    mapTypeId : google.maps.MapTypeId.ROADMAP
  };
  
  
  // create map           
  var map = new google.maps.Map( $el[0], args);
  
  
  // add a markers reference
  map.markers = [];
  
  
  // add markers
  $markers.each(function(){
    
      add_marker( $(this), map );
    
  });
  
  
  // center map
  center_map( map );

  handleMapInExpandableContainer( $el, map );

  // return
  return map;
}


function handleMapInExpandableContainer( $el, map ) {

  var isVisible = $el.is( ':visible' );
  window.wavbAddResizeListener( $el.get( 0 ), function onResize() {
    if ( isVisible !== $el.is( ':visible' ) ) {
      isVisible = $el.is( ':visible' );
      google.maps.event.trigger( map, 'resize' );
      center_map( map );
    }
  });

}


/*
*  add_marker
*
*  This function will add a marker to the selected Google Map
*
*  @type  function
*  @date  8/11/2013
*  @since 4.3.0
*
*  @param $marker (jQuery element)
*  @param map (Google Map object)
*  @return  n/a
*/

function add_marker( $marker, map ) {

  // var
  var latlng = new google.maps.LatLng( $marker.attr('data-lat'), $marker.attr('data-lng') );

  // create marker
  var marker = new google.maps.Marker({
    position  : latlng,
    map     : map
  });

  // add to array
  map.markers.push( marker );

  // if marker contains HTML, add it to an infoWindow
  if( $marker.html() )
  {
    // create info window
    var infowindow = new google.maps.InfoWindow({
      content   : $marker.html()
    });

    // show info window when marker is clicked
    google.maps.event.addListener(marker, 'click', function() {

      infowindow.open( map, marker );

    });
  }

}

/*
*  center_map
*
*  This function will center the map, showing all markers attached to this map
*
*  @type  function
*  @date  8/11/2013
*  @since 4.3.0
*
*  @param map (Google Map object)
*  @return  n/a
*/

function center_map( map ) {

  // vars
  var bounds = new google.maps.LatLngBounds();

  // loop through all markers and create bounds
  $.each( map.markers, function( i, marker ){

    var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );

    bounds.extend( latlng );

  });

  // only 1 marker?
  if( map.markers.length == 1 )
  {
    // set center of map
      map.setCenter( bounds.getCenter() );
      map.setZoom( 16 );
  }
  else
  {
    // fit to bounds
    map.fitBounds( bounds );
  }

}


function requestGoogleMapsApiSript( callback ) {
  // If Google Maps API is ready
  if ( window.google && google.maps ) {
    setTimeout( callback, 0 );
    return;
  }

  // Load Google Maps API Script if not loaded yet and if setting is set to load conditionally
  if ( ! vcAcfFieldPickerGoogleMap.isGoogleMapsApiScriptLoaded && ( vcAcfFieldPickerGoogleMap.shouldLoadGoogleMapsApiScript == 'auto' ) ) {
    vcAcfFieldPickerGoogleMap.isGoogleMapsApiScriptLoaded = true;
    $( '<script>', { 'src': vcAcfFieldPickerGoogleMap.googleApiScriptUrl, 'async':'true', 'defer': 'true' } ).appendTo( $( 'head' ) );
  }

  // Try again later
  setTimeout( function () {
    requestGoogleMapsApiSript( callback );
  }, 50 );
}

/*
*  document ready
*
*  This function will render each map when the document is ready (page has loaded)
*
*  @type  function
*  @date  8/11/2013
*  @since 5.0.0
*
*  @param n/a
*  @return  n/a
*/
// global var
var map = null;

// $(document).ready(function(){
$(window).load(function(){

  if ( ! vcAcfFieldPickerGoogleMap ) return;

  // Wait for when Google Maps API is ready
  requestGoogleMapsApiSript( function () {
    google.maps.visualRefresh = true;
    $( '.vc-acf-field-picker-google-map' ).each(function(){
      map = new_map( $( this ) );
    });
  });

});

})(jQuery);