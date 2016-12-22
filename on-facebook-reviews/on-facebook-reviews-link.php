<?php
/**
*	Plugin Name: Facebook Reviews
*	Description: Plugin to easily add Facebook page reviews on your website
*	Version: 1.0
*	Author: Oskar Nylén
**/

// Exit if Accessed Directly
if(!defined('ABSPATH')){
	exit;
}

// Global Options Variable
$onfbr_options = get_option('onfbr_settings');

// Load Scripts
require_once(plugin_dir_path(__FILE__).'/includes/on-facebook-reviews-scripts.php');

// Load Content
require_once(plugin_dir_path(__FILE__).'/includes/on-facebook-reviews-content.php');


if(is_admin()){
	// Load Settings
	require_once(plugin_dir_path(__FILE__).'/includes/on-facebook-reviews-settings.php');
}

