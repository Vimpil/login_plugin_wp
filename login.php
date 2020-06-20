<?php
/**
 * Plugin Name: Login plugin
 * Description: Plugin to login to the site through the 3rd party server checker
 * Plugin URI:  none
 * Author URI:  https://www.linkedin.com/in/lifetrue
 * Author:      Kirill Shevchenko
 * Version:     0.0.1
 *
 * Text Domain: none
 * Domain Path: none
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Network:     "false"
 */


defined('ABSPATH') or die('no wordpress path found');


function myplugin_activate() {
    // register taxonomies/post types here
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'myplugin_activate' );

function myplugin_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'myplugin_deactivate' );


add_action('admin_menu', 'test_plugin_setup_menu');
 
function test_plugin_setup_menu(){
        add_menu_page( 'Test Login', 'Test Login', 'manage_options', 'login-wp-plugin', 'test_init' );
}
 
function test_init(){
        echo "<h1>Test Login</h1><div>Use the following shortcode in order to add login form on any wordpress page:</div>
        <div class='login_admin_shrtc_color'>[logn_form]</div>";
}

// add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
// function callback_for_setting_up_scripts() {
//     wp_register_style( 'login_wp_plugin_css', 'Login/public/css/Login.css' );
//     wp_enqueue_style( 'login_wp_plugin_css' );
//     // wp_enqueue_script( 'namespaceformyscript', 'http://locationofscript.com/myscript.js', array( 'jquery' ) );
// }

function load_custom_wp_plugin($atts) {


    $Content = '
    <div class="container">
        <form>
            <div class="col">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </div>
        </form>
    </div>';

    // // $Content = "<style>\r\n";
    // // $Content .= "h3.demoClass {\r\n";
    // // $Content .= "color: #26b158;\r\n";
    // // $Content .= "}\r\n";
    // // $Content .= "</style>\r\n";
    // // $Content .= '<h3 class="demoClass">Check it out!</h3>';
     
    return $Content;
}
add_shortcode('login-wp-plugin', 'load_custom_wp_plugin');

add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
function load_custom_wp_admin_style($hook) {


        $current_screen = get_current_screen();

        if ( strpos($current_screen->base, 'login-wp-plugin') === false) {
            return;
        } else {
            wp_enqueue_style('login_wp_plugin_css', plugins_url('public/css/admin_submenu.css',__FILE__ ));
        }
}
        

