<?php
 /*
   Plugin Name: New Article
   Description: A plugin to create article publication entries
   Version: 1.0
   Author: Pravir Adlakha and Warren Chisasa
   License: GPL2
   */

 /* This plugin creates a new menu page.
  * This plugin allows for the addition of New Article Entries for faculty members in a department. Articles 
  * Posts will be added to the admin databse through the posting of the form using this plugin.
  */

// Action to create a new side menu Option
add_action('admin_menu', 'article_plugin_setup_menu');

// Action to load js scripts
add_action('wp_enqueue_scripts', 'prepare_scripts');

// Add the files of this plugin to the plugin directory
if ( ! defined( 'NEW_ARTICLE_PLUGIN_DIR' ) ) {
	define( 'NEW_ARTICLE_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );	
}
# include the eFetch parser class
include_once(NEW_ARTICLE_PLUGIN_DIR."/class.PMIDeFetch.php");
# include the file to handle article form post and ajax requests
include_once(NEW_ARTICLE_PLUGIN_DIR."/postArticle.php");

/* Add the New Article Form page to the side menu
 */
function article_plugin_setup_menu(){
	add_menu_page( 'New Article Page', 'New Article', 'manage_options', 'faculty-plugin', 'create_form' );
	}

/* Connect the ajax javascript script to this plugin
 */
function prepare_scripts() {
	wp_register_script('ajax_autofill', plugin_dir_url(__FILE__) . "js/autofill_ajax.js", array('jquery'));
	wp_enqueue_script('ajax_autofill');
}



/* Prepare HTML opiton tag inputs for all the post tags in database.
 * Return the string corresponding to this HTML code.
 */
function wp_post_tag_names(){
 	global $wpdb;
 	
	// Query all post tag names from database 
	$query = $sql = "SELECT `name`"
					." FROM `wp_terms` AS wpt"
					." JOIN `wp_term_taxonomy` AS wptt"
					."	  ON wpt.term_id = wptt.term_id"
					." WHERE"
					."	  wptt.taxonomy = 'post_tag'";
 	$get_tags_array = $wpdb->get_results($query);
	
	// Extract name of the post tags
	foreach($get_tags_array as $row => $tag_name) {
		$name = $tag_name->{'name'};
		//place the names in an array
		$tags_array[] = $name;
	}
	
	//Sort the names in the array and place the names in the HTML tag
	sort($tags_array);
	$tags_html = "";
	
	foreach($tags_array as $name){
		$tags_html.= "<option value='" . $name . "'>". str_replace("_", " ", $name) ."</option>";
	}
	
	return $tags_html;
}

/* Creates the contents of the New Article Page
 */
function create_form(){
	
	$table ="<html>";
	$table.="<head>";
	$table.="	<title>New Article Entry Page</title>";
	$table.="</head>";
	
	// URL for the wordpress post handling page
	$link_admin_post = admin_url('admin-post.php');
	$table.="<form name = 'myform' method=\"POST\" action='" . $link_admin_post . "' id = \"form1\">";

	// Input for Faculty member
	$table.= "	<br>";
	$table.= "  <div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Tag\">Faculty Member/Tag: </label></p>";
	$list_of_names = wp_post_tag_names();
	$table.="	<select name=\"Tag\">" . $list_of_names . "</select>";
	$table.="	</div>";
	
	// Input for PMID
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"PMID\">PMID*: </label></p>";
	$table.="	<input type=\"number\" name=\"PMID\" id=\"PMID\" required>";
	
	//autofill button
	$table.='   <a class="pmid" data-test="hi"><button id="autofill_btn" type="button" >Auto-Fill</button></a>';
	$table.="	</div>";
		
	// Input for Journal Issue
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label style=\"width: 200px;\" for=\"Journal_Issue\">Journal Issue: </label></p>";
	$table.="	<input id = 'issue' type=\"number\" name=\"Journal_Issue\">";
	$table.="	</div>";
	
	// Input for Journal Volume
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Volume\">Journal Volume: </label></p>";
	$table.="	<input id = 'journal_volume' type=\"number\" name=\"Journal_Volume\">";
	$table.="	</div>";
	
	// Input for Journal Title
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Title\">Journal Title: </label></p>";
	$table.="	<input id = 'journal_title' type=\"text\" name=\"Journal_Title\">";
	$table.="	</div>";
	
	// Input for Journal Year
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Year\">Journal Year: </label></p>";
	$table.="	<input id = 'year' value=\"2000\" type=\"number\" name=\"Journal_Year\">";
	$table.="	</div>";
	
	// Input for Journal Month
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Month\">Journal Month: </label></p>";
	$table.="	<select id='journal_month' name=\"Journal Month\">";
	$table.="		<option value=\"01\">Jan</option>";
	$table.="		<option value=\"02\">Feb</option>";
	$table.="		<option value=\"03\">Mar</option>";
	$table.="		<option value=\"04\">Apr</option>";
	$table.="		<option value=\"05\">May</option>";
	$table.="		<option value=\"06\">Jun</option>";
	$table.="		<option value=\"07\">Jul</option>";
	$table.="		<option value=\"08\">Aug</option>";
	$table.="		<option value=\"09\">Sep</option>";
	$table.="		<option value=\"10\">Oct</option>";
	$table.="		<option value=\"11\">Nov</option>";
	$table.="		<option value=\"12\">Dec</option>";
	$table.="	</select>";
	$table.="	</div>";
	
	// Input for Journal Day
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Day\">Journal Day: </label></p>";
	$table.="	<input id = 'journal_day' type=\"text\" name=\"Journal_Day\">";
	$table.="	</div>";
	
	// Input for Journal Date
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Date\">Journal Date: </label></p>";
	$table.="	<input id = 'journal_date' placeholder=\"YYYY-MM-DD\" type=\"text\" name=\"Journal_Date\">";
	$table.="	</div>";
	
	// Input for Journal Abbreviation
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Abbreviation\">Journal Abbreviation: </label></p>";
	$table.="	<input id = 'journal_ab' type=\"text\" name=\"Journal_Abbreviation\">";
	$table.="	</div>";

	// Input for Journal Citation
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Journal_Citation\">Journal Citation: </label></p>";
	$table.="	<input id = 'journal_citation' type=\"text\" name=\"Journal_Citation\">";
	$table.="	</div>";
	
	// Input for Article Title
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Article_Title\">Article Title: </label></p>";
	$table.="	<input id=\"article_title\" type=\"text\" name=\"Article_Title\">";
	$table.="	</div>";
	
	// Input for article abstract
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Article_Abstract\">Article Abstract: </label></p>";
	$table.="   <textarea id='abstract' rows = '4' cols = '60' name=\"Article_Abstract\"></textarea>";
	$table.="	</div>";
	
	// Input for Article URL
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Article_URL\">Article URL: </label></p>";
	$table.="	<input id = 'article_url' type=\"text\" name=\"Article_URL\">";
	$table.="	</div>";
	
	// Input for Article Pagination
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Article_Pagination\">Article Pagination: </label></p>";
	$table.="	<input id='pages' type=\"text\" name=\"Article_Pagination\">";
	$table.="	</div>";
	
	// Input for Article Date
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Article_Date\">Article Date: </label></p>";
	$table.="	<input id='article_date' placeholder=\"YYYY-MM-DD\" type=\"text\" name=\"Article_Date\">";
	$table.="	</div>";
	
	// Input for Article Authors
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Article_Authors\">Article Authors: </label></p>";
	$table.="	<input id='article_authors' type=\"text\" name=\"Article_Authors\">";
	$table.="	</div>";
	
	// Input for Article Affiliations
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Article_Affiliation\">Article Affiliation: </label></p>";
	$table.="	<input id = 'article_affiliation' type=\"text\" name=\"Article_Affiliation\">";
	$table.="	</div>";
	
	// Input for Date Created
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Date_Created\">Date Created: </label></p>";
	$table.="	<input id='date_created' placeholder=\"YYYY-MM-DD\" type=\"text\" name=\"Date_Created\">";
	$table.="	</div>";
	
	// Input for Date Completed
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Date_Completed\">Date Completed: </label></p>";
	$table.="	<input id='date_completed' placeholder=\"YYYY-MM-DD\" type=\"text\" name=\"Date_Completed\">";
	$table.="	</div>";

	// Input for Date Revised
	$table.="	<br>";
	$table.="	<div class=\"input\">";
	$table.="	<p class=\"label\"><label for=\"Date_Revised\">Date Revised: </label></p>";
	$table.="	<input id='date_revised' placeholder=\"YYYY-MM-DD\" type=\"text\" name=\"Date_Revised\">";
	$table.="	</div>";
	
	// Hidden input (for specifying hook)
	$table.="	<input type=\"hidden\" name=\"action\" value=\"new_article_form\">";
	
	// Submit and reset buttons
	$table.="	<br>";
	$table.="	<div id=\"form_btns\">"; 
	$table.="	<button class=\"form_btn\"><input type=\"reset\" value=\"Reset!\" onclick=\"window.location.reload()\"></button>";
	$table.="	<input class=\"form_btn\" type=\"submit\" value=\"Submit\">"; 
	$table.="	</div>";
	$table.="</form>";

	// Styling of the form (needs to be put in a seperate stylesheet)
	$table.="<style type=\"text/css\">";
	$table.="	#form_btns {";
	$table.="		margin-left: 150px;";
	$table.="	}";
	$table.="	.form_btn {";
	$table.="		width: 80px;";
	$table.="	}";
	$table.="	.label {";
	$table.="		width: 150px;";
	$table.="		margin: 0;";
	$table.="		float: left;";
	$table.="	}";
	$table.="	input::placeholder {";
	$table.="		color: #a4a4a4;";
	$table.="	}";
	$table.="</style>";
	
	$table.="<script type='text/javascript' src=" . plugin_dir_url(__FILE__) . "js/autofill_ajax.js></script>";

  	echo $table;
}
?>