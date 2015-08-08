<?php

add_filter('the_content', 'beans_questions_content', 10, 1);

/**
 * Get the categories
 *
 * @access public
 * @return void
 */
function beans_get_categories() {

	$categories = get_option('beans_categories');

	if ( $categories === false ) {
		$categories = array('Geography', 'History', 'Astronomy', 'Geology', 'Literacy', 'Technology');
	}

	return $categories;

}

/**
 * Save the categories
 *
 * @access public
 * @return void
 */
function beans_save_categories( $categories ) {

	$result = update_option('beans_categories', $categories);

	return $result;

}

/**
 * Get the points
 *
 * @access public
 * @return void
 */
function beans_get_points() {

	$points = get_option('beans_points');

	if ( $points === false ) {
		$points = array('1', '2', '3', '4', '5');
	}

	return $points;

}

/**
 * Save the points
 *
 * @access public
 * @return void
 */
function beans_save_points( $points ) {

	$result = update_option('beans_points', $points);

	return $result;

}

/**
 * beans_validate_answer function.
 *
 * @access public
 * @param mixed $answer
 * @param mixed $questions
 * @param mixed $question_id
 * @return void
 */
function beans_validate_answer($answer, $questions, $question_id) {

	if ( $questions[$question_id]['answer'] == $answer ) {
		return true;
	}

	return false;

}

/**
 * Hooks into the_content filter and populates the questions page
 *
 * @access public
 * @param mixed $content
 * @return void
 */
function beans_questions_content( $content ) {

	global $post;

	if ( isset($post->ID) && $post->ID == 13 ) {

		beans_enqueue_files();

		$categories = beans_get_categories();
		$points = beans_get_points();

		// Get all questions

		$questions = get_option('beans_questions');

		if ( $questions === false ) {

			$questions = array();

			for ( $i = 0; $i < 60; $i++ ) {
				$questions[$i] = array(
					'question'	=> '',
					'answer'	=> '',
					'points'	=> '',
					'category'	=> '',
				);
			}

		}

		// List out all questions

		$content .= '
		<div class="beans-wrap">
		<form method="post">
			<ol>';

			$i = 0;

			foreach ( $questions as $question ) {
				$content .= '<li class="col-xs-6" style="list-style-position: inside;margin:0;">
					<div class="form-group">
					 	<label for="beans_question_' . $i . '">Question</label>
    					<textarea rows="2" class="form-control" id="beans_question_' . $i . '" name="beans_question_' . $i . '">' . esc_attr($question['question']) . '</textarea>
					</div>

					<div class="form-group">
					 	<label for="beans_answer_' . $i . '">Answer</label>
    					<input type="text" class="form-control" id="beans_answer_' . $i . '" name="beans_answer_' . $i . '" value="' . esc_attr($question['answer']) . '"/>
					</div>

					<div class="form-group">
					 	<label for="beans_category_' . $i . '">Category</label>
					 	<select class="form-control" id="beans_category_' . $i . '" name="beans_category_' . $i . '">';

					 	for ( $c = 0; $c < count($categories); $c++ ) {
							$content .= '<option value="' . $categories[$c] . '" ' . selected($categories[$c], $question['category'], false) . '>' . $categories[$c] . '</option>';
						}

					 	$content .= '</select>
					</div>

					<div class="form-group hidden">
					 	<label for="beans_points_' . $i . '">Points</label>
    					<select class="form-control" id="beans_points_' . $i . '" name="beans_points_' . $i . '" value="' . $question['points'] . '">';
						for ( $p = 0; $p < count($points); $p++ ) {
							$content .= '<option value="' . $points[$p] . '" ' . selected($points[$p], $question['points'], false) . '>' . $points[$p] . '</option>';
						}

						$content .= '</select>
					</div>
				</li>';

				$i++;
			}

			$content .= '
			</ol>
			<input type="submit" name="submit" class="btn btn-primary" value="Submit"/>
		</form></div>';

		// Save questions

		if ( $_POST['submit'] ) {

			for ( $i = 0; $i < 60; $i++ ) {
				$questions[$i] = array(
					'question'	=> stripcslashes(sanitize_text_field($_POST['beans_question_' . $i])),
					'answer'	=> stripcslashes(sanitize_text_field($_POST['beans_answer_' . $i])),
					'points'	=> $_POST['beans_points_' . $i],
					'category'	=> $_POST['beans_category_' . $i],
				);
			}

			update_option('beans_questions', $questions);

			?>
			<script type="text/javascript">
				window.location = "<?php echo get_site_url(); ?>?p=13";
			</script>
			<?php

		}
	}

	return $content;
}