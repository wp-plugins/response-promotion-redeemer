<?php
// include plugin styles

// bad calls wp_enqueue_style('rpr-styles', plugin_dir_url( __FILE__ ) . 'css/plugin_styles.css');
// bad calls add_action('wp_enqueue_scripts', 'rpr_load_scripts');

 function promo_options_page() {

	global $promo_options;

	ob_start(); ?>
	<div class="wrap">
		<h2>Response Marketing Promotion Redemption Portal Options</h2>
		
		<form method="post" action="options.php">
		
			<?php settings_fields('promo_settings_group'); ?>
		
			<h4><?php _e('Enable Promotion Redemption System', 'promo_domain'); ?></h4>
			<p>
				<input id="promo_settings[enable]" name="promo_settings[enable]" type="checkbox" value="1" <?php checked(1, $promo_options['enable']); ?> />
				<label class="description" for="promo_settings[enable]"><?php _e('Enable the Response Promotion Redemption System', 'promo_domain'); ?></label>
			</p>
			<h4><?php _e('Enable Promo Content Type', 'promo_domain'); ?></h4>
            <p>(note this creates a new content type called promos and disables promo injection on pages)</p>
			<p>
				<input id="promo_settings[ctype]" name="promo_settings[ctype]" type="checkbox" value="1" <?php checked(1, $promo_options['ctype']); ?> />
				<label class="description" for="promo_settings[ctype]"><?php _e('Enable the Response Promo Content Type', 'promo_domain'); ?></label>
			</p>
			<!-- <h4><?php /* _e('Twitter Information', 'promo_domain'); ?></h4>
			<p>
				<input id="promo_settings[twitter_url]" name="promo_settings[twitter_url]" type="text" value="<?php echo $promo_options['twitter_url']; ?>"/>
				<label class="description" for="promo_settings[twitter_url]"><?php _e('Enter your Twitter URL', 'promo_domain'); ?></label>
			</p>
			
			<h4><?php _e('Theme', 'promo_domain'); ?></h4>
			<p>
				<?php $styles = array('blue', 'red'); ?>
				<select name="promo_settings[theme]" id="promo_settings[theme]">
					<?php foreach($styles as $style) { ?>
						<?php if($promo_options['theme'] == $style) { $selected = 'selected="selected"'; } else { $selected = ''; } ?>
						<option value="<?php echo $style; ?>" <?php echo $selected; ?>><?php echo $style; ?></option>
					<?php }  */?>
				</select>
			</p> -->
		
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Options', 'promo_domain'); ?>" />
			</p>
		
		</form>
		
	</div>
	<?php
	echo ob_get_clean();
}

function promo_add_options_link() {
	add_options_page('Response Marketing Promotion Redemption Portal Options', 'RM Promo Options', 'manage_options', 'promo-options', 'promo_options_page');
}
add_action('admin_menu', 'promo_add_options_link');

function promo_register_settings() {
	// creates our settings in the options table
	register_setting('promo_settings_group', 'promo_settings');
}
add_action('admin_init', 'promo_register_settings'); 