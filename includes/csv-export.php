<?php 
	/******************************
	* export data as csv
	******************************/
	define('RM_PLUGIN_SLUG', 'response-promo-redeemer');
	define('RM_PLUGIN_DIR', WP_PLUGIN_URL.'/'.RM_PLUGIN_SLUG.'/');	
	define('RM_SITE', 'thepowertoprovoke.com');
	
	if(!function_exists('fn_rm_csv_export')){
		function fn_rm_csv_export(){        
			include_once(RM_PLUGIN_DIR.'/includes/rm-csv-generate.php');
			$req_table = isset($_REQUEST['rmcsv']) ? $_REQUEST['rmcsv'] : '';
			if ($req_table){          
				echo fn_rm_csv_gen($req_table);
				exit;
			}
		}
	}
	add_action('init', 'fn_rm_csv_export');