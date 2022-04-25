(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
var CustomPresets = elementor.modules.controls.BaseData.extend({
  excludeControls: [
  /* Elementor controls */
  'text', 'number', 'textarea', 'icon', 'icons', 'url', 'media', 'gallery', 'wysiwyg', 'date_time',
  /* Raven controls */
  'raven_media', 'raven-posts', 'raven_file_uploader'],
  widgetType: '',
  syncing: false,

  ui: function ui() {
    var ui = elementor.modules.controls.BaseMultiple.prototype.ui.apply(this, arguments);

    ui.presetItems = '.wunderwp-element-custom-presets';
    ui.presetItem = '.wunderwp-element-custom-presets-item';
    ui.presetAddBtn = '.wunderwp-element-custom-presets-add-btn';
    ui.presetDeleteBtn = '.wunderwp-element-custom-presets-item-delete';
    ui.presetApplyBtn = '.wunderwp-element-custom-presets-item-apply';
    ui.presetInput = '.wunderwp-element-custom-presets-input';
    ui.presetInputWrapper = '.wunderwp-element-custom-presets-input-wrapper';

    return ui;
  },

  events: function events() {
    return _.extend(elementor.modules.controls.BaseMultiple.prototype.events.apply(this, arguments), {
      'click @ui.presetAddBtn ': 'onAddPreset',
      'click @ui.presetDeleteBtn': 'onDeletePreset',
      'click @ui.presetApplyBtn': 'onApplyPreset'
    });
  },

  onReady: function onReady() {
    this.widgetType = this.settingsModel().get('widgetType');

    window.wunderWpCustomPresets = window.wunderWpCustomPresets || {};

    this.loadPresets(this.widgetType);

    elementor.channels.data.bind('wunderwp:presets:sync', this.onPresetsSync.bind(this));
  },

  onPresetsSync: function onPresetsSync(element) {
    var _this = this;

    if (this.syncing) {
      return;
    }

    this.syncing = true;

    var presets = window.wunderWpCustomPresets || {};

    window.wunderWpCustomPresets = {};

    this.loadPresets(this.widgetType, function () {
      _this.syncing = false;
    }, function () {
      _this.syncing = false;
      window.wunderWpCustomPresets = presets;
    });
  },

  loadPresets: function loadPresets(widget, successCallback, errorCallback) {
    var _this2 = this;

    if (this.isPresetDataLoaded()) {
      this.ui.presetItems.removeClass('loading');

      return;
    }

    this.ui.presetItems.addClass('loading');

    wp.ajax.post('wunderwp_element_custom_presets', {
      '_ajax_nonce': window.wunderwp_editor.element_custom_presets_nonce,
      'element': widget
    }).done(function (data) {
      if (successCallback) {
        successCallback();
      }

      _this2.setPresets(data);
      _this2.setValue(null);
      _this2.ui.presetItems.removeClass('loading');

      _this2.render();
    }).fail(function () {
      if (errorCallback) {
        errorCallback();
      }

      _this2.setPresets([]);
      _this2.ui.presetItems.removeClass('loading');
    });
  },

  getPresets: function getPresets() {
    if (!window.wunderWpCustomPresets) {
      return [];
    }

    return jQuery.extend(true, [], window.wunderWpCustomPresets[this.widgetType]) || [];
  },

  setPresets: function setPresets(presets) {
    window.wunderWpCustomPresets[this.widgetType] = presets;
  },

  setPreset: function setPreset(preset) {
    var presets = window.wunderWpCustomPresets[this.widgetType];

    if (!presets) {
      window.wunderWpCustomPresets[this.widgetType] = [preset];

      return;
    }

    presets.unshift(preset);
  },

  isPresetDataLoaded: function isPresetDataLoaded() {
    if (window.wunderWpCustomPresets[this.widgetType]) {
      return true;
    }

    return false;
  },

  onApplyPreset: function onApplyPreset(e) {
    var $applyBtn = jQuery(e.target).closest(this.ui.presetApplyBtn);
    var id = jQuery(e.target).closest(this.ui.presetItem).data('preset-id');

    var preset = _.find(window.wunderWpCustomPresets[this.widgetType], { id: id.toString() });

    if (!preset) {
      return;
    }

    preset = jQuery.extend(true, {}, preset);

    $applyBtn.tipsy('hide');

    this.applyPreset(this.elementDefaultSettings(), preset);
    this.setValue(null);
  },

  onAddPreset: function onAddPreset(e) {
    var _this3 = this;

    var title = jQuery(e.target).closest(this.ui.presetInputWrapper).find(this.ui.presetInput).val();

    if (!title || title.trim().length === 0) {
      return;
    }

    var settings = jQuery.extend(true, {}, this.settingsModel().attributes);

    delete settings['wunderwp_presets'];
    delete settings['wunderwp_custom_presets'];

    var data = {
      title: title,
      content: JSON.stringify(settings),
      element_type: this.settingsModel().get('widgetType'),
      elementor_version: window.elementor.config.version,
      domain: window.location.hostname
    };

    this.ui.presetAddBtn.attr('disabled', 'disabled');

    this.ui.presetAddBtn.find('.fa').removeClass('fa-plus-circle').addClass('fa-spin fa-spinner');

    wp.ajax.post('wunderwp_store_custom_preset', {
      '_ajax_nonce': window.wunderwp_editor.store_custom_preset_nonce,
      'data': data
    }).done(function (data) {
      _this3.ui.presetAddBtn.removeAttr('disabled');

      _this3.ui.presetAddBtn.find('.fa').removeClass('fa-spin').removeClass('fa-spinner').addClass('fa-plus-circle');

      _this3.hideToolTip();
      _this3.setPreset(data);
      _this3.render();
    }).fail(function () {
      _this3.ui.presetAddBtn.removeAttr('disabled');

      _this3.ui.presetAddBtn.find('.fa').removeClass('fa-spin').removeClass('fa-spinner').addClass('fa-plus-circle');
    });
  },

  onDeletePreset: function onDeletePreset(e) {
    var _this4 = this;

    var translations = window.wunderwp_editor;
    var dialog = null;
    var $presetItem = jQuery(e.target).closest(this.ui.presetItem);
    var preset = _.findWhere(this.getPresets(), { id: $presetItem.data('preset-id').toString() });

    var options = {
      id: 'elementor-fatal-error-dialog',
      headerMessage: preset.title,
      message: translations.dialog_delete_preset_msg,
      position: {
        my: 'center center',
        at: 'center center'
      },
      strings: {
        confirm: translations.delete,
        cancel: translations.cancel
      },
      onConfirm: function onConfirm() {
        _this4.deletePreset(preset, dialog);
      },
      onCancel: function onCancel() {
        dialog.hide();
      },
      hide: {
        onBackgroundClick: false,
        onButtonClick: false
      }
    };

    dialog = window.elementor.dialogsManager.createWidget('confirm', options);

    this.hideToolTip();

    dialog.show();
  },

  deletePreset: function deletePreset(preset, dialog) {
    var _this5 = this;

    var translations = window.wunderwp_editor;

    dialog.setMessage(translations.deleting + ' <span class="fa fa-spin fa-spinner"></span>');

    wp.ajax.post('wunderwp_delete_custom_preset', {
      '_ajax_nonce': window.wunderwp_editor.delete_custom_preset_nonce,
      'id': preset.id,
      'element_type': preset.element_type
    }).done(function () {
      var cacheIndex = _.findIndex(window.wunderWpCustomPresets[_this5.widgetType], { id: preset.id });

      window.wunderWpCustomPresets[_this5.widgetType].splice(cacheIndex, 1);

      _this5.render();

      dialog.hide();
    }).fail(function () {
      dialog.setMessage(translations.dialog_delete_preset_error_msg);
    });
  },

  settingsModel: function settingsModel() {
    var currentVersion = window.elementor.config.version;
    var compareVersions = window.elementor.helpers.compareVersions;


    if (compareVersions(currentVersion, '2.8.0', '<')) {
      return this.elementSettingsModel;
    }

    return this.container.settings;
  },

  applyPreset: function applyPreset() {
    var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var preset = arguments[1];

    var presetSettings = JSON.parse(preset.content);

    for (var setting in presetSettings) {
      if (this.model.get('name') === setting) {
        continue;
      }

      var control = this.settingsModel().controls[setting];

      if (typeof control === 'undefined') {
        continue;
      }

      if (this.excludeControls.indexOf(control.type) >= 0) {
        continue;
      }

      if (control.is_repeater) {
        settings[setting] = new window.Backbone.Collection(this.getRepeaterSetting(control, setting, presetSettings), { model: _.partial(this.createRepeaterItemModel, _, _, control.fields) });

        continue;
      }

      settings[setting] = presetSettings[setting];
    }

    // Keep local settings.
    settings['wunderwp_presets'] = null;
    settings['wunderwp_custom_presets'] = this.settingsModel().get('wunderwp_custom_presets');

    this.applyPresetSettings(settings);
  },

  applyPresetSettings: function applyPresetSettings(settings) {
    var currentVersion = window.elementor.config.version;
    var compareVersions = window.elementor.helpers.compareVersions;


    if (compareVersions(currentVersion, '2.8.0', '<')) {
      for (var setting in this.settingsModel().controls) {
        this.settingsModel().set(setting, settings[setting]);
      }

      return;
    }

    this.settingsModel().set(settings);

    this.container.view.renderUI();
    this.container.view.renderHTML();
  },

  getRepeaterSetting: function getRepeaterSetting(control, setting, presetSettings) {
    var repeaterCurrentSettings = this.settingsModel().get(setting);
    var repeaterPresetSettings = jQuery.extend(true, [], presetSettings[setting]);
    var repeaterSettings = [];

    if (!repeaterCurrentSettings.models) {
      return;
    }

    for (var i = 0; i < repeaterCurrentSettings.models.length; i++) {
      var model = repeaterCurrentSettings.models[i];
      var modelSettings = {};

      for (var attr in model.controls) {
        if (this.excludeControls.indexOf(model.controls[attr].type) >= 0) {
          modelSettings[attr] = model.get(attr);

          continue;
        }

        if (i > repeaterPresetSettings.length - 1) {
          modelSettings[attr] = model.get(attr);

          continue;
        }

        modelSettings[attr] = repeaterPresetSettings[i][attr];
      }

      repeaterSettings.push(modelSettings);
    }

    return repeaterSettings;
  },

  createRepeaterItemModel: function createRepeaterItemModel(attrs, options, fields) {
    options = options || {};

    options.controls = fields;

    if (!attrs._id) {
      attrs._id = elementor.helpers.getUniqueID();
    }

    return new window.elementorModules.editor.elements.models.BaseSettings(attrs, options);
  },

  elementDefaultSettings: function elementDefaultSettings() {
    var self = this,
        controls = self.settingsModel().controls,
        settings = {};

    jQuery.each(controls, function (controlName, control) {
      if (self.excludeControls.indexOf(control.type) >= 0) {
        settings[controlName] = self.settingsModel().get(controlName);

        return;
      }

      settings[controlName] = control.default;
    });

    return settings;
  },

  hideToolTip: function hideToolTip() {
    jQuery.each(jQuery(this.ui.presetDeleteBtn.selector), function () {
      jQuery(this).tipsy('hide');
    });

    jQuery.each(jQuery(this.ui.presetApplyBtn.selector), function () {
      jQuery(this).tipsy('hide');
    });
  },

  onBeforeDestroy: function onBeforeDestroy() {
    elementor.channels.data.unbind('wunderwp:presets:sync');
  }
});

exports.default = CustomPresets;

},{}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
var Presets = elementor.modules.controls.BaseData.extend({
  excludeControls: [
  /* Elementor controls */
  'text', 'number', 'textarea', 'icon', 'icons', 'url', 'media', 'gallery', 'wysiwyg', 'date_time',
  /* Raven controls */
  'raven_media', 'raven-posts', 'raven_file_uploader'],

  syncing: false,

  ui: function ui() {
    var ui = elementor.modules.controls.BaseMultiple.prototype.ui.apply(this, arguments);

    ui.presetItems = '.wunderwp-element-presets';
    ui.presetItem = '.wunderwp-element-presets-item';

    return ui;
  },

  events: function events() {
    return _.extend(elementor.modules.controls.BaseMultiple.prototype.events.apply(this, arguments), {
      'click @ui.presetItem ': 'onPresetClick'
    });
  },

  onReady: function onReady() {
    window.wunderWpPresets = window.wunderWpPresets || {};

    this.loadPresets(this.settingsModel().get('widgetType'));

    elementor.channels.data.bind('wunderwp:element:after:reset:style', this.onElementAfterResetStyle.bind(this));
    elementor.channels.data.bind('wunderwp:element:before:reset:style', this.onElementBeforeResetStyle.bind(this));
    elementor.channels.data.bind('wunderwp:presets:sync', this.onPresetsSync.bind(this));
  },

  onElementAfterResetStyle: function onElementAfterResetStyle(model) {
    if (model.id !== this.container.model.id) {
      return;
    }

    if (this.isRendered) {
      this.render();
    }
  },

  onElementBeforeResetStyle: function onElementBeforeResetStyle(model) {
    if (model.id !== this.container.model.id) {
      return;
    }

    this.applyPresetSettings(this.elementDefaultSettings());
  },

  onPresetClick: function onPresetClick(e) {
    var $preset = jQuery(e.currentTarget);
    $preset.siblings('.wunderwp-element-presets-item').removeClass('active');
    $preset.addClass('active');

    var preset = _.find(this.getPresets(), { id: $preset.data('preset-id') });

    this.applyPreset(this.elementDefaultSettings(), preset);
  },

  onPresetsSync: function onPresetsSync(element) {
    var _this = this;

    if (this.syncing) {
      return;
    }

    this.syncing = true;

    var presets = window.wunderWpPresets || {};

    window.wunderWpPresets = {};

    this.loadPresets(this.settingsModel().get('widgetType'), function () {
      _this.syncing = false;
    }, function () {
      _this.syncing = false;
      window.wunderWpPresets = presets;
    });
  },

  settingsModel: function settingsModel() {
    var currentVersion = window.elementor.config.version;
    var compareVersions = window.elementor.helpers.compareVersions;


    if (compareVersions(currentVersion, '2.8.0', '<')) {
      return this.elementSettingsModel;
    }

    return this.container.settings;
  },

  applyPreset: function applyPreset() {
    var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var preset = arguments[1];

    for (var setting in preset.widget.settings) {
      if (this.model.get('name') === setting) {
        continue;
      }

      if (setting === 'wunderwp_custom_presets') {
        continue;
      }

      var control = this.settingsModel().controls[setting];

      if (typeof control === 'undefined') {
        continue;
      }

      if (this.excludeControls.indexOf(control.type) >= 0) {
        continue;
      }

      if (control.is_repeater) {
        settings[setting] = new window.Backbone.Collection(this.getRepeaterSetting(control, setting, preset), { model: _.partial(this.createRepeaterItemModel, _, _, control.fields) });

        continue;
      }

      settings[setting] = preset.widget.settings[setting];
    }

    // Keep local settings.
    settings['wunderwp_presets'] = this.settingsModel().get('wunderwp_presets');
    settings['wunderwp_custom_presets'] = this.settingsModel().get('wunderwp_custom_presets');

    this.applyPresetSettings(settings);
  },

  applyPresetSettings: function applyPresetSettings(settings) {
    var currentVersion = window.elementor.config.version;
    var compareVersions = window.elementor.helpers.compareVersions;


    if (compareVersions(currentVersion, '2.8.0', '<')) {
      for (var setting in this.settingsModel().controls) {
        this.settingsModel().set(setting, settings[setting]);
      }

      return;
    }

    this.settingsModel().set(settings);

    this.container.view.renderUI();
    this.container.view.renderHTML();
    this.setValue(null);
  },

  getRepeaterSetting: function getRepeaterSetting(control, setting, preset) {
    var repeaterCurrentSettings = this.settingsModel().get(setting);
    var repeaterPresetSettings = jQuery.extend(true, [], preset.widget.settings[setting]);
    var repeaterSettings = [];

    if (!repeaterCurrentSettings.models) {
      return;
    }

    for (var i = 0; i < repeaterCurrentSettings.models.length; i++) {
      var model = repeaterCurrentSettings.models[i];
      var modelSettings = {};

      for (var attr in model.controls) {
        if (this.excludeControls.indexOf(model.controls[attr].type) >= 0) {
          modelSettings[attr] = model.get(attr);

          continue;
        }

        if (i > repeaterPresetSettings.length - 1) {
          modelSettings[attr] = model.get(attr);

          continue;
        }

        modelSettings[attr] = repeaterPresetSettings[i][attr];
      }

      repeaterSettings.push(modelSettings);
    }

    return repeaterSettings;
  },

  createRepeaterItemModel: function createRepeaterItemModel(attrs, options, fields) {
    options = options || {};

    options.controls = fields;

    if (!attrs._id) {
      attrs._id = elementor.helpers.getUniqueID();
    }

    return new window.elementorModules.editor.elements.models.BaseSettings(attrs, options);
  },

  elementDefaultSettings: function elementDefaultSettings() {
    var self = this,
        controls = self.settingsModel().controls,
        settings = {};

    jQuery.each(controls, function (controlName, control) {
      if (self.excludeControls.indexOf(control.type) >= 0) {
        settings[controlName] = self.settingsModel().get(controlName);

        return;
      }

      settings[controlName] = control.default;
    });

    return settings;
  },

  loadPresets: function loadPresets(widget, successCallback, errorCallback) {
    var _this2 = this;

    if (this.isPresetDataLoaded()) {
      if (this.getPresets().length === 0) {
        return;
      }

      if (this.ui.presetItem.length === 0) {
        this.render();
      }

      return;
    }

    this.ui.presetItems.addClass('loading');

    wp.ajax.post('wunderwp_element_presets', {
      '_ajax_nonce': window.wunderwp_editor.element_presets_nonce,
      'element': widget
    }).done(function (data) {
      if (successCallback) {
        successCallback();
      }

      _this2.ui.presetItems.removeClass('loading');
      _this2.setPresets(data);
      _this2.setValue(null);
      _this2.render();
    }).fail(function () {
      if (errorCallback) {
        errorCallback();
      }

      _this2.ui.presetItems.removeClass('loading');
      _this2.setPresets([]);
    });
  },

  getPresets: function getPresets() {
    if (!window.wunderWpPresets) {
      return [];
    }

    return window.wunderWpPresets[this.settingsModel().get('widgetType')] || [];
  },

  setPresets: function setPresets(presets) {
    window.wunderWpPresets[this.settingsModel().get('widgetType')] = presets;
  },

  isPresetDataLoaded: function isPresetDataLoaded() {
    if (window.wunderWpPresets[this.settingsModel().get('widgetType')]) {
      return true;
    }

    return false;
  },

  onBeforeDestroy: function onBeforeDestroy() {
    elementor.channels.data.unbind('wunderwp:element:after:reset:style');
    elementor.channels.data.unbind('wunderwp:presets:sync');
  }
});

