<?php
/**
 * Plugin Name: Navigation menu as dropdown Widget
 * Description: WordPress plugin which provides a widget with a clickable dropdown of a WordPress navigation menu. It currently supports one level of parent-child structure
 * Version: 1.3.0
 * Author: Jeroen Peters
 * Author URI: https://jeroenpeters.dev
 * Text Domain: navigation-menu-as-dropdown-widget
 * Domain Path: /translation
 * License: GPL2
 */

/* Make sure we don't expose any info if called directly */
if (!function_exists('add_action')) {
    echo 'Nothing to see here. Move along now people.';
    exit;
}

function JP_Dropdown_Menu_widget_register()
{
    load_plugin_textdomain('navigation-menu-as-dropdown-widget', false, dirname(plugin_basename(__FILE__)) . '/translation/');

    return register_widget('JP_Dropdown_Menu_widget');
}

add_action('widgets_init', 'JP_Dropdown_Menu_widget_register');


class JP_Dropdown_Menu_widget extends WP_Widget
{
    private $widget_title = "Pages";
    private $menu_id = 1;
    private $first_option_title = "";
    private	$select_page = true;
    private	$open_in_new_tab = false;
    private	$truncate_titles = false;
    private	$truncate_at = 30;

    public function __construct()
    {
        parent::__construct(
            'JP_Dropdown_Menu_widget',
            __('Navigation menu as dropdown', 'navigation-menu-as-dropdown-widget'),
            array(
                'classname' => 'JP_Dropdown_Menu_widget',
                'description' => __('Displays a navigation menu as a dropdown, which can be used to navigate', 'navigation-menu-as-dropdown-widget')
            )
        );
    }

    /**
     * Front end Display of widgets
     * @param array $args Widget arguments
     * @param array $instance Saved values from Database
     * @see WP_Widget::widget()
     *
     */
    public function widget($args, $instance)
    {
        extract($args);

        echo $args['before_widget'];

        $this->widget_title = apply_filters('widget_title', $instance['title']);
        if( ! empty( $this->widget_title ) )
        {
            echo $args['before_title'] . $this->widget_title . $args['after_title'];
        }

        echo '<label class="screen-reader-text" for="select_' . $this->id . '">' . $this->widget_title . '</label>';
        echo '<select name="pd_' . $this->id . '" id="select_' . $this->id . '">';

        $this->menu_id = $instance['menu_id'];
        $this->first_option_title = $instance['first_option_title'];
        $this->select_page = ($instance['select_page'] == "1" ? true : false);
        $this->open_in_new_tab = ($instance['open_in_new_tab'] == "1" ? true : false);
        $this->truncate_titles = ($instance['truncate_titles'] == "1" ? true : false);
        $this->truncate_at = (int)$instance['truncate_at'];

        if (! empty( $this->menu_id ) ) {

            echo '<option class="pd_first" value="#">';
            if (! empty( $this->first_option_title ) ) {
                echo $this->first_option_title;
            } else {
                echo __('Select page', 'navigation-menu-as-dropdown-widget');
            }
            echo '</option>';

            $menu_items = wp_get_nav_menu_items($this->menu_id);
            $count = 0;
            $submenu = false;
            $current_post_id = get_the_id();
            $cai = 0;

            foreach ( $menu_items as $menu_link ) {
                if ( $current_post_id == $menu_link->object_id ) {
                    if (! $menu_link->menu_item_parent ) {
                        $current_post_id = $menu_link->ID;
                    } else {
                        $current_post_id = $menu_link->menu_item_parent;
                    }

                    $cai = $menu_link->ID;
                    break;
                }
            }

            foreach ( $menu_items as $menu_item ) {
                $link = $menu_item->url;
                $title = $menu_item->title;
                if($this->truncate_titles) {
                    $title = (strlen($menu_item->title) > $this->truncate_at) ? substr($menu_item->title, 0, $this->truncate_at) . '...' : $menu_item->title;
                }
                $prefix = '';
                $class = 'pd_tld';

                $menu_item->ID == $cai ? $selected2 = 'selected' : $selected2 = '';

                if (! $menu_item->menu_item_parent ) {
                    $parent_id = $menu_item->ID;
                }

                if ( $parent_id == $menu_item->menu_item_parent ) {
                    if (! $submenu ) {
                        $submenu = true;
                    }
                    $prefix = '&nbsp;&nbsp;&nbsp;&nbsp;';
                    $class = 'pd_sld';
                    if (empty($menu_items[$count + 1]) || $menu_items[$count + 1]->menu_item_parent != $parent_id && $submenu) {
                        $submenu = false;
                    }
                }

                if (empty($menu_items[$count + 1]) || $menu_items[$count + 1]->menu_item_parent != $parent_id) {
                    $submenu = false;
                }

                $count++;

                if( empty($this->select_page) ) {
                    $selected2 = '';
                }

                echo '<option value="' . $link . '" class="' . $class . '" ' . $selected2 . '>' . $prefix . $title . '</option>';
            }
        } else {
            echo '<option>' . __('No pages found', 'navigation-menu-as-dropdown-widget') . '</option>';
        }

        echo '</select>';

        $opener_command = "window.location = destination;";
        if( ! empty($this->open_in_new_tab) ) {
            $opener_command = "window.open(destination, '_blank'); document.getElementById('select_{$this->id}').value = '#';";
        }

        echo <<<WIDGETJS
                <script type="text/javascript">
				/* <![CDATA[ */
				(function() {
					document.getElementById('select_{$this->id}').onchange = function(e) {
                        let destination = document.getElementById('select_{$this->id}').value;
                        if ( destination != "#" ) {
							{$opener_command}
						}
					};
				})();
				/* ]]> */
				</script>
WIDGETJS;
        echo $args['after_widget'];
    }

