<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 17:02:41
 * @FilePath: /io-setting/fields/html.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

class ISET_Field_html extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        echo $this->field['content'];
        echo $this->get_field_dependency( $this->field );

    }

}