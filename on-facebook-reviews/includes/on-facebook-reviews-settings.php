<?php

// Create menu link
add_action('admin_menu', 'onfbr_options_menu_link');

function onfbr_options_menu_link(){
	add_options_page(
		'Facebook Reviews Options',
		'Facebook Reviews',
		'manage_options',
		'onfbr-options',
		'onfbr_options_content'
	);
}


// Create Options Page Content
function onfbr_options_content(){

	
	// Init Options Global
	global $onfbr_options;

	if(!isset($onfbr_options['general']['enable'])){
		$onfbr_options['general']['enable'] = 0;
	}

	if(!isset($onfbr_options['general']['orientation'])){
		$onfbr_options['general']['orientation'] = 'horizontal';
	}

	ob_start(); ?>
		<div class="wrap">

			<h2><?php _e('Facebook Reviews Settings', 'onfbr_domain'); ?></h2>
			<div class="onfbr-shortcode">To use this plugin, copy-paste this shortcode where you want your reviews: <span style="font-family: monospace; background-color: #cccccc;">[on-facebook-review]</span></div>

			
			<form method="post" action="options.php">
				<?php settings_fields('onfbr_settings_group'); ?>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="onfbr_settings[general][enable]"></label><?php _e('Enable', 'onfbr_domain'); ?></th>
							<td><input name="onfbr_settings[general][enable]" type="checkbox" id="onfbr_settings[general][enable]" value="1" <?php checked('1', $onfbr_options['general']['enable']); ?>></td>
						</tr>

						<tr>
							<th scope="row"><label for="onfbr_settings[general][facebook_url]"></label><?php _e('Facebook Page ID', 'onfbr_domain'); ?></th>
							<td><input name="onfbr_settings[general][facebook_url]" type="text" id="onfbr_settings[general][facebook_url]" value="<?php echo $onfbr_options['general']['facebook_url']; ?>" class="regular-text">
								<p class="description"><?php _e('Enter your Facebook Page ID', 'onfbr_domain') ?> </p></td>
						</tr>

						<tr>
							<th scope="row"><label for="onfbr_settings[general][access_token]"></label><?php _e('Page Access Token', 'onfbr_domain'); ?></th>
							<td><input name="onfbr_settings[general][access_token]" type="text" id="onfbr_settings[general][access_token]" value="<?php echo $onfbr_options['general']['access_token']; ?>" class="regular-text">
								<p class="description"><?php _e('Enter your Facebook Page Access Token', 'onfbr_domain') ?> </p></td>
						</tr>

						<tr>
							<th scope="row"><label for="onfbr_settings[general][enable]"></label><?php _e('Enable', 'onfbr_domain'); ?></th>
							<td>
								<input type="radio" name="onfbr_settings[general][orientation]" value='horizontal' <?php if($onfbr_options['general']['orientation'] == 'horizontal') echo 'checked="checked"'; ?>> Horizontal<br>
								<input type="radio" name="onfbr_settings[general][orientation]" value='vertical' <?php if($onfbr_options['general']['orientation'] == 'vertical') echo 'checked="checked"'; ?>> Vertical
							</td>
						</tr>

					</tbody>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'onfbr_domain'); ?>"</p>
			</form>
			
			
			<div class="support">
				<div class="support-heading">
					<h3 class="support-heading-text">Please support the plugin</h3>
				</div>
				<div class="support-text">
					<p>If you found the Facebook Reviews plugin useful, please support it! With your help I can keep the plugin free and up to date. The Facebook API continually updates, which makes the maintenance of this plugin quite time consuming. As the author I will greatly appreciate your support.
					<p>You can easily donate to my <a href="https://www.paypal.me/OskarN" target="_blank"><b>PayPal</b></a><br><br>
					<a href="https://www.paypal.me/OskarN" target="_blank"><img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" border="0" alt="PayPal Logo"></a>
					<p style="text-align: right; font-style: italic;">Regards, Oskar Nylén
				</div>
			</div>

		</div>
	<?php
	echo ob_get_clean();
	
}

// Register Settings
add_action('admin_init', 'onfbr_register_settings');

function onfbr_register_settings(){
	register_setting('onfbr_settings_group', 'onfbr_settings');
}