exports.default = Presets;

},{}],3:[function(require,module,exports){
'use strict';

(function ($, window) {
  var wunderwp = window.wunderwp || {};

  var Editor = function Editor() {
    var self = this;

    function initControls() {
      self.controls = {
        presets: require('./controls/presets').default,
        custom_presets: require('./controls/custom-presets').default
      };

      for (var control in self.controls) {
        elementor.addControlView('wunderwp_' + control, self.controls[control]);
      }
    }

    function initTemplateLibrary() {
      self.templates = require('./template-library/manager');

      self.templates.init();

      var event = 'preview:loaded';
      var compareVersions = window.elementor.helpers.compareVersions;


      if (compareVersions(window.elementor.config.version, '2.8.5', '>')) {
        event = 'document:loaded';
      }

      elementor.on(event, function () {
        if (elementor.$previewContents.find('.wunderwp-add-template-button').length > 0) {
          return;
        }

        var button = '<div\n          class="elementor-add-section-area-button wunderwp-add-template-button"\n          title="Add WunderWP Template">\n          <svg xmlns="http://www.w3.org/2000/svg" width="27.69" height="27.69"><path d="M16.02 11.32c0-.1-.08-.18-.18-.18-4.18 0-6.3-3.3-6.3-9.79 0-.1-.08-.18-.18-.18-.1 0-.18.08-.18.18 0 6.5-2.12 9.79-6.3 9.79-.1 0-.18.08-.18.18 0 .1.08.18.18.18 4.18 0 6.3 3.3 6.3 9.79 0 .1.08.18.18.18.1 0 .18-.08.18-.18 0-6.5 2.12-9.79 6.3-9.79.1 0 .18-.08.18-.18zm8.72 2.83c-2.56 0-3.86-2.03-3.86-6.04 0-.1-.08-.18-.18-.18-.1 0-.18.08-.18.18 0 4.01-1.3 6.04-3.86 6.04-.1 0-.18.08-.18.18s.08.18.18.18c2.56 0 3.86 2.03 3.86 6.04 0 .1.08.18.18.18.1 0 .18-.08.18-.18 0-4.01 1.3-6.04 3.86-6.04.1 0 .18-.08.18-.18s-.08-.18-.18-.18zm-6.99 8.66c-1.59 0-2.4-1.28-2.4-3.8 0-.1-.08-.18-.18-.18-.1 0-.18.08-.18.18 0 2.52-.81 3.8-2.4 3.8-.1 0-.18.08-.18.18 0 .1.08.18.18.18 1.59 0 2.4 1.28 2.4 3.8 0 .1.08.18.18.18.1 0 .18-.08.18-.18 0-2.52.81-3.8 2.4-3.8.1 0 .18-.08.18-.18 0-.1-.08-.18-.18-.18z" fill="#fff"/></svg>\n        </div>';

        elementor.$previewContents.find('.elementor-add-new-section .elementor-add-template-button').after(button).find('~ .wunderwp-add-template-button').on('click', function () {
          $e.run('wunderwp-library/open');
        });
      });
    }

    function bindEvents() {
      elementor.channels.data.bind('element:after:reset:style', onElementAfterResetStyle);
      elementor.channels.data.bind('element:before:reset:style', onElementBeforeResetStyle);

      $('.wunderwp-presets-sync span').off('click', '**');

      $(document).on('click', '.wunderwp-presets-sync span', function () {
        var _this = this;

        if ($(this).find('i').hasClass('eicon-animation-spin')) {
          return;
        }

        $(this).find('i').addClass('eicon-animation-spin');

        wp.ajax.post('wunderwp_sync_libraries', {
          _ajax_nonce: $(this).data('nonce'),
          library: 'presets'
        }).done(function () {
          $(_this).find('i').removeClass('eicon-animation-spin');

          elementor.channels.data.trigger('wunderwp:presets:sync', $(_this).data('element'));
        }).fail(function () {
          return $(_this).find('i').removeClass('eicon-animation-spin');
        });
      });
    }

    function onElementAfterResetStyle(model) {
      if (model.get('elType') !== 'widget') {
        return;
      }

      resetElementPresets(model);

      elementor.channels.data.trigger('wunderwp:element:after:reset:style', model);
    }

    function onElementBeforeResetStyle(model) {
      elementor.channels.data.trigger('wunderwp:element:before:reset:style', model);
    }

    function resetElementPresets(model) {
      var controls = model.get('settings').controls;

      if (!controls.wunderwp_presets) {
        return;
      }

      model.setSetting('wunderwp_presets', null);
    }

    function onElementorInit() {
      initControls();
      bindEvents();
      initTemplateLibrary();
    }

    $(window).on('elementor:init', onElementorInit);
  };

  wunderwp.editor = new Editor();
  window.wunderwp = wunderwp;
})(jQuery, window);

},{"./controls/custom-presets":1,"./controls/presets":2,"./template-library/manager":7}],4:[function(require,module,exports){
'use strict';

var InsertTemplateHandler;

InsertTemplateHandler = Marionette.Behavior.extend({
  ui: {
    insertButton: '.elementor-template-library-template-insert'
  },

  events: {
    'click @ui.insertButton': 'onInsertButtonClick'
  },

  onInsertButtonClick: function onInsertButtonClick() {
    var autoImportSettings = elementor.config.document.remoteLibrary.autoImportSettings;

    if (!autoImportSettings && this.view.model.get('hasPageSettings')) {
      InsertTemplateHandler.showImportDialog(this.view.model);

      return;
    }

    wunderwp.editor.templates.importTemplate(this.view.model, { withPageSettings: autoImportSettings });
  }
}, {
  dialog: null,

  showImportDialog: function showImportDialog(model) {
    var dialog = InsertTemplateHandler.getDialog();

    dialog.onConfirm = function () {
      wunderwp.editor.templates.importTemplate(model, { withPageSettings: true });
    };

    dialog.onCancel = function () {
      wunderwp.editor.templates.importTemplate(model);
    };

    dialog.show();
  },

  initDialog: function initDialog() {
    InsertTemplateHandler.dialog = elementorCommon.dialogsManager.createWidget('confirm', {
      id: 'elementor-insert-template-settings-dialog',
      headerMessage: elementor.translate('import_template_dialog_header'),
      message: elementor.translate('import_template_dialog_message') + '<br>' + elementor.translate('import_template_dialog_message_attention'),
      strings: {
        confirm: elementor.translate('yes'),
        cancel: elementor.translate('no')
      }
    });
  },

  getDialog: function getDialog() {
    if (!InsertTemplateHandler.dialog) {
      InsertTemplateHandler.initDialog();
    }

    return InsertTemplateHandler.dialog;
  }
});

module.exports = InsertTemplateHandler;

},{}],5:[function(require,module,exports){
'use strict';

var TemplateLibraryTemplateModel = require('../models/template'),
    TemplateLibraryCollection;

TemplateLibraryCollection = Backbone.Collection.extend({
  model: TemplateLibraryTemplateModel
});

module.exports = TemplateLibraryCollection;

},{"../models/template":8}],6:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var TemplateLibraryLayoutView = require('./views/library-layout');

