<?php

/**
*	Method to add Facebook reviews
*
*	@return The HTML code
*/
function onfbr_add_review($args){
	global $onfbr_options;

	$pull_args = shortcode_atts( array(
        'orientation' => 'horizontal',
        'min_stars' => '3',
        'number_of_reviews' => '4',
        'remove' => ''
    ), $args );

	$page_id = '';
	if(!empty($onfbr_options['general']['facebook_url'])){
		$page_id = $onfbr_options['general']['facebook_url'];
	}

	$access_token = '';
	if(!empty($onfbr_options['general']['access_token'])){
		$access_token = $onfbr_options['general']['access_token'];
	}

	$facebook_return_value = file_get_contents('https://graph.facebook.com/'.$page_id.'/ratings?access_token='.$access_token);

	if(substr_count($facebook_return_value, "review_text") >= 1){
		// Return value is containing a valid result
		//echo 'true';
	} else {
		// Return value is probably containing an error
		//echo 'false';
	}

	$facebook_review_obj = json_decode($facebook_return_value, true);

	// Init array containing all the Facebook Review objects
	$facebook_reviews = array();

	for($x = 0; $x < sizeof($facebook_review_obj['data']); $x++){

		$facebook_reviews[$x] = new Facebook_Review(
			$facebook_review_obj['data'][$x]['reviewer']['name'],
			$facebook_review_obj['data'][$x]['reviewer']['id'],
			(!empty($facebook_review_obj['data'][$x]['review_text']) ? $facebook_review_obj['data'][$x]['review_text'] : ''),
			$facebook_review_obj['data'][$x]['rating'],
			$facebook_review_obj['data'][$x]['created_time'],
			$page_id
		);

	}

	// LÄGGA TILL ATT DEN FORCAR VERTICAL OM NAMNET ÄR EXTREMT LÅNGT
	$remove_string = str_replace(" ", "", wp_kses_post($pull_args['remove']));
	$remove_array = explode(",", $remove_string);

	return create_output($facebook_reviews, wp_kses_post($pull_args['orientation']), wp_kses_post($pull_args['min_stars']), wp_kses_post($pull_args['number_of_reviews']), $remove_array);
}

/**
*	Facebook Reviews data structure
*
*/
class Facebook_Review {
	/* Member variables */
	var $name;
	var $id;
	var $review;
	var $rating;
	var $image_url;
	var $date;
	var $time_elapsed;
	var $stars = '';
	var $stars_icon;
	var $page_id;

	/**
	*	Constructor for a Facebook Review object
	*
	*	@param name, id, review, rating and date
	*/
	function __construct($name, $id, $review, $rating, $date, $page_id) {

		$this->name = $name;
		$this->id = $id;
		$this->review = $review;
		
		$this->image_url = 'http://graph.facebook.com/'.$id.'/picture';

		$this->date = $date;
		$this->time_elapsed = time_elapsed_string($this->date);

		$this->rating = $rating;
		$this->set_stars($this->stars);

		$this->page_id = $page_id;
		
	}

	/* Member functions */
	function set_stars($rating){
		for ($x = 0; $x < $this->rating; $x++) {
	    	$this->stars .= '&#x2605;';
		}
	}

	function get_name(){
		return $this->name;
	}

	function get_id(){
		return $this->id;
	}

	function get_rating(){
		return $this->rating;
	}

	function get_stars(){
		return $this->stars;
	}

	function get_image_url(){
		return $this->image_url;
	}

	function get_review(){
		return $this->review;
	}

	function get_date(){
		return $this->date;
	}

	function get_time_elapsed(){
		return $this->time_elapsed;
	}

	function get_page_id(){
		return $this->page_id;
	}

	function set_name($name){
		$this->name = $name;
	}

	function set_review($text){
		$this->review = $text;
	}

	function set_rating($number) {
		$this->rating = $number;
		$this->stars = '';
		$this->set_stars($number);
	}

	// Help-function for debugging
	function echo_to_string(){
		echo '{[Facebook Review] Name: '.$this->get_name().', ID: '.$this->get_id().', Rating: '.$this->get_rating().
			 ', Stars: '.$this->get_stars().', Image: '.$this->get_image_url().', Review: '.$this->get_review().', Date: '.$this->get_date().', Time Elapsed: '.$this->get_time_elapsed().'}';
	}
	
}

// Help method to convert a date into time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'år',
        'm' => 'månad',
        'w' => 'vecka',
        'd' => 'dag',
        'h' => 'timme',
        'i' => 'minut',
        's' => 'sekund',
    );

    $string_plur = array(
        'y' => 'år',
        'm' => 'månader',
        'w' => 'veckor',
        'd' => 'dagar',
        'h' => 'timmar',
        'i' => 'minuter',
        's' => 'sekunder',
    );

    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . ($diff->$k > 1 ? $string_plur[$k] : $string[$k]);
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' sedan' : 'precis nyss';
}

