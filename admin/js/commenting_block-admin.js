(function ($) {
  'use strict';
  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  // Ready.
  $(document).ready(function () {

    $(document).on('click', '.user-comented-on', function (e) {
      $('#custom-history-popup, #history-toggle').toggleClass('active');
      e.preventDefault();
      const dataid = $(this).attr('data-id');
      const topOfPopup = $("#" + dataid).offset().top
      $('.edit-post-layout__content').animate({
        scrollTop: topOfPopup
      }, 1000);

      $('#' + dataid + ', [datatext="' + dataid + '"]').addClass('focus');
      setTimeout(function () {
        $('[datatext="' + dataid + '"]').removeClass('focus');
      }, 1500);

    });

    $('.shareCommentContainer textarea').on('click', function () {
      $(this).parent().addClass('hovered');
    });

    $(document).on('click', '#history-toggle', function () {
      $('#custom-history-popup, #history-toggle').toggleClass('active');

      //if (!$('#custom-history-popup').hasClass('loaded')) {
      if ($('#custom-history-popup').hasClass('active')) {
        $('#custom-history-popup').addClass('loaded');

        const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();

        // Fetch comments from db.
        var data = {
          'action': 'mdgcf_comments_history',
          'currentPostID': CurrentPostID,
          'limit': 10,
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function (response) {
          $('#custom-history-popup').html(response);
        });

      }
    })
  });

  // Load.
  $(window).load(function () {
    $('.cid_popup_hover').parents('.wp-block.editor-block-list__block.block-editor-block-list__block').addClass('parent_cid_popup_hover');
  });

  $(document).on('click', '.markup', function (event) {
    $('.markup').removeClass('my-class');
    $(this).attr('data_name', true);
    $(this).addClass('my-class');
  });
  /*$(document).on('click', function (event) {
     if( ! $(this).hasClass('markup') ){
         $(this).attr('data_name', false);
         $(this).removeClass('my-class');
     }
  });*/
  $(document).mouseup(function (e) {
    var container = $(".edit-popup-option");
    var markup = $(".markup");
    // if the target of the click isn't the container nor a descendant of the container
    if (!container.is(e.target) && container.has(e.target).length === 0) {
      markup.attr('data_name', false);
      markup.removeClass('my-class');
    }
  });
  $(document).on('click', '.dashicon.dashicons-ellipsis', function (e) {
    $(this).parents('.buttons-holder').toggleClass('is_active');
    e.stopPropagation();
  });

  $(document).on('click', function (e) {
    if ($(e.target).is(".dashicon.dashicons-ellipsis") === false) {
      $(".buttons-holder").removeClass('is_active');
    }
  });

})(jQuery);
