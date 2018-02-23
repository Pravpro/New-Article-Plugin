<?php 
/* This file handles all post and ajax requests made by the New Article form in the admin menu.
 */

 /* This file has a few lines of code and a function that has been taken from the WP Pubmed Reflist
  * Plugin which has a GPL2 license.
  * The areas will be surronded by 'borrowed' indication.
  */

require_once(ABSPATH . "/wp-load.php");

// $remote_method = '';

// Function not being used currently
function set_remote_method() {
	// borrowed start
	if( ini_get('allow_url_fopen') ) {
		$remote_method = 'allow_url_fopen';
	} elseif(function_exists('curl_version')){
		$remote_method = 'curl';
	}
	// borrowed end

}

// Hooks for Post request
add_action( 'admin_post_nopriv_new_article_form', 'create_new_article_entry');
add_action( 'admin_post_new_article_form', 'create_new_article_entry' );
// Hooks for Ajax call
add_action("wp_ajax_pmid_autofill", "retrieve_article_info");
add_action("wp_ajax_nopriv_pmid_autofill", "retrieve_article_info");


/* Creates a new Article entry in the admin Database (updating the respective tables)
 */
function create_new_article_entry(){
	global $wpdb;
	
    $keys = array("Article_Affiliation", "Article_Authors", "Article_Date", "Article_Title", 
				  "Article_Pagination", "Article_URL", "Journal_Day", "Journal_Date", 
				  "Journal_Citation", "Journal_Abbreviation", "Date_Revised", "Date_Created", 
				  "Date_Completed", "Journal_Issue", "Journal_Month","Journal_Title", "Journal_Volume", 
				  "Journal_Year", "PMID", "Article_Abstract");
	
//1. Create Post
	$category_id = retrieve_category_id("Pubmed");
	$postType = 'post';
	$userID = '1';
	$postStatus = 'publish';
	
	$leadTitle = $_POST['Article_Title'];
	$leadContent = "<p>" . $_POST['Article_Authors'] . "</p>";
	$leadContent .= "<p>" . $_POST['Journal_Citation'] . "</p>";
	$leadContent .= "<p>PMID: <a href = '" . $_POST['Article_URL'] . "'</p>";
	$leadContent .= "<h2> Abstract </h2>";
	$leadContent .= "<p> <p>" . $_POST['Article_Abstract'] . "</p> </p>";

	// Array with necessary information for creating a new Article Post 
	$new_post = array(
		'post_title' => $leadTitle,
		'post_content' => $leadContent,
		'post_status' => $postStatus,
		'post_author' => $userID,
		'post_type' => $postType,
		'post_category' => array($category_id),
		'tags_input' => array($_POST['Tag'])
	);

	/*The wordpress post function */
	$post_id = wp_insert_post($new_post);
	
//2. Fill in the Post information in Post Meta
    //assign each_key to meta_key and meta_value
    foreach($keys as $each_key){
    $key = str_replace('_', ' ', $each_key);
        $wpdb ->insert(
            $wpdb->postmeta,
             array(
                'post_id' => $post_id,
                 'meta_key' => $key,
                'meta_value' => $_POST[$each_key]
            )
        );
    }
}


/* Retrieves the id of the category named $name. The id is the term_id of that category.
 */
function retrieve_category_id($name) {
	global $wpdb;
	
	// Query the term_id of the category needed
	$query = "SELECT wpt.term_id FROM `wp_terms` AS wpt"
    		. " JOIN `wp_term_taxonomy` AS wptt"
    		. "		ON wpt.term_id = wptt.term_id"
    		. " WHERE wptt.taxonomy = 'category' AND name = %s";
	$sql = $wpdb->prepare($query, $name);
	$id = $wpdb->get_results($sql)[0]->term_id;
	return $id;
	
}


/* Retrieves all information using an ncbi api for Pubmed articles.
 */
function retrieve_article_info() {
	$pmid = intval( $_POST['pubmedID'] );
	$url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=".$pmid."&retmode=xml";
	
	$encoded_url = urlencode($url);
	$xml = simplexml_load_file($encoded_url);
	
	$article = $xml->PubmedArticle;
	
	$p = new PMIDeFetch($article);
	$citation = $p->citation();
	
	// Encode associative array as a JSON object to be read in JavaScript
	$citationJSON = json_encode($citation);
	
	// Echo back an array of all Article information
	echo $citationJSON;
	wp_die();
}


// borrowed function start (Not being used currently)
function fetchXML($url){
	set_remote_method();
	$remote_method = 'curl';
	switch($remote_method){
		case 'allow_url_fopen':
			$encoded_url = urlencode($url);
			$xml = simplexml_load_file($encoded_url);
			break;
		case 'curl':
			$ch = curl_init($url);    
			curl_setopt  ($ch, CURLOPT_HEADER, false); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
			$string = curl_exec($ch);
			$xml = simplexml_load_string($string);
			break;
	}		
	return $xml;
}// borrowed function end


?>