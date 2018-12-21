var VcSnippets = (function VcSnippets( $ ) {

  var api, categories, posts;


  api = {};
  posts = [];


  api.getCategories = function getCategories( callback ) {
    if ( categories ) {
      return callback( categories );
    }

    $.ajax({
      'type': 'post',
      'dataType': 'json',
      'url': vc_snippet.url,
      'data': {
        'action': 'vc_snippet_get_categories',
        'nonce': vc_snippet.nonce,
      }
    })
      .done(function onGetCategoriesDone( data ) {
        return callback( data );
      })
      .fail(function onGetCategoriesFail() {
        return callback( false );
      })
    ;

  };


  api.getCategoriesOptionsHtml = function getCategoriesOptionsHtml( items ) {
    var result;

    result = '';
    result += '<option class="" value="">&mdash; Select &mdash;</option>';
    $.each( items, function iterateCategories( index, item ) {
      result += '<option class="' + item.slug + '" value="' + item.slug + '">' + item.name + '</option>';
    });

    return result;
  };


  api.getPosts = function getPosts( categories, callback ) {

    $.ajax({
      'type': 'post',
      'dataType': 'json',
      'url': vc_snippet.url,
      'data': {
        'action': 'vc_snippet_get_posts',
        'nonce': vc_snippet.nonce,
        'categories': categories
      }
    })
      .done(function onGetCategoriesDone( data ) {
        return callback( data );
      })
      .fail(function onGetCategoriesFail() {
        return callback( false );
      })
    ;

  };


  api.getPostsOptionsHtml = function getPostsOptionsHtml( items ) {
    var result;

    result = '';
    $.each( items, function iteratePosts( index, item ) {
      result += '<option value="' + item.ID + '">' + item.post_title + '</option>';
    });

    return result;
  };


  return api;


})( jQuery );