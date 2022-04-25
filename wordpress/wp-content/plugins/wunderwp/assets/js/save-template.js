(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

(function ($, window) {
  var wunderwp = window.wunderwp || {};

  var SaveTemplateModule = elementorModules.ViewModule.extend({

    getDefaultSettings: function getDefaultSettings() {
      return {
        selectors: {
          saveButton: '.wunderwp-save-template',
          deleteButton: '.wunderwp-delete-template'
        }
      };
    },

    bindEvents: function bindEvents() {
      var selectors = this.getSettings('selectors');

      jQuery(document).on('click', selectors.saveButton, this.onSaveButtonClick);
      jQuery(document).on('click', selectors.deleteButton, this.onDeleteButtonClick);
    },

    onInit: function onInit() {
      elementorModules.ViewModule.prototype.onInit.apply(this, arguments);
    },

    onSaveButtonClick: function onSaveButtonClick(event) {
      event.preventDefault();

      var self = this;
      var action = $(event.target);
      var initialText = action.text();
      var title = action.parents('.title').find('.row-title').text();

      action.text('Saving to WunderWP');
      console.group('"' + title + '" template is saving to WunderWP.');

      var ajaxOptions = {
        data: {
          'post_id': action.data('post-id')
        },
        success: function success(data) {
          console.log('ID generated "' + data.template_id + '".');
          var assets = parseInt(data.assets);
          data.action = action;

          if (assets) {
            console.log('Uploading ' + assets + ' asset(s) started.');
            return self.saveAsset(data);
          }

          self.saveContent(data);
        },
        error: function error(data) {
          elementorCommon.dialogsManager.createWidget('alert', {
            headerMessage: 'Error!',
            message: data
          }).show();

          action.text(initialText);
          console.groupEnd();
        }
      };

      elementorCommon.ajax.addRequest('wunderwp_save_template', ajaxOptions);
    },

    saveContent: function saveContent(data) {
      var originalData = data;

      var ajaxOptions = {
        data: {
          'post_id': originalData.post_id
        },
        success: function success(data) {
          console.log('Content uploaded.');
          console.log('The template saved successfully. \uD83C\uDF89');
          console.groupEnd();

          originalData.action.attr('class', 'wunderwp-delete-template');
          originalData.action.text('Delete from WunderWP');
        },
        error: function error(data) {
          elementorCommon.dialogsManager.createWidget('alert', {
            headerMessage: 'Error!',
            message: data
          }).show();

          console.log('Uploading content failed.');
          console.groupEnd();
        }
      };

      elementorCommon.ajax.addRequest('wunderwp_save_template_content', ajaxOptions);
    },

    saveAsset: function saveAsset(data) {
      var self = this;
      var originalData = data;

      var ajaxOptions = {
        data: {
          'post_id': originalData.post_id
        },
        success: function success(data) {
          console.log('"' + data.uploaded + '" uploaded.');

          if (data.remaining) {
            return self.saveAsset(originalData);
          }

          self.saveContent(originalData);
        },
        error: function error(data) {
          console.log('"' + data.uploaded + '" upload failed.');

          if (data.remaining) {
            return self.saveAsset(originalData);
          }
        }
      };

      elementorCommon.ajax.addRequest('wunderwp_save_template_asset', ajaxOptions);
    },

    onDeleteButtonClick: function onDeleteButtonClick(event) {
      event.preventDefault();

      var self = $(event.target);
      var initialText = self.text();

      self.text('Deleting from WunderWP');

      var ajaxOptions = {
        data: {
          'post_id': self.data('post-id'),
          'source': 'custom'
        },
        success: function success(data) {
          console.log('Template with ID of "' + data.template_id + '" deleted from WunderWP.com');

          self.attr('class', 'wunderwp-save-template');
          self.text('Save to WunderWP');
        },
        error: function error(data) {
          elementorCommon.dialogsManager.createWidget('alert', {
            headerMessage: 'Error!',
            message: data
          }).show();

          self.text(initialText);
        }
      };

      elementorCommon.ajax.addRequest('wunderwp_delete_template', ajaxOptions);
    }
  });

  wunderwp.saveTemplate = new SaveTemplateModule();
  window.wunderwp = wunderwp;
})(jQuery, window);

},{}]},{},[1]);
