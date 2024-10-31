<?php
/*
	Plugin Name: Rate My Whatever
	Plugin URI: 
	Description: Adds the option to rate posts.
	Author: Henric Johansson, henric-johansson@hotmail.com
	Version: 0.2-Beta
	Author URI: http://keklabprogramming.com
	Text Domain: rate-my-whatever
	Domain Path: /

    Copyright 2011  Henric Johansson  (email : henric-johansson@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

#To allow this to be as extensible as possible, make sure $table_prefix is globalised, we also need the $wpdb class functions too
global $table_prefix, $wpdb;
#Create the 'name' of our table which is prefixed by the standard WP table prefix (which you specified when you installed WP)
$wp_rate_my_we = $table_prefix . "ratemywe";
#Check to see if the table exists already, if not, then create it
if($wpdb->get_var("show tables like '$wp_rate_my_we'") != $wp_rate_my_we) {
	$cTable = "CREATE TABLE  `$wp_rate_my_we` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`post_id` INT NOT NULL ,
			`vote_type` INT NOT NULL ,
			`vote_ip` VARCHAR( 48 ) NOT NULL
			) ENGINE = INNODB;";
	#We need to include this file so we have access to the dbDelta function below (which is used to create the table)
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($cTable);
}

function get_percentage($type, $id)
{
	global $wpdb;

	$numrows = $wpdb->query("SELECT vote_ip FROM wp_ratemywe WHERE post_id='" . $id . "'", 'ARRAY_A');

	if($numrows > 0) {
		$row = $wpdb->get_results("SELECT COUNT(post_id) as nums FROM wp_ratemywe WHERE post_id = '" . $id . "' AND vote_type='1'", 'ARRAY_A');
		
		$up_votes = $row[0]['nums'];

		$row = $wpdb->get_results("SELECT COUNT(post_id) as nums FROM wp_ratemywe WHERE post_id = '" . $id . "' AND vote_type='2'", 'ARRAY_A');
		
		$down_votes = $row[0]['nums'];

		$down_procentage = ($down_votes/($down_votes + $up_votes)) * 100;

		$up_procentage = ($up_votes/($down_votes + $up_votes)) * 100;

		if($type==1) {
			return (int)$up_procentage;
		} else {
			return (int)$down_procentage;
		}
	}

	return 0;
}

function rate_my_options_page()
{
	$thumb_up = get_option('rate_my_thumb_up');
	$thumb_down = get_option('rate_my_thumb_down');
	$border = get_option('rate_my_border');
	if(isset($_POST['rem'])) {
		//If the user engaged a reset
		global $wpdb, $table_prefix;
		$wp_rate_my_we = $table_prefix . "ratemywe";
		$wpdb->query("delete from $wp_rate_my_we");

		$deleted = true;
	}

	if(isset($_POST['thumb_up'])) {
		$thumb_up = mysql_real_escape_string($_POST['thumb_up']);
		update_option('rate_my_thumb_up', $thumb_up);
	}

	if(isset($_POST['thumb_down'])) {
		$thumb_down = mysql_real_escape_string($_POST['thumb_down']);
		update_option('rate_my_thumb_down', $thumb_down);
	}

	if(isset($_POST['border-text'])) {
		$border = mysql_real_escape_string($_POST['border-text']);
		update_option('rate_my_border', $border);
	}

	if(isset($_POST['vote_type_icon'])) {
		if($_POST['vote_type_icon'] == "text") {
			update_option('rate_my_icon_option', 0);
		}
	}
	if(isset($_POST['vote_type_icon'])) {
		if($_POST['vote_type_icon'] == "icon") {
			update_option('rate_my_icon_option', 1);
		}
	}

	if(isset($_POST['vote_up_text']) && $_POST['vote_up_text'] != "") {
		update_option('rate_my_up_text', $_POST['vote_up_text']);
	}
	if(isset($_POST['vote_down_text']) && $_POST['vote_down_text'] != "") {
		update_option('rate_my_down_text', $_POST['vote_down_text']);
	}

	$vote_type = get_option('rate_my_icon_option');


echo <<<END
<div class="wrap" >
	<h2>Rate My Whatever 0.2 Beta</h2>
			
	
	 <div id="mainblock" style="width:710px">
END;
if(isset($deleted) && $deleted) {
	echo "Votings deleted.<br />";
}
echo <<<END
		<div class="dbx-content">
		 	<form action="" method="post">
		 	<input type="hidden" name="rem" value="1" />
			<input type="submit" value="Reset Stats" />
			</form>
			<br /><br />
			<h3>Edit standard icons (thumb up and down, remember to upload the new images to: wp-content/plugins/rate-my-whatever/):</h3>
			<form action="" method="post">
		 	<input type="text" name="thumb_up" value="$thumb_up" /><br />
		 	<input type="text" name="thumb_down" value="$thumb_down" /><br />
			<input type="submit" value="Change Icons" />
			</form>
			<br /><br />
			<h3>Edit border around vote-div (CSS):</h3>
			<form action="" method="post">
			<textarea name="border-text" rows=5 cols=40>$border</textarea><br />
			<input type="submit" value="Change Border" />
			</form>
			<br /><br />
			<h3>Use text or icons:</h3>
			<form action="" method="post">
END;
if($vote_type==1) {
	//Vote type is icon
	echo '<input type="radio" name="vote_type_icon" value="icon" checked/> Use Icons<br /><br />';
	echo '<input type="radio" name="vote_type_icon" value="text"/> Use Text<br /><br />';
} else {
	echo '<input type="radio" name="vote_type_icon" value="icon"/> Use Icons<br /><br />';
	echo '<input type="radio" name="vote_type_icon" value="text" checked/> Use Text<br /><br />';
}
echo <<<END
			Vote Up Text:<br />
			<input type="text" name="vote_up_text" value="Vote Up" /><br />
			Vote Down Text:<br />
			<input type="text" name="vote_down_text" value="Vote Down" /><br />
			<input type="submit" value="Change Vote Type" />
			</form>
   		</div>
   	</div>


<h5>A Wordpress plugin written by <a href="http://www.keklabprogramming.com/">Henric Johansson</a></h5>
<br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="RYG6UC4V2E9SG">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

</div>
END;
}

function rate_my_install()
{
	if(!get_option('rate_my_thumb_up')) {
		add_option('rate_my_thumb_up', 'up.png');
	}
	if(!get_option('rate_my_thumb_down')) {
		add_option('rate_my_thumb_down', 'down.png');
	}
	if(!get_option('rate_my_border')) {
		add_option('rate_my_border', 'border: 1px solid;');
	}
	if(!get_option('rate_my_icon_option')) {
		add_option('rate_my_icon_option', '1');
	}
	if(!get_option('rate_my_up_text')) {
		add_option('rate_my_up_text', 'Vote up');
	}
	if(!get_option('rate_my_down_text')) {
		add_option('rate_my_down_text', 'Vote down');
	}
}

function rate_my_options()
{
	add_options_page('Rate My Whatever Options', 'Rate My Whatever', 8, __FILE__, 'rate_my_options_page');            
}

function fix_rate_tag($content)
{
	global $post;
	$dir = 'wp-content/plugins/rate-my-whatever/';
	$thumb_down = get_option('rate_my_thumb_down');
	$thumb_up = get_option('rate_my_thumb_up');
	$border = get_option('rate_my_border');
	$icon_option = get_option('rate_my_icon_option');

	if($icon_option == 1) {
		$content = str_replace("[ratemywe]", "<center><br /><div>
		<input type=\"hidden\" id=\"post_id\" value=\"" . $post->ID . "\" />
		<table style=\"$border padding-left: 10px;\">
			<tr><td style=\"width: 50px;\"><a id=\"vote_up\" href=\"#\"><img style=\"float: none; border: 0px; background: none;\" src=\"/{$dir}{$thumb_up}\" alt=\"Vote Up\"/></a></td><td><a id=\"vote_down\" href=\"#\"><img style=\"float: none; border: 0px; background: none;\" src=\"/{$dir}{$thumb_down}\" alt=\"Vote Down\"/></a></td></tr>
			<tr><td id=\"up_perc\">" . get_percentage(1, $post->ID) ."%</td><td id=\"down_perc\">" . get_percentage(2, $post->ID) . "%		</td></tr>
		</table>
		<div id=\"vote_succ\"></div>
		</div></center>", $content);
	} else if($icon_option == 0) {
		$vote_up_text = get_option('rate_my_up_text');
		$vote_down_text = get_option('rate_my_down_text');
		$content = str_replace("[ratemywe]", "<center><br /><div>
		<input type=\"hidden\" id=\"post_id\" value=\"" . $post->ID . "\" />
		<table style=\"$border padding-left: 10px;\">
			<tr><td style=\"width: 100px;\"><a id=\"vote_up\" href=\"#\">$vote_up_text</a></td><td><a id=\"vote_down\" href=\"#\">$vote_down_text</a></td></tr>
			<tr><td>" . get_percentage(1, $post->ID) ."%</td><td>" . get_percentage(2, $post->ID) . "%		</td></tr>
		</table>
		<div id=\"vote_succ\"></div>
		</div></center>", $content);
	}
	
	return $content;
}

function rate_my_script()
{
   // register your script location, dependencies and version
   $x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
   wp_register_script('rate_my_js',
       $x . 'js/rate_my_js.js',
       array('jquery'),
       '1.0' );
   // enqueue the script
   wp_enqueue_script('rate_my_js');
}

add_filter('the_content', 'fix_rate_tag', 10);
add_action('admin_menu', 'rate_my_options');
add_action('plugins_loaded', 'rate_my_install');
add_action('wp_enqueue_scripts', 'rate_my_script');

?>