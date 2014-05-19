<?php
/*
Plugin Name: BuddyPress Lightbox Alerts
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Adds BuddyPress Lightbox Tools - lightbox alerts for different user situations
Tags: buddypress
Version: 1.0
Author: Alec Rippberger
Author URI: http://alecrippberger.com
License: GPL2
*/
 
/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
function my_plugin_init() {
    require( dirname( __FILE__ ) . '/buddypress-update-email-reminder-lightbox.php' );
}
add_action( 'bp_include', 'my_plugin_init' );

/* If you have code that does not need BuddyPress to run, then add it here. */