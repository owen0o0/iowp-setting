<?php

/**
 * Plugin Name: IO Setting API
 * Plugin URI:  https://www.iotheme.cn/
 * Description: IO Setting API 使用示例插件
 * Version: 1.1.0
 * Author: iowen
 * Author URI: https://www.iotheme.cn/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: iset_plugin
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) or exit;

use IO\Setting\ISET;
require_once plugin_dir_path( __FILE__ ) .'src/ISET.php';

$ISET = new ISET();


$options = array(
	'title'         => 'ISET-Setting-API Demo',
	'desc'          => '',
	'menu_title'    => 'ISET DEMO',
	'menu_slug'     => 'demo',
	//'menu_parent'   => 'options-general.php',
	'serialize'     => true, //unserialize
	'sidebar'       => true,
	'plugin_action' => 'iowp-setting/iset-demo.php',
	'class'         => 'io-option',
	'footer_html'   => '<p><strong>Footer:</strong>...</p>',
);

$ISET->create_options('iset_demo_config', $options);

$ISET->create_section(array(
	'id'     => 'basal',
	'title'  => __('Basal', 'iset_plugin'),
	'desc'   => __('Basic components', 'iset_plugin'),
	'fields' => array(
		array(
			'id'                => 'text-1',
			'type'              => 'text',
			'title'             => __('Text Input', 'iset_plugin'),
			'desc'              => __('Text input description', 'iset_plugin'),
			'placeholder'       => __('Text Input placeholder', 'iset_plugin'),
			'default'           => 'Title',
			'sanitize_callback' => 'sanitize_text_field'
		),
		array(
			'id'                => 'number-1',
			'type'              => 'number',
			'title'             => __('Number Input', 'iset_plugin'),
			'desc'              => __('Number field with validation callback `floatval`', 'iset_plugin'),
			'placeholder'       => __('1.99', 'iset_plugin'),
			'settings'          => array(
				'min'  => 0,
				'max'  => 100,
				'step' => '0.01'
			),
			'default'           => 1.1,
			'sanitize_callback' => 'floatval'
		),
		array(
			'id'    => 'switcher-1',
			'type'  => 'switcher',
			'title' => __('Switcher', 'iset_plugin'),
			'desc'  => __('Switcher description', 'iset_plugin')
		),
		array(
			'id'          => 'textarea-1',
			'title'       => __('Textarea Input', 'iset_plugin'),
			'desc'        => __('Textarea description', 'iset_plugin'),
			'placeholder' => __('Textarea placeholder', 'iset_plugin'),
			'type'        => 'textarea',
		),
		array(
			'type'    => 'html',
			'content' => __('Html description', 'iset_plugin'),
		),
		array(
			'id'      => 'checkbox-1',
			'type'    => 'checkbox',
			'title'   => __('Checkbox', 'iset_plugin'),
			'desc'    => __('Checkbox Label', 'iset_plugin'),
			'options' => 'pages',
		),
		array(
			'id'      => 'radio-1',
			'type'    => 'radio',
			'title'   => __('Radio Button', 'iset_plugin'),
			'desc'    => __('A radio button', 'iset_plugin'),
			'inline'  => true,
			'options' => array(
				'yes' => 'Yes',
				'no'  => 'No'
			),
			'default' => 'no'
		),
		array(
			'id'      => 'select-1',
			'type'    => 'select',
			'title'   => __('A Dropdown', 'iset_plugin'),
			'desc'    => __('Dropdown description', 'iset_plugin'),
			'options' => 'pages'
		),
		array(
			'id'       => 'wp_editor-1',
			'type'     => 'wp_editor',
			'title'    => __('Advanced Editor', 'iset_plugin'),
			'desc'     => __('WP_Editor description', 'iset_plugin'),
			'default'  => '',
			'settings' => array(
				'tinymce'       => false,//加载 TinyMCE
				'quicktags'     => true,//加载快速标签
				'media_buttons' => true,//显示媒体插入/上传按钮
				'height'        => '200px',
				'width'         => '600px',
			)
		)
	)
));

$ISET->create_section(array(
	'id'     => 'advanced',
	'title'  => __('Advanced', 'iset_plugin'),
	'fields' => array(
		array(
			'id'      => 'file-1',
			'type'    => 'file',
			'title'   => __('File all', 'iset_plugin'),
			'desc'    => __('File all description', 'iset_plugin'),
			'default' => '',
		),
		array(
			'id'       => 'file-2',
			'type'     => 'file',
			'title'    => __('File image', 'iset_plugin'),
			'desc'     => __('File image no preview description', 'iset_plugin'),
			'default'  => '',
			'settings' => array(
				'preview' => false,
				'library' => 'image'
			)
		),
		array(
			'type'    => 'notice',
			'style'   => 'info',
			'content' => __('Notice info description', 'iset_plugin'),
		),
		array(
			'id'       => 'file-3',
			'type'     => 'file',
			'title'    => __('File video', 'iset_plugin'),
			'desc'     => __('File video description', 'iset_plugin'),
			'default'  => '',
			'settings' => array(
				'library' => 'video'
			)
		),
		array(
			'id'      => 'color-1',
			'type'    => 'color',
			'title'   => __('Color', 'iset_plugin'),
			'desc'    => __('Color description', 'iset_plugin'),
			'default' => '#f35e00'
		),
		array(
			'id'          => 'radio-9',
			'type'        => 'radio',
			'title'       => 'Radio Group',
			'placeholder' => 'Radio an option',
			'options'     => array(
				'Group 1' => array(
					'option-1' => 'Option 1',
					'option-2' => 'Option 2',
					'option-3' => 'Option 3',
				),
				'Group 2' => array(
					'option-4'       => 'Option 4',
					'option-5'       => 'Option 5',
					'option-6'       => 'Option 6',
					'option-7'       => 'Option Option 7',
				),
			),
			'default'     => 'option-5',
			'desc'        => 'Radio Group'
		),
		array(
			'id'      => 'color-2',
			'type'    => 'color',
			'title'   => __('Color', 'iset_plugin'),
			'desc'    => __('Color description', 'iset_plugin'),
			'default' => '#035ef0'
		),
		array(
			'id'          => 'checkbox-9',
			'type'        => 'checkbox',
			'title'       => 'Checkbox Group',
			'placeholder' => 'Checkbox an option',
			'options'     => array(
				'Group 1' => array(
					'option-1' => 'Option 1',
					'option-2' => 'Option 2',
					'option-3' => 'Option 3',
				),
				'Group 2' => array(
					'option-4'       => 'Option 4',
					'option-5'       => 'Option 5',
					'option-6'       => 'Option 6',
					'option-7'       => 'Option Option 7',
				),
			),
			'default'     => array('option-5', 'option-2', 'option-7'),
			'desc'        => 'Checkbox Group'
		),
		array(
			'id'       => 'number-2',
			'type'     => 'number',
			'title'    => __('Month Input', 'iset_plugin'),
			'desc'     => __('Month field', 'iset_plugin'),
			'settings' => array(
				'min'  => 1,
				'max'  => 12,
				'step' => '1',
				'unit' => '月'
			),
			'default'  => 2,
		),
		array(
			'id'      => 'wp_editor-2',
			'type'    => 'wp_editor',
			'title'   => __('Advanced Editor', 'iset_plugin'),
			'desc'    => __('WP_Editor description', 'iset_plugin'),
			'default' => ''
		),
		array(
			'id'      => 'checkbox-2',
			'type'    => 'checkbox',
			'title'   => __('Multile checkbox', 'iset_plugin'),
			'desc'    => __('Multi checkbox description', 'iset_plugin'),
			'inline'  => true,
			'options' => array(
				'one'   => 'One',
				'two'   => 'Two',
				'three' => 'Three',
				'four'  => 'Four'
			),
			'default' => array('one', 'four')
		),
	)
));

$ISET->create_section(array(
	'id'     => 'dependency',
	'title'  => __('Dependency', 'iset_plugin'),
	'fields' => array(
		array(
			'id'      => 'switcher-2',
			'type'    => 'switcher',
			'title'   => __('Switcher', 'iset_plugin'),
			'desc'    => __('Switcher description', 'iset_plugin'),
			'default' => true
		),
		array(
			'id'          => 'textarea-3',
			'type'        => 'textarea',
			'title'       => __('Textarea Input', 'iset_plugin'),
			'desc'        => __('Textarea description', 'iset_plugin'),
			'placeholder' => __('Textarea placeholder', 'iset_plugin'),
			'dependency'  => array('switcher-2', '==', 'true', '', 'true'),
		),
		array(
			'id'      => 'checkbox-3',
			'type'    => 'checkbox',
			'title'   => __('Multile checkbox', 'iset_plugin'),
			'desc'    => __('Multi checkbox description', 'iset_plugin'),
			'default' => array('one', 'three'),
			'options' => array(
				'one'   => 'One',
				'two'   => 'Two',
				'three' => 'Three',
				'four'  => 'Four'
			)
		),
		array(
			'type'       => 'notice',
			'title'      => 'Notice',
			'style'      => 'success',
			'content'    => __('Notice error description', 'iset_plugin'),
			'dependency' => array('checkbox-3', '==', 'one,three'),
		),
		array(
			'id'    => 'checkbox-4',
			'type'  => 'checkbox',
			'title' => __('checkbox', 'iset_plugin'),
			'desc'  => __('checkbox description', 'iset_plugin'),
			//'default' => true,
		),
		array(
			'type'       => 'notice',
			'style'      => 'warning',
			'content'    => __('Notice warning description', 'iset_plugin'),
			'dependency' => array('checkbox-4', '==', 'true'),
		),
		array(
			'id'      => 'radio-3',
			'type'    => 'radio',
			'title'   => __('Radio Button', 'iset_plugin'),
			'desc'    => __('A radio button', 'iset_plugin'),
			'inline'  => true,
			'options' => array(
				'yes' => 'Yes',
				'no'  => 'No'
			),
			'default' => 'yes'
		),
	)
));

$ISET->create_section(array(
	'id'     => 'test',
	'title'  => __('Test', 'iset_plugin'),
	'fields' => array(
		array(
			'id'          => 'select-2',
			'type'        => 'select',
			'title'       => 'Select multiple',
			'placeholder' => 'Select an option',
			'options'     => array(
				'Group 1' => array(
					'option-1' => 'Option 1',
					'option-2' => 'Option 2',
					'option-3' => 'Option 3',
				),
				'Group 2' => array(
					'option-4' => 'Option 4',
					'option-5' => 'Option 5',
					'option-6' => 'Option 6',
				),
			),
			'default'     => array('option-2', 'option-5'),
			'settings'    => array(
				'multiple' => true
			)
		),
		array(
			'id'          => 'select-3',
			'type'        => 'select',
			'title'       => 'Select multiple sortable',
			'placeholder' => 'Select an option',
			'options'     => array(
				'Group 1' => array(
					'option-1' => 'Option 1',
					'option-2' => 'Option 2',
					'option-3' => 'Option 3',
				),
				'Group 2' => array(
					'option-4'       => 'Option 4',
					'option-5'       => 'Option 5',
					'option-6'       => 'Option 6',
					'option-7'       => 'Option Option 7',
				),
			),
			'default'     => array('option-5', 'option-2'),
			'desc'        => 'Select multiple chosen sortable',
			'settings'    => array(
				'multiple' => true,
				'chosen'   => true,
				'sortable' => true
			)
		),
		array(
			'id'          => 'select-4',
			'type'        => 'select',
			'title'       => 'Select chosen',
			'placeholder' => 'Select an option',
			'options'     => array(
				'option-1'       => 'Option 1',
				'option-2'       => 'Option 2',
				'option-3'       => 'Option 3',
				'option-4'       => 'Option 4',
				'option-5'       => 'Option 5',
				'option-6'       => 'Option 6',
				'option-7'       => 'Option Option 7',
			),
			'default'     => 'option-3',
			'settings'    => array(
				'chosen' => true
			)
		),
		array(
			'id'          => 'select-5',
			'type'        => 'select',
			'title'       => 'Select with pages', 
			'options'     => 'pages',
			'settings'    => array(
				'chosen'      => true,
				'ajax'        => true,
			),
			'dependency'  => array('select-4', '==', 'option-3'),
		),
		array(
			'id'          => 'select-6',
			'type'        => 'select',
			'title'       => 'Select with posts',
			'placeholder' => 'Select posts',
			'options'     => 'posts',
			'settings'    => array(
				'chosen'      => true,
				'ajax'        => true,
				'multiple'    => true,
			)
		),
	)
));



$ISET->create_sidebars(array(
	array(
		'name'    	 => __('简介', 'iset_plugin'),
		'content' 	 => __('Wordpress Setting API 简化', 'iset_plugin'),
		'buts'       => array(
			array(
				'title' => __('Github', 'iset_plugin'),
				'link'  => 'https://github.com/owen0o0/iowp-setting',
				'class' => 'button button-primary',
				'attr'  => 'target="_blank"'
			)
		)
	),
	array(
		'name'       => __('建站套件', 'iset_plugin'),
		'content'    => '<ul>
							<li><a href="https://www.iotheme.cn/store/onenav.html" target="_blank">' . __('OneNav 导航主题', 'iset_plugin') . '</a></li>
							<li><a href="https://www.iotheme.cn/store/swallow.html" target="_blank">' . __('Swallow 单栏博客主题', 'iset_plugin') . '</a></li>
							<li><a href="https://www.iotheme.cn/store/zerofoam.html" target="_blank">' . __('ZeroFoam 会员、圈子系统，社交利器', 'iset_plugin') . '</a></li>
							<li><a href="https://www.iotheme.cn/store/io-code-highlight.html" target="_blank">' . __('io Code Highlight 代码高亮插件', 'iset_plugin') . '</a></li>
						</ul>',
		'buts'       => array(
			array(
				'title' => __('了解更多', 'iset_plugin'),
				'link'  => 'https://www.iotheme.cn/goods',
				'class' => 'button button-primary',
				'attr'  => 'target="_blank"'
			)
		)
	),
	array(
		'section_id' => 'test',
		'name'       => __('测试', 'iset_plugin'),
		'content'    => '测试字段',
		'desc'       => '测试字段描述',
		'buts'       => array()
	)
));
