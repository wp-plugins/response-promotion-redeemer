<?php

/******************************
* data processing
******************************/

// this function saves the data
function rpr_save_data() {
	// save data here
}
function rpr_partner_install_codes() {
   global $wpdb;
   global $rpr_partner_table;
   $welcome_name = "Mr. WordPress";
   $welcome_text = "one";
   $partner_url = "test";

   $rows_affected = $wpdb->insert( $rpr_partner_table, array( 'time' => current_time('mysql'), 'partner_name' => $welcome_name, 'partner_type' => $welcome_text, 'partner_url' => $partner_url ) );
}
//register_activation_hook(__FILE__,'rpr_partner_install_data');