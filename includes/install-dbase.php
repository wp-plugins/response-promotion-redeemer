<?php
/******************************
* install db
******************************/

// run the install scripts upon plugin activation
class rpr_promo_table {
     static function rpr_install($post_ID) {
	   global $wpdb;
	   global $post;
	   global $rpr_db_version;
	   global $table_name;
	   $table_name .= '_'.$post->ID;
		$data_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name" ) );
	   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) { $sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time_entered datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  our_code tinytext NOT NULL,
		  partner_code text NOT NULL,
		  user_email VARCHAR(55) DEFAULT 'Not Used',
		  user_name VARCHAR(55) DEFAULT 'Not Used',
		  UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		   dbDelta($sql);
		 
		   add_option("rpr_db_version", $rpr_db_version);
	   } 
	   if ($wpdb->get_var("SHOW TABLES LIKE '$table_name';") == $table_name && $data_count <= "0" ) {
			$ptype = get_post_meta( $post->ID, 'ptype', true );
			$thefile = get_post_meta($post->ID, 'post_media', true);
			
			$myfileurl = $thefile['url'];
			$fileName = basename($myfileurl);
			
			$row = 1;
			if (($handle = fopen($myfileurl, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$num = count($data);
					$our_code;
					$partner_code;
					if($row > 1) {
						//echo "<p> $num fields in line $row: <br /></p>\n";
						for ($c=0; $c < $num; $c++) {
							//echo $data[$c] . "_".$c."<br />\n";
							if ($c == 0) { $our_code = $data[$c]; }
							else if ($c == 1) { $partner_code = $data[$c]; }
						}
						//$rows_afected = $wpdb->insert( $table_name, array('partner_type' => 'test','time_entered' => current_time('mysql'), 'redirect_url' => 'test', 'our_code' => 'test', 'partner_code' => 'test' ) );
					
					   global $wpdb;
					   global $table_name;
					   $rows_affected = $wpdb->insert( $table_name, array('time_entered' => current_time('mysql'), 'our_code' => $our_code, 'partner_code' => $partner_code) );
					}
					$row++;
				}
				fclose($handle);
			}
				
	   }
	   
	}
}


add_action( 'edit_post', array('rpr_promo_table', 'rpr_install') );