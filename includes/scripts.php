<?php

/******************************
* script control
******************************/

function rpr_load_scripts() {
	if(is_singular()) {
		wp_enqueue_style('rpr-styles', plugin_dir_url( __FILE__ ) . 'css/plugin_styles.css');
	}
}
add_action('wp_enqueue_scripts', 'rpr_load_scripts');
