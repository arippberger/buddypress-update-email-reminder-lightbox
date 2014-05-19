<?php

// Since is_user_logged_in is not available to us within this plugin, we must re-define the function.  Props to http://premium.wpmudev.org/forums/topic/is_user_logged_in-not-available-to-plugins#post-224234
if ( !function_exists('is_user_logged_in') ) :
/**
 * Checks if the current visitor is a logged in user.
 *
 * @since 2.0.0
 *
 * @return bool True if user is logged in, false if not logged in.
 */
function is_user_logged_in() {
	$user = wp_get_current_user();

	if ( empty( $user->ID ) )
		return false;

	return true;
}
endif;

//TODO - add translation
if ( is_user_logged_in() ) {
    
	add_action( 'wp_enqueue_scripts', 'bp_lb_scripts_method' ); // wp_enqueue_scripts action hook to link only on the front-end
	function bp_lb_scripts_method() {
		//get initial resources
		wp_enqueue_style( 'bp-lightbox-style', plugins_url('/css/style.css', __FILE__)); //styles
		wp_enqueue_script( 'jquery' );
		//wp_enqueue_script( 'show-caregiving-now-lightbox', plugins_url('/js/bp-lightbox-script.js', __FILE__), array('thickbox', 'jquery'), false, true); //javascript
	}
	
	add_thickbox(); //set up thickbox using WordPress's built in function, add_thickbox
	add_action( 'wp_head', 'bp_lb_add_styles_to_head' );
	add_action('wp_footer', 'bp_lb_check_bp_user_last_activity', 99);
	
	/*
	 * bp_lb_check_bp_user_last_activity
	 *
	 * what the function does
	 *
	 * @return (null)
	 */
	
	function bp_lb_check_bp_user_last_activity() {
		global $bp;
	
		//setup variables from settings page - get number of days before reminder
		if ( get_option( 'lightbox_alerts_days' ) ) {
	
			$time_to_check = get_option('lightbox_alerts_days') * 86400;
	
			//if lightbox_alerts_days is less than 0, set timer to 10 seconds
			if ( $time_to_check < 0 ) {
				$time_to_check = 10;
			}
	
		} else {
			$time_to_check = 8640000; //8640000 seconds = 100 days
		} 
		//get current BuddyPress user
		$current_user = bp_loggedin_user_id();
	
		// TODO - document the last activity user meta - was defines last_activity
		//get last activity date
		$last_activity =  strtotime( bp_get_user_meta( $current_user, 'last_activity', true ) );
	
		//calculate time since last activity
		$time_since_last_activity = time() - $last_activity;
		
		
		//check if time since last activity is greater than the setting value
		if ( $time_since_last_activity > $time_to_check ) { 
			//echo "<script type='text/javascript'>alert('HELLO');</script>";
			//show lightbox 
			bp_lb_show_email_still_correct_lightbox();
		}
	
	
	}
	
	/*
	 * Function name
	 *
	 * what the function does
	 *
	 * @param (type) about this param
	 * @return (type)
	 */
	
	function bp_lb_show_email_still_correct_lightbox() {
	
		global $bp;
	
		$border_color = get_option( 'border_color' );
	    $background_color = get_option( 'background_color' );
	    $header_color = get_option( 'header_color' );  
	    $email_address_color = get_option( 'email_address_color' );
	    $lb_link_color = get_option( 'lb_link_color' );
	
	    $border_color_style = !empty($border_color) ? 'border-color:' . $border_color . '; ': '';
	    $background_color_style = !empty($background_color) ? 'background-color:' . $background_color . '; ': '';
	    $header_color_style = !empty($header_color) ? 'color:' . $header_color . '; ': '';
	    $email_address_color_style = !empty($email_address_color) ? 'color:' . $email_address_color . '; ': 
		$lb_link_color_style = !empty($lb_link_color) ? 'color:' . $lb_link_color . '; ': '';
	
		// TODO - add ability to customize message
		// TODO - display all profile info and ask if it is correct
	
		?>
		<script>
			jQuery(document).ready( function(){
				console.log( "calling tb_show" );
				tb_show("","#TB_inline?inlineId=bp-hidden-lightbox-content", "");
			});	
		</script>
	
		<div id="bp-hidden-lightbox-content" style="display:none;background:none;">
			<div class="bp-hidden-lightbox-content-inner" style="<?php echo $border_color_style . $background_color_style; ?>">
				<div class="generic-button">
					<?php global $current_user; get_currentuserinfo(); ?>
				    <h1 style="<?php echo $header_color_style; ?>">Welcome back, <?php echo $current_user->display_name; ?>!</h1>
				    <h2 style="<?php echo $header_color_style; ?>">Is <span class="lb_email_address" style="<?php echo $email_address_color_style; ?>"><?php echo bp_core_get_user_email(get_current_user_id()); ?></span> still your email address?</h2>
				    <p>
				    	<a class="bp-lb-no" style="color: <?php echo $lb_link_color; ?>" href="<?php if ( function_exists( 'bp_loggedin_user_id' ) && bp_loggedin_user_id() ) { echo bp_loggedin_user_domain() . 'settings/';}?>">No</a>
			        	<a class="bp-lb-yes" style="color: <?php echo $lb_link_color; ?>" href="#" id="TB_closeWindowButton">Yes</a>
				    </p>    
				</div>
			</div>	
		</div>
		<a href="#TB_inline?inlineId=bp-hidden-lightbox-content" class="thickbox" style="display:none;">View</a><?php
		//wp_enqueue_script( 'show-caregiving-now-lightbox', plugins_url('/js/bp-lightbox-script.js', __FILE__), array('thickbox', 'jquery'));
	}
	
	function bp_lb_add_styles_to_head() {
	
		global $bp;
	
		?>
		<!-- BuddyPress Update Profile Field Reminder Styles -->
		<style>
			#TB_title {
				display: none;
			}
			#TB_ajaxContent {
				padding: 0;
				margin: 0;
				height: auto!important;
				width: auto!important;
			}
			/*do not show on settings page - TODO - imporove so this does not use CSS */
			.my-account #TB_overlay,
			.my-account #TB_window {
				display: none!important;
			}	
		</style>
		<script>
			jQuery(document).ready(function(){
				// jQuery('.bp-lb-no').click(function(){
				// 	jQuery('#TB_window').hide();
				// 	jQuery('#TB_overlay').hide();
				// 	window.location = '<?php if ( function_exists( 'bp_loggedin_user_id' ) && bp_loggedin_user_id() ) { echo bp_loggedin_user_domain() . 'settings/';}?>';
				// });
				// jQuery('.bp-lb-yes').click(function(){
				// 	jQuery('#TB_window').hide();
				// 	jQuery('#TB_overlay').hide();
				// });			
			});
		</script><?php
	}
	
	function bp_lb_settings_action_general() {
		echo 'yes';
	}
	add_action('bp_settings_action_general', 'bp_lb_settings_action_general');
	
	//add setting section
	add_action( 'admin_menu', 'bp_lb_plugin_menu' );
	
	//add options page to menu
	function bp_lb_plugin_menu() {
		add_options_page( 'BuddyPress Update Email Reminder Lightbox Settings', 'BuddyPress Update Email Reminder Lightbox Settings', 'manage_options', 'lb-alerts-settings', 'bp_lb_alerts_options' );
	}
	
	//generate page for alerts options
	function bp_lb_alerts_options() {
		global $bp;
	
	    //check if user can edit page - if not, boot
	    if ( ! current_user_can( 'manage_options' ) ) {
	      wp_die( __('You do not have sufficient permissions to access this page.') );
	    }
	
	    //hidden field name - hidden field to test form submission
	    $hidden_field_name = 'lightbox_alert_hidden_field';
	
	    // default variables
	
	    // Read in existing option value from database
	    $lightbox_alerts_days = get_option( 'lightbox_alerts_days' );
	    $border_color = get_option( 'border_color' );
	    $background_color = get_option( 'background_color' );
	    $header_color = get_option( 'header_color' ); 
	    $email_address_color = get_option( 'email_address_color' );
	    $lb_link_color = get_option( 'lb_link_color' ); 
	
	    // See if the user has posted us some information
	    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' && check_admin_referer( 'post_lightbox_alert_form', 'lightbox_alert_nonce' ) ) {
	        
	        // Read their posted value
	    	$lightbox_alerts_days = intval($_POST[ 'lightbox_alerts_days' ]);
	    	$border_color = $_POST[ 'border_color' ];
	    	$background_color = $_POST[ 'background_color' ];
	    	$header_color = $_POST[ 'header_color' ];
	    	$email_address_color = $_POST[ 'email_address_color' ];
	    	$lb_link_color = $_POST[ 'lb_link_color' ];
	
	        // Save the posted value in the database
			update_option( 'lightbox_alerts_days', $lightbox_alerts_days );
			update_option( 'border_color', $border_color );
			update_option( 'background_color', $background_color );
			update_option( 'header_color', $header_color );
			update_option( 'email_address_color', $email_address_color );
			update_option( 'lb_link_color', $lb_link_color );				
	
	        // Put an settings updated message on the screen
	        ?>
			<div class="updated"><p><strong><?php _e('settings saved.', 'bp-user-notification' ); ?></strong></p></div><?php
	    }
	
	    // Now display the settings editing screen
	    echo '<div class="wrap">';
	
	    // header
	    echo "<h2>" . __( 'BuddyPress Update Email Reminder Lightbox', 'bp-user-notification' ) . "</h2>";
	
	    // settings form
	    ?>
	
		<form name="form1" method="post" action="">
			<?php wp_nonce_field( 'post_lightbox_alert_form', 'lightbox_alert_nonce' ); ?>
			<input type="hidden" name="<?php esc_attr_e($hidden_field_name); ?>" value="Y">
			<h2>BuddyPress Update Email Reminder Lightbox Settings</h2>
			<p>Adjust how many days a user can be inactive for before being asked to update their profile.</p>
			<p><?php _e( 'Number of days:', 'lightbox_alerts' ); ?> 
				<input type="number" name="lightbox_alerts_days" value="<?php esc_attr_e($lightbox_alerts_days); ?>" size="20">
			</p>
			<h2><?php _e( 'Color options:', 'lightbox_alerts' ); ?> </h2>
			<p><?php _e( 'Border color:', 'lightbox_alerts' ); ?><br>
				<input class="colorpicker" type="text" value="<?php esc_attr_e($border_color); ?>" data-default-color="#effeff" name="border_color">
			</p>
			<p><?php _e( 'Background color:', 'lightbox_alerts' ); ?><br>		
				<input class="colorpicker" type="text" value="<?php esc_attr_e($background_color); ?>" data-default-color="#effeff" name="background_color">
			</p>
			<p><?php _e( 'Main header color:', 'lightbox_alerts' ); ?><br>		
				<input class="colorpicker" type="text" value="<?php esc_attr_e($header_color); ?>" data-default-color="#effeff" name="header_color">
			</p>
			<p><?php _e( 'Email address color:', 'lightbox_alerts' ); ?><br>		
				<input class="colorpicker" type="text" value="<?php esc_attr_e($email_address_color); ?>" data-default-color="#effeff" name="email_address_color">
			</p>
			<p><?php _e( 'Link color:', 'lightbox_alerts' ); ?><br>		
				<input class="colorpicker" type="text" value="<?php esc_attr_e($lb_link_color); ?>" data-default-color="#effeff" name="lb_link_color">
			</p>					
			<hr />
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
			</p>
		</form><?php
	
		//var_dump($_POST);
	
		echo '</div>';
	 
	}
	
	add_action( 'admin_enqueue_scripts', 'bp_lb_enqueue_color_picker' );
	function bp_lb_enqueue_color_picker( $hook_suffix ) {
	    // first check that $hook_suffix is appropriate for your admin page
	    wp_enqueue_style( 'wp-color-picker' );
	    wp_enqueue_script( 'lightbox-alert-color-picker-script', plugins_url('/js/lightbox-alert-color-picker-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}
}