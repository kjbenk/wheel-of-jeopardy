<?php

add_filter('the_content', 'beans_game_content', 10, 1);

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

		wp_enqueue_script('beans_wheel_js', plugins_url('../js/wheel.js', __FILE__));

		$content .= '
		<style>
			.table * {
				text-align:center;
			}
		</style>
		<div class="beans-wrap">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Category 1</th>
						<th>Category 2</th>
						<th>Category 3</th>
						<th>Category 4</th>
						<th>Category 5</th>
						<th>Category 6</th>
					</tr>
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

	return $content;
}