function create_output($facebook_review_objects, $orientation, $min_stars, $number_of_reviews, $remove){

	// Remove all reviews under the min_stars, and all in the remove list and rearrange the array
	for($x = 0; $x < sizeof($facebook_review_objects); $x++){
		if($facebook_review_objects[$x]->get_rating() < $min_stars){
			unset($facebook_review_objects[$x]);
		}
		for($y = 0; $y < sizeof($remove); $y++){
			if($facebook_review_objects[$x]->get_id() == $remove[$y]){
				unset($facebook_review_objects[$x]);
				break;
			}
		}
	}

	$facebook_review_objects = array_values($facebook_review_objects);

	$amount = $number_of_reviews;
	if(sizeof($facebook_review_objects) < $amount){
		$amount = sizeof($facebook_review_objects);
	}


	// Orientation is specified as vertical. One review per row
	if($orientation == 'vertical'){
		// Init output
		// div to wrap every review
		$output = '<div class="onfbr-wrapper-v">';
		for($x = 0; $x < sizeof($facebook_review_objects); $x++){

			$facebook_review_object = $facebook_review_objects[$x];

			if($facebook_review_object->get_rating() >= $min_stars){
				// Build output

				// parent div for one review
				$output .= '<div class="onfbr-review">';
					// top div that contains image, name, stars, time ago
					$output .= '<div class="onfbr-top">';
						// left part of top div, contains image
						$output .= '<div class="onfbr-top-left">';
							$output .= '<a href="http://www.facebook.se/'.$facebook_review_object->get_id().'/" target="_blank"><img src="http://graph.facebook.com/'.$facebook_review_object->get_id().'/picture" alt="Image Not Found" class="onfbr-img"></a>';
						$output .= '</div>';
						// right part of top div, contains name, stars and time ago
						$output .= '<div class="onfbr-top-right">';
							$output .= '<a href="http://www.facebook.se/'.$facebook_review_object->get_id().'/" target="_blank"><span class="onfbr-name">'.$facebook_review_object->get_name().'</span></a><span class="onfbr-stars-parent"><span class="onfbr-stars-child"><span class="onfbr-stars-text">'.$facebook_review_object->get_stars().'</span></span></span>';
							$output .= '<div class="onfbr-time">'.$facebook_review_object->get_time_elapsed().'</div>';
						$output .= '</div>';
					$output .= '</div>';
					// bottom div, containing the content of the review
					$output .= '<div class="onfbr-content">';
						$output .= '<p>'.$facebook_review_object->get_review().'</p>';
					$output .= '</div>';
				$output .= '</div>';
			}
		}
		$output .= '</div>
					<div class="onfbr-more">
						<a href="http://www.facebook.se/'.$facebook_review_object->get_page_id().'/reviews?ref=page_internal" target="_blank"><span class="onfbr-more">Se fler omdömen här</span></a>
					</div>';
		return $output;
	}

	// Orientation is specified as horizontal. Several reviews per row
	if($orientation == 'horizontal'){
		// Init output
		// div to wrap every review
		$output = '<div class="onfbr-wrapper-h">';

		for($x = 0; $x < $amount; $x++){

			$facebook_review_object = $facebook_review_objects[$x];

			//if($facebook_review_object->get_rating() >= $min_stars){
				// Build output

			// parent div for one review
			$output .= '<div class="onfbr-review">';
				// top div that contains image, name, stars, time ago
				$output .= '<div class="onfbr-top">';
					// left part of top div, contains image
					$output .= '<div class="onfbr-top-left">';
						$output .= '<a href="http://www.facebook.se/'.$facebook_review_object->get_id().'/" target="_blank"><img src="http://graph.facebook.com/'.$facebook_review_object->get_id().'/picture" alt="Image Not Found" class="onfbr-img"></a>';
					$output .= '</div>';
					// right part of top div, contains name, stars and time ago
					$output .= '<div class="onfbr-top-right">';
						$output .= '<a href="http://www.facebook.se/'.$facebook_review_object->get_id().'/" target="_blank"><span class="onfbr-name">'.$facebook_review_object->get_name().'</span></a><span class="onfbr-stars-parent"><span class="onfbr-stars-child"><span class="onfbr-stars-text">'.$facebook_review_object->get_stars().'</span></span></span>';
						$output .= '<div class="onfbr-time">'.$facebook_review_object->get_time_elapsed().'</div>';
					$output .= '</div>';
				$output .= '</div>';
				// bottom div, containing the content of the review
				$output .= '<div class="onfbr-content">';
					$output .= '<p>'.$facebook_review_object->get_review().'</p>';
				$output .= '</div>';
			$output .= '</div>';
			//}
		}

		// Make an empty review if the number is odd
		if(!($amount % 2 == 0)){
			$output .= '<div class="onfbr-review">';
			$output .= '</div>';
		}
		
		$output .= '</div>
					<div class="onfbr-more">
						<a href="http://www.facebook.se/'.$facebook_review_object->get_page_id().'/reviews?ref=page_internal" target="_blank"><span class="onfbr-more">Se fler omdömen här</span></a>
					</div>';
		return $output;	
	}
}

add_shortcode( 'on-facebook-review', 'onfbr_add_review');