<?php

add_filter('the_content', 'beans_home_content', 10, 1);

/**
 * Hooks into the_content filter and populates the home page
 *
 * @access public
 * @param mixed $content
 * @return void
 */
function beans_home_content( $content ) {

	global $post;

	if ( isset($post->ID) && $post->ID == 6 ) {

		beans_enqueue_files();

	}

	return $content;
}