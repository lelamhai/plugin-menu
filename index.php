<?php
/**
 * Plugin Name:     Menu show or hide
 * Plugin URI:      http://jinn/news/
 * Description:     Menu ẩn hiện theo role user
 * Version:         1.0.1
 * Author:          Lê Lam Hải
 * Author URI:      http://jinn/
 */


 /*
** add css in page admin
*/
function load_select2_admin_styles() {
    wp_enqueue_script( 'role_show_hide.js', plugins_url().'/Menu-Hide-Show/script.js', false, '1.0.5' );
    wp_enqueue_style( 'role_show_hide.css', plugins_url().'/Menu-Hide-Show/style.css', false, '1.0.5' ); 
}
add_action( 'admin_enqueue_scripts', 'load_select2_admin_styles' );

function edit_admin_menus() {
    // create database
    global $wpdb;
    global $menu;
    $table_name = $wpdb->prefix.'menu_current';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE  ".$table_name."(
          menu_id mediumint(9) NOT NULL AUTO_INCREMENT,
          name_menu text NOT NULL,
          menu_slug tinytext NOT NULL,
          PRIMARY KEY  (menu_id)
        ) $charset_collate;";
 
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    } 

    $table_list = $wpdb->prefix.'list_menu';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_list'") != $table_list) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE  ".$table_list."(
          list_menu_id mediumint(9) NOT NULL AUTO_INCREMENT,
          slug_role tinytext NOT NULL,
          datetime datetime NOT NULL,
          menu_id mediumint(9),
          PRIMARY KEY  (list_menu_id)
        ) $charset_collate;";
 
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    // insert menu current
    $query = $wpdb->get_results( "SELECT * FROM  $table_name", OBJECT );
    // check database isset not insert
    if(count($query) == 0)
    {
        foreach($menu as $value)
        {
            if($value[0] != "")
            {
                $data=array(
                    'name_menu' => $value[0], 
                    'menu_slug' => $value[2],
                );
                $wpdb->insert( $table_name, $data);
            } 
        }
    }
}
add_action( 'admin_init', 'edit_admin_menus' );



add_action( 'admin_menu', 'wp_menu' ); 
function wp_menu() {
     add_options_page(
         'Role Menu',
         'Role Menu',
         'manage_options',
         'viewMenu',
         'viewMenu'
     );
 }


function viewMenu()
{
    global $wpdb;
    $table_current = $wpdb->prefix.'menu_current';
    $table_list = $wpdb->prefix.'list_menu';

    if(isset($_POST['action']) && $_POST['action'] == "create")
    {
        // insert database
        $data=array(
            'slug_role'     => $_POST['slug_role'], 
            'datetime'      => date('Y-m-d'),
            'menu_id'       => $_POST['menu_id'],
        );
        $wpdb->insert( $table_list, $data);
    } else if(isset($_POST['action']) && $_POST['action'] == "delete") {
        $slug_role  = $_POST['slug_role'];
        $menu_id    = $_POST['menu_id'];
        $wpdb->query("DELETE FROM $table_list WHERE slug_role = '$slug_role' AND menu_id = $menu_id");
    }

    // get list menu current
    $table_name = $wpdb->prefix.'menu_current';
    $menu = $wpdb->get_results( "SELECT * FROM $table_name", OBJECT );

    // get list role
    $roles = get_editable_roles();
   
    ?>
        <br><br>
        <div id="wrap-main">
            <table>
                <tr>
                    <td class="tr-name">Tên menu</td>
                    <?php
                        foreach($roles as $key=>$role)
                        {
                            if($key != 'administrator')
                            {
                                ?>
                                    <td><?php echo  $role['name']?></td>
                                <?php
                            }
                        }
                    ?>
                </tr>
                <?php
                    foreach($menu as $value)
                    {
                        ?>
                            <tr>
                                <td class="td-name"><?php echo $value->name_menu ?></td>
                                    <?php
                                        foreach($roles as $slugRole=>$role)
                                        {
                                            if($slugRole != 'administrator')
                                            {
                                                $count = $wpdb->get_results( "SELECT count(*) as count FROM wp_list_menu l, wp_menu_current c WHERE l.menu_id = c.menu_id AND l.menu_id = $value->menu_id AND l.slug_role = '$slugRole'", OBJECT );
                                                if($count[0]->count > 0)
                                                {
                                                    ?>
                                                        <td class="td-checkbox"><input type="checkbox" class="menu-item" data-idmenu="<?php echo $value->menu_id?>" data-slugrole="<?php echo $slugRole?>" checked></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                        <td class="td-checkbox"><input type="checkbox" class="menu-item" data-idmenu="<?php echo $value->menu_id?>" data-slugrole="<?php echo $slugRole?>"></td>
                                                    <?php
                                                }
                                                
                                            }
                                        }
                                        
                                    ?>
                            </tr>
                        <?php
                    }
                ?>
            </table>
        </div>
    <?php
}


function remove_menus(){
    global $wpdb;
    $table_current = $wpdb->prefix.'menu_current';
    $table_list = $wpdb->prefix.'list_menu';
    $query = $wpdb->get_results( "SELECT c.menu_slug, l.slug_role FROM $table_current c, $table_list l WHERE c.menu_id = l.menu_id", OBJECT );

    foreach($query as $value)
    {
        $current_user = wp_get_current_user();
        $user = new WP_User( $current_user->ID );
        $user_role = $user->roles[0];
        switch($value->slug_role)
        {
            case $user_role == $value->slug_role:
                    remove_menu_page( $value->menu_slug );
                break;
        }
    }
}
add_action( 'admin_menu', 'remove_menus');