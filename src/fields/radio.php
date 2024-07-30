<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 22:22:09
 * @FilePath: /iowp-setting/src/fields/radio.php
 * @Description: 
 */
namespace IO\WP\Setting;
if (!defined('ABSPATH')) { die; }

class ISET_Field_radio extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        
        $depend_id  = ' data-depend-id="' . $this->field['id'] . '"';
        
        $options = $this->field['options'];
        $options = ( is_array( $options ) ) ? $options : array_filter( $this->field_data( $options, false, $this->field['query_args'] ) );

        $inline_class = ($this->field['inline']) ? ' class="iset-inline-list"' : '';
        $field_name   = "{$this->field['section']}[{$this->field['id']}]";
        
        echo '<fieldset>';

        echo '<ul'. $inline_class .'>';


        foreach ($options as $option_key => $option_value) {
            if (is_array($option_value) && !empty($option_value)) {
                echo '<li>';
                echo '<ul>';
                echo '<li><strong>' . esc_attr($option_key) . '</strong></li>';
                foreach ($option_value as $key => $label) {
                    $checked = ($key == $this->value) ? ' checked' : '';
                    echo '<li>';
                    echo '<label>';
                    echo '<input type="radio" name="' . $field_name . '" value="' . esc_attr($key) . '"' . $depend_id . esc_attr($checked) . '/>';
                    echo '<span class="iset-text">' . esc_attr($label) . '</span>';
                    echo '</label>';
                    echo '</li>';
                }
                echo '</ul>';
                echo '</li>';
            } else {
                $checked = ($option_key == $this->value) ? ' checked' : '';
                echo '<li>';
                echo '<label>';
                echo '<input type="radio" name="' . $field_name . '" value="' . esc_attr($option_key) . '"' . $depend_id . esc_attr($checked) . '/>';
                echo '<span class="iset-text">' . esc_attr($option_value) . '</span>';
                echo '</label>';
                echo '</li>';
            }
        }

        echo '</ul>';

        echo $this->get_field_description( $this->field );
        echo '</fieldset>';

    }

}