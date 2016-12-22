<?php

// Check if admin
if(is_admin()){
	// Add scripts
	function onfbr_add_admin_scripts(){
		wp_enqueue_style('onfbr-admin-style', plugins_url().'/on-facebook-reviews/css/style-admin.css');
	}

	add_action('admin_init', 'onfbr_add_admin_scripts');
}

// Add scripts
function onfbr_add_scripts(){
	wp_enqueue_style('onfbr-main-style', plugins_url() . '/on-facebook-reviews/css/style.css');
	wp_enqueue_script('onfbr-main-script', plugins_url() . '/on-facebook-reviews/js/main.js');
}

add_action('wp_enqueue_scripts', 'onfbr_add_scripts');