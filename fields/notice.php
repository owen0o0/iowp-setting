<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 17:02:48
 * @FilePath: /iowp-setting/fields/notice.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

class ISET_Field_notice extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        $style = $this->field['style'];
        echo '<div class="iset-notice iset-notice-' . esc_attr($style) . '">' . $this->field['content'] . '</div>';

        echo $this->get_field_dependency($this->field);
    }

}