    /* Sanitize data from values as they are saved */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['menu_id'] = (int)$new_instance['menu_id'];
        $instance['first_option_title'] = strip_tags($new_instance['first_option_title']);
        $instance['select_page'] = (bool)$new_instance['select_page'];
        $instance['open_in_new_tab'] = (bool)$new_instance['open_in_new_tab'];
        $instance['truncate_titles'] = (bool)$new_instance['truncate_titles'];
        $instance['truncate_at'] = (int)$new_instance['truncate_at'];
        return $instance;
    }

    /* Backend Widget config form */
    public function form($instance)
    {
        $defaults = array(
            'title' => $this->widget_title,
            'menu_id' => $this->menu_id,
            'select_page' => $this->select_page,
            'open_in_new_tab' => $this->open_in_new_tab,
            'first_option_title' => $this->first_option_title,
            'truncate_titles' => $this->truncate_titles,
            'truncate_at' => $this->truncate_at
        );
        $instance = wp_parse_args((array)$instance, $defaults); ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title', 'navigation-menu-as-dropdown-widget'); ?>:</label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('menu_id'); ?>"><?php echo __('Menu to use', 'navigation-menu-as-dropdown-widget'); ?>:</label>
            <select id="<?php echo $this->get_field_id('menu_id'); ?>" name="<?php echo $this->get_field_name('menu_id'); ?>" style="max-width: 100%;">
                <?php
                foreach(wp_get_nav_menus() as $menu)
                {
                    echo '<option value="' . $menu->term_id . '"' . ($instance['menu_id'] == $menu->term_id ? "selected" : "") . '>' . $menu->name . '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('first_option_title'); ?>"><?php echo __('Initial value', 'navigation-menu-as-dropdown-widget'); ?>:</label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('first_option_title'); ?>"
                   name="<?php echo $this->get_field_name('first_option_title'); ?>" value="<?php echo $instance['first_option_title']; ?>"/>
            <br><small><?php echo __('If you leave this blank, it will show "Select page"', 'navigation-menu-as-dropdown-widget'); ?></small>
        </p>

        <p>
            <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('select_page'); ?>" name="<?php echo $this->get_field_name('select_page'); ?>" value="1" <?php echo ($instance['select_page'] == "true" ? "checked='checked'" : ""); ?> />
            <label for="<?php echo $this->get_field_id('select_page'); ?>"><?php echo __('Preselect the visited page in dropdown', 'navigation-menu-as-dropdown-widget'); ?></label>
        </p>

        <p>
            <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('open_in_new_tab'); ?>" name="<?php echo $this->get_field_name('open_in_new_tab'); ?>" value="1" <?php echo ($instance['open_in_new_tab'] == "true" ? "checked='checked'" : ""); ?> />
            <label for="<?php echo $this->get_field_id('open_in_new_tab'); ?>"><?php echo __('Open pages a new tab/window', 'navigation-menu-as-dropdown-widget'); ?></label>
        </p>

        <p>
            <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('truncate_titles'); ?>" name="<?php echo $this->get_field_name('truncate_titles'); ?>" value="1" <?php echo ($instance['truncate_titles'] == "true" ? "checked='checked'" : ""); ?> />
            <label for="<?php echo $this->get_field_id('truncate_titles'); ?>">
                <?php echo __('Truncate titles in the dropdown ', 'navigation-menu-as-dropdown-widget'); ?>,
                <?php echo sprintf(__('at %s characters ', 'navigation-menu-as-dropdown-widget'),
                                    '<input type="text" class="widefat" style="max-width: 37px;" id="' . $this->get_field_id('truncate_at') . '"
                                            name="' . $this->get_field_name('truncate_at') . '" value="' . $instance['truncate_at'] . '"/>'); ?>
            </label>
        </p>

        <?php
    }
}
