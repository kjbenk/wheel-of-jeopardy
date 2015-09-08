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

		$current_game = beans_get_current_game();
		$categories = beans_get_categories();

		// $current_game = false;

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

			$games = beans_get_games();

			foreach ( $games as $game ) {
				//print_r($game);
			}

			// Create new game

			if ( isset($_POST['start-game-submit']) ) {

				$questions = get_option('beans_questions');
				$game_questions = array();

				$geography = array();
				$history = array();
				$astronomy = array();
				$geology = array();
				$literacy = array();
				$technology = array();

				foreach ( $questions as $question ) {

					if ( $question['category'] == 'Geography' ) {
						$geography[] = $question;
					} else if ( $question['category'] == 'History' ) {
						$history[] = $question;
					} else if ( $question['category'] == 'Astronomy' ) {
						$astronomy[] = $question;
					} else if ( $question['category'] == 'Geology' ) {
						$geology[] = $question;
					} else if ( $question['category'] == 'Literacy' ) {
						$literacy[] = $question;
					} else if ( $question['category'] == 'Technology' ) {
						$technology[] = $question;
					}

				}

				for ( $i = 0; $i < 60; $i += 6 ) {

					$game_questions[$i]        = $geography[$i/6];
					$game_questions[$i + 1]    = $history[$i/6];
					$game_questions[$i + 2]    = $astronomy[$i/6];
					$game_questions[$i + 3]    = $geology[$i/6];
					$game_questions[$i + 4]    = $literacy[$i/6];
					$game_questions[$i + 5]    = $technology[$i/6];

				}

				$current_game = array(
					'players'	=> array(
						array(
							'id'			=> $_POST['player-1'],
							'score'			=> '0',
							'free_spins'	=> 0,
						),
						array(
							'id'			=> $_POST['player-2'],
							'score'			=> '0',
							'free_spins'	=> 0,
						),
						array(
							'id'			=> $_POST['player-3'],
							'score'			=> '0',
							'free_spins'	=> 0,
						)
					),
					'questions'	=> $game_questions
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

			$next = 0;

			if ( $current_game['players'][0]['id'] == $current_game['current_turn']['id'] ) {
				$next = 1;
			} else if ( $current_game['players'][1]['id'] == $current_game['current_turn']['id'] ) {
				$next = 2;
			} else {
				$next = 0;
			}

			$current_player_id = ($next+2)%3;

			// Get all Game Questions

			$questions = $current_game['questions'];

			$free_spins = $current_game['players'][($next+2)%3]['free_spins'];

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
			<div class="beans-wrap">';

				if ( isset($_GET['category']) ) {
					$content .= '<div class="alert alert-success" role="alert">Great Spin!  You landed on ' . stripcslashes(urldecode($_GET['category'])) . '</div>';
				}

				$content .= '<div class="page-header">
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
				    		<div><strong>Free Spins:<strong> <span class="pull-right">' . $current_game['players'][0]['free_spins'] . '</span></div>
				    	</div>
				    	<div class="col-xs-4 ' . $player_2_current . '">
				    		<h3 class="text-center">' . $player_2->display_name . '</h3>
				    		<div><strong>Score:<strong> <span class="pull-right">' . $current_game['players'][1]['score'] . '</span></div>
				    		<div><strong>Free Spins:<strong> <span class="pull-right">' . $current_game['players'][1]['free_spins'] . '</span></div>
						</div>
				    	<div class="col-xs-4 ' . $player_3_current . '">
				    		<h3 class="text-center">' . $player_3->display_name . '</h3>
				    		<div><strong>Score:<strong> <span class="pull-right">' . $current_game['players'][2]['score'] . '</span></div>
				    		<div><strong>Free Spins:<strong> <span class="pull-right">' . $current_game['players'][2]['free_spins'] . '</span></div>
				    	</div>
					</div>
				</div>
				<table class="table table-bordered">
					<thead>
						<tr>';

						foreach ( $categories as $category ) {

							$category_state = 'disabled';

							if ( isset($_GET['category']) && urldecode($_GET['category']) == $category ) {
								$category_state = '';
							}

							if ( isset($_GET['category']) && ( stripcslashes(urldecode($_GET['category'])) == "Player's Choice" || stripcslashes(urldecode($_GET['category'])) == "Opponent's Choice" )  ) {
								$category_state = '';
							}

							$content .= '<th><a href="' . get_site_url() . '?p=15&category=' . $category . '" class="btn btn-link" ' . $category_state . '>' . $category . '</a></th>';
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

							// Check what questions can be selected

							$button_state = 'disabled';
							$category_state = 'disabled';

							// Categories

							if ( isset($_GET['category']) && urldecode($_GET['category']) == 'Geography' && $i%6 == 0 ) {
								$category_state = '';
							} else if ( isset($_GET['category']) && urldecode($_GET['category']) == 'History' && $i%6 == 1 ) {
								$category_state = '';
							} else if ( isset($_GET['category']) && urldecode($_GET['category']) == 'Astronomy' && $i%6 == 2 ) {
								$category_state = '';
							} else if ( isset($_GET['category']) && urldecode($_GET['category']) == 'Geology' && $i%6 == 3 ) {
								$category_state = '';
							} else if ( isset($_GET['category']) && urldecode($_GET['category']) == 'Literacy' && $i%6 == 4 ) {
								$category_state = '';
							} else if ( isset($_GET['category']) && urldecode($_GET['category']) == 'Technology' && $i%6 == 5 ) {
								$category_state = '';
							}

							// Players choice or oponents choice

							else if ( isset($_GET['category']) && ( stripcslashes(urldecode($_GET['category'])) == "Player's Choice" || stripcslashes(urldecode($_GET['category'])) == "Opponent's Choice" ) ) {
								$category_state = '';
							}

							$question_popup = '<form method="get">
							<div class="modal fade" id="' . $i . '-question-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-top: 40px;background-color: rgba(0,0,0,0.8);">
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
							      	<input type="hidden" name="question" value="' . $i . '"/>
							      	<input type="hidden" name="points" value="' . $points . '"/>
							      </div>
							      <div class="modal-footer">';

							      	if ( isset($free_spins) && (int) $free_spins > 0 ) {
								  		$question_popup .= '<a href="' . get_site_url() . '?p=15&action=free-spin&player=' . $current_player_id . '" class="btn btn-success">Use Free Spin</a>';
							      	}

							        $question_popup .= '<button type="submit" class="btn btn-default">Submit</button>
							      </div>
							    </div>
							  </div>
							</div>
							</form>';

							if ( $i%30 < 6 ) {

								if ( $i%30 == 0 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $i . '-question-modal" ' . $button_state . '>200</button>' . $question_popup . '</td>';

								if ( $i%30 == 5 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 12 ) {

								if ( $i%30 == 6 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $i . '-question-modal" ' . $button_state . '>400</button>' . $question_popup . '</td>';

								if ( $i%30 == 11 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 18 ) {

								if ( $i%30 == 12 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $i . '-question-modal" ' . $button_state . '>600</button>' . $question_popup . '</td>';

								if ( $i%30 == 17 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 24 ) {

								if ( $i%30 == 18 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $i . '-question-modal" ' . $button_state . '>800</button>' . $question_popup . '</td>';

								if ( $i%30 == 23 ) {
									$content .= '</tr>';
								}

							} else if ( $i%30 < 30 ) {

								if ( $i%30 == 24 ) {
									$content .= '<tr>';
								}

								$content .= '<td class="' . $answered_class . '"><button class="btn btn-link" data-toggle="modal" data-target="#' . $i . '-question-modal" ' . $button_state . '>1000</button>' . $question_popup . '</td>';

								if ( $i%30 == 29 ) {
									$content .= '</tr>';
								}

							}

							$i++;
						}

					$content .= '</tbody>
			    </table>

			    <div class="modal fade" id="free-spin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-top: 40px;background-color: rgba(0,0,0,0.8);">
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				        <h4 class="modal-title" id="myModalLabel">Free Spin</h4>
				      </div>
				      <div class="modal-body">
				      	Do you want to use one of your free spins?
				      </div>
				      <div class="modal-footer">
				      	<a href="' . get_site_url() . '?p=15&free-spin=no&category=' . urlencode('Lose Turn') . '" class="btn btn-default">No</button>
				      	<a href="' . get_site_url() . '?p=15&free-spin=yes&category=' . urlencode('Lose Turn') . '" class="btn btn-success">Yes</a>
				      </div>
				    </div>
				  </div>
				</div>

			</div>

			<div id="venues" style="float: left; display:none;"><ul></ul></div>

			<div id="wheel" >
			    <canvas id="canvas" width="1000" height="600"></canvas>
			</div>

			<div id="stats">
			    <div id="counter"></div>
			</div>';

			// Check to move to next round

			if ( $unanswered == false || (int) $current_game['turns'] >= 50 ) {

				$current_game['turns'] = 0;
				$current_game['round']++;

				if ( (int) $current_game['round'] > 2 ) {
					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&end_game=true";
					</script>
					<?php
				} else {
					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&end_game=false";
					</script>
					<?php
				}

				beans_save_current_game( $current_game );

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15&round=<?php echo $current_game['round']; ?>";
				</script>
				<?php
			}

			// End Game

			if ( isset($_GET['end_game']) && $_GET['end_game'] == 'true' ) {

				beans_archive_current_game();

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15&message=archive_game";
				</script>
				<?php

			}

			// Redirect to question modal when user landed on category or selects category

			if ( isset($_GET['category']) && $_GET['category'] != '' ) {

				$category = urldecode($_GET['category']);

				// Get next question in that category

				if ( $current_game['round'] == 1 ) {
					$i = 0;
				} else {
					$i = 30;
				}

				for ( $i; $i < 30 * $current_game['round']; $i += 6 ) {

					if ( $category == 'Geography' && ( !isset($questions[$i]['answered']) ||
						( isset($questions[$i]['answered']) && !$questions[$i]['answered'] ) ) ) {

						?>
						<script type="text/javascript">
							window.location = "<?php echo get_site_url(); ?>?p=15&action=asnwer&question=<?php echo $i; ?>";
						</script>
						<?php

					} else if ( $category == 'History' && ( !isset($questions[$i + 1]['answered']) ||
						( isset($questions[$i + 1]['answered']) && !$questions[$i + 1]['answered'] ) ) ) {

						?>
						<script type="text/javascript">
							window.location = "<?php echo get_site_url(); ?>?p=15&action=asnwer&question=<?php echo $i + 1; ?>";
						</script>
						<?php

					} else if ( $category == 'Astronomy' && ( !isset($questions[$i + 2]['answered']) ||
						( isset($questions[$i + 2]['answered']) && !$questions[$i + 2]['answered'] ) ) ) {

						?>
						<script type="text/javascript">
							window.location = "<?php echo get_site_url(); ?>?p=15&action=asnwer&question=<?php echo $i + 2; ?>";
						</script>
						<?php

					} else if ( $category == 'Geology' && ( !isset($questions[$i + 3]['answered']) ||
						( isset($questions[$i + 3]['answered']) && !$questions[$i + 3]['answered'] ) ) ) {

						?>
						<script type="text/javascript">
							window.location = "<?php echo get_site_url(); ?>?p=15&action=asnwer&question=<?php echo $i + 3; ?>";
						</script>
						<?php

					} else if ( $category == 'Literacy' && ( !isset($questions[$i + 4]['answered']) ||
						( isset($questions[$i + 4]['answered']) && !$questions[$i + 4]['answered'] ) ) ) {

						?>
						<script type="text/javascript">
							window.location = "<?php echo get_site_url(); ?>?p=15&action=asnwer&question=<?php echo $i + 4; ?>";
						</script>
						<?php

					} else if ( $category == 'Technology' && ( !isset($questions[$i + 5]['answered']) ||
						( isset($questions[$i + 5]['answered']) && !$questions[$i + 5]['answered'] ) ) ) {

						?>
						<script type="text/javascript">
							window.location = "<?php echo get_site_url(); ?>?p=15&action=asnwer&question=<?php echo $i + 5; ?>";
						</script>
						<?php

					}
				}
			}

			// Free Spin

			if ( isset($_GET['action']) && $_GET['action'] == 'free-spin' && isset($_GET['player']) && $_GET['player'] != '' ) {
				$current_game['players'][$current_player_id]['free_spins']--;
				$current_game['turns']++;
				beans_save_current_game( $current_game );

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15&message=free_spin";
				</script>
				<?php
			}

			// Validate answer

			if ( isset($_GET['answer']) && isset($_GET['question']) ) {

				// Save this answer in current game questions

				$current_game['questions'][$_GET['question']]['answered'] = true;

				// Right

				if ( beans_validate_answer(urldecode($_GET['answer']), $questions, $_GET['question']) ) {

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

					// Change player score

					$current_game['players'][($next+2)%3]['score'] -= $_GET['points'];

					beans_save_current_game( $current_game );

					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&message=wrong";
					</script>
					<?php
				}

			}

			// Free turn

			else if ( isset($_GET['category']) && urldecode($_GET['category']) == "Free Spin" ) {

				$current_game['players'][($next+2)%3]['free_spins']++;
				$current_game['current_turn'] = $current_game['players'][$next];
				$current_game['turns']++;
				beans_save_current_game( $current_game );

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15&message=free_spin";
				</script>
				<?php
			}

			// Spin Again

			else if ( isset($_GET['category']) && urldecode($_GET['category']) == "Spin Again" ) {

				$current_game['turns']++;
				//$current_game['turns'] = 49;
				beans_save_current_game( $current_game );

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15&message=spin_again";
				</script>
				<?php
			}

			// Lose turn

			else if ( isset($_GET['category']) && urldecode($_GET['category']) == "Lose Turn" ) {

				if ( !isset($_GET['free-spin']) && (int) $current_game['players'][($next+2)%3]['free_spins'] > 0 ) {
					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&free-spin=true";
					</script>
					<?php
				} else if ( isset($_GET['free-spin']) && $_GET['free-spin'] == 'yes' ) {

					$current_game['players'][($next+2)%3]['free_spins']--;
					$current_game['turns']++;
					beans_save_current_game( $current_game );

					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&message=free_spin";
					</script>
					<?php
				} else if ( isset($_GET['free-spin']) && $_GET['free-spin'] == 'no' ) {
					$current_game['current_turn'] = $current_game['players'][$next];
					$current_game['turns']++;
					beans_save_current_game( $current_game );

					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&message=lose_turn";
					</script>
					<?php
				} else {
					$current_game['current_turn'] = $current_game['players'][$next];
					$current_game['turns']++;
					beans_save_current_game( $current_game );

					?>
					<script type="text/javascript">
						window.location = "<?php echo get_site_url(); ?>?p=15&message=lose_turn";
					</script>
					<?php
				}
			}

			// Bankrupt

			else if ( isset($_GET['category']) && urldecode($_GET['category']) == "Bankrupt" ) {

				$current_game['current_turn'] = $current_game['players'][$next];
				$current_game['turns']++;

				if ( (int) $current_game['players'][($next+2)%3]['score'] > 0 ) {
					$current_game['players'][($next+2)%3]['score'] = 0;
				}

				beans_save_current_game( $current_game );

				?>
				<script type="text/javascript">
					window.location = "<?php echo get_site_url(); ?>?p=15&message=bankrupt";
				</script>
				<?php
			}

		}
	}

	return $content;
}

/**
 * Archive the current game
 *
 * @access public
 * @return void
 */
function beans_archive_current_game( $current_game ) {

	$games = get_option('beans_games');

	if ( $games === false || !is_array($games) ) {
		$games = array();
	}

	// Pick winner

	$winner = null;

	foreach ( $current_game['players'] as $player ) {

		if ( !isset($winner) || $winner['score'] < $player['score'] ) {
			$winner = $player;
		}
	}

	$current_game['winner'] = $winner;

	$games[] = $current_game;

	beans_remove_current_game();

	$result = update_option('beans_games', $games);

	return $result;

}

/**
 * Get all games
 *
 * @access public
 * @return void
 */
function beans_get_games() {

	$games = get_option('beans_games');

	return $games;

}

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