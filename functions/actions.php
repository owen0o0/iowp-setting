<?php 
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 16:15:03
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-29 15:23:55
 * @FilePath: /iowp-setting/functions/actions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { die; }


if (!function_exists('iset_chosen_ajax')) {

    /**
     * 处理通过AJAX提交的设置选择项的请求。
     *
     * 此函数验证请求的合法性，包括nonce验证、参数验证，并根据验证结果返回成功或错误信息。
     * 它使用了ISET_Fields类来获取特定类型和术语的字段数据。
     *
     * @since 1.0.0
     * @access public
     */    
    function iset_chosen_ajax() {

        $nonce = (!empty($_POST['nonce'])) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        $type  = (!empty($_POST['type'])) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
        $term  = (!empty($_POST['term'])) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
        $query = (!empty($_POST['query_args'])) ? wp_kses_post_deep($_POST['query_args']) : array();

        if (!wp_verify_nonce($nonce, 'iset_chosen_ajax_nonce')) {
            wp_send_json_error(array('error' => esc_html__('Error: Invalid nonce verification.', 'iset_plugin')));
        }

        if (empty($type) || empty($term)) {
            wp_send_json_error(array('error' => esc_html__('Error: Invalid term ID.', 'iset_plugin')));
        }

        $capability = apply_filters('iset_chosen_ajax_capability', 'manage_options');

        if (!current_user_can($capability)) {
            wp_send_json_error(array('error' => esc_html__('Error: You do not have permission to do that.', 'iset_plugin')));
        }

        $options = ISET_Fields::field_data($type, $term, $query);

        wp_send_json_success($options);

    }
    add_action('wp_ajax_iset-chosen', 'iset_chosen_ajax');
}
