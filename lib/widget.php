<?php
if(!class_exists('SimplyPollWidget')){

class SimplyPollWidget extends WP_Widget
	{
	function SimplyPollWidget()
		{
		parent::WP_Widget(false, 'Simply Poll Widget');
		}
 
	function widget($args, $instance)
		{
		$poll = intval($instance['poll']);
		echo $args['before_widget'];
    if(!empty($instance['title'])) echo $args['before_title'].$instance['title'].$args['after_title'];
		if(intval($poll) > 0) :
			echo do_shortcode("[poll id='".$poll."']");
		else :
			?><p>No poll currently selected.</p><?php
		endif;
		echo $args['after_widget'];
		}
 
	function update($new_instance, $old_instance)
		{
		$instance = $old_instance;
		$instance['poll'] = strip_tags($new_instance['poll']);
		return $instance;
		}
 
	function form($instance)
		{
		global $spAdmin;
		$polls	= $spAdmin->grabPoll();
		$poll		= esc_attr($instance['poll']);
		?>
		<p>
			<?php if(count($polls) > 0) : ?>
			<label for="<?php echo $this->get_field_id('poll'); ?>"><?php _e('Poll:'); ?>
			&nbsp;
			<select id="<?php echo $this->get_field_id('poll'); ?>" name="<?php echo $this->get_field_name('poll'); ?>" style="width: 80%">
				<?php
				foreach($polls['polls'] as $p) : $selected = (intval($instance['poll']) == intval($p['id']) ? ' selected="selected"' : '');
					?><option value="<?php echo intval($p['id']); ?>"<?php echo $selected; ?>><?php echo stripslashes($p['question']); ?></option><?php
				endforeach;
				?>
			</select>
			<?php else : ?>
			You must create a poll before you can use this widget.
			<?php endif; ?>
		</p>
		<?php
		}
	}

add_action('widgets_init', 'SimplyPollWidget_register');
function SimplyPollWidget_register()
	{
  register_widget('SimplyPollWidget');
	}

}

?>