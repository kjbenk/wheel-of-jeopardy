<?php
/*
Plugin Name: Wheel Of Jeopardy
Plugin URI:
Description: Create a Wheel of Jeopardy game
Version: 0.5.0
Author: BEANS
Author URI: https://kylebenk.com
License: GPL2
*/

include('controllers/questions.php');

/**
 * Include all needed styling and script files
 *
 * @access public
 * @return void
 */
function beans_enqueue_files() {
	wp_enqueue_style('beans_bootstrap_css', 	plugins_url('includes/bootstrap/css/bootstrap.css', __FILE__));
	wp_enqueue_style('beans_font_awesome_css', 	plugins_url('includes/font-awesome/css/font-awesome.min.css', __FILE__));

	wp_enqueue_script('beans_bootstrap_js', 	plugins_url('includes/bootstrap/js/bootstrap.min.js', __FILE__));
}