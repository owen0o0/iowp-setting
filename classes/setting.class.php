<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-20 11:31:42
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-28 18:30:31
 * @FilePath: /io-setting/classes/setting.class.php
 * @Description: 
 */

if (!defined('ABSPATH')) { die; }

if ( ! class_exists( 'ISET' ) ) :

class ISET {
    protected $version  = '1.0.0';
    /**
     * 设置选项卡数组
     *
     * @var array
     */
    protected $settings_sections = array();

    /**
     * 设置字段数组
     *
     * @var array
     */
    protected $settings_fields = array();
    /**
     * 设置侧边栏数组
     *
     * @var array
     */
    protected $settings_sidebars = array();
    
    /**
     * 设置选项
     * @var array
     */
    protected $options = array();

    /**
     * 启用的设置字段
     * @var array
     */
    protected $enabled_fields = array();

    /**
     * 设置前缀
     * @var string
     */
    protected $prefix = 'iset_option_config';

    /**
     * 是否序列化
     * 
     * 如果为 true，则将所有设置保存为一个数组，
     * 如果为 false，则将每个选项卡的设置保存为一个单独的选项，sections['id']
     * @var bool
     */
    protected $serialize = true;

    /**
     * 设置页面URL
     * @var string
     */
    protected $url = '';

    /**
     * 设置页面目录
     * @var string
     */
    protected $dir = '';

    /**
     * 设置缓存
     * @var array
     */
    protected $config = array();


    /**
     * 构造函数初始化插件或主题的基本设置。
     * 
     * 在对象实例化时自动调用，负责执行一系列初始化任务，包括加载常量、包含文件、设置文本域。
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->constants();
        $this->includes();
        $this->textdomain();

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }

    /**
     * 确定插件或主题的目录和URL，用于设置目录和URL的常量。
     * 
     * 本函数主要用于解决插件或主题在不同环境下的路径和URL问题。
     * 它通过规范化和比较路径来判断当前代码是位于插件目录还是主题目录，
     * 并据此设置相应的目录路径和URL。
     */
    function constants() {
        $dirname        = str_replace('//', '/', wp_normalize_path(dirname(dirname(__FILE__))));
        $theme_dir      = str_replace('//', '/', wp_normalize_path(get_parent_theme_file_path()));
        $plugin_dir     = str_replace('//', '/', wp_normalize_path(WP_PLUGIN_DIR));
        $plugin_dir     = str_replace('/opt/bitnami', '/bitnami', $plugin_dir);
        $located_plugin = (preg_match('#' . $this->sanitize_dirname($plugin_dir) . '#', $this->sanitize_dirname($dirname))) ? true : false;
        $directory      = ($located_plugin) ? $plugin_dir : $theme_dir;
        $directory_uri  = ($located_plugin) ? WP_PLUGIN_URL : get_parent_theme_file_uri();
        $foldername     = str_replace($directory, '', $dirname);
        $protocol_uri   = (is_ssl()) ? 'https' : 'http';
        $directory_uri  = set_url_scheme($directory_uri, $protocol_uri);

        $this->dir = $dirname;
        $this->url = $directory_uri . $foldername;
    }

