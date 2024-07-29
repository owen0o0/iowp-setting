<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-07-25 13:51:46
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-25 20:18:01
 * @FilePath: /iowp-setting/classes/fields.class.php
 * @Description: 
 */
if (!defined('ABSPATH')) { die; }

abstract class ISET_Fields
{
    protected $field = array();

    protected $value = '';

    public function __construct($field = array(), $value = '') {
        $this->field  = $field;
        $this->value  = $value;
    }

    /**
     * 获取字段的描述和依赖信息
     *
     * 该函数用于根据传入的参数，构造并返回字段的描述信息和依赖条件的HTML代码。
     * 如果参数中包含了描述信息，则会格式化并添加到输出中。同时，它还会调用
     * get_field_dependency 函数来获取并添加任何相关的依赖条件信息。
     *
     * @param array $args  $args['desc'] 字段的描述信息
     *
     * @return string 包含字段描述和依赖条件的HTML代码
     */
    public function get_field_description( $args ) {
        if ( ! empty( $args['desc'] ) ) {
            $desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
        } else {
            $desc = '';
        }

        $desc .= $this->get_field_dependency($args);

        return $desc;
    }

    /**
     * 获取字段依赖关系的HTML代码
     * 
     * 此函数用于根据传入的参数生成一个表示字段依赖关系的HTML元素。依赖关系通常是通过一些特定的属性来定义的，
     * 这些属性指定了该字段的显示或行为与其他字段的依赖关系。如果定义了依赖关系，则会生成一个包含这些依赖关系属性的HTML div元素；
     * 否则，生成一个空字符串。
     * 
     * @param array $args 包含依赖关系信息的数组。预期包含一个名为'dependency'的键，其值是一个描述依赖关系的字符串。
     * @return string 一个表示依赖关系的HTML div元素，如果不存在依赖关系，则返回一个空字符串。
     */
    public function get_field_dependency($args){
        if( ! empty( $args['dependency'] )){
            $dependency = '<div class="iset-dependency" ' . $args['dependency'] . '>';
        } else {
            $dependency = '';
        }
        return $dependency;
    }
    
