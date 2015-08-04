<?php

add_filter('the_content', 'beans_questions_content', 10, 1);

/**
 * Hooks into the_content filter and populates the questions page
 *
 * @access public
 * @param mixed $content
 * @return void
 */
function beans_questions_content( $content ) {

	$categories = array('Category 1', 'Category 2', 'Category 3', 'Category 4', 'Category 5');
	$points = array('1', '2', '3', '4', '5');

	global $post;

	if ( isset($post->ID) && $post->ID == 13 ) {

		beans_enqueue_files();

		// Get all questions

		$questions = get_option('beans_questions');

		if ( $questions === false ) {

			$questions = array();

			for ( $i = 0; $i < 50; $i++ ) {
				$questions[$i] = array(
					'question'	=> '',
					'answer'	=> '',
					'points'	=> '',
					'category'	=> '',
				);
			}

		}

		// List out all questions

		$content .= '<div class="beans-wrap">
		<form method="post">
			<ol>';

			$i = 0;

			foreach ( $questions as $question ) {
				$content .= '<li>
					<div class="form-group">
					 	<label for="beans_question_' . $i . '">Question</label>
    					<textarea rows="2" class="form-control" id="beans_question_' . $i . '" name="beans_question_' . $i . '">' . $question['question'] . '</textarea>
					</div>

					<div class="form-group">
					 	<label for="beans_answer_' . $i . '">Answer</label>
    					<input type="text" class="form-control" id="beans_answer_' . $i . '" name="beans_answer_' . $i . '" value="' . $question['answer'] . '"/>
					</div>

					<div class="form-group">
					 	<label for="beans_category_' . $i . '">Category</label>
					 	<select class="form-control" id="beans_category_' . $i . '" name="beans_category_' . $i . '" value="' . $question['category'] . '">';

					 	for ( $i = 0; $i < count($categories); $i++ ) {
							$content .= '<option value="" ' . selected($categories[$i], $question['category'], false) . '>' . $categories[$i] . '</option>';
						}

					 	$content .= '</select>
					</div>

					<div class="form-group">
					 	<label for="beans_points_' . $i . '">Points</label>
    					<select class="form-control" id="beans_points_' . $i . '" name="beans_points_' . $i . '" value="' . $question['points'] . '">';
						for ( $i = 0; $i < count($points); $i++ ) {
							$content .= '<option value="" ' . selected($points[$i], $question['points'], false) . '>' . $points[$i] . '</option>';
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

			for ( $i = 0; $i < 50; $i++ ) {
				$questions[$i] = array(
					'question'	=> $_POST['beans_question_' . $i],
					'answer'	=> $_POST['beans_answer_' . $i],
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