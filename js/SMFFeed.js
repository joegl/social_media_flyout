/**
 * @file
 * JavaScript for SMFFeed
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.SMFFeed = {
    attach: function(context, settings) {

      // add the smf_flyout container if it doesn't exist
      if(!$('#smf_flyout').length > 0) {
        $('body').append('<div id="smf_flyout" class="smf-flyout"></div>');
      }

    }
  };

})(jQuery, Drupal);