    /**
     * 根据不同的类型查询并返回数据选项
     * 
     * 此函数根据提供的类型和条件查询WordPress的数据，并以选项数组的形式返回。
     * 支持的类型包括页面、文章、分类、标签、菜单、用户、侧边栏、角色、文章类型和位置。
     * 对于每种类型，可以根据一个搜索术语进行过滤，以进一步限制查询结果。
     * 
     * @param string $type 查询的数据类型，可以是多种预定义的类型之一，如页面、文章等。
     * @param string $term 查询的搜索术语，用于过滤结果，ajax select
     * @param array $query_args 查询的额外参数，可以修改查询的行为。
     * @return array 返回一个包含查询结果的选项数组。
     */
    public static function field_data($type = '', $term = false, $query_args = array()) {

        $options      = array();
        $array_search = false;

        // 根据$type的值，确定查询的类型并赋值给$option变量。
        // 这是基于类型的一个条件判断，用于后续的查询。
        if (in_array($type, array('page', 'pages'))) {
            $option = 'page';
        } else if (in_array($type, array('post', 'posts'))) {
            $option = 'post';
        } else if (in_array($type, array('category', 'categories'))) {
            $option = 'category';
        } else if (in_array($type, array('tag', 'tags'))) {
            $option = 'post_tag';
        } else if (in_array($type, array('menu', 'menus'))) {
            $option = 'nav_menu';
        } else {
            $option = '';
        }

        // 根据$type的值执行不同的查询逻辑。
        switch ($type) {

            case 'page':
            case 'pages':
            case 'post':
            case 'posts':

                // 根据是否有搜索术语来构建不同的查询参数。
                if (!empty($term)) {

                    $query = new WP_Query(wp_parse_args($query_args, array(
                        's'              => $term,
                        'post_type'      => $option,
                        'post_status'    => 'publish',
                        'posts_per_page' => 25,
                    )));

                } else {

                    $query = new WP_Query(wp_parse_args($query_args, array(
                        'post_type'   => $option,
                        'post_status' => 'publish',
                    )));

                }

                if (!is_wp_error($query) && !empty($query->posts)) {
                    foreach ($query->posts as $item) {
                        $options[$item->ID] = $item->post_title;
                    }
                }

                break;

            case 'category':
            case 'categories':
            case 'tag':
            case 'tags':
            case 'menu':
            case 'menus':

                if (!empty($term)) {

                    $query = new WP_Term_Query(wp_parse_args($query_args, array(
                        'search'     => $term,
                        'taxonomy'   => $option,
                        'hide_empty' => false,
                        'number'     => 25,
                    )));

                } else {

                    $query = new WP_Term_Query(wp_parse_args($query_args, array(
                        'taxonomy'   => $option,
                        'hide_empty' => false,
                    )));

                }

                if (!is_wp_error($query) && !empty($query->terms)) {
                    foreach ($query->terms as $item) {
                        $options[$item->term_id] = $item->name;
                    }
                }

                break;

            case 'user':
            case 'users':

                if (!empty($term)) {

                    $query = new WP_User_Query(
                        array(
                            'search'  => '*' . $term . '*',
                            'number'  => 25,
                            'orderby' => 'title',
                            'order'   => 'ASC',
                            'fields'  => array('display_name', 'ID')
                        ));

                } else {

                    $query = new WP_User_Query(array('fields' => array('display_name', 'ID')));

                }

                if (!is_wp_error($query) && !empty($query->get_results())) {
                    foreach ($query->get_results() as $item) {
                        $options[$item->ID] = $item->display_name;
                    }
                }

                break;

            case 'sidebar':
            case 'sidebars':

                global $wp_registered_sidebars;

                if (!empty($wp_registered_sidebars)) {
                    foreach ($wp_registered_sidebars as $sidebar) {
                        $options[$sidebar['id']] = $sidebar['name'];
                    }
                }

                $array_search = true;

                break;

            case 'role':
            case 'roles':

                global $wp_roles;

                if (!empty($wp_roles)) {
                    if (!empty($wp_roles->roles)) {
                        foreach ($wp_roles->roles as $role_key => $role_value) {
                            $options[$role_key] = $role_value['name'];
                        }
                    }
                }

                $array_search = true;

                break;

            case 'post_type':
            case 'post_types':

                $post_types = get_post_types(array('show_in_nav_menus' => true), 'objects');

                if (!is_wp_error($post_types) && !empty($post_types)) {
                    foreach ($post_types as $post_type) {
                        $options[$post_type->name] = $post_type->labels->name;
                    }
                }

                $array_search = true;

                break;

            case 'location':
            case 'locations':

                $nav_menus = get_registered_nav_menus();

                if (!is_wp_error($nav_menus) && !empty($nav_menus)) {
                    foreach ($nav_menus as $nav_menu_key => $nav_menu_name) {
                        $options[$nav_menu_key] = $nav_menu_name;
                    }
                }

                $array_search = true;

                break;

            default:

                if (is_callable($type)) {
                    if (!empty($term)) {
                        $options = call_user_func($type, $query_args);
                    } else {
                        $options = call_user_func($type, $term, $query_args);
                    }
                }

                break;

        }

        // 如果有搜索术语，且选项不为空，对选项进行过滤。
        if (!empty($term) && !empty($options) && !empty($array_search)) {
            $options = preg_grep('/' . $term . '/i', $options);
        }

        // 如果有搜索术语，且选项不为空，将选项数组转换为包含'value'和'text'键的数组，以适应AJAX搜索。
        if (!empty($term) && !empty($options)) {
            $arr = array();
            foreach ($options as $option_key => $option_value) {
                $arr[] = array('value' => $option_key, 'text' => $option_value);
            }
            $options = $arr;
        }

        return $options;

    }

