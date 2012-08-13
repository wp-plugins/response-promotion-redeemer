<?php
/*
Plugin Name: Response Promo Redeemer
Plugin URI: http://wordpress.org/extend/plugins/response-promotion-redeemer/
Description: This plugin was developed to streamline the Promotion Code Redemption process for businesses. It will integrate with and external database for cross-domain inclusion of partner promotions, create a querystring redirect to partner sites or generate an email to the person redeeming a promotion code with instructions for the partner site. It allows you to re-skin each page with the apropriate artwork for an individualized partner relationship.
Version: 1.1.0
Author: Bryan Bielefeldt at Response Marketing in New Haven CT
Author URI: http://thepowertoprovoke.com
License: http://www.gnu.org/licenses/gpl-2.0.html


/******************************
* global variables
******************************/

$promo_prefix = 'promo_';
$promo_plugin_name = 'My First WordPress Plugin';



global $wpdb;
global $rpr_db_version;
$rpr_db_version = "1.0";
global $table_name;
$table_name = $wpdb->prefix . "rpr_Partner";
global $promo_options;
// retrieve our plugin settings from the options table
$promo_options = get_option('promo_settings');
/******************************
* includes
******************************/
include('includes/admin-page.php'); // the plugin options page HTML and save functions

if ($promo_options['enable'] == true) {
	include('includes/scripts.php'); // this controls all JS / CSS
	include('includes/display-functions.php'); // display content functions
	
	include('includes/csv-export.php'); // csv export function
	if ($promo_options['ctype'] == true) {
		include('includes/promo-ctype.php'); // add cutome content type promo
	}
	
	include('includes/form-shortcodes.php'); // add form and shorcode selector to wysiwyg for post tye pormo
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
			  our_code text NOT NULL,
			  partner_code text NOT NULL,
			  user_email VARCHAR(55) DEFAULT 'Not Used',
			  user_name VARCHAR(55) DEFAULT 'Not Used',
			  UNIQUE KEY id (id)
				);";
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			   dbDelta($sql);
			 
			   add_option("rpr_db_version", $rpr_db_version);
		   }
		   $thefile = get_post_meta($post->ID, 'post_media', true);
		   $myfileurl = $thefile;
		   if ($wpdb->get_var("SHOW TABLES LIKE '$table_name';") == $table_name && $data_count <= "0" && $myfileurl == true) {
				
				$ptype = get_post_meta( $post->ID, 'ptype', true );
				
				
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
						   $our_code = str_replace("1", "*%", $our_code, $count);
						   $our_code = str_replace("5", "/&*o", $our_code, $count);	
						   
						   $partner_code = str_replace("6", "*%", $partner_code, $count);
						   $partner_code = str_replace("2", "/&*o", $partner_code, $count);					
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
	//custom meta box
	 
	// Hook into WordPress
	add_action( 'admin_init', 'add_promo_metabox' );
	add_action( 'save_post', 'save_promo_url' );
	/******************************
	* add costom meta boxes
	******************************/
	function add_promo_metabox() {
		global $promo_options;
		if ($promo_options['ctype'] == true) {
		add_meta_box( 'promo-metabox', __( 'Partner Information' ), 'url_promo_metabox', 'promo', 'side', 'high' );
		add_meta_box( 'post_media', 'Add Promo.csv File', 'post_media', 'promo', 'side', 'high' );
		add_meta_box( 'short_code_opt', 'Form shordcodes', 'short_code_opt', 'promo', 'side', 'high' );
		add_meta_box( 'list_the_codes', 'Promo Codes and Related Users', 'list_the_codes', 'promo', 'normal', 'high' );
		} else if ($promo_options['ctype'] == false) {
		add_meta_box( 'promo-metabox', __( 'Partner Information' ), 'url_promo_metabox', 'page', 'side', 'high' );
		add_meta_box( 'post_media', 'Add Promo.csv File', 'post_media', 'page', 'side', 'high' );
		add_meta_box( 'short_code_opt', 'Form shordcodes', 'short_code_opt', 'page', 'side', 'high' );
		add_meta_box( 'list_the_codes', 'Promo Codes and Related Users', 'list_the_codes', 'page', 'normal', 'high' );
		}
	}
	 
	/******************************
	* display metaboxes
	******************************/
	function url_promo_metabox() {
		global $post;
		global $wpdb; 
		$urllink = get_post_meta( $post->ID, 'urllink', true );
		$ptype = get_post_meta( $post->ID, 'ptype', true );
		$from = get_post_meta( $post->ID, 'from', true );
		$redURL = get_post_meta( $post->ID, 'redURL', true );
		$conURL = get_post_meta( $post->ID, 'conURL', true );
		$conNAME = get_post_meta( $post->ID, 'conNAME', true );
		$conTNAME = get_post_meta( $post->ID, 'conTNAME', true );
		$conUSER = get_post_meta( $post->ID, 'conUSER', true );
		$conPASSWORD = get_post_meta( $post->ID, 'conPASSWORD', true );
		$conQSTRING = get_post_meta( $post->ID, 'conQSTRING', true );
		$queV1 = get_post_meta( $post->ID, 'queV1', true );
		$queV2 = get_post_meta( $post->ID, 'queV2', true );
		$queV3 = get_post_meta( $post->ID, 'queV3', true );
		
		if ( !preg_match( "/http(s?):\/\//", $urllink )) {
			$errors = '<span style="color:red; font-weight:bold;">Url not valid</span>';
			$urllink = 'http://';
		}
		if ( !preg_match( "/http(s?):\/\//", $redURL )) {
			$rederrors = '<span style="color:red; font-weight:bold;">Url not valid</span>';
			$redURL = 'http://';
		}
	 
		// output invlid url message and add the http:// to the input field
		if( $errors ) { echo $errors; } 
		$post_title = get_the_title();?>
	
		<p><label for="siteurl"><strong>Partner Portal Url:</strong><br />
			<input id="siteurl" style="width:95%;" name="siteurl" value="<?php if( $urllink ) { echo $urllink; } ?>" /></label></p>
		<p><label for="from"><strong>Send Emails From:</strong><br />
			<input id="from" style="width:95%;" name="from" value="<?php if( $from ) { echo $from; } ?>" /></label></p>
		<p><label for="new_partner_type"><strong>Partner Type:</strong><br />
			<?php $partner_type = array('Redirect'/*, 'Connect'*/, 'Query'); ?>
			<select name="new_partner_type" id="new_partner_type">
				<?php foreach($partner_type as $the_type) { ?>
					<?php if( $ptype && $ptype == $the_type ) { $selected = 'selected="selected"'; } else { $selected = ''; } ?>
					<option value="<?php echo $the_type; ?>" <?php echo $selected; ?>><?php echo $the_type; ?></option>
				<?php } ?>
			</select></p>
		<div id="redirectURL">
			<p>Please enter the url for the partner promotion redemption portal below.</p>
			<p><label for="redURL"><strong>Partner Redirect Url:</strong><br /><input id="redURL" style="width:95%;" name="redURL" value="<?php if( $redURL ) { echo $redURL; } ?>" /></label></p>
		</div>
		<div id="connectURL" style="display:none;">
			<p>Your have selected the partner type &lsquo;Connect&rsquo;, please enter the Database Host url, db name, db table name, db user, db password, and SQL query for the partner promotion redemption portal below.</p>
			<p><label for="conURL"><strong>Database Connection Url:</strong><br /><input id="conURL" style="width:95%;" name="conURL" value="<?php if( $conURL ) { echo $conURL; } ?>" /></label></p>
			<p><label for="conNAME"><strong>Database Name:</strong><br /><input id="conNAME" style="width:95%;" name="conNAME" value="<?php if( $conNAME ) { echo $conNAME; } ?>" /></label></p>
			<p><label for="conTNAME"><strong>Database Table Name:</strong><br /><input id="conTNAME" style="width:95%;" name="conTNAME" value="<?php if( $conTNAME ) { echo $conTNAME; } ?>" /></label></p>
			<p><label for="conUSER"><strong>Database User:</strong><br /><input id="conUSER" style="width:95%;" name="conUSER" value="<?php if( $conUSER ) { echo $conUSER; } ?>" /></label></p>
			<p><label for="conPASSWORD"><strong>Database Password:</strong><br /><input id="conPASSWORD" style="width:95%;" name="conPASSWORD" value="<?php if( $conPASSWORD ) { echo $conPASSWORD; } ?>" /></label></p>
            <p><label for="conQSTRING"><strong>Database SQL Query:</strong><br /><input id="conQSTRING" style="width:95%;" name="conQSTRING" value="<?php if( $conQSTRING ) { echo $conQSTRING; } ?>" /></label></p>
            <p>Example &ldquo;SELECT a_column FROM table_name WHERE condition_column = &lsquo;unused_variable&rsquo;&rdquo;<br /></p>
		</div>
		<div id="queryURL" style="display:none;">
			<p>Your have selected the partner type &lsquo;Query&rsquo;, please enter the partner Query variables below</p>
			<p><label for="queV1"><strong>Name Variable:</strong><br /><input id="queV1" style="width:95%;" name="queV1" value="<?php if( $queV1 ) { echo $queV1; } ?>" /></label></p>
            <p><label for="queV2"><strong>Email Variable:</strong><br /><input id="queV2" style="width:95%;" name="queV2" value="<?php if( $queV2 ) { echo $queV2; } ?>" /></label></p>
            <p><label for="queV3"><strong>Partner Code Variable:</strong><br /><input id="queV3" style="width:95%;" name="queV3" value="<?php if( $queV3 ) { echo $queV3; } ?>" /></label></p>
		</div>
			<script type="text/javascript">
				var testing = jQuery("select").val();
				function displayVals() {
					  var singleValues = jQuery("select#new_partner_type").val();
					  var multipleValues = jQuery("select#new_partner_type").val() || [];
					  if (singleValues == 'Connect') {
						  jQuery('#connectURL').slideDown();
					  } else if (singleValues != 'Connect') {
						  jQuery('#connectURL').slideUp();
					  } 
					  if (singleValues == 'Query') {
						  jQuery('#queryURL').slideDown();
					  } else if (singleValues != 'Query') {
						  jQuery('#queryURL').slideUp();
					  }
						  
				}
			
				jQuery("select#new_partner_type").change(displayVals);
				displayVals();
			</script>
		<?php 
		if ($ptype) {
			
		} 
		
	}
//file upload
function post_media() {
 			
    		global $post;
			global $wpdb;
			 
					global $table_name;
					$table_name .= '_'.$post->ID;
					$dirNAME = plugin_dir_url( __FILE__ );
					$thefile = get_post_meta($post->ID, 'post_media', true);
					$redURL = get_post_meta( $post->ID, 'redURL', true );
					$ptype = get_post_meta( $post->ID, 'ptype', true );
						if ($thefile) {
							$myfileurl = $thefile;
							$fileName = basename($myfileurl);
							echo '<a href="tools.php?page=export&rmcsv='.$table_name.'" target="_blank" ><img src="'. $dirNAME . 'includes/images/Excel2007Logo.gif" height="40px" style="float:left; margin-right:10px;"></a><div style="font-size:15px; font-weight:bold;padding:10px 0px;">'.$table_name.'.csv</div><div style="clear:both;"></div>';
							
							
							$data_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name" ) );
							if ($data_count < 1) {
								echo "<p>records found: <strong>{$data_count}</strong></p>";
								echo "<p>click &ldquo;<strong style='color:#298CBA;'>Update</strong>&rdquo; below to populate the promotion codes.</p>";
								echo '<input type="hidden" id="post_media" style="width:95%;" name="post_media" value="'.$thefile.'" />';
							} else {
								echo "<p>records found: <strong>{$data_count}</strong></p>";
								echo "<p>click the <strong style='color:#217F36;'>Excel</strong> icon to download the most current data.</p>";
								echo '<input type="hidden" id="post_media" style="width:95%;" name="post_media" value="'.$thefile.'" />';
							}
							
						} else {
							$view = '<label for="post_media"><strong>.CSV file url:</strong><br /><input id="post_media" style="width:95%;" name="post_media" value="';
							if( $thefile ) { $view .= $thefile; }
                            $view .= '" /></label>';
							echo $view;
							echo 'Please upload a .CSV file with the add media button and thecopy the file url and past it here. Not sure what the .csv should look like... <a href="' . $dirNAME . '/includes/Template.csv" target="_blank" >Download a template here </a>';
						}
			 
} // end post_media
function short_code_opt() {
 			
    		?><div id="shortside">
			<p>Copy and pase the below shortcode into your post where you would like the form to be displayed.</p>
			<p><strong>[promo-form]</strong></p>
            <p>The form will include name, email address, and promotion code fields that the user will use to enter their infromation.</p>
		</div><?
			 
} // end short_code_opt
add_filter('upload_mimes', 'custom_upload_mimes');
	function custom_upload_mimes ( $existing_mimes=array() ) {

	// Add file extension 'extension' with mime type 'mime/type'
	$existing_mimes['csv'] = 'text/csv';

	return $existing_mimes;
}

	function register_admin_scripts() {
	 
		wp_register_script('promo_admin_script', plugin_dir_url( __FILE__ ) . 'includes/js/admin.js');
		wp_enqueue_script('promo_admin_script');
	 
	} // end register_scripts
	add_action('admin_enqueue_scripts', 'register_admin_scripts');





	// display codes and related users
	function list_the_codes() {
		global $wpdb;
		global $post;
		global $table_name;
		$thefile = get_post_meta($post->ID, 'post_media', true);
		$data_count = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM $table_name" ) );		
		$fivesdrafts = $wpdb->get_results("SELECT id, user_email, user_name, our_code, partner_code FROM $table_name");
		$thead = '<table width="100%" cellpadding="5" id="data_table">';
		$thead .= '<tr bgcolor="#99CCFF"><td align="center">ID</td><td>User Email</td><td>User Name</td><td>Our Code</td><td>Partner Code</td></tr>';
		$thead .= '<tr bgcolor="#99CCFF"><td align="center" colspan="6"><a href="tools.php?page=export&rmcsv='.$table_name.'" id="csvDownload">Download The complete .CSV file here</a></td></tr>';
		
		echo $thead;
		foreach ( $fivesdrafts as $fivesdraft ) {
		   $our_code = str_replace("*%", "1", $fivesdraft->our_code, $count);
		   $our_code = str_replace("/&*o", "5", $our_code, $count);	
		   
		   $partner_code = str_replace("*%", "6", $fivesdraft->partner_code, $count);
		   $partner_code = str_replace("/&*o", "2", $partner_code, $count);
		  echo '<tr style="border-bottom:1px solid #ccc;">';
		  if ($fivesdraft->user_name != 'Not Used') {
			  echo '<td width="2%" align="center" bgcolor="#99CCFF"><strong>'.$fivesdraft->id.'</strong></td>';
			  echo '<td width="10%"><strong>'.$fivesdraft->user_email.'</strong></td>';
			  echo '<td width="10%"><strong>'.$fivesdraft->user_name.'</strong></td>';
		  } else {
			  echo '<td width="2%" align="center">'.$fivesdraft->id.'</td>';
			  echo '<td width="10%">'.$fivesdraft->user_email.'</td>';
			  echo '<td width="10%">'.$fivesdraft->user_name.'</td>';
		  }
		  if ($fivesdraft->user_name != 'Not Used') {
			  echo '<td width="10%"><span style=" text-decoration:line-through;">'.$our_code.'</span></td>';
			  echo '<td width="10%"><span style=" text-decoration:line-through;">'.$partner_code.'</span></td>';
		  } else {
			  echo '<td width="10%">'.$our_code.'</td>';
			  echo '<td width="10%">'.$partner_code.'</td>';
		  }
		  echo '</tr>';
		}
		echo '</table>'; 
	}
	
	/**
	 * Process the custom metabox fields
	 */
	function save_promo_url( $post_id ) {
		global $post;  
		if( $_POST ) {
			update_post_meta( $post->ID, 'urllink', $_POST['siteurl'] );
			update_post_meta( $post->ID, 'from', $_POST['from'] );
			update_post_meta( $post->ID, 'ptype', $_POST['new_partner_type'] );
			update_post_meta( $post->ID, 'redURL', $_POST['redURL'] );
			update_post_meta( $post->ID, 'conURL', $_POST['conURL'] );
			update_post_meta( $post->ID, 'conNAME', $_POST['conNAME'] );
			update_post_meta( $post->ID, 'conTNAME', $_POST['conTNAME'] );
			update_post_meta( $post->ID, 'conUSER', $_POST['conUSER'] );
			update_post_meta( $post->ID, 'conPASSWORD', $_POST['conPASSWORD'] );
			update_post_meta( $post->ID, 'conQSTRING', $_POST['conQSTRING'] );
			update_post_meta( $post->ID, 'queV1', $_POST['queV1'] );
			update_post_meta( $post->ID, 'queV2', $_POST['queV2'] );
			update_post_meta( $post->ID, 'queV3', $_POST['queV3'] );
			update_post_meta( $post->ID, 'post_media', $_POST['post_media'] );
			add_action('save_post', 'register_admin_scripts');
		}
	}
	 
	/**
	 * Get and return the values for the URL and description
	 */
	function get_url_desc_box() {
		global $post;
		$urllink = get_post_meta( $post->ID, 'urllink', true );
		$ptype = get_post_meta( $post->ID, 'ptype', true );
		$from = get_post_meta( $post->ID, 'from', true );
		$redURL = get_post_meta( $post->ID, 'redURL', true );
		$conURL = get_post_meta( $post->ID, 'conURL', true );
		$conNAME = get_post_meta( $post->ID, 'conNAME', true );
		$conTNAME = get_post_meta( $post->ID, 'conTNAME', true );
		$conUSER = get_post_meta( $post->ID, 'conUSER', true );
		$conPASSWORD = get_post_meta( $post->ID, 'conPASSWORD', true );
		$conQSTRING = get_post_meta( $post->ID, 'conQSTRING', true );
		$queV1 = get_post_meta( $post->ID, 'queV1', true );
		$queV2 = get_post_meta( $post->ID, 'queV2', true );
		$queV3 = get_post_meta( $post->ID, 'queV3', true );
		return array( $urllink, $ptype, $from, $redURL, $conURL, $conNAME, $conTNAME, $conUSER, $conPASSWORD, $conQSTRING, $queV1, $queV2, $queV3);
	}
	//adding contextual help
	add_action( 'contextual_help', 'codex_add_help_text', 10, 3 );
	
	function codex_add_help_text( $contextual_help, $screen_id, $screen ) { 
		  //$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id
		  if ( 'page' == $screen->id ) {
			$contextual_help = '<p>' . __('Things to remember when adding or editing a promo:') . '</p>';
			  $contextual_help .= '<ul>';
			  $contextual_help .= '<li><p>'. __('Dont forget to enter all the info in the right sidebar meta-boxes.') .'</p></li>';
			  $contextual_help .= '<li><p>'. __('To insert your .csv file you will need to use the WordPress Uplaod button on the top of the WYSIWYG editor in visual mode. Once you have uploaded your .csv file you need to copy the url from the media overlay box and then paste it into the right sidbar meta-box titled Add .CSV File, save your post.') .'</p></li>';
			  $contextual_help .= '</ul>';
		  } elseif ( 'edit-page' == $screen->id ) {
			$contextual_help = '<p>' . __('This is the help screen displaying the table of Pages, you have however, optted not to use the promo custom content type so you will add your promos directly to a page.') . '</p>' ;
		  }
		  if ( 'promo' == $screen->id ) {
			$contextual_help = '<p>' . __('Things to remember when adding or editing a promo:') . '</p>';
			  $contextual_help .= '<ul>';
			  $contextual_help .= '<li><p>'. __('Dont forget to enter all the info in the right sidebar meta-boxes.') .'</p></li>';
			  $contextual_help .= '<li><p>'. __('To insert your .csv file you will need to use the WordPress Uplaod button on the top of the WYSIWYG editor in visual mode. Once you have uploaded your .csv file you need to copy the url from the media overlay box and then paste it into the right sidbar meta-box titled Add .CSV File, save your post.') .'</p></li>';
			  $contextual_help .= '</ul>';
		  } elseif ( 'edit-promo' == $screen->id ) {
			$contextual_help = '<p>' . __('This is the help screen displaying the table of custom content type promos.') . '</p>' ;
		  }
		  return $contextual_help;
	}
}

?>