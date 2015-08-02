<?php

add_filter('the_content', 'beans_questions_content', 10, 1);

/**
 * Hooks into the_content fileter and populates the questions page
 *
 * @access public
 * @param mixed $content
 * @return void
 */
function beans_questions_content( $content ) {

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
    					<textarea rows="3" class="form-control" id="beans_question_' . $i . '" name="beans_question_' . $i . '">' . $question['question'] . '</textarea>
					</div>

					<div class="form-group">
					 	<label for="beans_answer_' . $i . '">Answer</label>
    					<input type="text" class="form-control" id="beans_answer_' . $i . '" name="beans_answer_' . $i . '" value="' . $question['answer'] . '"/>
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