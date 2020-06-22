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


/* PLUG ACTIVATION */
function myplugin_activate() {
    

    //  'table creation';
        function create_plugin_database_table()
    {
         
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . 'my_analysis';
            $login_table_db=$table_name;

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                views smallint(5) NOT NULL,
                clicks smallint(5) NOT NULL,
                UNIQUE KEY id (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );


    }
    


    create_plugin_database_table();
    register_activation_hook( __FILE__, 'create_plugin_database_table' );

    // 'END table creation';

    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'myplugin_activate' );

/* END PLUG ACTIVATION */


/* PLUG DEACTIVATION */
function myplugin_deactivate() {
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'myplugin_deactivate' );
/* END PLUG DEACTIVATION */


/* PLUG UNINSTALL */
register_uninstall_hook( __FILE__, 'myplugin_uninstall' );
function myplugin_uninstall() {
     global $wpdb;
    $table_name = $wpdb->prefix . 'my_analysis';
    $wpdb->query( "DROP TABLE IF EXISTS ".$table_name.';' );
    delete_option( 'my_plugin_option' );
}

/* END PLUG UNINSTALL */


/* ADMIN PAGE */

add_action('admin_menu', 'test_plugin_setup_menu');
 
function test_plugin_setup_menu(){
        add_menu_page( 'Test Login', 'Test Login', 'manage_options', 'login-wp-plugin', 'test_init' );
}
 
function test_init(){
        echo "<h1>Test Login</h1><div>Use the following shortcode in order to add login form on any wordpress page:</div>
        <div class='login_admin_shrtc_color'>[logn_form]</div>";
}

/* END ADMIN PAGE */


/* SCRIPTS FOR ADMIN PAGE */

add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
function load_custom_wp_admin_style($hook) {


        $current_screen = get_current_screen();

        if ( strpos($current_screen->base, 'login-wp-plugin') === false) {
            return;
        } else {
            wp_enqueue_style('login_wp_plugin_css', plugins_url('public/css/admin_submenu.css',__FILE__ ));
        }

}
/* END SCRIPTS FOR ADMIN PAGE */
        



// ======= LOGIN FORM =====>
 
function get_tiny_form_login($redirect=false) {
  global $tiny_form_count;
  ++$tiny_form_count;
  
  if (is_user_logged_in()) :
    $return = "<form action=\"\" method=\"post\" class=\"tiny_form tiny_form_login\">\r\n";
    $error = get_tiny_error($tiny_form_count);

    if ($error)
      $return .= "<p class=\"error\">{$error}</p>\r\n";
    $success = get_tiny_success($tiny_form_count);
    
    if ($success)
      $return .= "<p class=\"success\">{$success}</p>\r\n";

    $return .= "  <p>
      <label for=\"tiny_username\">".__('Username','tiny_login')."</label>
      <input type=\"text\" id=\"tiny_username\" name=\"tiny_username\"/>
    </p>\r\n";

    $return .= "  <p>
      <label for=\"tiny_password\">".__('Password','tiny_login')."</label>
      <input type=\"password\" id=\"tiny_password\" name=\"tiny_password\"/>
    </p>\r\n";
   
    if ($redirect)

    $return .= "  <input type=\"hidden\" name=\"redirect\" value=\"{$redirect}\">\r\n";
   
    $return .= "  <input type=\"hidden\" name=\"tiny_action\" value=\"login\">\r\n";
    $return .= "  <input type=\"hidden\" name=\"tiny_form\" value=\"{$tiny_form_count}\">\r\n";
    $return .= "  <button type=\"submit\">".__('Login','tiny_login')."</button>\r\n";
    $return .= "</form>\r\n";
 
  endif;
  return $return;
}




// print form #1
/* usage: <?php the_tiny_form_login(); ?> */

function the_tiny_form_login($redirect=false) {
  echo get_tiny_form_login($redirect);
}

// shortcode for form #1
// usage: [tiny_form_login] in post/page content

add_shortcode('login-wp-plugin','tiny_form_login_shortcode');
function tiny_form_login_shortcode ($atts,$content=false) {
  $atts = shortcode_atts(array(
    'redirect' => false
  ), $atts);
  return get_tiny_form_LOGIN($atts['redirect']);
}



 
// <============== END LOGIN FORM

 
// ============ FORM SUBMISSION HANDLER

add_action('init','tiny_handle');

