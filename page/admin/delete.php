<?php
	global $spAdmin;
	
	$pollDB = new SimplyPollDB();
	
	$id = (int)$_GET['id'];
	$poll = $spAdmin->grabPoll($id);
		
	if(isset($_POST['delete']) && $_POST['delete'] == 'Yes') {
		$pollDB->deletePoll($_POST['id']);
		$message = 'Poll deleted';
		
	} elseif(isset($_POST['delete']) && $_POST['delete'] == 'No') {
		$message = 'Fair enough, it\'s not deleted';
	}
	
?><div class="wrap">
	<div id="icon-edit-comments" class="icon32"><br /></div> 
	<h2>
		Delete Poll
	</h2>
	
	<?php if(isset($message)) : ?>
		
		<script>
			setTimeout( "pageRedirect()", 3000 );
			
			function pageRedirect() {
				window.location.replace('<?php admin_url(); ?>admin.php?page=sp-poll');
			}
		</script>
		
		<p><?php echo $message; ?></p>
		
		<p><a href="<?php admin_url(); ?>admin.php?page=sp-poll" class="button">Go back</a></p>
		
	<?php else : ?>
	
		<?php if(!$poll) : ?>
			<p>There is no poll with the ID <strong><?php echo $id; ?></p>
			
			<p><a href="<?php admin_url(); ?>admin.php?page=sp-poll">Go back</a></p>
		<?php else : ?>
			
			<p>Are you sure you want to delete poll "<strong><?php echo $poll['question']; ?></strong>"?</p>
			
			<form method="post">
				
				<input type="hidden" name="id" value="<?php echo $id; ?>" />
				<p><input type="submit" name="delete" class="button" value="Yes" /> <input type="submit" class="button" name="delete" value="No" /></p>
				
			</form>
		
		<?php endif; ?>
		
	<?php endif; ?>

</div>