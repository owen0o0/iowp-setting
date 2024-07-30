<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 22:21:44
 * @FilePath: /iowp-setting/src/fields/checkbox.php
 * @Description: 
 */
namespace IO\WP\Setting;
if (!defined('ABSPATH')) { die; }

class ISET_Field_checkbox extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        $depend_id  = ' data-depend-id="' . $this->field['id'] . '"';

        echo '<fieldset>';
        if (!empty($this->field['options'])) {
            // 多选
            $value = (is_array($this->value)) ? $this->value : array_filter((array) $this->value);

            $inline_class = ($this->field['inline']) ? ' class="iset-inline-list"' : '';
            $field_name   = "{$this->field['section']}[{$this->field['id']}][]";

            $options = $this->field['options'];
            $options = ( is_array( $options ) ) ? $options : array_filter( $this->field_data( $options, false, $this->field['query_args'] ) );

            echo '<ul'. $inline_class .'>';

            foreach ($options as $option_key => $option_value) {
                if (is_array($option_value) && !empty($option_value)) {
                    echo '<li>';
                    echo '<ul>';
                    echo '<li><strong>' . esc_attr($option_key) . '</strong></li>';
                    foreach ($option_value as $key => $label) {
                        $checked = (in_array($key, $value)) ? ' checked' : '';
                        echo '<li>';
                        echo '<label>';
                        echo '<input type="checkbox" name="' . $field_name . '" value="' . esc_attr($key) . '"' . $depend_id . esc_attr($checked) . '/>';
                        echo '<span class="iset-text">' . esc_attr($label) . '</span>';
                        echo '</label>';
                        echo '</li>';
                    }
                    echo '</ul>';
                    echo '</li>';
                } else {
                    $checked = (in_array($option_key, $value)) ? ' checked' : '';

                    echo '<li>';
                    echo '<label>';
                    echo '<input type="checkbox" name="' . $field_name . '" value="' . esc_attr($option_key) . '"' . $depend_id . esc_attr($checked) . '/>';
                    echo '<span class="iset-text">' . esc_attr($option_value) . '</span>';
                    echo '</label>';
                    echo '</li>';
                }
            }
            echo '</ul>';
        } else {
            $field_name = "{$this->field['section']}[{$this->field['id']}]";
            // 单选
            echo '<label class="iset-checkbox">';
            echo '<input type="hidden" class="iset-input" name="' . $field_name . '" value="'. $this->value .'"' . $depend_id . '/>';
            echo '<input type="checkbox" class="checkbox-single" name="_pseudo"' . esc_attr(checked($this->value, 1, false)) . $depend_id . '/>';
            echo '</label>';
        }

        echo $this->get_field_description( $this->field );
        echo '</fieldset>';

    }

}