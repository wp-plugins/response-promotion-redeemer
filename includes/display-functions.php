<?php

/*our display functions for outputting information*/

function rpr_add_content($content) {
	
	$extra_content = '';
	$content .= $extra_content;
	return $content;
	
}
add_filter('the_content', 'rpr_add_content');