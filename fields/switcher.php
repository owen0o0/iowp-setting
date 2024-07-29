<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 17:04:05
 * @FilePath: /iowp-setting/fields/switcher.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

class ISET_Field_switcher extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {

        $active = (!empty($this->value)) ? ' iset-active' : '';

        $html = '<div class="iset-switcher' . esc_attr($active) . '">';
        $html .= '<span class="iset-on">◯</span>';
        $html .= '<span class="iset-off">－</span>';
        $html .= '<span class="iset-ball"></span>';
        $html .= sprintf(
            '<input type="hidden" name="%1$s[%2$s]" id="%1$s[%2$s]" value="%3$s"  data-depend-id="%2$s"/>',
            $this->field['section'],
            $this->field['id'],
            $this->value
        );
        $html .= '</div>';

        $html .= $this->get_field_description($this->field);
        
        echo $html;

    }

}