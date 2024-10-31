<?php
require_once('../../../wp-blog-header.php');
header('HTTP/1.1 200 OK');

if(isset($_POST['action']) && $_POST['action'] == "getPercentage") {
	$vote_type = mysql_real_escape_string($_POST['vote_type']);
	$post_id = mysql_real_escape_string($_POST['post_id']);

	echo get_percentage($vote_type, $post_id) . "%";
	die();
} else {
	//$redir = $_GET['redir'];
	$id = $_POST['post_id'];
	$vote = $_POST['vote_type'];

	if(!is_numeric($id) || !is_numeric($vote)) {
		echo "Vote not registered.";
		die();
	}

	global $wpdb;
	$numrows = $wpdb->query("SELECT vote_ip FROM wp_ratemywe WHERE vote_ip = '" . $_SERVER['REMOTE_ADDR'] . "' AND post_id='" . $id . "'", 'ARRAY_A');

	if($numrows > 0) {
		//Person has already voted on this post. redirect

		echo "You have already voted.";
		die();
	} else {
		//Insert vote
		$wpdb->query("INSERT INTO wp_ratemywe (post_id, vote_type, vote_ip) VALUES('$id', '$vote', '" . $_SERVER['REMOTE_ADDR'] . "')");
	}
	//Redirect
	echo "Vote successful.";
}
?>