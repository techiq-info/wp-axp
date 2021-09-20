(function ($) {
  'use strict';

  // ELEMENTS
  var $colorPicker = $('#wpSirColorPicker'),
    $sizesSelect = $('#wpSirResizeSizes'),
    $compressImageSlider = $('.wpSirSlider');

  // ------------------------------------------------------------------------------------------
  // INITILIAZE COLOR PICKER
  // ------------------------------------------------------------------------------------------

  $colorPicker.wpColorPicker();

  // ------------------------------------------------------------------------------------------
  // INITILIAZE MULTI SELECT
  // ------------------------------------------------------------------------------------------

  $sizesSelect.multipleSelect();

  // ------------------------------------------------------------------------------------------
  // INITILIAZE COMPRESSION SLIDER.
  // ------------------------------------------------------------------------------------------

  $compressImageSlider.each(function () {
    var handle = $(this).find('.wpSirSliderHandler');
    var inputElement = $('.' + $(this).data('input'));
    $(this).slider({
      create: function () {
        $(this).slider('value', inputElement.val());
        handle.text($(this).slider('value') + '%');
      },
      slide: function (event, ui) {
        handle.text(ui.value + '%');
        inputElement.val(ui.value);
      },
      change: function (event, ui) {
        handle.text(ui.value + '%');
      },
    });
  });

  // we'll wait until the box is rendered, so we can move it to the top.
  var wpsirLoadIntervalId = setInterval(() => {
    if ($('.wpsirProcessMediaLibraryImageWraper').length) {
      clearInterval(wpsirLoadIntervalId);

      $('.wpsirProcessMediaLibraryImageWraper')
        .insertBefore($('#wp-media-grid > .media-frame'));

      $(document).on('change', '#processMediaLibraryImage', function () {
        handleProcessMediaLibraryChange($(this));
      });
    }
  }, 100);

  /**
   * Allow user to decide whether to process image being uploaded.
   * We'll place a checkbox input where we cannot determine image attachment parent
   * under "Media > Library" and "Media > Add" new pages.
   */


  function handleProcessMediaLibraryChange($input) {
    var isProcessable = $input.is(':checked');
    // Normal HTML uploader.
    if ($('#html-upload-ui').length) {
      var $htmlProcessableInput = $('input[name="_processable_image"]');

      if ($htmlProcessableInput.length === 0) {
        $('#html-upload-ui').append(
          '<input type="hidden"  name="_processable_image" >'
        );
        $htmlProcessableInput = $($htmlProcessableInput.selector);
      }
      $htmlProcessableInput.val(isProcessable);
    }

    // Drag-and-drop uploader box.
    if (
      typeof wpUploaderInit === 'object' &&
      wpUploaderInit.hasOwnProperty('multipart_params')
    ) {
      wpUploaderInit.multipart_params._processable_image = isProcessable;
    }

    // Media library modal.
    if (
      wp.media &&
      wp.media.frame &&
      wp.media.frame.uploader &&
      wp.media.frame.uploader.uploader
    ) {
      wp.media.frame.uploader.uploader.param(
        '_processable_image',
        isProcessable
      );
    }
  }


  $('#wp-sir-enable-trim').on('change', function () {
    if ($(this).is(':checked')) {
      $('#wp-sir-trim-feather-wrap').removeClass('hidden');
    } else {
      $('#wp-sir-trim-feather-wrap').addClass('hidden');
    }
  }).change();
})(jQuery);
