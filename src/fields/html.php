<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 22:22:00
 * @FilePath: /iowp-setting/src/fields/html.php
 * @Description: 
 */
namespace IO\WP\Setting;
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