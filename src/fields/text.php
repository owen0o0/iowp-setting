<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 22:22:17
 * @FilePath: /iowp-setting/src/fields/text.php
 * @Description: 
 */
namespace IO\Setting;
if (!defined('ABSPATH')) { die; }

class ISET_Field_text extends ISET_Fields
{

    public function __construct($field, $value = '') {
        parent::__construct($field, $value);
    }

    public function render() {
        $type        = isset($this->field['type']) ? $this->field['type'] : 'text';
        $placeholder = empty($this->field['placeholder']) ? '' : ' placeholder="' . $this->field['placeholder'] . '"';

        $html = sprintf(
            '<input type="%1$s" class="regular-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"%5$s data-depend-id="%3$s"/>',
            $type,
            $this->field['section'],
            $this->field['id'],
            $this->value,
            $placeholder
        );
        $html .= $this->get_field_description($this->field);

        echo $html;

    }

}