    /**
     * 载入插件所需的文件和类。
     * 
     * 本函数负责根据当前插件的需求，动态载入插件的功能文件和类文件。
     * 它首先载入插件的基础功能文件和字段类文件，然后根据过滤器`iset_fields`的返回值，
     * 动态载入对应的字段类文件。这样设计的目的是为了提高插件的灵活性和可扩展性，
     * 只加载真正需要的字段类型，减少不必要的资源消耗。
     * 
     * @return void 无返回值。
     */
    function includes() {
        require_once ($this->dir . '/functions/actions.php');

        require_once ($this->dir . '/classes/fields.class.php');

        $fields = apply_filters('iset_fields', array(
            'callback',
            'checkbox',
            'color',
            'file',
            'html',
            'notice',
            'number',
            'radio',
            'select',
            'switcher',
            'text',
            'textarea',
            'wp_editor'
        ));

        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!class_exists('ISET_Field_' . $field) && class_exists('ISET_Fields')) {
                    require_once ($this->dir . '/fields/' . $field . '.php');
                }
            }
        }
    }

    /**
     * 加载翻译文件
     * @return void
     */
    function textdomain() {
        load_textdomain('iset_plugin', $this->dir . '/languages/' . get_locale() . '.mo');
    }
    
    /**
     * 在管理页面加载脚本和样式。
     * 
     * 该函数负责根据当前的管理页面hook_suffix来决定是否加载插件的样式和脚本。
     * 它还负责初始化颜色选择器，以及加载本地化脚本以支持Ajax操作和国际化文本。
     * 
     * @param string $hook_suffix 当前页面的hook_suffix，用于判断是否应该加载脚本和样式。
     */
    function admin_enqueue_scripts($hook_suffix) {
        // 检查当前页面是否为插件的设置页面，如果不是，则退出函数
        if ( strpos( $hook_suffix, $this->options['menu_slug'] ) === false ) {
            return;
        }

        // 注册并加载WordPress媒体管理器所需的脚本和样式
        wp_enqueue_media();

        // 注册并加载WordPress颜色选择器所需的样式和脚本
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // 注册并加载jQuery
        //wp_enqueue_script('jquery');


        // 根据WP_DEBUG和SCRIPT_DEBUG变量决定是否使用.min版本的样式和脚本
        $min = (WP_DEBUG || SCRIPT_DEBUG) ? '' : '.min';

        wp_enqueue_style('iset_setting', $this->url . '/assets/css/main'. $min .'.css', array(), $this->version);

        wp_enqueue_script('iset_plugins', $this->url . '/assets/js/plugins'. $min .'.js', array('jquery'), $this->version, true);
        wp_enqueue_script('iset_setting', $this->url . '/assets/js/main'. $min .'.js', array('iset_plugins'), $this->version, true);

        wp_localize_script('iset_setting', 'iset_vars', array(
            'color_palette' => apply_filters('iset_color_palette', array()),
            'menu_slug'     => $this->options['menu_slug'],
            'ajax_url'      => admin_url('admin-ajax.php'),
            'i18n'          => array(
                'confirm'         => esc_html__('Are you sure?', 'iset_plugin'),//Are you sure?
                'typing_text'     => esc_html__('Please enter %s or more characters', 'iset_plugin'),//Please enter %s or more characters
                'searching_text'  => esc_html__('Searching...', 'iset_plugin'),//Searching...
                'no_results_text' => esc_html__('No results found.', 'iset_plugin'),/* translators: 未找到结果。 */
            ),
        ));

        // 遍历启用的字段，如果字段类型存在对应的类和enqueue方法，则调用该方法加载字段特定的脚本和样式
        if (!empty($this->enabled_fields)) {
            foreach ($this->enabled_fields as $field) {
                if (!empty($field['type'])) {
                    $classname = 'ISET_Field_' . $field['type'];
                    if (class_exists($classname) && method_exists($classname, 'enqueue')) {
                        $instance = new $classname($field);
                        if (method_exists($classname, 'enqueue')) {
                            $instance->enqueue();
                        }
                        unset($instance);
                    }
                }
            }
        }

        // 触发'iset_enqueue'动作，允许其他插件或主题进行额外的加载操作
        do_action( 'iset_enqueue' );
    }

    /**
     * 在WordPress管理菜单中添加子菜单页面。
     * 此函数负责在WordPress的后台菜单中创建一个新的子菜单页面，用于显示插件的设置。
     * 它还处理在插件列表页面上添加一个链接到插件的设置页面。
     *
     * @hook admin_menu 该函数通过钩子admin_menu调用，用于在WordPress后台菜单中添加项。
     */
    function admin_menu() {
        extract($this->options);
        if (empty($menu_parent)) {
            add_menu_page(esc_attr($menu_title), esc_attr($menu_title), 'manage_options', $menu_slug, array($this, 'show_setting_page'), $menu_icon, $menu_position);
            if (count($this->settings_sections) > 1) {
                foreach ($this->settings_sections as $section) {
                    add_submenu_page( $menu_slug, esc_attr($section['title']), esc_attr($section['title']), 'manage_options', $menu_slug . '#tab=' . sanitize_title($section['id']), '__return_null');
                }
                remove_submenu_page($menu_slug, $menu_slug);
            }
        } else {
            add_submenu_page($menu_parent, esc_attr($menu_title), esc_attr($menu_title), 'manage_options', $menu_slug, array($this, 'show_setting_page'));
        }
        // 如果插件选项中启用了插件行动链接，则添加设置链接到插件行动链接
        if (!empty($plugin_action)) {
            // 插件页设置
            add_filter('plugin_action_links', function ($links, $file) {
                // 检查当前插件是否是本插件，如果不是则返回原链接
                if ($this->options['plugin_action'] !== $file) {
                    return $links;
                }
                $settings_url  = add_query_arg(array('page' => $this->options['menu_slug']), ($this->options['menu_parent'] ?: 'admin.php'));
                $settings_link = '<a href="' . $settings_url . '#tab=">' . esc_html__('Settings', 'iset_plugin') . '</a>';
                array_unshift($links, $settings_link);
                return $links;
            }, 10, 2);
        }
    }

    /**
     * 创建设置选项页面
     * 
     * 本函数用于初始化和配置一个设置页面。它接受一个前缀和一个可选的参数数组，
     * 用于自定义设置页面的各种属性，如页面标题、描述、菜单标题等。
     * 
     * @param string $prefix 设置项的前缀，用于唯一标识设置项。
     * @param array $args 可选参数数组，用于定制设置页面的显示和行为。
     *                    包括页面标题、描述、菜单位置等。
     */
    function create_options($prefix, $args = array()) {
        $defaults = array(
            'title'         => '设置',
            'desc'          => '',
            'menu_title'    => '设置',
            'menu_slug'     => 'iset_setting',
            'menu_parent'   => '', //options-general.php
            'menu_icon'     => '',
            'menu_position' => null,
            'serialize'     => true,   //unserialize
            'sidebar'       => true,
            'plugin_action' => '',
            'class'         => '',
            'footer_html'   => '',
        );
        $options  = wp_parse_args($args, $defaults);

        $this->serialize = $options['serialize'] ? true : false;
        $this->options   = $options;
        $this->prefix    = $prefix;
    }

    /**
     * 设置设置选项卡
     *
     * 本函数用于设置配置页面的各个选项卡。选项卡的设置对于组织和分类大量配置选项至关重要，
     * 它使得用户能够更有序地浏览和修改设置。
     *
     * @param array $sections 设置选项卡的数组。每个元素代表一个选项卡，元素值为选项卡的标题或描述。
     *
     * @return $this 返回当前对象，支持链式调用。
     */
    function set_sections( $sections) {
        $this->settings_sections = $sections;

        return $this;
    }

    /**
     * 添加一个新的设置选项卡到设置框架中。
     *
     * 该方法用于向一个设置框架中添加一个新的设置选项卡。通过向 $this->settings_sections
     * 数组中添加一个新的选项卡配置数组来实现。此方法支持链式调用。
     *
     * @param array $section 包含选项卡信息的数组。数组应包含选项卡的唯一标识和其他必要信息。
     *
     * @return $this 返回当前实例，支持链式调用。
     */
    function add_section( $section ) {
        $this->settings_sections[] = $section;

        return $this;
    }

    /**
     * 设置侧边栏
     * 
     * 该方法用于配置可用的侧边栏。通过将侧边栏的配置数组赋值给类的属性，
     * 提供了对侧边栏布局和内容的自定义能力。
     * 
     * @param array $sidebars 一个包含侧边栏配置的数组。每个侧边栏都应该在数组中
     *                         作为一个独立的元素，每个元素自身是一个数组，包含
     *                         侧边栏的相关设置，如名称、位置等。
     * 
     * @return $this 返回当前类实例，支持链式调用。
     */
    function set_sidebars( $sidebars) {
        $this->settings_sidebars = $sidebars;
        return $this;
    }

    /**
     * 添加一个侧边栏
     *
     * 该函数用于向系统中添加一个新的侧边栏。侧边栏是一个用于展示内容的区域，可以包括文本、图像等各种类型的内容。
     * 通过侧边栏，用户可以在主题的侧边位置自定义展示各种信息或功能。
     *
     * @param array $sidebar 侧边栏的配置数组，包含侧边栏的名称、内容、描述和按钮等信息。
     *                      - 'name'    string 侧边栏的名称，用于标识和区别不同的侧边栏。
     *                      - 'content' string 侧边栏的具体内容，可以是文本、HTML代码等。
     *                      - 'desc'    string 侧边栏的描述，用于说明侧边栏的用途或功能。
     *                      - 'buts'    array  侧边栏中包含的按钮数组，每个按钮是一个键值对，键是按钮名称，值是按钮链接。
     * 
     * @return $this 返回当前类实例，支持链式调用。
     */
    function add_sidebar( $sidebar ) {
        $defaults = array(
            'name'    => '',
            'content' => '',
            'desc'    => '',
            'buts'    => array()
        );
        $args = wp_parse_args( $sidebar, $defaults );

        $this->settings_sidebars[] = $args;
        return $this;
    }
    /**
     * 设置设置字段
     * 
     * 本函数用于配置可用的设置字段。这些字段通常用于构建一个配置界面，允许用户自定义一些应用或系统的设置。
     * 
     * @param array $fields 设置字段数组。该数组结构为多层嵌套，外层数组代表不同的设置区域（或称为节），内层数组则包含该节的所有设置字段及其详细信息。
     * 
     * @return $this 返回当前对象实例，支持链式调用。
     */
    function set_fields( $fields ) {
        $this->settings_fields = $fields;

        if (!empty($fields)) {
            foreach ($fields as $section) {
                foreach ($section as $field) {
                    $this->enabled_fields[$field['type']] = $field;
                }
            }
        }
    
        return $this;
    }

    /**
     * 添加一个设置字段到指定的段落中。
     *
     * 该方法用于在配置界面中添加一个新的设置字段。它允许开发者定义一个字段，
     * 并将其添加到特定的设置段落中。字段的详细信息可以通过参数 $field 提供，
     * 如果未提供全部信息，则会使用默认值。
     *
     * @param string $section 段落的ID，字段将被添加到这个段落中。
     * @param array $field 字段的配置信息。一个数组，包含 'id', 'title', 'desc', 'type' 等字段的值。
     *
     * @return $this 返回当前对象，允许链式调用。
     */
    function add_field( $section, $field ) {
        $defaults = array(
            'id'  => '',
            'type'  => 'text',
            'title' => '',
            'desc'  => '',
        );

        $args = wp_parse_args( $field, $defaults );
        $this->settings_fields[$section][] = $args;

        if (!empty($args)) {
            $this->enabled_fields[$args['type']] = $args;
        }

        return $this;
    }

    /**
     * 初始化设置部分和文件并将其注册到 WordPress
     *
     * 通常这应该在`admin_init`钩子上调用。
     * 此函数用于获取已启动的设置部分和字段。然后将它们注册到WordPress并准备使用。
     * 
     * @hook admin_init 该函数通过钩子admin_init调用。
     */
    function admin_init() {
        //注册设置部分
        foreach ($this->settings_sections as $section) {
            // 为具有描述的节添加内联HTML
            if (isset($section['desc']) && !empty($section['desc'])) {
                $section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
                $callback        = function () use ($section) {
                    echo str_replace('"', '\"', $section['desc']);
                };
            } else if (isset($section['callback'])) {
                $callback = $section['callback'];
            } else {
                $callback = null;
            }

            add_settings_section($section['id'], $section['title'], $callback, $section['id']);
        }

        $_option = array();
        
        //注册设置字段
        foreach ( $this->settings_fields as $section => $field ) {
            foreach ( $field as $option ) {
                $_section = $this->serialize ? $this->prefix : $section;

                $id       = isset($option['id']) ? $option['id'] : '';
                $type     = isset($option['type']) ? $option['type'] : 'text';
                $label    = isset($option['title']) ? $option['title'] : '';
                $callback = isset($option['callback']) ? $option['callback'] : array($this, 'show_field');

                $depend  = '';
                $visible = '';
                $this->get_depend($option, $depend, $visible);

                $class = 'iset-field iset-field-' . $type;
                $class .= $visible;
                $class .= (isset($option['class']) ? ' ' . $option['class'] : '');
                $class .= empty($label)? ' iset-no-title' : '';

                $label_for = empty($id) ? '' : "{$_section}[{$id}]";
                $id        = empty($id) ? mt_rand(10000, 99999) : $id;
                
                $args = array(
                    'id'                => $id,
                    'class'             => $class,
                    'label_for'         => $label_for,
                    'desc'              => isset($option['desc']) ? $option['desc'] : '',
                    'content'           => isset($option['content']) ? $option['content'] : '',
                    'name'              => $label,
                    'section'           => $_section,
                    'options'           => isset($option['options']) ? $option['options'] : array(),
                    'std'               => isset($option['default']) ? $option['default'] : '',
                    'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
                    'type'              => $type,
                    'placeholder'       => isset($option['placeholder']) ? $option['placeholder'] : '',
                    'inline'            => isset($option['inline']) ? $option['inline'] : false,
                    'query_args'        => isset($option['query_args']) ? $option['query_args'] : array(),
                    'settings'          => isset($option['settings']) ? $option['settings'] : array(),
                    'dependency'        => $depend,

                    'style'             => isset($option['style']) ? $option['style'] : 'normal', //normal success info warning danger
                    'function'          => isset($option['function']) ? $option['function'] : '',
                    'args'              => isset($option['args']) ? $option['args'] : null,
                );
                
                if (isset($option['id'])) {
                    if ($this->serialize) {
                        $_option[$id] = $args['std'];
                    } else {
                        $_option[$section][$id] = $args['std'];
                    }
                }

                add_settings_field( "{$_section}[{$id}]", $label, $callback, $section, $section, $args );
            }
        }

        // 初始化设置，存储默认值
        if ($this->serialize) {
            if (!get_option($this->prefix)) {
                add_option($this->prefix, $_option);
            }
        } else {
            foreach ($this->settings_sections as $section) {
                if (!get_option($section['id']) && isset($_option[$section['id']])) {
                    add_option($section['id'], $_option[$section['id']]);
                }
            }
        }

        // 在选项表中创建设置，以便可以存储它们
        if ($this->serialize) {
            register_setting($this->prefix, $this->prefix, array($this, 'sanitize_options'));
        } else {
            foreach ($this->settings_sections as $section) {
                register_setting($section['id'], $section['id'], array($this, 'sanitize_options'));
            }
        }
    }

    /**
     * 根据选项的依赖性，生成数据属性和可见性类名。
     *
     * 此函数用于处理特定选项的依赖关系，根据依赖条件生成相应的数据属性（data-attributes）
     * 和可见性类名。以实现基于条件的动态显示和隐藏输入字段或其他UI元素。
     *
     * @param array $option 选项数组，包含依赖性信息。
     * @param string &$depend 用于累积生成的数据属性字符串的引用。
     * @param string &$visible 用于累积生成的可见性类名字符串的引用。
     */
    function get_depend($option, &$depend, &$visible){
        if (isset($option['dependency']) && !empty($option['dependency'])) {
            $dependency      = $option['dependency'];
            $depend_visible  = '';
            $data_controller = '';
            $data_condition  = '';
            $data_value      = '';
            $data_global     = '';

            if (is_array($dependency[0])) {
                $data_controller = implode('|', array_column($dependency, 0));
                $data_condition  = implode('|', array_column($dependency, 1));
                $data_value      = implode('|', array_column($dependency, 2));
                $data_global     = implode('|', array_column($dependency, 3));
                $depend_visible  = implode('|', array_column($dependency, 4));
            } else {
                $data_controller = (!empty($dependency[0])) ? $dependency[0] : '';
                $data_condition  = (!empty($dependency[1])) ? $dependency[1] : '';
                $data_value      = (!empty($dependency[2])) ? $dependency[2] : '';
                $data_global     = (!empty($dependency[3])) ? $dependency[3] : '';
                $depend_visible  = (!empty($dependency[4])) ? $dependency[4] : '';
            }

            $depend .= ' data-controller="' . esc_attr($data_controller) . '"';
            $depend .= ' data-condition="' . esc_attr($data_condition) . '"';
            $depend .= ' data-value="' . esc_attr($data_value) . '"';
            $depend .= (!empty($data_global)) ? ' data-depend-global="' . esc_attr($data_global) . '"' : '';
            $visible = (!empty($depend_visible)) ? ' iset-depend-visible' : ' iset-depend-hidden';
        }
    }

    /**
     * 根据参数显示特定字段。
     * 
     * 此函数负责根据传入的参数显示一个特定类型的字段。它首先尝试根据参数的ID、部分和默认值获取选项的值。
     * 然后，它尝试根据字段类型创建并初始化一个特定的字段类实例，并调用其渲染方法来显示字段。
     * 如果相应的字段类不存在，则显示一个字段未找到的消息。
     * 
     * @param array $args 包含字段相关参数的数组。必须包含'id'、'section'、'std'和'type'键。
     */
    public function show_field($args) {
        // 根据参数ID、部分和默认值获取字段的当前值
        $value     = $this->get_option($args['id'], $args['section'], $args['std']);
        $classname = 'ISET_Field_' . $args['type']; // 构建字段类的名称
        if (class_exists($classname)) {
            // 创建字段类实例，并传入参数和字段值
            $instance = new $classname($args, $value);
            $instance->render();
        } else {
            echo '<p>' . esc_html__('Field not found!', 'iset_plugin') . '</p>';
        }
    }
 

    /**
     * 清理和验证选项值
     * 
     * 此函数用于处理和清理给定选项数组的每个值。它通过调用适当的清理回调函数
     * 来确保每个选项的值符合预期的格式和安全性。
     * 
     * @param array $options 包含待清理选项的数组。
     * 
     * @return array 清理后的选项数组。
     */
    function sanitize_options( $options ) {

        if ( !$options ) {
            return $options;
        }

        foreach( $options as $option_slug => $option_value ) {
            $sanitize_callback = $this->get_sanitize_callback( $option_slug );

            // 如果回调函数存在，则调用它来清理当前选项的值
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }
        }

        return $options;
    }

    /**
     * 根据选项的slug获取对应的消毒回调函数
     * 
     * 此函数旨在通过选项的slug，从预注册的设置字段中查找并返回相应的消毒回调函数。
     * 如果找到了匹配的slug且其关联的消毒回调函数是可调用的，则返回该回调函数；
     * 否则，如果找不到匹配的slug或回调函数不可调用，则返回false。
     * 
     * @param string $slug 选项的slug，用于唯一标识一个选项。
     * @return mixed 返回找到的消毒回调函数，如果未找到或回调不可调用则返回false。
     */
    function get_sanitize_callback( $slug = '' ) {
        if ( empty( $slug ) ) {
            return false;
        }

        // 遍历已注册的字段，看看是否能找到合适的回调
        foreach( $this->settings_fields as $section => $options ) {
            foreach ( $options as $option ) {
                if ( !isset($option['id']) || $option['id'] != $slug ) {
                    continue;
                }

                // 返回回调名称
                return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
            }
        }

        return false;
    }

    /**
     * 根据选项名和板块名获取配置选项的值。
     *
     * 此函数用于从配置数组或数据库中检索指定选项的值。
     * 如果配置数据已加载，则直接从内存中获取值。
     * 否则，将尝试从数据库中加载配置数据，并缓存以供后续使用。
     * 如果指定的选项不存在，则返回默认值。
     * 
     * @param string $option  选项名称。
     * @param string $section 设置字段所在的选项卡或者板块名称
     * @param string $default 默认值
     * @return mixed 返回选项的值，如果选项不存在则返回默认值。
     */
    function get_option( $option, $section, $default = '' ) {
        if(isset($this->config[$section])){
            $options = $this->config[$section];
        } else {
            $options = get_option($section);
            $this->config[$section] = $options;
        }

        if (isset($options[$option])) {
            return $options[$option];
        }

        return $default;
    }

    /**
     * 输出导航栏
     * 
     * 该函数用于在设置页面上显示一个导航栏，根据可用的设置部分来生成选项卡。
     * 如果存在多个设置部分，则导航栏将显示每个部分的选项卡，允许用户在不同设置部分之间切换。
     * 如果只有一个设置部分，则不显示导航栏。
     * 
     * @since 1.0
     */
    function show_navigation() {
        $html = '<div class="nav-tab-wrapper iset-nav-options">';
        $html .= '<nav class="container">';

        $count = count( $this->settings_sections );
        // 如果只有一个选项卡，则不显示导航
        if ( $count === 1 ) {
            return;
        }

        foreach ( $this->settings_sections as $tab ) {
            $html .= sprintf( '<a href="#tab=%1$s" class="nav-tab iset-section-nav" data-tab-id="%1$s_tab">%2$s</a>', $tab['id'], $tab['title'] );
        }

        $html .= '</nav>';
        $html .= '</div>';

        echo $html;
    }

    /**
     * 输出设置字段表单
     * 
     * 此函数负责在界面中展示设置字段的表单。它首先创建一个包含设置字段的容器，
     * 然后根据$serialize属性的值决定是序列化还是反序列化表单数据。
     * 
     * @see serialize_forms() 如果$serialize属性为真，则调用此方法来序列化表单数据。
     * @see unserialize_forms() 如果$serialize属性为假，则调用此方法来反序列化表单数据。
     */
    function show_forms() {
        echo '<div class="metabox-holder iset-section-group">';

        // 根据$serialize属性的值选择合适的方法处理表单数据
        $this->serialize ? $this->serialize_forms() : $this->unserialize_forms();

        echo '</div>';
    }

    /**
     * 序列化表单函数。
     * 
     * 此函数负责根据设置部分的数组生成一个包含所以选项的表单HTML结构。
     */
    function serialize_forms() {
        echo '<form method="post" action="options.php">';

        // 遍历设置部分数组，为每个部分生成一个表单分区
        foreach ($this->settings_sections as $form) {
            echo '<div id="' . $form['id'] . '_tab" class="iset-field-group iset-section" style="display: none;">';

            // 执行在表单顶部定义的自定义动作，允许插件或主题进行扩展
            do_action('iset_form_top_' . $form['id'], $form);
            // 显示该表单分区的所有设置字段
            do_settings_sections($form['id']);
            // 执行在表单底部定义的自定义动作，进一步允许扩展
            do_action('iset_form_bottom_' . $form['id'], $form);

            echo '</div>';
        }
        $this->load_placeholder_field();

        // 生成提交按钮和表单隐藏字段，用于提交表单数据
        echo '<div>';
        settings_fields($this->prefix);
        submit_button();
        echo '</div>';

        echo '</form>';
    }

    /**
     * 反序列化表单函数。
     * 
     * 该函数用于在后台界面动态生成多个设置表单，每个表单对应一个设置节。
     */
    function unserialize_forms() {
        foreach ($this->settings_sections as $form) {
            echo '<div id="' . $form['id'] . '_tab" class="iset-field-group iset-section" style="display: none;">';
            
            echo '<form method="post" action="options.php">';

            // 执行表单顶部的自定义动作，允许插件或主题进行扩展
            do_action('iset_form_top_' . $form['id'], $form);
            // 显示该设置节的所有选项段
            do_settings_sections($form['id']);
            // 执行表单底部的自定义动作，允许进一步的扩展
            do_action('iset_form_bottom_' . $form['id'], $form);

            // 如果当前设置节有定义的字段，则生成字段和提交按钮
            if (isset($this->settings_fields[$form['id']]) && count($this->settings_fields[$form['id']]) > 0) {
                echo '<div>';
                settings_fields($form['id']);
                submit_button();
                echo '</div>';
            }

            echo '</form>';
            echo '</div>';
        }
        $this->load_placeholder_field();
    }

    /**
     * 加载占位符字段界面元素。
     * 
     * @since 1.0.0
     */
    function load_placeholder_field() {
        echo '<div class="iset-placeholder-field">';
        echo '<span class="placeholder-h2"></span>';
        echo '<table class="form-table">';
        echo '<tbody>';
        for ($i = 0; $i < 5; $i++) {
            echo '<tr class="iset-field iset-field-' . $i . '">';
            echo '<th scope="row">';
            echo '<span class="placeholder-title"></span>';
            echo '</th>';
            echo '<td>';
            if ($i == 3) {
                echo '<span class="placeholder-field" style="width:80px"></span>';
                echo '<span class="placeholder-field" style="width:100px"></span>';
                echo '<span class="placeholder-field" style="width:90px"></span>';
                echo '<span class="placeholder-desc" style="width:100%"></span>';
                echo '<span class="placeholder-desc"></span>';
            } else {
                echo '<span class="placeholder-field"></span>';
                echo '<span class="placeholder-desc"></span>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    /**
     * 显示侧边栏。
     * 
     * 此函数检查当前配置中是否启用了侧边栏，并且是否有已定义的侧边栏区域。如果条件满足，
     * 它将渲染一个包含所有侧边栏区域的容器。侧边栏区域通过循环遍历并调用show_sidebar_card函数来显示。
     * 
     * @since 1.0.0
     */
    function show_sidebar(){
        // 检查是否启用了侧边栏并且是否有设置的侧边栏存在
        if ($this->options['sidebar'] && count($this->settings_sidebars) > 0) {
            // 开始输出侧边栏容器
            echo '<div class="right-column">';
            // 遍历所有设置的侧边栏，并为每个侧边栏调用show_sidebar_card函数
            foreach($this->settings_sidebars as $sidebar){
                $this->show_sidebar_card($sidebar);
            }
            // 结束侧边栏容器的输出
            echo '</div>';
        }
    }

    /**
     * 显示侧边栏卡片
     * 
     * 该函数用于在网页侧边栏中渲染一个卡片式布局，包含标题、内容和底部按钮区域。
     * 卡片的样式通过类名 ".card" 进行定义，各个部分使用相应的子元素和类名进行样式化。
     * 
     * @param array $sidebar 卡片的内容数组，包含以下键值：
     *   - name: 卡片的标题
     *   - content: 卡片的内容
     *   - buts: 卡片底部的按钮数组，每个按钮作为一个字符串元素包含在数组中
     */
    function show_sidebar_card($sidebar) {
        echo '<div class="card">';
        echo '<h3>' . $sidebar['name'] . '</h3>';

        // 显示卡片内容
        echo '<div class="card-body">' . $sidebar['content'] . '</div>';

        echo '<div class="card-footer">';
        // 遍历并显示卡片底部的所有按钮
        foreach ($sidebar['buts'] as $but) {
            echo $but;
        }
        echo '</div>';

        echo '</div>';
    }

    /**
     * 显示设置页面的头部区域。
     * 
     * 此函数负责输出页面头部的HTML结构，包括设置页面的标题和描述。
     * 它使用类的$options属性来获取标题和描述的具体内容，然后动态生成相应的HTML代码。
     */
    function show_header() {
        echo '<div class="iset-header">';
        echo '<div class="container">';
        echo '<h1>' . $this->options['title'] . '</h1>';
        echo '<h3>' . $this->options['desc'] . '</h3>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * 显示页面底部的HTML内容。
     * 
     * 如果配置选项中存在'footer_html'字段且不为空，则将该字段的值作为HTML内容输出。
     * 这样设计的目的是允许用户或开发者通过配置选项来自定义页面底部的展示内容，例如添加版权信息或额外的脚本。
     */
    function show_footer(){
        if(!empty($this->options['footer_html'])){
            echo '<div class="iset-footer">';
            echo $this->options['footer_html'];
            echo '</div>';
        }
    }

    /**
     * 显示设置页面
     */
    function show_setting_page() {
        if (empty($this->options['menu_parent'])) {
            settings_errors('general');
        }
        
        echo '<div class="wrap iset-dashboard ' . $this->options['class'] . '">';
        $this->show_header();
        $this->show_navigation();
        echo '<div class="iset-body">';
        echo '<div class="container-flex">';
        echo '<div class="left-column">';

        $this->show_forms();

        $this->show_footer();

        echo '</div>';
        $this->show_sidebar();
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    /**
     * 目录名消毒
     * @param mixed $dirname
     * @return array|string|null
     */
    function sanitize_dirname($dirname) {
        return preg_replace('/[^A-Za-z]/', '', $dirname);
    }

}

endif;