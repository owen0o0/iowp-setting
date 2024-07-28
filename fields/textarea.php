<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 18:33:05
 * @FilePath: /io-setting/fields/textarea.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

class ISET_Field_textarea extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        $placeholder = empty($this->field['placeholder']) ? '' : ' placeholder="' . $this->field['placeholder'] . '"';

        $html = sprintf(
            '<textarea class="regular-text" id="%1$s[%2$s]" name="%1$s[%2$s]"%3$s data-depend-id="%2$s">%4$s</textarea>',
            $this->field['section'],
            $this->field['id'],
            $placeholder,
            $this->value
        );

        $html .= $this->get_field_description($this->field);

        echo $html;
    }

}