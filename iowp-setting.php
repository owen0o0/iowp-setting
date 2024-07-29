<?php
/**
 * Plugin Name: IO Setting API
 * Plugin URI:  https://www.iotheme.cn/
 * Description: IO Setting API 使用示例插件
 * Version: 1.0.2
 * Author: iowen
 * Author URI: https://www.iotheme.cn/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: iset_plugin
 * Domain Path: /languages/
 */

defined('ISET_VERSION') or define('ISET_VERSION', '1.0.2');

require_once plugin_dir_path( __FILE__ ) .'classes/setting.class.php';

// 演示 DEMO
require_once plugin_dir_path( __FILE__ ) .'setting-demo.php';

