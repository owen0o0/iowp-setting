<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 18:45:11
 * @FilePath: /iowp-setting/fields/select.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

class ISET_Field_select extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {

        $value = (is_array($this->value)) ? $this->value : array_filter((array) $this->value);

        $settings = wp_parse_args($this->field['settings'], array(
            'multiple'      => false,  // 多选模式
            'chosen'        => false,  // chosen 模式，不支持移动端
            'sortable'      => false,  // 可排序，依赖 chosen
            'ajax'          => false,  // ajax 模式，依赖 chosen
            'settings'      => array(),
        ));

        $ajax_settings = array();
        if ($settings['ajax']) {
            $ajax_settings['data']['type']  = $this->field['options'];
            $ajax_settings['data']['nonce'] = wp_create_nonce('iset_chosen_ajax_nonce');
            if (!empty($this->field['query_args'])) {
                $ajax_settings['data']['query_args'] = $this->field['query_args'];
            }
        }

        $chosen_rtl       = (is_rtl()) ? ' chosen-rtl' : '';
        $multiple_name    = ($settings['multiple']) ? '[]' : '';
        $multiple_attr    = ($settings['multiple']) ? ' multiple="multiple"' : '';
        $chosen_sortable  = ($settings['chosen'] && $settings['sortable']) ? ' iset-chosen-sortable' : '';
        $chosen_ajax      = ($settings['chosen'] && $settings['ajax']) ? ' iset-chosen-ajax' : '';
        $placeholder_attr = ($settings['chosen'] && $this->field['placeholder']) ? ' data-placeholder="' . esc_attr($this->field['placeholder']) . '"' : '';
        $field_class      = ($settings['chosen']) ? ' class="iset-chosen' . esc_attr($chosen_rtl . $chosen_sortable . $chosen_ajax) . '"' : '';
        $field_name       = "{$this->field['section']}[{$this->field['id']}]" . $multiple_name;
        $field_attr       = ' data-depend-id="' . $this->field['id'] . '"' . (($settings['multiple']) ? ' style="min-width: 200px;"' : '');
        $chosen_data_attr = ($settings['chosen'] && !empty($ajax_settings)) ? ' data-chosen-settings="' . esc_attr(json_encode($ajax_settings)) . '"' : '';

        $options = $this->field['options'];
        if (is_string($options) && !empty($settings['chosen']) && !empty($settings['ajax'])) {
            $options = $this->field_wp_query_data_title($options, $value);
        } else if (is_string($options)) {
            $options = $this->field_data($options, false, $this->field['query_args']);
        }

        if (!empty($settings['chosen']) && !empty($settings['multiple'])) {
            echo '<select name="' . $field_name . '" class="iset-hide-select hidden"' . $multiple_attr . $field_attr . '>';
            foreach ($value as $option_key) {
                echo '<option value="' . esc_attr($option_key) . '" selected>' . esc_attr($option_key) . '</option>';
            }
            echo '</select>';

            $field_name = '_pseudo';
            $field_attr = '';
        }

        echo '<select name="'. esc_attr( $field_name ) .'"'. $field_class . $multiple_attr . $placeholder_attr . $field_attr . $chosen_data_attr .'>';

        if ($this->field['placeholder'] && empty($settings['multiple'])) {
            if (!empty($settings['chosen'])) {
                echo '<option value=""></option>';
            } else {
                echo '<option value="">' . esc_attr($this->field['placeholder']) . '</option>';
            }
        }

        foreach ($options as $option_key => $option) {
            if (is_array($option) && !empty($option)) {
                echo '<optgroup label="' . esc_attr($option_key) . '">';

                foreach ($option as $key => $label) {
                    $selected = (in_array($key, $value)) ? ' selected' : '';
                    echo '<option value="' . esc_attr($key) . '" ' . esc_attr($selected) . '>' . esc_attr($label) . '</option>';
                }

                echo '</optgroup>';
            } else {
                $selected = (in_array($option_key, $value)) ? ' selected' : '';
                echo '<option value="' . esc_attr($option_key) . '" ' . esc_attr($selected) . '>' . esc_attr($option) . '</option>';
            }
        }

        echo'</select>';
        echo $this->get_field_description( $this->field );

    }

    public function enqueue() {
        if (!wp_script_is('jquery-ui-sortable')) {
            wp_enqueue_script('jquery-ui-sortable');
        }
    }
}