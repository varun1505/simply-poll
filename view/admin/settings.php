<?php
$limit = get_option('sp_limit', SP_OPTIONS_DEFAULT_SP_LIMIT);
$transition_speed = get_option('sp_transition_speed', SP_OPTIONS_DEFAULT_SP_TRANSITION_SPEED);
if (isset($_POST['submit']) && wp_verify_nonce($_POST['spcheck'], 'settings')) {
	
	$messageArray	= array();

	// Vote limit
	if ($_POST['limit'] == 'yes') {
		update_option('sp_limit', 'yes');
		$messageArray[] = 'Limited to only one submission per computer';
	} else if ($_POST['limit'] == 'no') {
		update_option('sp_limit', 'no');
		$messageArray[] = 'Not limited to only one submission per computer';
	}

	// Transition speed
	if (isset($_POST['transition_speed'])) {
		update_option('sp_transition_speed', intval($_POST['transition_speed']));
		$messageArray[] = 'Transition speed was set to "'.intval($_POST['transition_speed']).'"';
	}

	// Setup the message
	$message	= '';
	if(count($messageArray) > 0)
		{
		$message .= '<ul class="sp-admin-message">';
		foreach($messageArray as $k => $v)
			{
			$message .= '<li>'.$v.'</li>';
			}
		$message .= '</ul>';
		}
}
?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div> 
	<h2>
		<?php _e('Global Poll Settings'); ?>
	</h2>
	
	<?php if(!empty($message)) : ?>
		
		<p><?php echo $message; ?></p>
		
		<p><a href="<?php admin_url(); ?>admin.php?page=sp-settings"><?php _e('Back'); ?></a></p>
		
	<?php else : ?>
		
		<form method="post">
			
			<?php wp_nonce_field('settings', 'spcheck'); ?>
			
			<p><?php _e('Limit to one submission per computer'); ?>?</p>
			<p><input type="radio" name="limit" value="yes" id="limityes" <?php if($limit == 'yes') { echo 'checked="checked"'; } ?> />&nbsp;<label for="limityes"><?php _e('Yes'); ?></label></p>
			<p><input type="radio" name="limit" value="no" id="limitno" <?php if ($limit == 'no') { echo 'checked="checked"'; } ?> />&nbsp;<label for="limitno"><?php _e('No'); ?></label></p>

			<p><label for="transition_speed"><?php _e('Speed of the poll transition'); ?>:</label></p>
			<p><input type="text" name="transition_speed" id="transition_speed" value="<?php echo $transition_speed; ?>"/></p>

			<p><input type="submit" name="submit" value="<?php _e('Submit'); ?>" class="button-primary" /></p>
			
		</form>
		
	<?php endif; ?>

</div>