jQuery(document).ready(function($) { 
	$("#vote_up").click(function(e) {
		e.preventDefault();
		$.post('/wp-content/plugins/rate-my-whatever/rate.php', {vote_type: "1", post_id: $("#post_id").val()}, function(data) {
			$("#vote_succ").html(data);
			$.post('/wp-content/plugins/rate-my-whatever/rate.php', {action: "getPercentage", vote_type: "1", post_id: $("#post_id").val()}, function(data2) {
				$("#up_perc").html(data2);
			});
			$.post('/wp-content/plugins/rate-my-whatever/rate.php', {action: "getPercentage", vote_type: "2", post_id: $("#post_id").val()}, function(data3) {
				$("#down_perc").html(data3);
			});
		});
	});
	$("#vote_down").click(function(e) {
		e.preventDefault();
		$.post('/wp-content/plugins/rate-my-whatever/rate.php', {vote_type: "2", post_id: $("#post_id").val()}, function(data) {
			$("#vote_succ").html(data);
			$.post('/wp-content/plugins/rate-my-whatever/rate.php', {action: "getPercentage", vote_type: "1", post_id: $("#post_id").val()}, function(data2) {
				$("#up_perc").html(data2);
			});
			$.post('/wp-content/plugins/rate-my-whatever/rate.php', {action: "getPercentage", vote_type: "2", post_id: $("#post_id").val()}, function(data3) {
				$("#down_perc").html(data3);
			});
		});
	});
});