<?php
/*
Plugin Name: wooCommerce order Notification Twilio
Description: Wordpress plugin for admin sms notification on wooCommerce order
Version: 1.0.0
Author: Hamid Azad
Author URI: https://hamidazad.com
License: GPLv2 or later
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}
if ( !function_exists( 'twilio_woocommerce_order_notification' ) ) { 
    require_once ABSPATH . '/wp-admin/install-helper.php'; 
} 


    function twilio_woocommerce_order_notification() {

    global $wpdb;
    $table_name= $wpdb->prefix."twilio_woocommerce_order_notification";
    $create_ddl= "CREATE TABLE $table_name(
      id int(10) NOT NULL ,
      
      twilio_number varchar (100) DEFAULT '',
      twilio_sid varchar (100) DEFAULT '',
      twilio_auth varchar (100) DEFAULT '',
      to_number varchar (100) DEFAULT '',
      message varchar (160) DEFAULT '',

      PRIMARY KEY (id)

    )";
    
    foreach ($wpdb->get_col("SHOW TABLES", 0) as $table ) { 
        if ($table == $table_name) { 
            return true; 
        } 
    } 
    // Didn't find it, so try to create it. 
    $wpdb->query($create_ddl); 
 
    // We cannot directly tell that whether this succeeded! 
    foreach ($wpdb->get_col("SHOW TABLES", 0) as $table ) { 
        if ($table == $table_name) { 
            return true; 
        } 
    } 
    return false; 
} 
register_activation_hook( __FILE__, 'twilio_woocommerce_order_notification' );

function twilio_woocommerece_notification_settings_page()
{
    add_menu_page(
        'Order notification configuration',
        'Order notification',
        'manage_options',
        'order-notifier',
        'order_notification_settings_markup',
        'dashicons-format-chat',
        100
    );

}
add_action( 'admin_menu', 'twilio_woocommerece_notification_settings_page' );


function order_notification_settings_markup()
{
    // Double check user capabilities
    if ( !current_user_can('manage_options') ) {
      return;
    }
    ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <div class="wrap">
      <h1><?php esc_html_e( get_admin_page_title() ); ?></h1>

      <div class="row">
        <div class="col-md-6">
      <form method="post">
        <label for="twilio_number">Twilio Number </label>
        <input required="" class="form-control" type="text" name="twilio_number" id="twilio_number" placeholder="Ex:+###########">
        <br>
        <label for="twilio_sid">SID:</label>
        <input required="" class="form-control" type="text" name="twilio_sid" id="twilio_sid">
        <br>
        <label for="twilio_auth">Auth Token:</label>
        <input required="" class="form-control" type="text" name="twilio_auth" id="twilio_auth">
        <br>
        <br>
        <label for="to_number">Your Number:</label>
        <input required="" class="form-control" type="text" name="to_number" id="to_number" placeholder="Ex:+###########">
        <br>
        <br>
        <label for="to_number">Message:</label>
        <input required="" class="form-control" type="text" name="message" id="message" placeholder="160 character for one sms">
        <br>
        <button type="submit" class="btn btn-success" name="submit">Save</button>
      </form>
      <br>
      <?php 
      
        if(isset($_POST['submit'])){
    
    $twilio_number=$_POST['twilio_number'];
    $twilio_sid= $_POST['twilio_sid'];
    $twilio_auth= $_POST['twilio_auth'];
    $to_number= $_POST['to_number'];
    $message= $_POST['message'];
    global $wpdb;
    $table_name= $wpdb->prefix."twilio_woocommerce_order_notification";
    
      $wpdb->replace( 
  $table_name, 
  array( 
                
                'twilio_number' => $twilio_number,
                'twilio_sid' => $twilio_sid, 
                'twilio_auth' => $twilio_auth,
                'to_number'=>$to_number,
                'message'=>$message

  ), 
  array( 
                
                '%s',
                '%s', 
                '%s',
                '%s',
                '%s' 
  ) 
);

    
    
    
    
  }
    
      ?>
      </div>
      </div>
      
    </div>
    
<?php



}




function twilio_woocommerce_order_status_completed() {
  
   global $wpdb;
   $table_name= $wpdb->prefix."twilio_woocommerce_order_notification";

   $result= $wpdb->get_results("SELECT * FROM $table_name");
   if($result){
   foreach ($result as $row) {
      $twilio_number=$row->twilio_number;
      $sid=$row->twilio_sid;
      $token=$row->twilio_auth;
      $to_number=$row->to_number;
      $message=$row->message;
    }
   require_once(plugin_dir_path( __FILE__ ).'/vendor/autoload.php');
   
  //twilio
  $client = new Twilio\Rest\Client($sid, $token);
  $message=$client->messages->create(
  // Where to send a text message (your cell phone?)
       $to_number,
       array(
           'from' => $twilio_number,
           'body' => $message
       )
       );
   if($message->sid){
     echo "Message sent!";
   }
 }
}
add_action( 'woocommerce_order_status_completed', 'twilio_woocommerce_order_status_completed', 10, 1 );