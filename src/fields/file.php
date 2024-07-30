<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 22:21:57
 * @FilePath: /iowp-setting/src/fields/file.php
 * @Description: 
 */
namespace IO\WP\Setting;
if (!defined('ABSPATH')) { die; }

class ISET_Field_file extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {

        $settings = wp_parse_args($this->field['settings'], array(
            'button_title'   => esc_html__('Upload', 'iset_plugin'),
            'remove_title'   => esc_html__('Remove', 'iset_plugin'),
            'preview'        => true,//显示媒体
            'preview_width'  => '120',
            'preview_height' => '120',
            'library'        => array(),//告诉模态框显示特定格式。例如，image或者，video或者两者等等。
        ));

        $library = (is_array($settings['library'])) ? $settings['library'] : array_filter((array) $settings['library']);
        $library = (!empty($library)) ? implode(',', $library) : '';
        $hidden = (empty($this->value)) ? ' hidden' : '';

        if (!empty($settings['preview'])) {

            $preview_type   = (!empty($this->value)) ? strtolower(substr(strrchr($this->value, '.'), 1)) : '';
            $preview_src    = (!empty($preview_type) && in_array($preview_type, array('jpg', 'jpeg', 'gif', 'png', 'svg', 'webp'))) ? $this->value : '';
            $preview_width  = (!empty($settings['preview_width'])) ? 'max-width:' . esc_attr($settings['preview_width']) . 'px;' : '';
            $preview_height = (!empty($settings['preview_height'])) ? 'max-height:' . esc_attr($settings['preview_height']) . 'px;' : '';
            $preview_style  = (!empty($preview_width) || !empty($preview_height)) ? ' style="' . esc_attr($preview_width . $preview_height) . '"' : '';
            $preview_hidden = (empty($preview_src)) ? ' hidden' : '';

            echo '<div class="iset-preview' . esc_attr($preview_hidden) . '">';
            echo '<div class="iset-image-preview"' . $preview_style . '>';
            echo '<span class="iset-remove">✕</span><span class="iset-src-span"><img src="' . esc_url($preview_src) . '" class="iset-src" /></span>';
            echo '</div>';
            echo '</div>';

        }

        $html  = '<div class="iset-wrap">';
        $html .= sprintf(
            '<input type="text" class="regular-text iset-url" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" data-depend-id="%2$s"/>',
            $this->field['section'],
            $this->field['id'],
            $this->value
        );
        $html .= '<a href="javascript:;" type="button" class="button button-primary iset-browse" data-library="'. esc_attr( $library ) .'"/>' . $settings['button_title'] . '</a>';
        $html .= '<a href="javascript:;" type="button" class="button iset-remove' . $hidden . '"/>' . $settings['remove_title'] . '</a>';
        $html .= '</div>';
        $html .= $this->get_field_description($this->field);

        echo $html;

    }

}