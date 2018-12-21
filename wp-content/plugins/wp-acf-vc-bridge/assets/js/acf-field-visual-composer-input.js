(function($){
  var pageIdentity, pageFields, isDisabled;

  function initVisualComposerEditor( jqElement ) {
    var viewMode, allowClassicMode, editor, origin;  

    function visualComposerEditor( jqElement ) {
      var addListenerEventMethod, messageEvent;
      var jqWrapper, jqIframe, iframeWindow, api;
    

      if ( true === isDisabled ) {
        return;
      }

      addListenerEventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
      messageEvent = addListenerEventMethod == "attachEvent" ? "onmessage" : "message";

      api = {};
      api.switchMode = function switchMode( isFullscreen ) {
        if ( isFullscreen ) {
          jqWrapper.addClass( 'fullscreen' );
        }
        else {
          jqWrapper.removeClass( 'fullscreen' );
        }
      };
      api.save = function save( content, css ) {
        jqElement.find( '.input-content' ).val( content );
        jqElement.find( '.input-css' ).val( css );
        jqElement.find( '.input-description' ).val( jqElement.find( '.acf-field-vc-input-description' ).val() );
        jqElement.find( '.acf-field-vc-description' ).html( jqElement.find( '.acf-field-vc-input-description' ).val() );
      };

      jqWrapper = jqElement.find( '.acf-field-vc-wrapper' );
      jqWrapper.addClass( 'loading' );


      // Setup iframe
      jqIframe = jqWrapper.find( '.acf-field-vc-iframe' );
      jqIframe[ 0 ].onload = function onIframeLoad() {
        jqWrapper.addClass( 'initialized' );
        iframeWindow = jqIframe.get( 0 ).contentWindow;
        iframeWindow.postMessage( JSON.stringify( { 'action': 'setContent', 'content': jqElement.find( '.input-content' ).val(), 'css': jqElement.find( '.input-css' ).val() } ), origin );
        jqWrapper.removeClass( 'loading' );

        if ( 'full' == viewMode ) {
          iframeWindow.postMessage( JSON.stringify( { 'action': 'watchUpdates' } ), origin );
        }

        if ( allowClassicMode ) {
          iframeWindow.postMessage( JSON.stringify( { 'action': 'allowClassicMode' } ), origin );
        }

        // Set current page ID
        pageFields = getPageFields();
        iframeWindow.postMessage( JSON.stringify( { 'action': 'setPageIdentity', 'identity': pageIdentity, 'fields': pageFields } ), origin );

        // Update fields
        $( document ).ajaxSuccess( function onGlobalAjaxSuccess( event, jqXHR, ajaxOptions, data ) {
          var params;
          
          if ( ! ajaxOptions || ! ajaxOptions.data ) return;
          params = ajaxOptions.data.split( '&' );

          // if ( -1 != ajaxOptions.data.indexOf( '&action=acf%2Fpost%2Fget_field_groups' ) ) {
          if ( -1 !== $.inArray( 'action=acf%2Fpost%2Fget_field_groups', params ) ) {
            setTimeout( function() {
              pageFields = getPageFields();
              iframeWindow.postMessage( JSON.stringify( { 'action': 'updateAcfFields', 'fields': pageFields } ), origin );
            }, 100 );
          }
        } );

      };

      // Load iframe
      $(function onDomReady () {
        jqIframe.attr( 'src', jqIframe.data( 'src' ) );
      });

      // Close editor
      jqWrapper.on( 'click', '.acf-field-vc-done', function onVisualComposerFieldDoneClick( e ){
        e.preventDefault(); e.stopPropagation();
        jqWrapper.addClass( 'loading' );
        iframeWindow.postMessage( JSON.stringify( { 'action': 'getContent' } ), origin );
      } );


      // Listen iframe for the edited content
      eventer = window[ addListenerEventMethod ];
      eventer( messageEvent, function onWindowMessageReceived( event ) { // Setup Listener to hear for reply from our iframe
        var data;

        if ( event.source === iframeWindow ) { // Make sure it is the right message sender

          try {
            data = JSON.parse( event.data );
          }
          catch ( err ) {
            return;
          }

          switch ( data[ 'action' ] ) {
            case 'heightUpdated':
              jqIframe.css( 'min-height', data[ 'height' ] + 'px' );
            break;
            case 'update':
              api.save( data[ 'content' ], data[ 'css' ] );
            break;
            case 'save':
              api.save( data[ 'content' ], data[ 'css' ] );
              editor.switchMode( false );
            break;
          }

        }

      }, false);

      return api;
    }


    function init_editor() {
      if ( ! editor ) {
        editor = new visualComposerEditor( jqElement );
      }
    }

    origin = location.protocol + '//' + location.hostname + ( location.port ? ':' + location.port : '' );
    viewMode = jqElement.find( '.view-mode-compact' ).length ? 'compact' : 'full';
    allowClassicMode = jqElement.find( '.allow-classic-mode-1' ).length ? true : false;
    
    if ( 'full' == viewMode ) {
      init_editor();
    }

    jqElement.on( 'click', '.acf-field-vc-edit', function onVisualComposerFieldEditClick( e ) {
      e.preventDefault();
      init_editor();

      // Switch to fullscreen
      editor.switchMode( true );
    } );

  }


  $(function onDomReady () {
    // Setup page identity and fields
    pageIdentity = {};
    if ( $( '#post_ID' ).length ) {
      pageIdentity.type = 'post';
      pageIdentity.id = $( '#post_ID' ).val();
      pageIdentity.postType = $( '#post_type' ).val();
      $( 'body' ).addClass( 'acf-field-vc-enabled' );
    }
    // TODO: Disable VC on New term creation
    else if ( $( '#edittag' ).length ) {
      pageIdentity.type = 'term';
      pageIdentity.id = $( '#edittag' ).find( '[name="tag_ID"]' ).val();
      pageIdentity.taxonomy = $( '#edittag' ).find( '[name="taxonomy"]' ).val();
      $( 'body' ).addClass( 'acf-field-vc-enabled' );
    }
    else if ( $( '.acf-settings-wrap' ).length ) {
      pageIdentity.type = 'option';
      $( 'body' ).addClass( 'acf-field-vc-enabled' );
    }
    else {
      isDisabled = true;
      $( 'body' ).addClass( 'acf-field-vc-disabled' );
    }
  });

  function getPageFields() {
    var fields;

    switch ( pageIdentity.type ) {
      case 'option':
      case 'post':
        fields = {};
        $( '.acf-postbox' ).not( '.acf-hidden' ).each(function iterateAcfGroups( index, group ) {
          var groupName, options = [];

          groupName = $( group ).find( '.hndle span' ).html();
          $( group )
            .find( '.acf-field' )
            .each(function iterateAcfFields( index, field ) {
              // Skip no-input
              if ( $( field ).is( '.acf-hidden,.acf-field-tab,.acf-field-message,.acf-field-clone' ) ) {
                return true;
              }
              // Skip sub-fields
              if ( $( field ).parent().closest( '.acf-field-repeater,.acf-field-flexible-content' ).length ) {
                return true;
              }
              options.push( {
                'name': $( field ).attr( 'data-name' ),
                'label': $( field ).find( '.acf-label label' ).html(),
              } );
            } )
          ;

          if ( groupName && options.length ) {
            fields[ groupName ] = options;
          }
        });
      break;
      case 'term':
        fields = [];
        $( '.acf-field' )
          .each(function iterateAcfFields( index, field ) {
            // Skip no-input
            if ( $( field ).is( '.acf-hidden,.acf-field-tab,.acf-field-message,.acf-field-clone' ) ) {
              return true;
            }
            // Skip sub-fields
            if ( $( field ).parent().closest( '.acf-field-repeater,.acf-field-flexible-content' ).length ) {
              return true;
            }
            fields.push( {
              'name': $( field ).attr( 'data-name' ),
              'label': $( field ).find( '.acf-label label' ).html(),
            } );
          } )
        ;

      break;
    }

    return fields;
  }
  

  function initialize_field( $el ) {
    new initVisualComposerEditor( $el );
  }
  
  
  if( typeof acf.add_action !== 'undefined' ) {
  
    acf.add_action('ready append', function( $el ){
      
      acf.get_fields({ type : 'visual_composer'}, $el).each(function(){
        initialize_field( $(this) );
      });
      
    });
    
  }

})(jQuery);
