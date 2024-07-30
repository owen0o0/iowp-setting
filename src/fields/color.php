<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 22:21:55
 * @FilePath: /iowp-setting/src/fields/color.php
 * @Description: 
 */
namespace IO\WP\Setting;
if (!defined('ABSPATH')) { die; }

class ISET_Field_color extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {

        $html = sprintf(
            '<input type="text" class="iset-color-picker" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" data-default-color="%4$s" data-depend-id="%2$s"/>',
            $this->field['section'],
            $this->field['id'],
            $this->value,
            $this->field['std']
        );

        $html .= $this->get_field_description( $this->field );

        echo $html;

    }

}