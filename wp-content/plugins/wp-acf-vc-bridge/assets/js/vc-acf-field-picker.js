(function ( $ ) {
  var pageIdentity, pageFields, customPageOptions, jqPanel, jqPageTypeField, jqPageCustomField, jqPageCustomFieldId, jqPageSelector, xhrLoadPages, jqPageIdField, loadPagesInt, xhrLoadPageFields;

  $( function onDomReady() {
    jqPanel = $( '#vc_ui-panel-edit-element' );

    if ( window.acfVcEmbedded ) {
      modeEmbedded();
    }
    else {
      modeStandAlone();
    }

    initElementView();
  } );

  function initElementView() {
    if ( ! window.vc || ! vc.shortcode_view ) return;

    window.VcAcfFieldPickerView = vc.shortcode_view.extend( {
      elementTemplate: false,
      $wrapper: false,
      changeShortcodeParams: function ( model ) {
        var params, template, content, group;
        
        window.VcAcfFieldPickerView.__super__.changeShortcodeParams.call( this, model );
        params = _.extend( {}, model.get( 'params' ) );
        if ( ! this.$wrapper ) {
          this.$wrapper = this.$el.find( '.wpb_element_wrapper' );
        }
        if ( ! this.elementTemplate ) {
          this.elementTemplate = this.$wrapper.html();
        }
        if ( _.isObject( params ) ) {
          content = [];
          params.field_context = params.field_context ? params.field_context : 'custom';
          switch ( params.field_context ) {
            case 'page':
              content.push( 'This page field: <i>' + params.page_field + '</i>' );
            break;
            case 'page_custom':
              content.push( 'Another page field: <i>' + params.page_custom_field_id + '</i><br />' );
              content.push( 'Page type: <i>' + params.page_type + '</i>. ' );
              content.push( 'Page: <i>' + params.page_custom + '</i>. ' );
            break;
            case 'option':
              content.push( 'Option field: <i>' + params.option_field + '</i>' );
            break;
            case 'custom':
              $.each( model.view.params.fields_group.value, function( name, value ) {
                if ( value == params.fields_group ) {
                  group = name;
                  return false;
                }
              } );
              content.push( 'Custom field: <i>' + params[ 'group_field_' + params.fields_group ] + '</i><br />' );
              content.push( 'Fields group: <i>' + group + '</i>' );
            break;
          }
          content = content.join( '' );
          template = _.template( this.elementTemplate, vc.templateOptions.custom );
          this.$wrapper.html( template( { content: content } ) );
        }
      }
    } );
  }

  function setupFormEditListener() {

    loadCustomPageOptions( function( options ) {
      customPageOptions = options;
    });

    $( document ).ajaxSuccess( function onGlobalAjaxSuccess( event, jqXHR, ajaxOptions, data ) {
      var params;
      
      if ( ! ajaxOptions || ! ajaxOptions.data || ( 'string' !== typeof ajaxOptions.data ) ) return;
      params = ajaxOptions.data.split( '&' );

      if ( ( -1 !== $.inArray( 'action=vc_edit_form', params ) ) && ( -1 !== $.inArray( 'tag=vc-acf-field-picker', params ) ) ) {
        setTimeout( function() {
          updateEditFormData();
        }, 100 );
      }
    } );

    jqPanel.on( 'change', '[name="page_type"]', function onPageTypeChange( e ) {
      var pageType;

      pageType = $( this ).val();
      if ( ! pageType ) return;
      
      if ( jqPageSelector && jqPageSelector.length ) {
        jqPageSelector.find( '.vc-acf-field-picker-page-selector-list' ).html( '' );
      }
      if ( jqPageCustomField && jqPageCustomField.length ) {
        jqPageCustomField.trigger( 'input' );
      }
    } );
  }

  function modeEmbedded() {

    pageIdentity = acfVcEmbedded.identity;
    pageFields = acfVcEmbedded.fields;

    // Listen data update
    $( document ).on( 'vc_acf_field_picker_embedded_updated', function onDataUpdated() {
      pageIdentity = acfVcEmbedded.identity;
      pageFields = acfVcEmbedded.fields;
      updateEditFormData();
    } );

    setupFormEditListener();
  }

  function modeStandAlone() {

    // Identify page
    pageIdentity = {};
    if ( $( '#post_ID' ).length ) {
      pageIdentity.type = 'post';
      pageIdentity.id = $( '#post_ID' ).val();
      pageIdentity.postType = $( '#post_type' ).val();
    }
    else if ( $( '#edittag' ).length ) {
      pageIdentity.type = 'term';
      pageIdentity.id = $( '#edittag' ).find( '[name="tag_ID"]' ).val();
      pageIdentity.taxonomy = $( '#edittag' ).find( '[name="taxonomy"]' ).val();
    }
    else {
      pageIdentity.type = 'option';
    }

    // Listed global ajax form load
    $( document ).ajaxSuccess( function onGlobalAjaxSuccess( event, jqXHR, ajaxOptions, data ) {
      var params;
      
      if ( ! ajaxOptions || ! ajaxOptions.data || ( 'string' !== typeof ajaxOptions.data ) ) return;
      params = ajaxOptions.data.split( '&' );

      if ( -1 !== $.inArray( 'action=acf%2Fpost%2Fget_field_groups', params ) ) {
        setTimeout( function() {
          pageFields = getPageFields();
          updateEditFormData();
        }, 100 );
      }
    } );

    pageFields = getPageFields();

    setupFormEditListener();
  }


  function updateEditFormData() {
    var jqPageField, currentPageFieldValue, optionsHtml, currentPageTypeValue;
    
    if ( ! jqPanel.length || ! jqPanel.is( ':visible' ) ) return;
    if ( 'vc-acf-field-picker' != jqPanel.attr( 'data-vc-shortcode' ) ) return;

    jqPageField = jqPanel.find( '.page_field[name="page_field"]' );

    // Get current val
    currentPageFieldValue = jqPageField.attr( 'data-option' );

    // Compose new dropdown options/option groups
    optionsHtml = '';
    if ( $.isArray( pageFields ) ) {
      optionsHtml = getOptionsFromArray( pageFields );
    }
    else if ( pageFields !== null ) {
      $.each( pageFields, function( group, items ) {
        optionsHtml += '<optgroup label="' + group + '">' + getOptionsFromArray( items ) + '</optgroup>';
      });
    }

    // Update html
    jqPageField.html( optionsHtml );

    // Restore current val
    if ( currentPageFieldValue ) {
      jqPageField.val( currentPageFieldValue );
      jqPageField.attr( 'data-option', currentPageFieldValue );
    }

    jqPanel.addClass( 'vc-acf-field-picker-page-fields-ready' );

    // Page type
    jqPageTypeField = jqPanel.find( '.page_type[name="page_type"]' );
    currentPageTypeValue = jqPageTypeField.attr( 'data-option' );
    jqPageTypeField.html( customPageOptions );
    
    if ( currentPageTypeValue ) {
      jqPageTypeField.attr( 'data-option', currentPageTypeValue );
      jqPageTypeField.val( currentPageTypeValue );
      jqPageTypeField.trigger( 'change' );
    }


    // Page custom
    jqPageIdField = jqPanel.find( '.page_id[name="page_id"]' );
    jqPageCustomField = jqPanel.find( '.page_custom[name="page_custom"]' );
    initPageCustomSelector();

    updateDataContext( jqPanel );
  }

  function initPageCustomSelector() {
    var currentPageCustomFieldIdValue;

    jqPageCustomFieldId = jqPanel.find( '.page_custom_field_id[name="page_custom_field_id"]' );
    currentPageCustomFieldIdValue = jqPageCustomFieldId.attr( 'data-option' );
    jqPageCustomFieldId.slideUp();


    jqPageSelector = $( '<div>', { 'class': 'vc-acf-field-picker-page-selector' } )
      .append(
        $( '<div>', { 'class': 'vc-acf-field-picker-page-selector-heading' } )
          .html( 'Start typing' )
      )
      .append(
        $( '<div>', { 'class': 'vc-acf-field-picker-page-selector-list' } )
      )
    ;

    jqPageCustomField.parent().append( jqPageSelector );
    jqPageCustomField.on( 'input', function onPageChange() {
      var jqItem

      jqItem = $( this );
      jqPageSelector.find( '.vc-acf-field-picker-page-selector-heading' ).html( 'Loading...' );
      jqPageSelector.find( '.vc-acf-field-picker-page-selector-list' ).slideUp();

      if ( loadPagesInt ) clearTimeout( loadPagesInt );
      loadPagesInt = setTimeout( function() {
        loadPages( function onPagesLoaded( items ) {
          jqPageSelector.find( '.vc-acf-field-picker-page-selector-heading' ).html( items ? 'Select item below' : 'Nothing found' );
          jqPageSelector.find( '.vc-acf-field-picker-page-selector-list' ).html( items ).slideDown();

          if ( jqPageIdField.val() ) {
            jqPageSelector.find( '.vc-acf-field-picker-page-selector-list-item[data-id="' + jqPageIdField.val() + '"]' ).click();
          }
        });
      }, 800 );
    } );
    // Load pages initially
    setTimeout( function() {
      jqPageCustomField.trigger( 'input' );
    }, 100 );


    jqPageSelector.on( 'click', '.vc-acf-field-picker-page-selector-list-item', function onPageSelect( e ) {
      var pageTitle, pageId;

      e.preventDefault();

      $( this ).parent().children().removeClass( 'selected' );
      $( this ).addClass( 'selected' );

      pageId = $( this ).attr( 'data-id' );
      pageTitle = $( this ).find( 'span' ).text();
      if ( 0 == pageTitle.indexOf( '(' ) ) {
        pageTitle = pageId;
      }
      jqPageIdField.val( pageId );
      jqPageCustomField.val( pageTitle );
      updateDataContext();

      loadPageFields( function onPageFieldsLoaded( result ) {
        jqPageCustomFieldId.html( result );

        if ( currentPageCustomFieldIdValue ) {
          jqPageCustomFieldId.val( currentPageCustomFieldIdValue );
        }
        jqPageCustomFieldId.slideDown();
      });
    } );
  }

  function loadPages( callback ) {
    if ( xhrLoadPages ) xhrLoadPages.abort();

    xhrLoadPages = $.ajax({
      'type': 'post',
      'dataType': 'json',
      'url': vcAcfFieldPicker.url,
      'data': {
        'action': 'acf_field_picker_page_custom_items',
        'page_type': jqPageTypeField.find( ':selected' ).attr( 'rel' ) + ':' + jqPageTypeField.val(),
        'page': jqPageCustomField.val(),
        'nonce': vcAcfFieldPicker.nonce
      }
    })
      .done(function onLoadCustomPageOptionsDone( data ) {
        return callback( data && data.items ? data.items : false );
      })
      .fail(function onLoadCustomPageOptionsFail() {
        return callback( false );
      })
    ;
  }

  // function updateDataContext( jqPanel ) {
  function updateDataContext() {
    var jqFieldContext, jqDataContext;
    
    // Data context
    jqFieldContext = jqPanel.find( '.field_context[name="field_context"]' );
    jqDataContext = jqPanel.find( '.data_context[name="data_context"]' );

    // Update on change
    jqPanel.on( 'change', function() {
      jqDataContext.val( getDataContext( jqFieldContext.val() ) );
    } );

    // Set initially
    jqDataContext.val( getDataContext( jqFieldContext.val() ) );
  }

  function getDataContext( fieldContext ) {
    if ( ( 'option' == fieldContext ) || ( 'option' == pageIdentity.type ) ) {
      return 'option';
    }
    else if ( 'page_custom' == fieldContext ) {
      return jqPageIdField.val();
    }
    else if ( 'post' == pageIdentity.type ) {
      return pageIdentity.id;
    }
    else if ( 'term' == pageIdentity.type ) {
      return pageIdentity.taxonomy + '_' + pageIdentity.id;
    }
  }


  function getOptionsFromArray( items ) {
    var result;

    result = '';
    $.each( items, function( index, item ) {
      result += '<option class="' + item.name + '" value="' + item.name + '">' + item.label + '</option>';
    });

    return result;
  }

  
  function loadCustomPageOptions( callback ) {
    $.ajax({
      'type': 'post',
      'dataType': 'json',
      'url': vcAcfFieldPicker.url,
      'data': {
        'action': 'acf_field_picker_get_page_custom_options',
        'nonce': vcAcfFieldPicker.nonce
      }
    })
      .done(function onLoadCustomPageOptionsDone( data ) {
        return callback( data && data.options ? data.options : false );
      })
      .fail(function onLoadCustomPageOptionsFail() {
        return callback( false );
      })
    ;
  }

  function loadPageFields( callback ) {
    var url, pageId, pageType;

    if ( xhrLoadPageFields ) xhrLoadPageFields.abort();

    xhrLoadPageFields = $.ajax({
      'type': 'post',
      'dataType': 'json',
      'url': vcAcfFieldPicker.url,
      'data': {
        'action': 'acf_field_picker_page_custom_fields',
        'field_id': jqPageIdField.val(),
        'nonce': vcAcfFieldPicker.nonce
      }
    })
      .done(function onLoadPageFieldsDone( data ) {
        return callback( data.fields );
      })
      .fail(function onLoadPageFieldsFail() {
        return callback( false );
      })
    ;    
  }


  function getPageFields( jqContainer ) {
    var fields;

    jqContainer = jqContainer ? jqContainer : $( document );

    switch ( pageIdentity.type ) {
      case 'option':
      case 'post':
        fields = {};
        jqContainer.find( '.acf-postbox' ).not( '.acf-hidden' ).each(function iterateAcfGroups( index, group ) {
          var groupName, options = [];

          groupName = jqContainer.find( group ).find( '.hndle span' ).html();
          jqContainer.find( group )
            .find( '.acf-field' )
            .each(function iterateAcfFields( index, field ) {
              // Skip no-input
              if ( jqContainer.find( field ).is( '.acf-hidden,.acf-field-tab,.acf-field-message,.acf-field-clone' ) ) {
                return true;
              }
              // Skip sub-fields
              if ( jqContainer.find( field ).parent().closest( '.acf-field-repeater,.acf-field-flexible-content,.acf-field-group' ).length ) {
                return true;
              }
              options.push( {
                'name': jqContainer.find( field ).attr( 'data-name' ),
                'label': jqContainer.find( field ).find( '.acf-label label' ).html(),
              } );
          } );

          if ( groupName && options.length ) {
            fields[ groupName ] = options;
          }
        });
      break;
      case 'term':
        fields = [];
        jqContainer.find( '.acf-field' )
          .each(function iterateAcfFields( index, field ) {
            // Skip no-input
            if ( jqContainer.find( field ).is( '.acf-hidden,.acf-field-tab,.acf-field-message,.acf-field-clone' ) ) {
              return true;
            }
            // Skip sub-fields
            if ( jqContainer.find( field ).parent().closest( '.acf-field-repeater,.acf-field-flexible-content,.acf-field-group' ).length ) {
              return true;
            }
            fields.push( {
              'name': jqContainer.find( field ).attr( 'data-name' ),
              'label': jqContainer.find( field ).find( '.acf-label label' ).html(),
            } );
        } );

      break;
    }

    return fields;
  }
  

})( jQuery );