function tiny_handle() {
  
  $success = false;

  if (isset($_REQUEST['tiny_action'])) {

      if (!$_POST['tiny_username']) {
            
          set_tiny_error(__('<strong>ERROR</strong>: Empty username','tiny_login'),$_REQUEST['tiny_form']);
        } else if (!$_POST['tiny_password']) {
      
          set_tiny_error(__('<strong>ERROR</strong>: Empty password','tiny_login'),$_REQUEST['tiny_form']);
        } else {
          

       // ================= BLOCKING IP CHECKING

      $ip = $_SERVER['REMOTE_ADDR'];       
      $date = new DateTime(date("Y-m-d H:i:s"));
      $fdate = $date->format('Y-m-d H:i:s');
      global $wpdb;
      $result = $wpdb->get_row("SELECT * FROM wp_LoginAttempts WHERE IP LIKE '$ip';");
      $blocked = 1;

      if($result->Attempts!==null){
        
        $number=(($result)->Attempts);

        if($number>2){
          
            $blocked=0;
            
            $now = $date->format('Y-m-d H:i:s');
            $prevdata = new DateTime((($result)->LastLogin));
            // $prevdata2 = $prevdata->format('Y-m-d H:i:s');
            $tempdate=$date->date;

           
            $diff = ($prevdata)->diff($date)->format('%H:%i:%s');
           
            $timesplit=explode(':', $diff);

            $min= $timesplit[0]*60+$timesplit[1];


            /* IF BLOCKED TIME > 30 MINUTES */

            if($min>29){
              $sql= "DELETE FROM `wp_LoginAttempts` WHERE IP = '$ip';";
              $wpdb->query($sql);
              $blocked=1;
              
            }else{
              set_tiny_error(__('<strong>ERROR</strong>: Too much attempts, please wait '.$min.' minutes','tiny_login'),$_REQUEST['tiny_form']);
            }

        }

          /* IF BLOCK EXIST UPDATE ROW IN DATABASE */

          if($blocked==1){
            $number++;
            $sql="UPDATE wp_LoginAttempts SET Attempts = '$number'
  WHERE IP = '$ip';";
            $wpdb->query($sql);            

          }

          print_r(json_encode(($result)->Attempts));

        }else{
          
          /* IF BLOCK DOESN'T EXIST CREATE ROW IN DATABASE */

          $sql = "INSERT INTO wp_LoginAttempts (Ip,Attempts,LastLogin) VALUES ('$ip', 1,'$fdate');";

          $wpdb->query( $sql );
        }


        /* CALL TO REMOTE SERVER */

        $creds = array();
        $creds['user_login'] = $_POST['tiny_username'];
        $creds['user_password'] = $_POST['tiny_password'];


        $url="http://demo1.btobet.net/wp-json/btobet/v1/login/?";
          
          $response = wp_remote_post( $url, array(
                "content-type" => "application/json",
                "charset" => "utf-8",
                "body"        => json_encode(array(
                    // "username" => utf8_encode("testwp"),
                    // "password" => utf8_encode("Ii123123#"),
                    "username" => utf8_encode($creds['user_login']),
                    "password" => utf8_encode($creds['user_password']),
                )),
                )
            );

          if ( is_wp_error( $response ) ) {
              $error_message = $response->get_error_message();
              echo "Something went wrong: $error_message";
          } else {



            // == RESPONSE ==
            echo '<p>Response:</p>';
            echo '<p>code:</p><p>';
            $code = json_decode($response['body'])->code;
            print_r($code);
            echo '</p><p>data:</p>';
            print_r( json_decode($response['body'])->data);
            echo '<script>console.log('.$response['body'].')</script>';
            echo '</pre>';
            // == END RESPONSE ==


            if($code==='Request Failed'){

              }else{
                $loginFail = 0;
              }
            }
        
      }

  }
 
          // if redirect is set and action was successful
          if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] && $success) {
            wp_redirect($_REQUEST['redirect']);
            die();
          }      
        }



// ================= UTILITIES

if (!function_exists('set_tiny_error')) {
  function set_tiny_error($error,$id=0) {
    $_SESSION['tiny_error_'.$id] = $error;
  }
}
// shows error message
if (!function_exists('the_tiny_error')) {
  function the_tiny_error($id=0) {
    echo get_tiny_error($id);
  }
}
 
if (!function_exists('get_tiny_error')) {
  function get_tiny_error($id=0) {
    if ($_SESSION['tiny_error_'.$id]) {
      $return = $_SESSION['tiny_error_'.$id];
      unset($_SESSION['tiny_error_'.$id]);
      return $return;
    } else {
      return false;
    }
  }
}

if (!function_exists('set_tiny_success')) {
  function set_tiny_success($error,$id=0) {
    $_SESSION['tiny_success_'.$id] = $error;
  }
}
if (!function_exists('the_tiny_success')) {
  function the_tiny_success($id=0) {
    echo get_tiny_success($id);
  }
}
 
if (!function_exists('get_tiny_success')) {
  function get_tiny_success($id=0) {
    if ($_SESSION['tiny_success_'.$id]) {
      $return = $_SESSION['tiny_success_'.$id];
      unset($_SESSION['tiny_success_'.$id]);
      return $return;
    } else {
      return false;
    }
  }
}