var _class = function (_elementorModules$com) {
  _inherits(_class, _elementorModules$com);

  function _class() {
    _classCallCheck(this, _class);

    return _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).apply(this, arguments));
  }

  _createClass(_class, [{
    key: '__construct',
    value: function __construct(args) {
      // Before contruct because it's used in defaultTabs().
      this.docLibraryConfig = elementor.config.document.remoteLibrary;

      _get(_class.prototype.__proto__ || Object.getPrototypeOf(_class.prototype), '__construct', this).call(this, args);

      this.setDefaultRoute('templates/pre-made');
    }
  }, {
    key: 'getNamespace',
    value: function getNamespace() {
      return 'wunderwp-library';
    }
  }, {
    key: 'getModalLayout',
    value: function getModalLayout() {
      return TemplateLibraryLayoutView;
    }
  }, {
    key: 'defaultTabs',
    value: function defaultTabs() {
      return {
        'templates/pre-made': {
          title: 'Pre-made',
          filter: {
            source: 'pre-made'
          }
        },
        'templates/custom': {
          title: 'Custom',
          filter: {
            source: 'custom'
          }
        }
      };
    }
  }, {
    key: 'defaultRoutes',
    value: function defaultRoutes() {
      var _this2 = this;

      return {
        import: function _import() {
          _this2.manager.layout.showImportView();
        },
        preview: function preview(args) {
          _this2.manager.layout.showPreviewView(args.model);
        }
      };
    }
  }, {
    key: 'defaultCommands',
    value: function defaultCommands() {
      return Object.assign(_get(_class.prototype.__proto__ || Object.getPrototypeOf(_class.prototype), 'defaultCommands', this).call(this), {
        open: this.show
      });
    }
  }, {
    key: 'getTabsWrapperSelector',
    value: function getTabsWrapperSelector() {
      return '#elementor-template-library-header-menu';
    }
  }, {
    key: 'renderTab',
    value: function renderTab(tab) {
      this.manager.setScreen(this.tabs[tab].filter);
    }
  }, {
    key: 'activateTab',
    value: function activateTab(tab) {
      $e.routes.saveState('wunderwp-library');

      _get(_class.prototype.__proto__ || Object.getPrototypeOf(_class.prototype), 'activateTab', this).call(this, tab);
    }
  }, {
    key: 'open',
    value: function open() {
      _get(_class.prototype.__proto__ || Object.getPrototypeOf(_class.prototype), 'open', this).call(this);

      if (!this.manager.layout) {
        this.manager.layout = this.layout;
      }

      return true;
    }
  }, {
    key: 'close',
    value: function close() {
      if (!_get(_class.prototype.__proto__ || Object.getPrototypeOf(_class.prototype), 'close', this).call(this)) {
        return false;
      }

      this.manager.modalConfig = {};

      return true;
    }
  }, {
    key: 'show',
    value: function show(args) {
      this.manager.modalConfig = args;

      if (args.toDefault || !$e.routes.restoreState('wunderwp-library')) {
        $e.route(this.getDefaultRoute());
      }
    }
  }]);

  return _class;
}(elementorModules.common.ComponentModal);

