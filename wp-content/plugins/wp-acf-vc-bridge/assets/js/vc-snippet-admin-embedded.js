(function ( $ ) {

  var addListenerEventMethod, messageEvent, origin, addElementOffset;
  addListenerEventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
  messageEvent = addListenerEventMethod === "attachEvent" ? "onmessage" : "message";

  window.acfVcEmbedded = {};


  $( function onDomReady() {

    if ( ! window.vc ) {
      var adminURI = wp.ajax.settings.url.replace( 'admin-ajax.php', '' );
      var roleManagerLink = '<a target="_blank" href="' + adminURI + 'admin.php?page=vc-roles">Role Manager</a>';
      $( 'body' ).append( '<div id="vc-snippet-disabled-vc"><div class="vc-snippet-disabled-vc-title">Visual Composer is disabled!</div>Please enable Visual Composer for <strong>VC Snippets</strong> under Visual Composer -> ' + roleManagerLink + '</div>' );
      return;
    }

    origin = location.protocol + '//' + location.hostname + ( location.port ? ':' + location.port : '' );

    // Move VC
    $( '#wpb_visual_composer' ).appendTo( $( '#post' ) );

    // Listen messages
    var eventer = window[ addListenerEventMethod ];
    eventer(messageEvent, function onWindowMessageReceived( event ) {
      var data;

      if ( event.origin == origin ) { // Domain restriction (to not leak variables to any page..)
      
        try {
          data = JSON.parse( event.data );
        }
        catch ( err ) {
          return;
        }

        switch ( data[ 'action' ] ) {
          case 'setPageIdentity':
            acfVcEmbedded[ 'identity' ] = data[ 'identity' ];
            acfVcEmbedded[ 'fields' ] = data[ 'fields' ];
            $( document ).trigger( 'vc_acf_field_picker_embedded_updated' );
          break;
          case 'updateAcfFields':
            acfVcEmbedded[ 'fields' ] = data[ 'fields' ];
            $( document ).trigger( 'vc_acf_field_picker_embedded_updated' );
          break;
          case 'watchUpdates':
            updateOnDocumentHeightChange( function onDocumentHeightChanged( height ){
              parent.postMessage(
                JSON.stringify( {
                  'action': 'heightUpdated',
                  'height': height,
                  } ),
                event.origin
              );
            });

            updateOnVCChange( function onVCUpdated(){
              parent.postMessage(
                JSON.stringify( {
                  'action': 'update',
                  'content': getEditorContent(),
                  'css': $( '#vc_post-custom-css' ).val()
                  } ),
                event.origin
              );
            });
          break;
          case 'setContent':
            setContent( $.extend( {}, data ) );
          break;
          case 'getContent':
            parent.postMessage(
              JSON.stringify( {
                'action': 'save',
                'content': getEditorContent(),
                'css': $( '#vc_post-custom-css' ).val()
                } ),
              event.origin
            );
          break;
          case 'allowClassicMode':
            $( 'body' ).addClass( 'allow-classic-mode' );
          break;
        }

      }
    }, false);

    $( '#wpb_visual_composer' ).on( 'click', '#vc_add-new-element,.vc_controls [data-vc-control="add"],a[data-vc-element="add-element-action"],a[data-vc-control="edit"],.vc_control-btn-edit', function onAddElement( e ) {
      var offset = $( this ).offset();
      addElementOffset = offset ? offset.top : 0;
      positionPanelWindow();
    } );

    $( document ).ajaxSuccess( function onGlobalAjaxSuccess( event, jqXHR, ajaxOptions, data ) {
      var params;
      
      if ( ! ajaxOptions || ! ajaxOptions.data || ( 'string' !== typeof ajaxOptions.data ) ) return;
      params = ajaxOptions.data.split( '&' );

      if ( -1 == $.inArray( 'action=vc_edit_form', params ) ) {
        return;
      }

      setTimeout( function() {
        positionPanelWindow();
      }, 100 );

    } );


    cleanPage();

    autoPositionMediaUploadPanel();

  });

  function setContent( data, attempts ) {

    // Set content mode to HTML
    if ( ! _.isUndefined( window.switchEditors ) ) {

      // var isEditorVisible = $( '#postdivrich' ).is( ':visible' );
      // $( '#postdivrich' ).show(); // Bug fix with editors switch
      
      try {
        window.switchEditors.go( 'content', 'html' );
      }
      catch ( err ) {
        console.log( 'Error switching editors', err );

        attempts = attempts ? attempts + 1 : 1;
        if ( attempts < 20 ) {
          setTimeout( function() {
            setContent( data, attempts );
          }, 50 );
        }
        else {
          console.log( 'Give up as editors can not be switched...' );
        }

        return;
      }

      // if ( ! isEditorVisible ) {
      //   $( '#postdivrich' ).hide(); // Bug fix with editors switch
      // }
    }
    else {
      $( '#content-html' ).click();
    }

    // Update content
    $( '#content' ).val( data[ 'content' ] );

    // Update CSS
    $( '#vc_post-custom-css' ).val( data[ 'css' ] );

    // Switch to Visual Composer editor
    vc.app.show();

    // Stop asking before leaving
    $( window ).off();
  }


  function positionPanelWindow( attempts ) {
    var jqPanelWindow = $( '.vc_ui-panel-window.vc_active' );
    var windowHeight, panelHeight, panelTopPosition;

    if ( attempts && ( attempts > 100 ) ) {
      return;
    }

    if ( ! jqPanelWindow.length ) {
      setTimeout( function() {
        positionPanelWindow( attempts ? attempts + 1 : 1 );
      }, 50 );
    }
    else {
      windowHeight = $( window ).height();
      panelHeight = jqPanelWindow.outerHeight();

      panelTopPosition = addElementOffset - 200;
      panelTopPosition = windowHeight > panelTopPosition + panelHeight ? panelTopPosition : windowHeight - panelHeight;
      panelTopPosition = panelTopPosition < 0 ? 0 : panelTopPosition;
      if ( panelTopPosition ) {
        jqPanelWindow.css( 'top', panelTopPosition + 'px' );
        panelTopPosition = 0;
      }
      else {
        jqPanelWindow.css( 'top', '' );
      }
    }
  }


  function cleanPage() {
    $( '#wpbody-content' ).children().not( '.wrap' ).remove();
    $( '.wrap' ).children().not( '#post' ).remove();
    $( '#wpb_visual_composer' ).removeClass( 'postbox' );
  }

  function  getEditorContent() {
    // if ( $( '#wpb_visual_composer' ).is( ':visible' ) || $( '#content' ).is( ':visible' ) ) {
    if ( $( '#wpb_vc_js_status' ).val() == 'true' ) {
      return $( '#content' ).val();
    }
    else {
      return tinymce && tinymce.activeEditor ? tinymce.activeEditor.getContent() : '';
    }
  }


  function updateOnVCChange( callback ){
    var jqCss, lastContent, lastCss, newContent, newCss, timer;

    jqCss = $( '#vc_post-custom-css' );
    lastContent = getEditorContent();
    lastCss = jqCss.val();

    (function run(){
      newContent = getEditorContent();
      newCss = jqCss.val();
      if ( ( lastContent != newContent ) || ( lastCss != newCss ) ) {
        lastContent = newContent;
        lastCss = newCss;
        callback();
      }

      if ( timer ) {
        clearTimeout( timer );
      }

      timer = setTimeout( run, 200 );
    })();
  }


  function updateOnDocumentHeightChange( callback ){
    var jqContent, lastHeight, newHeight, timer;

    jqContent = $( '#wpbody-content' );
    lastHeight = 0;

    (function run() {
      newHeight = jqContent.height();
      if ( Math.abs( newHeight - lastHeight ) > 20 ) {
        lastHeight = newHeight;
        callback( newHeight );
      }

      if ( timer ) {
        clearTimeout( timer );
      }

      timer = setTimeout( run, 200 );
    })();
  }


  function autoPositionMediaUploadPanel() {
    var jqMediaModal, isModalVisible, timer;
    var jqWindow, windowWidth, windowHeight, modalHeight, topMargin, bottomMargin, bottomBoundary;
    var jqFrameContainer, scrollPos;

    try {
      jqFrameContainer = window.parent.jQuery( window.frameElement ).parent();
    }
    catch ( err ) {
      console.log( 'Can not get a reference to VC Snippet parent container', err );
      return;
    }

    scrollPos = jqFrameContainer.scrollTop();
    jqWindow = $( window );
    windowWidth = jqWindow.width();
    windowHeight = jqWindow.height();
    jqMediaModal = $( '.media-modal' );
    isModalVisible = jqMediaModal.is( ':visible' );
    topMargin = 30;
    bottomMargin = bottomBoundary = 30;

    (function updateModalData() {
      var newWindowWidth, newWindowHeight, newIsModalVisible, newScrollPos, changed;
      if ( ! jqMediaModal.length ) jqMediaModal = $( '.media-modal' );
      newIsModalVisible = jqMediaModal.is( ':visible' );

      if ( newIsModalVisible !== isModalVisible ) {
        isModalVisible = newIsModalVisible;
        changed = true;
      }

      newWindowWidth = jqWindow.width();
      newWindowHeight = jqWindow.height();
      if ( ( newWindowWidth !== windowWidth ) || ( newWindowHeight !== windowHeight ) ) {
        windowWidth = newWindowWidth;
        windowHeight = newWindowHeight;
        changed = true;
      }

      newScrollPos = jqFrameContainer.scrollTop();
      if ( newScrollPos !== scrollPos ) {
        scrollPos = newScrollPos;
        changed = true;
      }

      if ( isModalVisible && changed ) {
        refreshModal();
      }

      timer && clearTimeout( timer );
      timer = setTimeout( updateModalData, 200 );
    })();

    var slideGallery = _.debounce( function slideGalleryF( newPosition ) {
      jqMediaModal.stop().animate({
        top: newPosition
      }, 300);
    }, 100 );

    var updateBoundaries = function updateBoundariesF() {
      bottomBoundary = jqWindow.height() - bottomMargin;
      modalHeight = jqMediaModal.outerHeight();
    };

    var updatePosition = function updateModalPositionF() {
      var newPosition = scrollPos + topMargin;

      // Check top boundary
      if ( newPosition < 0 ) {
        newPosition = 0;
      } else if ( newPosition + modalHeight + topMargin > bottomBoundary ) { // Check bottom boundary
        newPosition = bottomBoundary - modalHeight - topMargin;
      }

      slideGallery( newPosition );
    }

    var refreshModal = function refreshGalleryF() {
      updateBoundaries();
      updatePosition();
    };
  }


})( jQuery );