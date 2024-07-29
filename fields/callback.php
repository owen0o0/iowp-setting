<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 17:02:07
 * @FilePath: /iowp-setting/fields/callback.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

class ISET_Field_callback extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        if (!empty($this->field['function']) && is_callable($this->field['function'])) {
            call_user_func($this->field['function'], $this->field['args']);
        }
        echo $this->get_field_dependency($this->field);

    }

}