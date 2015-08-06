<?php

add_filter('the_content', 'beans_game_content', 10, 1);


/**
 * Get the current game
 *
 * @access public
 * @return void
 */
function beans_get_current_game() {

	$current_game = get_option('beans_current_game');

	return $current_game;

}

/**
 * Save the current game
 *
 * @access public
 * @return void
 */
function beans_save_current_game( $current_game ) {

	$result = update_option('beans_current_game', $current_game);

	return $result;

}

/**
 * Adds a new game
 *
 * @access public
 * @param mixed $current_game
 * @return void
 */
function beans_add_current_game( $current_game ) {

	return beans_save_current_game( $current_game );
}

/**
 * Remove current game
 *
 * @access public
 * @return void
 */
function beans_remove_current_game() {

	$result = delete_option('beans_current_game');

	return $result;

}

/**
 * Hooks into the_content filter and populates the game page
 *
 * @access public
 * @param mixed $content
 * @return void
 */
function beans_game_content( $content ) {

	global $post;

	if ( isset($post->ID) && $post->ID == 15 ) {

		beans_enqueue_files();

		$current_game = beans_get_current_game();
		$categories = beans_get_categories();

		// Start Game

		if ( $current_game === false ) {

		}

		// Current Game

		else {

			wp_enqueue_script('beans_wheel_js', plugins_url('../js/wheel.js', __FILE__));

			$content .= '
			<style>
				.table td,
				.table thead th {
					text-align:center;
				}
			</style>
			<div class="beans-wrap">
				<table class="table table-bordered">
					<thead>
						<tr>';

						foreach ( $categories as $category ) {
							$content .= '<th>' . $category . '</th>';
						}

						$content .= '</tr>
					</thead>
					<tbody>
						<tr>
							<td>200</td>
							<td>200</td>
							<td>200</td>
							<td>200</td>
							<td>200</td>
							<td>200</td>
						</tr>

						<tr>
							<td>400</td>
							<td>400</td>
							<td>400</td>
							<td>400</td>
							<td>400</td>
							<td>400</td>
						</tr>

						<tr>
							<td>600</td>
							<td>600</td>
							<td>600</td>
							<td>600</td>
							<td>600</td>
							<td>600</td>
						</tr>

						<tr>
							<td>800</td>
							<td>800</td>
							<td>800</td>
							<td>800</td>
							<td>800</td>
							<td>800</td>
						</tr>

						<tr>
							<td>1000</td>
							<td>1000</td>
							<td>1000</td>
							<td>1000</td>
							<td>1000</td>
							<td>1000</td>
						</tr>
					</tbody>
			    </table>
			</div>

			<div id="venues" style="float: left; display:none;"><ul></ul></div>

			<div id="wheel" >
			    <canvas id="canvas" width="1000" height="600"></canvas>
			</div>

			<div id="stats">
			    <div id="counter"></div>
			</div>';

		}
	}

	return $content;
}