<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 18:30:28
 * @FilePath: /iowp-setting/fields/number.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

class ISET_Field_number extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        $type        = isset($this->field['type']) ? $this->field['type'] : 'number';
        $placeholder = empty($this->field['placeholder']) ? '' : ' placeholder="' . $this->field['placeholder'] . '"';

        $settings = wp_parse_args($this->field['settings'], array(
            'min'  => '',
            'max'  => '',
            'step' => '',
            'unit' => '',
        ));
        $min         = ($settings['min'] == '') ? '' : ' min="' . $settings['min'] . '"';
        $max         = ($settings['max'] == '') ? '' : ' max="' . $settings['max'] . '"';
        $step        = ($settings['step'] == '') ? ' step="any"' : ' step="' . $settings['step'] . '"';
        $unit        = ($settings['unit'] == '') ? '' : '<span class="iset-unit">' . $settings['unit'] . '</span>';

        $html = sprintf(
            '<div class="iset-wrap"><input type="%1$s" class="regular-number" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"%5$s%6$s%7$s%8$s data-depend-id="%3$s"/>%9$s</div>',
            $type,
            $this->field['section'],
            $this->field['id'],
            $this->value,
            $placeholder,
            $min,
            $max,
            $step,
            $unit
        );
        $html .= $this->get_field_description($this->field);
        echo $html;
    }

}