exports.default = _class;

},{"./views/library-layout":9}],7:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _component = require('./component');

var _component2 = _interopRequireDefault(_component);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var TemplateLibraryCollection = require('./collections/templates'),
    TemplateLibraryManager;

TemplateLibraryManager = function TemplateLibraryManager() {
  this.modalConfig = {};

  var self = this,
      templateTypes = {};

  var deleteDialog = void 0,
      errorDialog = void 0,
      templatesCollection = void 0,
      config = {},
      filterTerms = {};

  var registerDefaultTemplateTypes = function registerDefaultTemplateTypes() {
    var data = {
      saveDialog: {
        description: elementor.translate('save_your_template_description')
      },
      ajaxParams: {
        success: function success(successData) {
          $e.route('wunderwp-library/templates/my-templates', {
            onBefore: function onBefore() {
              if (templatesCollection) {
                var itemExist = templatesCollection.findWhere({
                  template_id: successData.template_id
                });

                if (!itemExist) {
                  templatesCollection.add(successData);
                }
              }
            }
          });
        },
        error: function error(errorData) {
          self.showErrorDialog(errorData);
        }
      }
    };

    _.each(['page', 'section', elementor.config.document.type], function (type) {
      var safeData = jQuery.extend(true, {}, data, {
        saveDialog: {
          title: elementor.translate('save_your_template', [elementor.translate(type)])
        }
      });

      self.registerTemplateType(type, safeData);
    });
  };

  var registerDefaultFilterTerms = function registerDefaultFilterTerms() {
    filterTerms = {
      text: {
        callback: function callback(value) {
          value = value.toLowerCase();

          if (this.get('title').toLowerCase().indexOf(value) >= 0) {
            return true;
          }

          return _.any(this.get('tags'), function (tag) {
            return tag.toLowerCase().indexOf(value) >= 0;
          });
        }
      },
      type: {},
      subtype: {},
      favorite: {},
      plugins: {
        callback: function callback(value) {
          return _.isEqual(value.split(','), this.get('plugins'));
        }
      }
    };
  };

  this.init = function () {
    registerDefaultTemplateTypes();

    registerDefaultFilterTerms();

    this.component = $e.components.register(new _component2.default({ manager: this }));

    elementor.addBackgroundClickListener('libraryToggleMore', {
      element: '.elementor-template-library-template-more'
    });

    this.modifyElementorTemplates();
  };

  this.modifyElementorTemplates = function () {
    var self = this;

    // Modify save template to add a checkbox.
    var templateSave = jQuery('#tmpl-elementor-template-library-save-template');
    var templateSaveText = templateSave.text();
    var checkbox = '<div class="wunderwp-checkbox">\n      <input type="checkbox" name="wunderwp" id="elementor-template-library-save-template-wunderwp">\n      <label for="elementor-template-library-save-template-wunderwp">Save to WunderWP</label>\n    </div>';

    templateSaveText = templateSaveText.replace(/<.form>/gm, checkbox + ' $&');

    templateSave.text(templateSaveText);

    // Watch save template form. If "Save to WunderWP" checkbox is checked, save the template.
    jQuery(document).on('submit', '#elementor-template-library-save-template-form', function () {
      var inputs = new URLSearchParams(jQuery(this).serialize());
      var title = inputs.get('title');
      var postId = false;

      // Make sure if "Save to WunderWP" checkbox is checked.
      if (inputs.get('wunderwp') == null) {
        return;
      }

      // Get templates collection from Elementor library.
      var templatesCollection = elementor.templates.getTemplatesCollection();

      // If template collection is defined, let's bind to collection.
      // If no, get the saved template via ajaxSussess. This happens when user tries to save a template
      // immediately after the page load, the collection is not defined.
      if (typeof templatesCollection !== 'undefined') {
        templatesCollection.bind('add', function (addedModel) {
          postId = addedModel.attributes.template_id;
        });
      } else {
        jQuery(document).ajaxSuccess(function (event, xhr, settings) {
          if (typeof xhr.responseJSON.data === 'undefined' || typeof xhr.responseJSON.data.responses === 'undefined' || typeof xhr.responseJSON.data.responses.save_template === 'undefined') {
            return;
          }

          if (typeof xhr.responseJSON.data.responses.save_template.data.template_id === 'number') {
            postId = xhr.responseJSON.data.responses.save_template.data.template_id;

            jQuery(this).unbind('ajaxSuccess');
          }
        });
      }

      var postIdExists = setInterval(function () {
        if (postId) {
          clearInterval(postIdExists);
          self.saveTemplate(postId, title);
        }
      }, 100, title, postId);
    });

    // Modify template local to add save/delete buttons.
    var templateLocal = jQuery('#tmpl-elementor-template-library-template-local');
    var templateLocalText = templateLocal.text();
    var buttons = '<# if (typeof saved_to_wunderwp !== \'undefined\') { #>\n      <# if ( ! saved_to_wunderwp ) { #>\n        <div class="wunderwp-template-library-template-save" data-wunderwp-post-id="{{ template_id }}" data-wunderwp-title="{{ title }}">\n          <i class="eicon-cloud-upload" aria-hidden="true"></i>\n          <span class="elementor-template-library-template-control-title">Save to WunderWP</span>\n        </div>\n      <# } else { #>\n        <div class="wunderwp-template-library-template-delete" data-wunderwp-post-id="{{ template_id }}" data-wunderwp-title="{{ title }}">\n          <i class="eicon-trash-o" aria-hidden="true"></i>\n          <span class="elementor-template-library-template-control-title">Delete from WunderWP</span>\n        </div>\n      <# } #>\n    <# } #>';

    templateLocalText = templateLocalText.replace(/<div class="elementor-template-library-template-delete">/gm, buttons + ' $&');

    templateLocal.text(templateLocalText);

    // Watch save/delete buttons to save/delete a template.
    jQuery(document).on('click', '.wunderwp-template-library-template-save', function () {
      self.saveTemplate(jQuery(this).data('wunderwpPostId'), jQuery(this).data('wunderwpTitle'));
    }).on('click', '.wunderwp-template-library-template-delete', function () {
      var $deleteButton = jQuery(this);

      self.deleteTemplate(jQuery(this).data('wunderwpPostId'), {
        onConfirm: function onConfirm() {
          $deleteButton.parents('.elementor-template-library-template-controls').find('.elementor-template-library-template-more-toggle > i').removeClass('eicon-ellipsis-h').addClass('eicon-loading eicon-animation-spin');
        },
        onSuccess: function onSuccess(response, elementorTemplatesCollection, templateModel) {
          elementorTemplatesCollection.find(templateModel).set('saved_to_wunderwp', false);
          elementor.templates.showTemplates();
        }
      });
    });
  };

  this.getTemplateTypes = function (type) {
    if (type) {
      return templateTypes[type];
    }

    return templateTypes;
  };

  this.registerTemplateType = function (type, data) {
    templateTypes[type] = data;
  };

  this.deleteTemplate = function (templateModel, options) {
    var dialog = self.getDeleteDialog();
    var elementorTemplatesCollection = elementor.templates.getTemplatesCollection() || {};

    if ((typeof templateModel === 'undefined' ? 'undefined' : _typeof(templateModel)) !== 'object') {
      templateModel = elementorTemplatesCollection.findWhere({ template_id: +templateModel });
    }

    dialog.onConfirm = function () {
      var templateId = templateModel.get('template_id');
      var postId = templateModel.get('post_id');

      if (options.onConfirm) {
        options.onConfirm();
      }

      // If template is local, send post_id. The template id will be detected on backend.
      if (templateModel.get('saved_to_wunderwp')) {
        postId = templateId;
        templateId = false;
      }

      elementorCommon.ajax.addRequest('wunderwp_delete_template', {
        data: {
          source: 'custom',
          template_id: templateId,
          post_id: postId
        },
        success: function success(response) {
          if (options.onSuccess) {
            options.onSuccess(response, elementorTemplatesCollection, templateModel);
          }
        }
      });
    };

    dialog.show();
  };

  this.importTemplate = function (templateModel, options) {
    var checkInactivePlugins = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

    var self = this;
    var inactivePlugins = templateModel.get('inactive_plugins') || {};
    options = options || {};

    if (inactivePlugins.length && checkInactivePlugins) {
      /**
       * @todo Make strings translation ready.
       */
      return elementorCommon.dialogsManager.createWidget('confirm', {
        headerMessage: 'Missing Required Plugin(s)',
        message: 'This template requires <b>' + inactivePlugins + '</b> plugin(s) to be installed and activated. Clicking on <b>Continue</b> button, will import the template but some elements and settings may be missing.',
        strings: {
          confirm: 'Continue'
        },
        defaultOption: 'confirm',
        onConfirm: function onConfirm() {
          self.importTemplate(templateModel, options, false);
        }
      }).show();
    }

    self.layout.showLoadingView();

    self.requestTemplateContent(templateModel.get('source'), templateModel.get('template_id'), {
      data: {
        page_settings: options.withPageSettings
      },
      success: function success(data) {
        // Clone `self.modalConfig` because it deleted during the closing.
        var importOptions = jQuery.extend({}, self.modalConfig.importOptions);

        // Hide for next open.
        self.layout.hideLoadingView();

        self.layout.hideModal();

        elementor.channels.data.trigger('template:before:insert', templateModel);

        elementor.getPreviewView().addChildModel(data.content, importOptions);

        elementor.channels.data.trigger('template:after:insert', templateModel);

        if (options.withPageSettings) {
          elementor.settings.page.model.setExternalChange(data.page_settings);
        }
      },
      error: function error(data) {
        self.showErrorDialog(data);
      },
      complete: function complete() {
        self.layout.hideLoadingView();
      }
    });
  };

  this.saveTemplate = function (postId, title) {
    if (!self.saveTemplateDialog) {
      self.saveTemplateDialog = elementorCommon.dialogsManager.createWidget('lightbox', {
        id: 'wunderwp-save-template-dialog',
        headerMessage: '"' + title + '" Template is Saving to WunderWP',
        message: 'Preparing the template.<br>',
        hide: {
          onOutsideClick: false,
          onOutsideContextMenu: false,
          onBackgroundClick: false,
          onEscKeyPress: false
        }
      }).show();
    }

    var ajaxOptions = {
      data: {
        'post_id': postId
      },
      success: function success(data) {
        self.getSaveTemplateDialogMessage('Pre-saved the template.<br>');

        if (+data.assets) {
          self.getSaveTemplateDialogMessage('Started to upload ' + data.assets + ' asset(s).<br>');
          return self.saveTemplateAsset(data);
        }

        self.saveTemplateContent(data);
      },
      error: function error(data) {
        self.getSaveTemplateDialogError(data, postId);
      }
    };

    elementorCommon.ajax.addRequest('wunderwp_save_template', ajaxOptions);
  };

  this.saveTemplateContent = function (data) {
    var originalData = data;

    var ajaxOptions = {
      data: {
        'post_id': originalData.post_id
      },
      success: function success(data) {
        self.getSaveTemplateDialogMessage('Content uploaded.<br>');
        self.getSaveTemplateDialogMessage('The template saved successfully. 🎉');

        self.saveTemplateDialog.addButton({
          name: 'ok',
          text: 'Okay',
          callback: function callback() {
            var templatesCollection = elementor.templates.getTemplatesCollection();

            templatesCollection.findWhere({ template_id: +originalData.post_id }).set('saved_to_wunderwp', true);
            elementor.templates.showTemplates();
            self.saveTemplateDialog.hide();
            self.saveTemplateDialog = false;
          }
        });
      },
      error: function error(data) {
        self.getSaveTemplateDialogError('Uploading content failed.', originalData.post_id);
      }
    };

    elementorCommon.ajax.addRequest('wunderwp_save_template_content', ajaxOptions);
  };

  this.saveTemplateAsset = function (data) {
    var self = this;
    var originalData = data;

    var ajaxOptions = {
      data: {
        'post_id': originalData.post_id
      },
      success: function success(data) {
        self.getSaveTemplateDialogMessage('"' + data.uploaded + '" uploaded. (' + (+originalData.assets - +data.remaining) + '/' + +originalData.assets + ')<br>');

        if (data.remaining) {
          self.saveTemplateDialog.refreshPosition();
          return self.saveTemplateAsset(originalData);
        }

        self.saveTemplateContent(originalData);
      },
      error: function error(data) {
        self.getSaveTemplateDialogError(data, originalData.post_id);
      }
    };

    elementorCommon.ajax.addRequest('wunderwp_save_template_asset', ajaxOptions);
  };

  this.getSaveTemplateDialogError = function (message, postId) {
    self.saveTemplateDialog.getElements('message').append('<span class="wunderwp-save-template-dialog-error">' + message + '</span>');

    self.saveTemplateDialog.addButton({
      name: 'try_again',
      text: 'Try Again',
      callback: function callback() {
        self.saveTemplateDialog.hide();
        self.saveTemplateDialog = false;
        elementor.templates.showTemplates();

        elementorCommon.ajax.addRequest('wunderwp_delete_template', {
          data: {
            source: 'custom',
            post_id: postId
          }
        });
      }
    });
  };

  this.getSaveTemplateDialogMessage = function (message) {
    var messageElement = self.saveTemplateDialog.getElements('message');

    messageElement.append(message);
    jQuery(messageElement).animate({ scrollTop: jQuery(messageElement).prop('scrollHeight') });
  };

  this.requestTemplateContent = function (source, id, ajaxOptions) {
    var options = {
      unique_id: id,
      data: {
        source: source,
        edit_mode: true,
        display: true,
        template_id: id
      }
    };

    if (ajaxOptions) {
      jQuery.extend(true, options, ajaxOptions);
    }

    return elementorCommon.ajax.addRequest('wunderwp_get_template_data', options);
  };

  this.markAsFavorite = function (templateModel, favorite) {
    var options = {
      data: {
        source: templateModel.get('source'),
        template_id: templateModel.get('template_id'),
        favorite: favorite
      }
    };

    return elementorCommon.ajax.addRequest('mark_template_as_favorite', options);
  };

  this.getDeleteDialog = function () {
    if (!deleteDialog) {
      deleteDialog = elementorCommon.dialogsManager.createWidget('confirm', {
        id: 'elementor-template-library-delete-dialog',
        headerMessage: elementor.translate('delete_template'),
        message: 'Are you sure you want to delete this template from WunderWP?',
        strings: {
          confirm: elementor.translate('delete')
        }
      });
    }

    return deleteDialog;
  };

  this.getErrorDialog = function () {
    if (!errorDialog) {
      errorDialog = elementorCommon.dialogsManager.createWidget('alert', {
        id: 'elementor-template-library-error-dialog',
        headerMessage: elementor.translate('an_error_occurred')
      });
    }

    return errorDialog;
  };

  this.getTemplatesCollection = function () {
    return templatesCollection;
  };

  this.getConfig = function (item) {
    if (item) {
      return config[item] ? config[item] : {};
    }

    return config;
  };

  this.requestLibraryData = function (options) {
    if (templatesCollection && !options.forceUpdate) {
      if (options.onUpdate) {
        options.onUpdate();
      }

      return;
    }

    if (options.onBeforeUpdate) {
      options.onBeforeUpdate();
    }

    var ajaxOptions = {
      data: {},
      success: function success(data) {
        templatesCollection = new TemplateLibraryCollection(data.templates);

        if (data.config) {
          config = data.config;
        }

        if (options.onUpdate) {
          options.onUpdate();
        }
      }
    };

    if (options.forceSync) {
      ajaxOptions.data.sync = true;
    }

    elementorCommon.ajax.addRequest('wunderwp_get_library_data', ajaxOptions);
  };

  this.getFilter = function (name) {
    return elementor.channels.templates.request('filter:' + name);
  };

  this.setFilter = function (name, value, silent) {
    elementor.channels.templates.reply('filter:' + name, value);

    if (!silent) {
      elementor.channels.templates.trigger('filter:change');
    }
  };

  this.getFilterTerms = function (termName) {
    if (termName) {
      return filterTerms[termName];
    }

    return filterTerms;
  };

  this.setScreen = function (args) {
    elementor.channels.templates.stopReplying();

    self.setFilter('source', args.source, true);
    self.setFilter('type', args.type, true);
    self.setFilter('subtype', args.subtype, true);

    self.showTemplates();
  };

  this.loadTemplates = function (_onUpdate) {
    self.requestLibraryData({
      onBeforeUpdate: self.layout.showLoadingView.bind(self.layout),
      onUpdate: function onUpdate() {
        self.layout.hideLoadingView();

        if (_onUpdate) {
          _onUpdate();
        }
      }
    });
  };

  this.showTemplates = function () {
    // The tabs should exist in DOM on loading.
    self.layout.setHeaderDefaultParts();

    self.loadTemplates(function () {
      var templatesToShow = self.filterTemplates();

      self.layout.showTemplatesView(new TemplateLibraryCollection(templatesToShow));
    });
  };

  this.filterTemplates = function () {
    var activeSource = self.getFilter('source');
    return templatesCollection.filter(function (model) {
      if (activeSource !== model.get('source')) {
        return false;
      }

      var typeInfo = templateTypes[model.get('type')];

      return !typeInfo || typeInfo.showInLibrary !== false;
    });
  };

  this.showErrorDialog = function (errorMessage) {
    if ((typeof errorMessage === 'undefined' ? 'undefined' : _typeof(errorMessage)) === 'object') {
      var message = '';

      _.each(errorMessage, function (error) {
        message += '<div>' + error.message + '.</div>';
      });

      errorMessage = message;
    } else if (errorMessage) {
      errorMessage += '.';
    } else {
      errorMessage = '<i>&#60The error message is empty&#62</i>';
    }

    self.getErrorDialog().setMessage(elementor.translate('templates_request_error') + '<div id="elementor-template-library-error-info">' + errorMessage + '</div>').show();
  };
};

