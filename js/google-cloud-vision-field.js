(function ($, Drupal) {
  Drupal.behaviors.googleCloudVision = {
    attach: function (context, settings) {
      var $googleVisionFields = $('[data-drupal-selector="google-field-wrapper"]');
      $($googleVisionFields, context).once('googleCloudVisionFields').each(function (i, fieldWrapper) {
        $(fieldWrapper).find('input[type="file"]').on('change', function() {
          if (this.files.length !== 0) {
            populateBase64(this.files[0], $(fieldWrapper).find('input[type="hidden"]'));
          }
        });
      });

      function populateBase64(file, $hiddenInput) {
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function () {
          var fileBytes = reader.result.substring(reader.result.indexOf('base64,') + 7);
          $hiddenInput.val(fileBytes);
        };
        reader.onerror = function (error) {
          console.log('Error: ', error);
        };
     }
    }
  };
})(jQuery, Drupal);
