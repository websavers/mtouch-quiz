<?php
require('../../../wp-blog-header.php');
auth_redirect();
if($wp_version >= '2.6.5') check_admin_referer('mtq_create_edit_quiz');
require('wpframe.php');

// I could have put this in the quiz_form.php - but the redirect will not work.
$answer_mode = isset($_REQUEST['answer_mode']) && in_array((string) $_REQUEST['answer_mode'], array('0', '1', '2'), true) ? (string) $_REQUEST['answer_mode'] : '2';
$single_page = '0';
if (isset($_REQUEST['single_page'])) $single_page = '1';
$show_hints =  '0';
if (isset($_REQUEST['show_hints'])) $show_hints = '1';
$show_start =  '0';
if (isset($_REQUEST['show_start'])) $show_start = '1';
$show_final =  '0';
if (isset($_REQUEST['show_final'])) $show_final = '1';
$multiple_chances =  '0';
if (isset($_REQUEST['multiple_chances'])) $multiple_chances = '1';
$random_questions =  '0';
if (isset($_REQUEST['random_questions'])) $random_questions = '1';
$random_answers =  '0';
if (isset($_REQUEST['random_answers'])) $random_answers = '1';
		
if(isset($_REQUEST['submit'])) {
	if($_REQUEST['action'] == 'edit') { //Update goes here

		$wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}mtouchquiz_quiz SET name=%s, description=%s,final_screen=%s,answer_mode=%s,single_page=%s, show_hints=%s, show_start=%s, show_final=%s, multiple_chances=%s, random_questions=%s, random_answers=%s, form_code=%s, time_limit=%s WHERE ID=%d", sanitize_text_field(wp_unslash($_REQUEST['name'])), wp_kses_post(wp_unslash($_REQUEST['description'])), wp_kses_post(wp_unslash($_REQUEST['content'])), $answer_mode, $single_page, $show_hints, $show_start, $show_final,$multiple_chances, $random_questions, $random_answers,wp_kses_post(wp_unslash($_REQUEST['gravity'])), absint($_REQUEST['mtq_timer']),absint($_REQUEST['quiz'])));
		
		wp_redirect($wpframe_home . '/wp-admin/admin.php?page=mtouch-quiz/quiz.php&message=updated');
	
	//Yes, we need 2 different counters - the $counter will skip over empty answers - $sort_order_counter will not.
	$counter = 1;
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mtouchquiz_ratings WHERE quiz_id=%d", absint($_REQUEST['quiz'])));
	foreach ($_REQUEST['score_rating'] as $score_rating) {
		if(trim($score_rating) != "") {
			$min_points = $_REQUEST['min_points'][$counter-1];
			if (is_numeric($min_points) ){
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}mtouchquiz_ratings(quiz_id,score_rating,min_points) 
				VALUES(%s, %s, %d)", $_REQUEST['quiz'], wp_kses_post(wp_unslash($score_rating)), absint($min_points))); 
			}
		}
		$counter++;
	}
	
	
	
	
	} else {
		$wpdb->get_results($wpdb->prepare("INSERT INTO {$wpdb->prefix}mtouchquiz_quiz(name,description,final_screen, answer_mode, single_page, show_hints, show_start, show_final, multiple_chances, random_questions, random_answers, form_code, added_on) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())", sanitize_text_field(wp_unslash($_REQUEST['name'])), wp_kses_post(wp_unslash($_REQUEST['description'])), wp_kses_post(wp_unslash($_REQUEST['content'])), $answer_mode, $single_page, $show_hints, $show_start, $show_final,$multiple_chances, $random_questions, $random_answers,wp_kses_post(wp_unslash($_REQUEST['gravity'])), absint($_REQUEST['mtq_timer'])));
		$quiz_id = $wpdb->insert_id;
		$counter = 1;
		foreach ($_REQUEST['score_rating'] as $score_rating) {
			if($score_rating) {
				$min_points = $_REQUEST['min_points'][$counter-1];
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}mtouchquiz_ratings(quiz_id,score_rating,min_points) 
					VALUES(%s, %s, %d)", $quiz_id, wp_kses_post(wp_unslash($score_rating)), absint($min_points))); 
			}
			$counter++;
		}
		wp_redirect($wpframe_home . '/wp-admin/edit.php?page=mtouch-quiz/question.php&message=new_quiz&quiz='.$quiz_id);
	}
}
exit;