module.exports = new TemplateLibraryManager();

},{"./collections/templates":5,"./component":6}],8:[function(require,module,exports){
'use strict';

module.exports = Backbone.Model.extend({
  defaults: {
    template_id: 0,
    title: '',
    source: '',
    type: '',
    subtype: '',
    author: '',
    thumbnail: '',
    url: '',
    export_link: '',
    tags: []
  }
});

},{}],9:[function(require,module,exports){
'use strict';

var TemplateLibraryHeaderActionsView = require('./parts/header-parts/actions'),
    TemplateLibraryHeaderMenuView = require('./parts/header-parts/menu'),
    TemplateLibraryHeaderPreviewView = require('./parts/header-parts/preview'),
    TemplateLibraryHeaderBackView = require('./parts/header-parts/back'),
    TemplateLibraryCollectionView = require('./parts/templates'),
    TemplateLibraryImportView = require('./parts/import'),
    TemplateLibraryPreviewView = require('./parts/preview');

module.exports = elementorModules.common.views.modal.Layout.extend({
  getModalOptions: function getModalOptions() {
    return {
      id: 'wunderwp-template-library-modal'
    };
  },

  getLogoOptions: function getLogoOptions() {
    return {
      title: 'WunderWP Templates',
      click: function click() {
        $e.run('wunderwp-library/open', { toDefault: true });
      }
    };
  },

  getTemplateActionButton: function getTemplateActionButton(templateData) {
    var viewId = '#tmpl-elementor-template-library-' + (templateData.isPro ? 'get-pro-button' : 'insert-button');

    var template = Marionette.TemplateCache.get(viewId);

    return Marionette.Renderer.render(template);
  },

  setHeaderDefaultParts: function setHeaderDefaultParts() {
    var headerView = this.getHeaderView();

    headerView.tools.show(new TemplateLibraryHeaderActionsView());
    headerView.menuArea.show(new TemplateLibraryHeaderMenuView());

    this.showLogo();
  },

  showTemplatesView: function showTemplatesView(templatesCollection) {
    this.modalContent.show(new TemplateLibraryCollectionView({
      collection: templatesCollection
    }));
  },

  showImportView: function showImportView() {
    this.getHeaderView().menuArea.reset();

    this.modalContent.show(new TemplateLibraryImportView());
  },

  showPreviewView: function showPreviewView(templateModel) {
    this.modalContent.show(new TemplateLibraryPreviewView({
      url: templateModel.get('url')
    }));

    var headerView = this.getHeaderView();

    headerView.menuArea.reset();

    headerView.tools.show(new TemplateLibraryHeaderPreviewView({
      model: templateModel
    }));

    headerView.logoArea.show(new TemplateLibraryHeaderBackView());
  }
});

},{"./parts/header-parts/actions":10,"./parts/header-parts/back":11,"./parts/header-parts/menu":12,"./parts/header-parts/preview":13,"./parts/import":14,"./parts/preview":15,"./parts/templates":17}],10:[function(require,module,exports){
'use strict';

module.exports = Marionette.ItemView.extend({
  template: '#tmpl-elementor-template-library-header-actions',

  id: 'elementor-template-library-header-actions',

  ui: {
    import: '#elementor-template-library-header-import i',
    sync: '#elementor-template-library-header-sync i',
    save: '#elementor-template-library-header-save i'
  },

  events: {
    'click @ui.import': 'onImportClick',
    'click @ui.sync': 'onSyncClick',
    'click @ui.save': 'onSaveClick'
  },

  onImportClick: function onImportClick() {
    $e.route('wunderwp-library/import');
  },

  onSyncClick: function onSyncClick() {
    var self = this;

    self.ui.sync.addClass('eicon-animation-spin');

    wunderwp.editor.templates.requestLibraryData({
      onUpdate: function onUpdate() {
        self.ui.sync.removeClass('eicon-animation-spin');

        $e.routes.refreshContainer('wunderwp-library');
      },
      forceUpdate: true,
      forceSync: true
    });
  },

  onSaveClick: function onSaveClick() {
    $e.route('wunderwp-library/save-template');
  }
});

},{}],11:[function(require,module,exports){
'use strict';

module.exports = Marionette.ItemView.extend({
  template: '#tmpl-elementor-template-library-header-back',

  id: 'elementor-template-library-header-preview-back',

  events: {
    click: 'onClick'
  },

  onClick: function onClick() {
    $e.routes.restoreState('wunderwp-library');
  }
});

},{}],12:[function(require,module,exports){
'use strict';

module.exports = Marionette.ItemView.extend({
  template: '#tmpl-elementor-template-library-header-menu',

  id: 'elementor-template-library-header-menu',

  templateHelpers: function templateHelpers() {
    return {
      tabs: $e.components.get('wunderwp-library').getTabs()
    };
  }
});

},{}],13:[function(require,module,exports){
'use strict';

var TemplateLibraryInsertTemplateBehavior = require('../../../behaviors/insert-template');

module.exports = Marionette.ItemView.extend({
  template: '#tmpl-wunderwp-template-library-header-preview',

  id: 'elementor-template-library-header-preview',

  behaviors: {
    insertTemplate: {
      behaviorClass: TemplateLibraryInsertTemplateBehavior
    }
  }
});

},{"../../../behaviors/insert-template":4}],14:[function(require,module,exports){
'use strict';

var TemplateLibraryImportView;

TemplateLibraryImportView = Marionette.ItemView.extend({
  template: '#tmpl-elementor-template-library-import',

  id: 'elementor-template-library-import',

  ui: {
    uploadForm: '#elementor-template-library-import-form',
    fileInput: '#elementor-template-library-import-form-input'
  },

  events: {
    'change @ui.fileInput': 'onFileInputChange'
  },

  droppedFiles: null,

  submitForm: function submitForm() {
    var _this = this;

    var file = void 0;

    if (this.droppedFiles) {
      file = this.droppedFiles[0];

      this.droppedFiles = null;
    } else {
      file = this.ui.fileInput[0].files[0];

      this.ui.uploadForm[0].reset();
    }

    var fileReader = new FileReader();

    fileReader.onload = function (event) {
      return _this.importTemplate(file.name, event.target.result.replace(/^[^,]+,/, ''));
    };

    fileReader.readAsDataURL(file);
  },

  importTemplate: function importTemplate(fileName, fileData) {
    var layout = elementor.templates.layout;

    var options = {
      data: {
        fileName: fileName,
        fileData: fileData
      },
      success: function success(successData) {
        elementor.templates.getTemplatesCollection().add(successData);

        $e.route('wunderwp-library/templates/my-templates');
      },
      error: function error(errorData) {
        elementor.templates.showErrorDialog(errorData);

        layout.showImportView();
      },
      complete: function complete() {
        layout.hideLoadingView();
      }
    };

    elementorCommon.ajax.addRequest('import_template', options);

    layout.showLoadingView();
  },

  onRender: function onRender() {
    this.ui.uploadForm.on({
      'drag dragstart dragend dragover dragenter dragleave drop': this.onFormActions.bind(this),
      dragenter: this.onFormDragEnter.bind(this),
      'dragleave drop': this.onFormDragLeave.bind(this),
      drop: this.onFormDrop.bind(this)
    });
  },

  onFormActions: function onFormActions(event) {
    event.preventDefault();
    event.stopPropagation();
  },

  onFormDragEnter: function onFormDragEnter() {
    this.ui.uploadForm.addClass('elementor-drag-over');
  },

  onFormDragLeave: function onFormDragLeave(event) {
    if (jQuery(event.relatedTarget).closest(this.ui.uploadForm).length) {
      return;
    }

    this.ui.uploadForm.removeClass('elementor-drag-over');
  },

  onFormDrop: function onFormDrop(event) {
    this.droppedFiles = event.originalEvent.dataTransfer.files;

    this.submitForm();
  },

  onFileInputChange: function onFileInputChange() {
    this.submitForm();
  }
});

module.exports = TemplateLibraryImportView;

},{}],15:[function(require,module,exports){
'use strict';

var TemplateLibraryPreviewView;

TemplateLibraryPreviewView = Marionette.ItemView.extend({
  template: '#tmpl-elementor-template-library-preview',

  id: 'elementor-template-library-preview',

  ui: {
    iframe: '> iframe'
  },

  onRender: function onRender() {
    this.ui.iframe.attr('src', this.getOption('url'));
  }
});

module.exports = TemplateLibraryPreviewView;

},{}],16:[function(require,module,exports){
'use strict';

var TemplateLibraryTemplatesEmptyView;

TemplateLibraryTemplatesEmptyView = Marionette.ItemView.extend({
  id: 'elementor-template-library-templates-empty',

  template: '#tmpl-elementor-template-library-templates-empty',

  ui: {
    title: '.elementor-template-library-blank-title',
    message: '.elementor-template-library-blank-message'
  },

  modesStrings: {
    empty: {
      title: elementor.translate('templates_empty_title'),
      message: elementor.translate('templates_empty_message')
    },
    noResults: {
      title: elementor.translate('templates_no_results_title'),
      message: elementor.translate('templates_no_results_message')
    },
    noFavorites: {
      title: elementor.translate('templates_no_favorites_title'),
      message: elementor.translate('templates_no_favorites_message')
    },
    unconnected: {
      title: wunderwp_editor.library_unconnected_title,
      message: wunderwp_editor.library_unconnected_message
    }
  },

  getCurrentMode: function getCurrentMode() {
    if (elementor.templates.getFilter('text')) {
      return 'noResults';
    }

    if (elementor.templates.getFilter('favorite')) {
      return 'noFavorites';
    }

    if (!wunderwp_editor.is_connected) {
      return 'unconnected';
    }

    return 'empty';
  },

  onRender: function onRender() {
    var modeStrings = this.modesStrings[this.getCurrentMode()];
    var message = modeStrings.message;

    if (this.getCurrentMode() === 'unconnected') {
      message += wunderwp_editor.library_unconnected_button;
    }

    this.ui.title.html(modeStrings.title);
    this.ui.message.html(message);
  }
});

module.exports = TemplateLibraryTemplatesEmptyView;

},{}],17:[function(require,module,exports){
'use strict';

var TemplateLibraryTemplateLocalView = require('../template/local'),
    TemplateLibraryTemplateRemoteView = require('../template/remote'),
    TemplateLibraryCollectionView;

TemplateLibraryCollectionView = Marionette.CompositeView.extend({
  template: '#tmpl-wunderwp-template-library-templates',

  id: 'elementor-template-library-templates',

  childViewContainer: '#elementor-template-library-templates-container',

  reorderOnSort: true,

  emptyView: function emptyView() {
    var EmptyView = require('./templates-empty');

    return new EmptyView();
  },

  ui: {
    textFilter: '#elementor-template-library-filter-text',
    selectFilter: '.elementor-template-library-filter-select',
    myFavoritesFilter: '#elementor-template-library-filter-my-favorites',
    orderInputs: '.elementor-template-library-order-input',
    orderLabels: 'label.elementor-template-library-order-label'
  },

  events: {
    'input @ui.textFilter': 'onTextFilterInput',
    'change @ui.selectFilter': 'onSelectFilterChange',
    'change @ui.myFavoritesFilter': 'onMyFavoritesFilterChange',
    'mousedown @ui.orderLabels': 'onPluginsLabelsClick'
  },

  comparators: {
    title: function title(model) {
      return model.get('title').toLowerCase();
    },
    popularityIndex: function popularityIndex(model) {
      var popularityIndex = model.get('popularityIndex');

      if (!popularityIndex) {
        popularityIndex = model.get('date');
      }

      return -popularityIndex;
    },
    trendIndex: function trendIndex(model) {
      var trendIndex = model.get('trendIndex');

      if (!trendIndex) {
        trendIndex = model.get('date');
      }

      return -trendIndex;
    }
  },

  getChildView: function getChildView(childModel) {
    if (childModel.get('source') === 'pre-made') {
      return TemplateLibraryTemplateRemoteView;
    }

    return TemplateLibraryTemplateLocalView;
  },

  initialize: function initialize() {
    this.listenTo(elementor.channels.templates, 'filter:change', this._renderChildren);
  },

  filter: function filter(childModel) {
    var filterTerms = wunderwp.editor.templates.getFilterTerms(),
        passingFilter = true;

    jQuery.each(filterTerms, function (filterTermName) {
      var filterValue = elementor.templates.getFilter(filterTermName);

      if (!filterValue) {
        return;
      }

      if (this.callback) {
        var callbackResult = this.callback.call(childModel, filterValue);

        if (!callbackResult) {
          passingFilter = false;
        }

        return callbackResult;
      }

      var filterResult = filterValue === childModel.get(filterTermName);

      if (!filterResult) {
        passingFilter = false;
      }

      return filterResult;
    });

    return passingFilter;
  },

  order: function order(by, reverseOrder) {
    var comparator = this.comparators[by] || by;

    if (reverseOrder) {
      comparator = this.reverseOrder(comparator);
    }

    this.collection.comparator = comparator;

    this.collection.sort();
  },

  reverseOrder: function reverseOrder(comparator) {
    if (typeof comparator !== 'function') {
      var comparatorValue = comparator;

      comparator = function comparator(model) {
        return model.get(comparatorValue);
      };
    }

    return function (left, right) {
      var l = comparator(left),
          r = comparator(right);

      if (undefined === l) {
        return -1;
      }

      if (undefined === r) {
        return 1;
      }

      if (l < r) {
        return 1;
      }
      if (l > r) {
        return -1;
      }
      return 0;
    };
  },

  addSourceData: function addSourceData() {
    var isEmpty = this.children.isEmpty();
    var source = elementor.templates.getFilter('source') === 'pre-made' ? 'remote' : 'local';

    this.$el.attr('data-template-source', isEmpty ? 'empty' : source);
  },

  setFiltersUI: function setFiltersUI() {
    var $filters = this.$(this.ui.selectFilter);

    $filters.select2({
      placeholder: elementor.translate('category'),
      allowClear: true,
      width: 150
    });
  },

  setMasonrySkin: function setMasonrySkin() {
    var masonry = new elementorModules.utils.Masonry({
      container: this.$childViewContainer,
      items: this.$childViewContainer.children()
    });

    this.$childViewContainer.imagesLoaded(masonry.run.bind(masonry));
  },

  toggleFilterClass: function toggleFilterClass() {
    this.$el.toggleClass('elementor-templates-filter-active', !!(elementor.templates.getFilter('text') || elementor.templates.getFilter('favorite')));
  },

  onRender: function onRender() {
    if (elementor.templates.getFilter('source') === 'pre-made') {
      this.setFiltersUI();
    }
  },


  onRenderCollection: function onRenderCollection() {
    this.addSourceData();

    this.toggleFilterClass();

    if (elementor.templates.getFilter('source') === 'pre-made') {
      this.setMasonrySkin();
    }
  },

  onBeforeRenderEmpty: function onBeforeRenderEmpty() {
    this.addSourceData();
  },

  onTextFilterInput: function onTextFilterInput() {
    elementor.templates.setFilter('text', this.ui.textFilter.val());
  },

  onSelectFilterChange: function onSelectFilterChange(event) {
    var $select = jQuery(event.currentTarget),
        filterName = $select.data('elementor-filter');

    elementor.templates.setFilter(filterName, $select.val());
  },

  onMyFavoritesFilterChange: function onMyFavoritesFilterChange() {
    elementor.templates.setFilter('favorite', this.ui.myFavoritesFilter[0].checked);
  },

  onOrderLabelsClick: function onOrderLabelsClick(event) {
    var $clickedInput = jQuery(event.currentTarget.control),
        toggle;

    if (!$clickedInput[0].checked) {
      toggle = $clickedInput.data('default-ordering-direction') !== 'asc';
    }

    $clickedInput.toggleClass('elementor-template-library-order-reverse', toggle);

    this.order($clickedInput.val(), $clickedInput.hasClass('elementor-template-library-order-reverse'));
  },

  onPluginsLabelsClick: function onPluginsLabelsClick(event) {
    var $clickedInput = jQuery(event.currentTarget.control);

    elementor.templates.setFilter('plugins', $clickedInput.val());
  }
});

module.exports = TemplateLibraryCollectionView;

},{"../template/local":19,"../template/remote":20,"./templates-empty":16}],18:[function(require,module,exports){
'use strict';

var TemplateLibraryInsertTemplateBehavior = require('../../behaviors/insert-template'),
    TemplateLibraryTemplateView;

TemplateLibraryTemplateView = Marionette.ItemView.extend({
  className: function className() {
    var classes = 'elementor-template-library-template',
        source = this.model.get('source');

    source = source === 'pre-made' ? 'remote' : 'local';

    classes += ' elementor-template-library-template-' + source;

    if (source === 'pre-made') {
      classes += ' elementor-template-library-template-' + this.model.get('type');
    }

    if (this.model.get('isPro')) {
      classes += ' elementor-template-library-pro-template';
    }

    return classes;
  },

  ui: function ui() {
    return {
      previewButton: '.elementor-template-library-template-preview'
    };
  },

  events: function events() {
    return {
      'click @ui.previewButton': 'onPreviewButtonClick'
    };
  },

  behaviors: {
    insertTemplate: {
      behaviorClass: TemplateLibraryInsertTemplateBehavior
    }
  }
});

module.exports = TemplateLibraryTemplateView;

},{"../../behaviors/insert-template":4}],19:[function(require,module,exports){
'use strict';

var TemplateLibraryTemplateView = require('./base'),
    TemplateLibraryTemplateLocalView;

TemplateLibraryTemplateLocalView = TemplateLibraryTemplateView.extend({
  template: '#tmpl-elementor-template-library-template-local',

  ui: function ui() {
    return _.extend(TemplateLibraryTemplateView.prototype.ui.apply(this, arguments), {
      deleteButton: '.elementor-template-library-template-delete',
      morePopup: '.elementor-template-library-template-more',
      toggleMore: '.elementor-template-library-template-more-toggle',
      toggleMoreIcon: '.elementor-template-library-template-more-toggle i'
    });
  },

  events: function events() {
    return _.extend(TemplateLibraryTemplateView.prototype.events.apply(this, arguments), {
      'click @ui.deleteButton': 'onDeleteButtonClick',
      'click @ui.toggleMore': 'onToggleMoreClick'
    });
  },

  onDeleteButtonClick: function onDeleteButtonClick() {
    var toggleMoreIcon = this.ui.toggleMoreIcon;

    wunderwp.editor.templates.deleteTemplate(this.model, {
      onConfirm: function onConfirm() {
        toggleMoreIcon.removeClass('eicon-ellipsis-h').addClass('eicon-loading eicon-animation-spin');
      },
      onSuccess: function onSuccess(response, elementorTemplatesCollection, templateModel) {
        var templateCollection = wunderwp.editor.templates.getTemplatesCollection();

        templateCollection.remove(templateModel, { silent: true });
        wunderwp.editor.templates.showTemplates();
      }
    });
  },

  onToggleMoreClick: function onToggleMoreClick() {
    this.ui.morePopup.show();
  },

  onPreviewButtonClick: function onPreviewButtonClick() {
    open(this.model.get('url'), '_blank');
  }
});

module.exports = TemplateLibraryTemplateLocalView;

},{"./base":18}],20:[function(require,module,exports){
'use strict';

var TemplateLibraryTemplateView = require('./base'),
    TemplateLibraryTemplateRemoteView;

TemplateLibraryTemplateRemoteView = TemplateLibraryTemplateView.extend({
  template: '#tmpl-wunderwp-template-library-template-remote',

  ui: function ui() {
    return jQuery.extend(TemplateLibraryTemplateView.prototype.ui.apply(this, arguments), {
      favoriteCheckbox: '.elementor-template-library-template-favorite-input'
    });
  },

  events: function events() {
    return jQuery.extend(TemplateLibraryTemplateView.prototype.events.apply(this, arguments), {
      'change @ui.favoriteCheckbox': 'onFavoriteCheckboxChange'
    });
  },

  onPreviewButtonClick: function onPreviewButtonClick() {
    $e.route('wunderwp-library/preview', { model: this.model });
  },

  onFavoriteCheckboxChange: function onFavoriteCheckboxChange() {
    var isFavorite = this.ui.favoriteCheckbox[0].checked;

    this.model.set('favorite', isFavorite);

    elementor.templates.markAsFavorite(this.model, isFavorite);

    if (!isFavorite && elementor.templates.getFilter('favorite')) {
      elementor.channels.templates.trigger('filter:change');
    }
  }
});

module.exports = TemplateLibraryTemplateRemoteView;

},{"./base":18}]},{},[3]);
