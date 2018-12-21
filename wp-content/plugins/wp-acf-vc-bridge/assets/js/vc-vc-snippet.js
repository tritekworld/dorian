(function( $ ) {

  var adminBaseUrl;

  $( function onDomReady() {
    var jqEditPanel, jqPostsSelect, currentId;

    adminBaseUrl = wp && wp.ajax && wp.ajax.settings && wp.ajax.settings.url ? wp.ajax.settings.url : '/wp-admin/';
    adminBaseUrl = adminBaseUrl.replace( 'admin-ajax.php', '' );

    initElementView();

    jqEditPanel = $( '#vc_ui-panel-edit-element' );
    if ( ! jqEditPanel.length ) {
      return;
    }

    $( document ).ajaxSuccess( function onGlobalAjaxSuccess( event, jqXHR, ajaxOptions, data ) {
      var params;
      
      if ( ! ajaxOptions || ! ajaxOptions.data ) return;
      params = ajaxOptions.data.split( '&' );

      if ( -1 == $.inArray( 'action=vc_edit_form', params ) ) {
        return;
      }

      if ( -1 !== $.inArray( 'tag=vc-vc-snippet', params ) ) {
        setTimeout( function() {
          embedFilter();
        }, 100 );
      }

    } );

  function initElementView() {
    window.VcVcSnippetView = vc.shortcode_view.extend( {
      elementTemplate: false,
      $wrapper: false,
      changeShortcodeParams: function ( model ) {
        var params, template, content, title;
        
        window.VcVcSnippetView.__super__.changeShortcodeParams.call( this, model );
        params = _.extend( {}, model.get( 'params' ) );
        if ( ! this.$wrapper ) {
          this.$wrapper = this.$el.find( '.wpb_element_wrapper' );
        }
        if ( ! this.elementTemplate ) {
          this.elementTemplate = this.$wrapper.html();
        }
        if ( _.isObject( params ) ) {
          content = [];

          $.each( model.view.params.id.value, function( name, value ) {
            if ( value == params.id ) {
              title = name;
              return false;
            }
          } );
          content.push( title );

          content = content.join( '' );
          template = _.template( this.elementTemplate, vc.templateOptions.custom );
          this.$wrapper.html( template( { content: content } ) );
        }
      }
    } );
  }

    function  embedLink() {
      var jqLink;

      currentId = jqEditPanel.find( '[name="id"]' ).val();
      if ( ! currentId ) return;

      jqLink = $( '<a>', { 'class': 'vc-vc-snippet-link vc_btn vc_btn-primary vc_btn-sm', 'href': adminBaseUrl + 'post.php?post=' + currentId + '&action=edit', 'target': '_blank' } ).html( 'Edit Snippet' );

      $( '<div>', { 'class': 'vc_col-xs-12 vc_column' } )
        .append(
          jqLink
        )
        .insertBefore(
          jqPostsSelect.closest( '.vc_shortcode-param' )
        )
      ;

      jqPostsSelect.on( 'change', function onVcSnippetPostChange() {
        jqLink.attr( 'href', adminBaseUrl + 'post.php?post=' + $( this ).val() + '&action=edit' );
      } );

    };

    function embedFilter() {
      var jqFilterSelect, jqFilter;

      jqPostsSelect = jqEditPanel.find( '.wpb_vc_param_value[name="id"]' );
      if ( ! jqPostsSelect.length ) {
        return;
      }

      embedLink();

      VcSnippets.getCategories( function onGetCategories( categories ) {
        if ( ! categories || ! categories.length ) {
          return;
        }

        jqFilterSelect = $( '<select>' ).html( VcSnippets.getCategoriesOptionsHtml( categories ) );
        jqFilter = $( '<div>', { 'class': 'vc_col-xs-12 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param' } )
          .append(
            $( '<div>', { 'class': 'wpb_element_label' } ).html( 'Filter by category' )
          )
          .append(
            jqFilterSelect
          )
        ;
        jqFilter.insertBefore(
          jqPostsSelect.closest( '.vc_shortcode-param' )
        );


        jqFilterSelect.on( 'change', function onCategoryFilterChange() {
          var currentValue;

          currentValue = jqPostsSelect.val();
          jqPostsSelect.slideUp();
          VcSnippets.getPosts( jqFilterSelect.val(), function onGetFilteredPosts( posts ) {
            if ( ! posts ) return;
            jqPostsSelect.html( VcSnippets.getPostsOptionsHtml( posts ) ).val( currentValue );

            if ( ! jqPostsSelect.val() && jqPostsSelect.find( 'option' ).length ) {
              jqPostsSelect.val( jqPostsSelect.children().first().attr( 'value' ) );
            }

            jqPostsSelect.slideDown();
          });
        } );


      });
    }
    
  } );
})( jQuery );