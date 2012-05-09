<?php
/******************************
* edit wysiwyg
******************************/
if(!class_exists('ShortcodesEditorSelector')):

class ShortcodesEditorSelector{
	var $buttonName = 'ShortcodeSelector';
	function addSelector(){
		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
	 
	   // Add only in Rich Editor mode
	    if ( get_user_option('rich_editing') == 'true') {
	      add_filter('mce_external_plugins', array($this, 'registerTmcePlugin'));
	      //you can use the filters mce_buttons_2, mce_buttons_3 and mce_buttons_4 
	      //to add your button to other toolbars of your tinymce
	      add_filter('mce_buttons', array($this, 'registerButton'));
	    }
	}
	
	function registerButton($buttons){
		array_push($buttons, "separator", $this->buttonName);
		return $buttons;
	}
	
	function registerTmcePlugin($plugin_array){
		define('RM_PLUGIN_SLUG', 'response-promo-redeemer');
		define('RM_PLUGIN_DIR', WP_PLUGIN_URL.'/'.RM_PLUGIN_SLUG.'/');	
		$plugin_array[$this->buttonName] = RM_PLUGIN_DIR.'/includes/js/editor_plugin.js.php';
		if ( get_user_option('rich_editing') == 'true') 
		 	
		return $plugin_array;
	}
}

endif;

if(!isset($shortcodesES)){
	$shortcodesES = new ShortcodesEditorSelector();
	add_action('admin_head', array($shortcodesES, 'addSelector'));
}
/******************************
* add shortcodes
******************************/



function promo_form( $atts, $content = null ) {
	$wp_wall_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
	global $wpdb;
	global $post;
	global $rpr_db_version;
	global $table_name;
	$table_name .= '_'.$post->ID;
	$from = get_post_meta( $post->ID, 'from', true );
	$urllink = get_post_meta( $post->ID, 'urllink', true );
	$ptype = get_post_meta( $post->ID, 'ptype', true );
	$from = get_post_meta( $post->ID, 'from', true );
	$redURL = get_post_meta( $post->ID, 'redURL', true );
	if ($ptype == 'Connect') {
		$conURL = get_post_meta( $post->ID, 'conURL', true );
		$conNAME = get_post_meta( $post->ID, 'conNAME', true );
		$conTNAME = get_post_meta( $post->ID, 'conTNAME', true );
		$conUSER = get_post_meta( $post->ID, 'conUSER', true );
		$conPASSWORD = get_post_meta( $post->ID, 'conPASSWORD', true );
		$conQSTRING = get_post_meta( $post->ID, 'conQSTRING', true );
	} else if ($ptype == 'Query') {
		$queV1 = get_post_meta( $post->ID, 'queV1', true );
		$queV2 = get_post_meta( $post->ID, 'queV2', true );
		$queV3 = get_post_meta( $post->ID, 'queV3', true );
	}
	
   extract( shortcode_atts( array(
      'attr_1' => 'attribute 1 default',
      'attr_2' => 'attribute 2 default',
      // ...etc
      ), $atts ) );
	  $form = '<div class="message" style="display:none; position:absolute;top:0px; left:0px; width:100%;"><div id="alert" style="padding:10px; border:1px solid #999; background-color:#E7E7E7;"></div></div>';
	  $form .= '<form id="contactform" class="appnitro"  method="post" action="'.$wp_wall_plugin_url.'/promo-codes.php"><ul>';
	  $form .= '<li id="li_1" ><label class="description" for="name"><span class="listTitle">Name: </span></label><div><input id="name" name="name" class="element text medium fullwidth" type="text" maxlength="255" value=""/> </div> </li>';
	  $form .= '<li id="li_10_0" ><label class="description" for="email"><span class="listTitle">Email: </span></label><div><input id="email" name="email" class="element text medium fullwidth" type="text" maxlength="255" value=""/> </div> </li>';
	  $form .= '<li id="li_2" ><label class="description" for="license"><span class="listTitle">Redemption Code: </span></label><div><input id="license" name="license" class="element text medium fullwidth" type="text" maxlength="255" value=""/> </div> </li>';
	  $form .= '<span style="display:none;"><p>Honeypot:</p> <input type="text" name="last" value="" id="last" /></span>';
	  $form .= '
	  	<input type="hidden" name="tablename" value="'.$table_name.'" id="" />
		<input type="hidden" name="from_email" value="'.$from.'" id="" />
		<input type="hidden" name="ptype" value="'.$ptype.'" id="" />
		<input type="hidden" name="redURL" value="'.$redURL.'" id="" />';
	  if ($ptype == 'Connect') {
		$form .= '<input type="hidden" name="conURL" value="'.$conURL.'" id="" />
		<input type="hidden" name="conNAME" value="'.$conNAME.'" id="" />
		<input type="hidden" name="conTNAME" value="'.$conTNAME.'" id="" />
		<input type="hidden" name="conUSER" value="'.$conUSER.'" id="" />
		<input type="hidden" name="conPASSWORD" value="'.$conPASSWORD.'" id="" />
		<input type="hidden" name="conQSTRING" value="'.$conQSTRING.'" id="" />
		<input type="hidden" name="queURL" value="'.$queURL.'" id="" />';
	  } else if ($ptype == 'Query') {
		$form .= '<input type="hidden" name="queV1" value="'.$queV1.'" id="" /><input type="hidden" name="queV2" value="'.$queV2.'" id="" /><input type="hidden" name="queV3" value="'.$queV3.'" id="" />';
	  }
		$form .= '<input type="hidden" name="pID" value="'.$post->ID.'" id="" />
	  ';
	  $form .= '<li class="buttons"><input type="image" name="submit" class="sendit" src="img/images/submit.png" width="153" height="57"> </li>';
	  $form .= '</ul></form><script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script><script type="text/javascript" src="'.$wp_wall_plugin_url.'/js/jquery.form.js"></script><script type="text/javascript" src="'.$wp_wall_plugin_url.'/js/jquery.formfooter.js"></script>';
	  return $form;
}
add_shortcode( 'promo-form', 'promo_form' );