    /**
     * 根据给定的类型和值，为各种WordPress查询类型生成一个带有标题的选项数组。
     *
     * 此函数为不同的WordPress查询类型（如文章、分类、用户等）生成一个选项数组，
     * 每个选项的键是查询值，值是相应的标题。它支持多种类型，并为每种类型动态生成标题。
     * 如果给定的类型不是预定义的类型之一，它将尝试调用一个自定义函数来获取标题。
     *
     * @param string $type 查询类型，例如：'post', 'category', 'user'等。
     * @param array $values 查询值的数组，每个值对应一个查询对象的ID或其他标识符。
     * @return array 一个键为查询值，值为标题的选项数组。
     */
    public function field_wp_query_data_title($type, $values)
    {
        // 初始化选项数组
        $options = array();

        // 检查传入的值是否非空且为数组
        if (!empty($values) && is_array($values)) {
            // 遍历值数组，为每个值生成对应的标题
            foreach ($values as $value) {
                // 默认情况下，使用值的首字母大写作为标题
                $options[$value] = ucfirst($value);
                // 根据类型，动态生成标题
                switch ($type) {
                    // 文章和页面类型
                    case 'post':
                    case 'posts':
                    case 'page':
                    case 'pages':
                        $title = get_the_title($value);
                        // 如果获取标题成功且不为空，则使用标题作为选项的值
                        if (!is_wp_error($title) && !empty($title)) {
                            $options[$value] = $title;
                        }
                        break;
                    // 分类和标签类型
                    case 'category':
                    case 'categories':
                    case 'tag':
                    case 'tags':
                        $term = get_term($value);
                        // 如果获取分类或标签成功且不为空，则使用其名称作为选项的值
                        if (!is_wp_error($term) && !empty($term)) {
                            $options[$value] = $term->name;
                        }
                        break;
                    // 用户类型
                    case 'user':
                    case 'users':
                        $user = get_user_by('id', $value);
                        // 如果获取用户成功且不为空，则使用其显示名称作为选项的值
                        if (!is_wp_error($user) && !empty($user)) {
                            $options[$value] = $user->display_name;
                        }
                        break;
                    // 侧边栏类型
                    case 'sidebar':
                    case 'sidebars':
                        global $wp_registered_sidebars;
                        // 如果获取侧边栏成功且不为空，则使用其名称作为选项的值
                        if (!empty($wp_registered_sidebars[$value])) {
                            $options[$value] = $wp_registered_sidebars[$value]['name'];
                        }
                        break;
                    // 角色类型
                    case 'role':
                    case 'roles':
                        global $wp_roles;
                        // 如果获取角色成功且不为空，则使用其名称作为选项的值
                        if (!empty($wp_roles) && !empty($wp_roles->roles) && !empty($wp_roles->roles[$value])) {
                            $options[$value] = $wp_roles->roles[$value]['name'];
                        }
                        break;
                    // 文章类型
                    case 'post_type':
                    case 'post_types':
                        $post_types = get_post_types(array('show_in_nav_menus' => true));
                        // 如果获取文章类型成功且不为空，则使用其名称作为选项的值
                        if (!is_wp_error($post_types) && !empty($post_types) && !empty($post_types[$value])) {
                            $options[$value] = ucfirst($value);
                        }
                        break;
                    // 导航菜单位置类型
                    case 'location':
                    case 'locations':
                        $nav_menus = get_registered_nav_menus();
                        // 如果获取导航菜单位置成功且不为空，则使用其名称作为选项的值
                        if (!is_wp_error($nav_menus) && !empty($nav_menus) && !empty($nav_menus[$value])) {
                            $options[$value] = $nav_menus[$value];
                        }
                        break;
                    // 默认情况，尝试调用自定义函数来获取标题
                    default:
                        if (is_callable($type . '_title')) {
                            $options[$value] = call_user_func($type . '_title', $value);
                        }
                        break;
                }
            }
        }

        // 返回生成的选项数组
        return $options;
    }


}