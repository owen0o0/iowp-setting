<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 22:50:59
 * @FilePath: /iowp-setting/src/fields/wp_editor.php
 * @Description: 
 */
namespace IO\Setting;
if (!defined('ABSPATH')) { die; }

class ISET_Field_wp_editor extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        $settings = wp_parse_args($this->field['settings'], array(
            'tinymce'       => true,//加载 TinyMCE
            'quicktags'     => true,//加载快速标签
            'media_buttons' => true,//显示媒体插入/上传按钮
            'wpautop'       => false,
            'height'        => '',
            'width'         => '',
        ));
        if(!$settings['tinymce']){
            $settings['media_buttons'] = false;
        }

        $editor_height = (!empty($settings['height'])) ? ' style="height:' . esc_attr($settings['height']) . ';"' : '';
        $editor_width  = (!empty($settings['width'])) ? ' style="max-width:' . esc_attr($settings['width']) . ';"' : '';


        $editor_settings = array(
            'tinymce'       => $settings['tinymce'],
            'quicktags'     => $settings['quicktags'],
            'media_buttons' => $settings['media_buttons'],
            'wpautop'       => $settings['wpautop']
        );

        $editor_name = $this->field['section'] . '[' . $this->field['id'] . ']';


        echo '<div class="iset-wp-editor" data-editor-settings="'. esc_attr( json_encode( $editor_settings ) ) .'" ' . $editor_width . '>' ;
        echo '<textarea name="' . $editor_name . '" rows="10" class="wp-editor-area" autocomplete="off" data-depend-id="' . $this->field['id'] . '" ' . $editor_height . '>' . $this->value . '</textarea>';
        echo '</div>' ;

        echo $this->get_field_description( $this->field );

    }

    function setup_wp_editor_media_buttons() {

        if (!function_exists('media_buttons')) {
            return;
        }

        ob_start();
        echo '<div class="wp-media-buttons">';
        do_action('media_buttons');
        echo '</div>';
        $media_buttons = ob_get_clean();

        echo '<script type="text/javascript">';
        echo 'var iset_media_buttons = ' . json_encode($media_buttons) . ';';
        echo '</script>';

    }

    public function enqueue() {
        if (function_exists('wp_enqueue_editor')) {
            wp_enqueue_editor();
            $this->setup_wp_editor_settings();
            add_action('print_default_editor_scripts', array($this, 'setup_wp_editor_media_buttons'));
        }
    }

    public function setup_wp_editor_settings() {
        if (class_exists('_WP_Editors')) {

            $defaults = apply_filters('iset_wp_editor', array(
                'tinymce' => array(
                    'wp_skip_init' => true
                ),
            ));

            $setup = \_WP_Editors::parse_settings('iset_wp_editor', $defaults);

            \_WP_Editors::editor_settings('iset_wp_editor', $setup);

        }
    }
    
}