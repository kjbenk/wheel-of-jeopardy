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

		//$current_game = false;

		// Start Game

		if ( $current_game === false ) {

			$users = get_users();

			$user_option = '';

			foreach ( $users as $user ) {
				$user_option .= '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
			}

			$user_option .= '</select>';

			$content .= '<div class="beans-wrap">
				<div class="panel panel-default">
					<div class="panel-body">

						<form role="form" method="post">

					    	<div class="col-xs-4">
					    		<h1 class="text-center">Player 1</h1>
					    		<div class="form-group text-center">' .
					    			'<select id="player-1" name="player-1">' . $user_option
								. '</div>
					    	</div>
					    	<div class="col-xs-4">
					    		<h1 class="text-center">Player 2</h1>
					    		<div class="form-group text-center">' .
					    			'<select id="player-2" name="player-2">' . $user_option
								. '</div>
							</div>
					    	<div class="col-xs-4">
					    		<h1 class="text-center">Player 3</h1>
					    		<div class="form-group text-center">' .
					    			'<select id="player-3" name="player-3">' . $user_option
								. '</div>
					    	</div>
					    	<div class="form-group">
					    		<button type="submit" name="start-game-submit" class="btn btn-success">Start Game</button>
					    	</div>

				    	</form>
					</div>
				</div>
			</div>';

			// Create new game

			if ( isset($_POST['start-game-submit']) ) {

				$current_game = array(
					'players'	=> array(
						array(
							'id'			=> $_POST['player-1'],
							'score'			=> '0',
							'free_turns'	=> 0,
						),
						array(
							'id'			=> $_POST['player-2'],
							'score'			=> '0',
							'free_turns'	=> 0,
						),
						array(
							'id'			=> $_POST['player-3'],
							'score'			=> '0',
							'free_turns'	=> 0,
						)
					),
					'questions'	=> get_option('beans_questions')
				);

				beans_save_current_game( $current_game );

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15";
				</script>
				<?php

			}
		}

		// Current Game

		else {

			wp_enqueue_script('beans_wheel_js', plugins_url('../js/wheel.js', __FILE__));

			// Get player data

			$player_1 = get_user_by('id', $current_game['players'][0]['id']);
			$player_2 = get_user_by('id', $current_game['players'][1]['id']);
			$player_3 = get_user_by('id', $current_game['players'][2]['id']);

			// Init game data if we have not already

			if ( !isset($current_game['current_turn']) ) {
				$current_game['current_turn'] = $current_game['players'][0];
				$current_player = get_user_by('id', $current_game['current_turn']['id']);
			} else {
				$current_player = get_user_by('id', $current_game['current_turn']['id']);
			}

			$player_1_current = '';
			$player_2_current = '';
			$player_3_current = '';

			if ( $current_game['current_turn']['id'] == $current_game['players'][0]['id'] ) {
				$player_1_current = 'bg-warning';
			} else if ( $current_game['current_turn']['id'] == $current_game['players'][1]['id'] ) {
				$player_2_current = 'bg-warning';
			} else {
				$player_3_current = 'bg-warning';
			}

			if ( !isset($current_game['turns']) ) {
				$current_game['turns'] = 0;
			}

			if ( !isset($current_game['round']) ) {
				$current_game['round'] = 1;
			}

			// Get all Game Questions

			$questions = $current_game['questions'];

			// Validate answer

			if ( isset($_GET['answer']) && isset($_GET['question']) ) {

				// Save this answer in current game questions

				$current_game['questions'][$_GET['question']]['answered'] = true;

				$next = 0;

				if ( $current_game['players'][0]['id'] == $current_game['current_turn']['id'] ) {
					$next = 1;
				} else if ( $current_game['players'][1]['id'] == $current_game['current_turn']['id'] ) {
					$next = 2;
				} else {
					$next = 0;
				}

				// Right

				if ( beans_validate_answer($_GET['answer'], $questions, $_GET['question']) ) {

					$current_game['questions'][$_GET['question']]['correct'] = true;
					$current_game['current_turn'] = $current_game['players'][$next];
					$current_game['turns']++;

					// Change player score

					$current_game['players'][($next+2)%3]['score'] += $_GET['points'];

					beans_save_current_game( $current_game );

					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&message=right";
					</script>
					<?php
				}

				// Wrong

				else {

					$current_game['questions'][$_GET['question']]['correct'] = false;
					$current_game['current_turn'] = $current_game['players'][$next];
					$current_game['turns']++;
					beans_save_current_game( $current_game );

					// Change player score

					$current_game['players'][($next+2)%3]['score'] -= $_GET['points'];

					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&message=wrong";
					</script>
					<?php
				}

			}

			$content .= '<style>
				.table td,
				.table thead th {
					text-align:center;
				}

				.question-answered {
					background-color: #f5f5f5;
				}

				.question-answered button {
					visibility: hidden;
				}

			</style>
			<div class="beans-wrap">

				<div class="page-header">
					<h1>' . $current_player->display_name . ' its your turn! <small class="pull-right col-xs-3">' .
						'<div>Turn: <span class="pull-right">' . $current_game['turns'] . '</span></div>' .
						'<div>Round: <span class="pull-right">' . $current_game['round'] . '</span></div>' .
					'</small></h1>
				</div>

				<div class="panel panel-default">
					<div class="panel-body">
				    	<div class="col-xs-4 ' . $player_1_current . '">
				    		<h3 class="text-center">' . $player_1->display_name . '</h3>
				    		<div><strong>Score:<strong> <span class="pull-right">' . $current_game['players'][0]['score'] . '</span></div>
				    		<div><strong>Free Turns:<strong> <span class="pull-right">' . $current_game['players'][0]['free_turns'] . '</span></div>
				    	</div>
				    	<div class="col-xs-4 ' . $player_2_current . '">
				    		<h3 class="text-center">' . $player_2->display_name . '</h3>
				    		<div><strong>Score:<strong> <span class="pull-right">' . $current_game['players'][1]['score'] . '</span></div>
				    		<div><strong>Free Turns:<strong> <span class="pull-right">' . $current_game['players'][1]['free_turns'] . '</span></div>
						</div>
				    	<div class="col-xs-4 ' . $player_3_current . '">
				    		<h3 class="text-center">' . $player_3->display_name . '</h3>
				    		<div><strong>Score:<strong> <span class="pull-right">' . $current_game['players'][2]['score'] . '</span></div>
				    		<div><strong>Free Turns:<strong> <span class="pull-right">' . $current_game['players'][2]['free_turns'] . '</span></div>
				    	</div>
					</div>
				</div>
				<table class="table table-bordered">
					<thead>
						<tr>';

						foreach ( $categories as $category ) {
							$content .= '<th>' . $category . '</th>';
						}

						$content .= '</tr>
					</thead>
					<tbody>';

						if ( $current_game['round'] == 1 ) {
							$i = 0;
						} else {
							$i = 30;
						}

						$unanswered = false;

						for ( $count = 0; $count < 30; $count++ ) {

						    $question = $questions[$i];

							$answered_class = '';

							if ( isset($question['answered']) && $question['answered'] ) {
								$answered_class = 'question-answered';
							} else {
								$unanswered = true;
							}

							// Points

							if ( $i%30 < 6 ) {
								$points = 200;
							} else if ( $i%30 < 12 ) {
								$points = 400;
							} else if ( $i%30 < 18 ) {
								$points = 600;
							} else if ( $i%30 < 24 ) {
								$points = 800;
							} else if ( $i%30 < 30 ) {
								$points = 1000;
							}

							$question_popup = '<form method="get">
							<div class="modal fade" id="' . $id . '-question-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-top: 40px;background-color: rgba(0,0,0,0.8);">
							  <div class="modal-dialog" role="document">
							    <div class="modal-content">
							      <div class="modal-header">
							        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							        <h4 class="modal-title" id="myModalLabel">Question</h4>
							      </div>
							      <div class="modal-body">
							      	<p>' . $question['question'] . '</p>
								  	<label>Answer</label>
							      	<input type="text" name="answer" class="form-control"/>
							      	<input type="hidden" name="question" value="' . $id . '"/>
							      	<input type="hidden" name="points" value="' . $points . '"/>
							      </div>
							      <div class="modal-footer">
							        <button type="submit" class="btn btn-default">Submit</button>
							      </div>
							    </div>
							  </div>
							</div>
							</form>';

							if ( $i%30 < 6 ) {

								if ( $i%30 == 0 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $id . '-question-modal">200</button>' . $question_popup . '</td>';

								if ( $i%30 == 5 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 12 ) {

								if ( $i%30 == 6 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $id . '-question-modal">400</button>' . $question_popup . '</td>';

								if ( $i%30 == 11 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 18 ) {

								if ( $i%30 == 12 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $id . '-question-modal">400</button>' . $question_popup . '</td>';

								if ( $i%30 == 17 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 24 ) {

								if ( $i%30 == 18 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $id . '-question-modal">800</button>' . $question_popup . '</td>';

								if ( $i%30 == 23 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 30 ) {

								if ( $i%30 == 24 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $id . '-question-modal">1000</button>' . $question_popup . '</td>';

								if ( $i%30 == 29 ) {
									$content .= '</tr>';
								}

							}

							$i++;
						}

					$content .= '</tbody>
			    </table>
			</div>

			<div id="venues" style="float: left; display:none;"><ul></ul></div>

			<div id="wheel" >
			    <canvas id="canvas" width="1000" height="600"></canvas>
			</div>

			<div id="stats">
			    <div id="counter"></div>
			</div>';

			// Check to move to next round

/*
			if ( $unanswered == false ) {

				$current_game['turns'] = 0;
				$current_game['round']++;
				beans_save_current_game( $current_game );

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15&round=<?php echo $current_game['round']; ?>";
				</script>
				<?php
			}
*/

		}
	}

	return $content;
}