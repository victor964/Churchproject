(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

(function ($) {
  /**
   * Manage preset settings.
   *
   * @since 1.0.0
   */
  var Presets = {
    init: function init() {
      this.bindEvents();
    },

    bindEvents: function bindEvents() {
      $(document).on('click', '#wunderwp-preset-library-sync-button', function () {
        var self = this;
        $(this).removeClass('success').removeClass('error').addClass('loading');

        wp.ajax.post('wunderwp_sync_libraries', {
          _ajax_nonce: window.wunderwp_sync_library_nonce,
          library: 'presets'
        }).done(function () {
          $(self).removeClass('loading').addClass('success');
        }).fail(function () {
          $(self).removeClass('loading').addClass('error');
        });

        return false;
      });

      $(document).on('click', '.wunderwp-connect-notice .notice-dismiss', function () {
        var nonce = $(this).closest('.wunderwp-connect-notice').data('nonce');

        wp.ajax.post('wunderwp_dismiss_connect_notice', {
          _ajax_nonce: nonce
        });
      });
    }
  };

  Presets.init();

  /**
   * Navigate feedback notification bar notice.
   *
   * @since 1.6.0
   */
  $('.wunderwp-feedback-notification-bar-notice-step button').on('click', function () {
    var $step = $(this).closest('.wunderwp-feedback-notification-bar-notice-step');
    var step = $(this).data('step');

    if (!step) {
      $('.wunderwp-feedback-notification-bar-notice').find('.notice-dismiss').trigger('click');

      return;
    }

    $step.addClass('hidden');

    $step.siblings('[data-step="' + step + '"]').removeClass('hidden');
  });

  /**
   * Dismiss feedback notification bar notice on close button click.
   *
   * @since 1.6.0
   */
  $(document).on('click', '.wunderwp-feedback-notification-bar-notice .notice-dismiss', function (event) {
    var nonce = $(this).closest('.wunderwp-feedback-notification-bar-notice').data('nonce');

    wp.ajax.post('wunderwp_dismiss_feedback_notification_bar_notice', {
      _ajax_nonce: nonce
    });
  });

  /**
   * Dismiss survey notification bar notice on close button click.
   *
   * @since 1.6.0
   */
  $(document).on('click', '.wunderwp-survey-notification-bar-notice .notice-dismiss', function (event) {
    var nonce = $(this).closest('.wunderwp-survey-notification-bar-notice').data('nonce');

    wp.ajax.post('wunderwp_dismiss_survey_notification_bar_notice', {
      _ajax_nonce: nonce
    });
  });

  /**
   * Dismiss survey notification bar notice on cta button click.
   *
   * @since 1.6.0
   */
  $(document).on('click', '.wunderwp-survey-notification-bar-notice-cta', function () {
    $('.wunderwp-survey-notification-bar-notice .notice-dismiss').trigger('click');
  });
})(jQuery);

},{}]},{},[1]);
