/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-21 20:33:05
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-27 17:56:51
 * @FilePath: /io-setting/assets/js/main.js
 * @Description: 
 */
'use strict';
(function ($) {
  $.fn.iset_dependency = function () {
    return this.each(function () {
      var $this = $(this),
        $fields = $this.find('[data-controller]');

      if ($fields.length) {
        var normal_ruleset = $.iset_deps.createRuleset(),
          global_ruleset = $.iset_deps.createRuleset(),
          normal_depends = [],
          global_depends = [];

        $fields.each(function () {
          var $field = $(this),
            controllers = $field.data('controller').split('|'),
            conditions = $field.data('condition').split('|'),
            values = $field.data('value').toString().split('|'),
            is_global = $field.data('depend-global') ? true : false,
            ruleset = (is_global) ? global_ruleset : normal_ruleset;

          $.each(controllers, function (index, depend_id) {
            var value = values[index] || '',
              condition = conditions[index] || conditions[0];

            ruleset = ruleset.createRule('[data-depend-id="' + depend_id + '"]', condition, value);
            ruleset.include($field);
            if (is_global) {
              global_depends.push(depend_id);
            } else {
              normal_depends.push(depend_id);
            }
          });

        });

        if (normal_depends.length) {
          $.iset_deps.enable($this, normal_ruleset, normal_depends);
        }

        if (global_depends.length) {
          $.iset_deps.enable($('.iset-section-group'), global_ruleset, global_depends);
        }
      }
    });
  };

  $.fn.iset_field_wp_editor = function () {
    return this.each(function () {
      if (typeof window.wp.editor === 'undefined' || typeof window.tinyMCEPreInit === 'undefined' || typeof window.tinyMCEPreInit.mceInit.iset_wp_editor === 'undefined') {
        return;
      }

      var $this = $(this),
        $editor = $this.find('.iset-wp-editor'),
        $textarea = $this.find('textarea');

      // 如果有 wp-editor 将其删除以避免重复的 wp-editor 冲突。
      var $has_wp_editor = $this.find('.wp-editor-wrap').length || $this.find('.mce-container').length;

      if ($has_wp_editor) {
        $editor.empty();
        $editor.append($textarea);
        $textarea.css('display', '');
      }

      // 生成唯一的id
      var uid = 'iset-editor-' + Math.random().toString(36).substr(2, 9);

      $textarea.attr('id', uid);

      // 获取默认编辑器设置
      var default_editor_settings = {
        tinymce: window.tinyMCEPreInit.mceInit.iset_wp_editor,
        quicktags: window.tinyMCEPreInit.qtInit.iset_wp_editor
      };

      // 获取默认编辑器设置
      var field_editor_settings = $editor.data('editor-settings');

      // 旧wp编辑器的回调
      var wpEditor = wp.oldEditor ? wp.oldEditor : wp.editor;

      if (wpEditor && wpEditor.hasOwnProperty('autop')) {
        wp.editor.autop = wpEditor.autop;
        wp.editor.removep = wpEditor.removep;
        wp.editor.initialize = wpEditor.initialize;
      }

      // 添加更改事件
      var editor_on_change = function (editor) {
        editor.on('change keyup', function () {
          var value = (field_editor_settings.wpautop) ? editor.getContent() : wp.editor.removep(editor.getContent());
          $textarea.val(value).trigger('change');
        });
      };

      // 扩展编辑器和更改事件
      default_editor_settings.tinymce = $.extend({}, default_editor_settings.tinymce, { selector: '#' + uid, setup: editor_on_change });

      // 覆盖编辑器tinymce设置
      if (field_editor_settings.tinymce === false) {
        default_editor_settings.tinymce = false;
        $editor.addClass('iset-no-tinymce');
      }

      // 覆盖编辑器quicktags设置
      if (field_editor_settings.quicktags === false) {
        default_editor_settings.quicktags = false;
        $editor.addClass('iset-no-quicktags');
      }

      // 等待至：可见
      var interval = setInterval(function () {
        if ($this.is(':visible')) {
          window.wp.editor.initialize(uid, default_editor_settings);
          clearInterval(interval);
        }
      });

      // 添加媒体按钮
      if (field_editor_settings.media_buttons && window.iset_media_buttons) {
        var $editor_buttons = $editor.find('.wp-media-buttons');
        if ($editor_buttons.length) {
          $editor_buttons.find('.iset-shortcode-button').data('editor-id', uid);
        } else {
          var $media_buttons = $(window.iset_media_buttons);
          $media_buttons.find('.iset-shortcode-button').data('editor-id', uid);
          $editor.prepend($media_buttons);
        }
      }
    });
  };

  $.fn.iset_field_file = function () {
    return this.each(function () {

      var $this = $(this),
        $input = $this.find('input'),
        $upload_button = $this.find('.iset-browse'),
        $remove_button = $this.find('.iset-remove'),
        $preview_wrap = $this.find('.iset-preview'),
        $preview_src = $this.find('.iset-src'),
        $library = $upload_button.data('library') && $upload_button.data('library').split(',') || '',
        wp_media_frame;

      // 打开媒体框架
      $upload_button.on('click', function (e) {
        e.preventDefault();
        if (typeof window.wp === 'undefined' || !window.wp.media || !window.wp.media.gallery) {
          return;
        }
        if (wp_media_frame) {
          wp_media_frame.open();
          return;
        }
        wp_media_frame = window.wp.media({
          library: {
            type: $library
          },
        });
        wp_media_frame.on('select', function () {
          var src;
          var attributes = wp_media_frame.state().get('selection').first().attributes;

          if ($library.length && $library.indexOf(attributes.subtype) === -1 && $library.indexOf(attributes.type) === -1) {
            return;
          }
          $input.val(attributes.url).trigger('change');

        });
        wp_media_frame.open();
      });
      // 移除按钮
      $remove_button.on('click', function (e) {
        e.preventDefault();
        $input.val('').trigger('change');
      });
      // 输入更改
      $input.on('change', function (e) {
        var $value = $input.val();
        if ($value) {
          $remove_button.removeClass('hidden');
        } else {
          $remove_button.addClass('hidden');
        }
        if ($preview_wrap.length) {
          if ($.inArray($value.split('.').pop().toLowerCase(), ['jpg', 'jpeg', 'gif', 'png', 'svg', 'webp']) !== -1) {
            $preview_wrap.removeClass('hidden');
            $preview_src.attr('src', $value);
          } else {
            $preview_wrap.addClass('hidden');
          }
        }
      });
    });
  };

  if (typeof Color === 'function') {
    Color.prototype.toString = function () {
      if (this._alpha < 1) {
        return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
      }
      var hex = parseInt(this._color, 10).toString(16);

      if (this.error) { return ''; }

      if (hex.length < 6) {
        for (var i = 6 - hex.length - 1; i >= 0; i--) {
          hex = '0' + hex;
        }
      }

      return '#' + hex;
    };
  }

  var parse_color = function(color) {
    var value = color.replace(/\s+/g, ''),
      trans = (value.indexOf('rgba') !== -1) ? parseFloat(value.replace(/^.*,(.+)\)/, '$1') * 100) : 100,
      rgba = (trans < 100) ? true : false;
    
    return { value: value, transparent: trans, rgba: rgba };
  };

  $.fn.iset_field_color = function () {
    return this.each(function () {

      var $input = $(this),
        picker_color = parse_color($input.val()),
        palette_color = window.iset_vars.color_palette.length ? window.iset_vars.color_palette : true, // 默认调色板
        $container;

      // 销毁并重新初始化
      if ($input.hasClass('wp-color-picker')) {
        $input.closest('.wp-picker-container').after($input).remove();
      }

      $input.wpColorPicker({
        palettes: palette_color,
        change: function (event, ui) {
          // 当颜色改变时触发
          var ui_color_value = ui.color.toString();
                    
          if (ui.color._alpha === 1) {
            $input.removeClass('iset-rgba');
          } else {
            $input.addClass('iset-rgba');
          }

          $container.removeClass('iset-transparent-active');
          $container.find('.iset-transparent-offset').css('background-color', ui_color_value);
          $input.val(ui_color_value).trigger('change');
        },
        create: function () {
          // 创建透明度滑块
          $container = $input.closest('.wp-picker-container');

          var a8cIris = $input.data('a8cIris'),
            $transparent_wrap = $('<div class="iset-transparent-wrap">' +
              '<div class="iset-transparent-slider"></div>' +
              '<div class="iset-transparent-offset"></div>' +
              '<div class="iset-transparent-text"></div>' +
              '<div class="iset-transparent-button">transparent</div>' +
              '</div>').appendTo($container.find('.wp-picker-holder')),
            $transparent_slider = $transparent_wrap.find('.iset-transparent-slider'), //滑块
            $transparent_text = $transparent_wrap.find('.iset-transparent-text'),
            $transparent_offset = $transparent_wrap.find('.iset-transparent-offset'),
            $transparent_button = $transparent_wrap.find('.iset-transparent-button'); //透明按钮

          if ($input.val() === 'transparent') {
            $container.addClass('iset-transparent-active');
          }

          if (picker_color.transparent < 100) {
            $input.addClass('iset-rgba');
          }

          $transparent_button.on('click', function () {
            if ($input.val() !== 'transparent') {
              $input.val('transparent').trigger('change').removeClass('iris-error');
              $container.addClass('iset-transparent-active');
            } else {
              $input.val(a8cIris._color.toString()).trigger('change');
              $container.removeClass('iset-transparent-active');
            }
          });

          $transparent_slider.slider({
            value: picker_color.transparent,
            step: 1,
            min: 0,
            max: 100,
            slide: function (event, ui) {
              var slide_value = parseFloat(ui.value / 100);
              a8cIris._color._alpha = slide_value;
              $input.wpColorPicker('color', a8cIris._color.toString());
              $transparent_text.text((slide_value === 1 || slide_value === 0 ? '' : slide_value));
            },
            create: function () {
              var slide_value = parseFloat(picker_color.transparent / 100),
                text_value = slide_value < 1 ? slide_value : '';

              $transparent_text.text(text_value);
              $transparent_offset.css('background-color', picker_color.value);

              $container.on('click', '.wp-picker-clear', function () {

                a8cIris._color._alpha = 1;
                $transparent_text.text('');
                $transparent_slider.slider('option', 'value', 100);
                $container.removeClass('iset-transparent-active');
                $input.trigger('change');

              });

              $container.on('click', '.wp-picker-default', function () {

                var default_color = parse_color($input.data('default-color')),
                  default_value = parseFloat(default_color.transparent / 100),
                  default_text = default_value < 1 ? default_value : '';

                a8cIris._color._alpha = default_value;
                $transparent_text.text(default_text);
                $transparent_slider.slider('option', 'value', default_color.transparent);

                if (default_color.value === 'transparent') {
                  $input.removeClass('iris-error');
                  $container.addClass('iset-transparent-active');
                }
              });
            }
          });
        }
      });
    });
  };

  $.fn.iset_field_chosen = function () {
    return this.each(function () {
      var $this = $(this),
        $inited = $this.parent().find('.chosen-container'),
        is_sortable = $this.hasClass('iset-chosen-sortable') || false,
        is_ajax = $this.hasClass('iset-chosen-ajax') || false,
        is_multiple = $this.attr('multiple') || false,
        set_width = is_multiple ? '100%' : 'auto',
        set_options = $.extend({
          allow_single_deselect: true,
          disable_search_threshold: 10,
          width: set_width,
          no_results_text: window.iset_vars.i18n.no_results_text,
        }, $this.data('chosen-settings'));

      if ($inited.length) {
        $inited.remove();
      }

      // ajax
      if (is_ajax) {
        var set_ajax_options = $.extend({
          data: {
            type: 'post',
            nonce: '',
          },
          allow_single_deselect: true,
          disable_search_threshold: -1,
          width: '100%',
          min_length: 2,
          type_delay: 500,
          typing_text: window.iset_vars.i18n.typing_text,
          searching_text: window.iset_vars.i18n.searching_text,
          no_results_text: window.iset_vars.i18n.no_results_text,
        }, $this.data('chosen-settings'));

        $this.ajaxChosen(set_ajax_options);
      } else {
        $this.chosen(set_options);
      }

      // 多选保持选择顺序，增加选项时刷新 select 的值
      if (is_multiple) {
        var $hidden_select = $this.parent().find('.iset-hide-select');
        var $hidden_value = $hidden_select.val() || [];

        $this.on('change', function (obj, result) {
          if (result && result.selected) {
            $hidden_select.append('<option value="' + result.selected + '" selected="selected">' + result.selected + '</option>');
          } else if (result && result.deselected) {
            $hidden_select.find('option[value="' + result.deselected + '"]').remove();
          }

          // 强制自定义刷新
          if (window.wp.customize !== undefined && $hidden_select.children().length === 0 && $hidden_select.data('customize-setting-link')) {
            window.wp.customize.control($hidden_select.data('customize-setting-link')).setting.set('');
          }

          $hidden_select.trigger('change');
        });

        // 按照选项设置选择顺序
        $this.setSelectionOrder($hidden_value, true);
      }

      // 选择可排序
      if (is_sortable) {
        var $chosen_container = $this.parent().find('.chosen-container');
        var $chosen_choices = $chosen_container.find('.chosen-choices');

        $chosen_choices.bind('mousedown', function (event) {
          if ($(event.target).is('span')) {
            event.stopPropagation();
          }
        });

        $chosen_choices.sortable({
          items: 'li:not(.search-field)',
          helper: 'orginal',
          cursor: 'move',
          placeholder: 'search-choice-placeholder',
          start: function (e, ui) {
            ui.placeholder.width(ui.item.innerWidth());
            ui.placeholder.height(ui.item.innerHeight());
          },
          update: function (e, ui) {
            var select_options = '';
            var chosen_object = $this.data('chosen');
            var $prev_select = $this.parent().find('.iset-hide-select');

            $chosen_choices.find('.search-choice-close').each(function () {
              var option_array_index = $(this).data('option-array-index');
              $.each(chosen_object.results_data, function (index, data) {
                if (data.array_index === option_array_index) {
                  select_options += '<option value="' + data.value + '" selected>' + data.value + '</option>';
                }
              });
            });

            $prev_select.children().remove();
            $prev_select.append(select_options);
            $prev_select.trigger('change');
          }
        });
      }
    });
  };

  $.fn.iset_nav_options = function () {
    return this.each(function () {
      var $nav = $(this),
          $window = $(window),
          $links = $nav.find('a'),
          $menus = $('#toplevel_page_' + iset_vars.menu_slug).find('li'),
          $last = $('.iset-placeholder-field'),
          $is_into = false;
      
      var changeTab = function (slug) {
        var $link = $('[data-tab-id="' + slug + '_tab"]');
    
        if (typeof (localStorage) != 'undefined') {
          localStorage.setItem("iset_active_tab", slug);
        }

        if ($link.length) {
          $links.removeClass('nav-tab-active');
          $link.addClass('nav-tab-active');

          $menus.removeClass('current');
          $('[href="admin.php?page=' + iset_vars.menu_slug + '#tab=' + slug + '"]').parent().addClass('current');

          if ($last) {
            $last.hide();
          }

          var $tab = $('#' + slug + '_tab');
          if (!$tab.data('depend')) {
            $tab.iset_dependency();
          }
          if (!$is_into) {
            $is_into = true;
            $tab.show().data('depend', true);
          } else {
            $tab.fadeIn().data('depend', true);
          }
          $last = $tab;
        }
      }

      var intoTab = function () {
        let active_tab = '';
    
        if (window.location.hash) {
          active_tab = window.location.hash.replace('#tab=', '');
        } else {
          if (typeof (localStorage) != 'undefined') {
            // 从本地存储中获取活动选项卡,解决提交保存后刷新问题
            active_tab = localStorage.getItem("iset_active_tab");
          }
        }
        
        var $link = $('[data-tab-id="' + active_tab + '_tab"]');
        if (!$link.length) {
          $link = $('.iset-section-nav:first');
          active_tab = $link.attr('href').replace('#tab=', '');
        }
        
        location.hash = $link.attr('href');
        changeTab(active_tab);
      }
      intoTab();
      
      $window.on('hashchange', function () {
        var hash = window.location.hash.replace('#tab=', '');
        var slug = hash ? hash : $('.iset-section-nav:first').attr('href').replace('#tab=', '');
        changeTab(slug);
      });

    });
  };
  
})(jQuery);

(function ($) {
  $('.iset-no-title').find('td').attr('colspan', 2);
  
  $(document).ready(function () {
    $('.iset-nav-options').iset_nav_options();
    $('.iset-field-file').iset_field_file();
    $('.iset-field-wp_editor').iset_field_wp_editor();
    $('.iset-field-select').find('.iset-chosen').iset_field_chosen();
    
    // 启动颜色选择器
    $('.iset-field-color').find('.iset-color-picker').iset_field_color();
    //$('.iset-color-picker--sss').wpColorPicker();
  });

  // 开关切换
  $(document).on('click', '.iset-switcher', function () {
    var $this = $(this);
    var $input = $this.find('input');

    var value = 0;
    if ($this.hasClass('iset-active')) {
      $this.removeClass('iset-active');
    } else {
      value = 1;
      $this.addClass('iset-active');
    }

    $input.val(value).trigger('change');
  });

  // 复选框单选
  $(document).on('click', '.iset-checkbox .checkbox-single', function () {
    var $this = $(this).parent();
    var $input = $this.find('.iset-input');

    $input.val(Number($(this).prop('checked'))).trigger('change');
  });

  
})(jQuery);