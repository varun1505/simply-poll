<?php
global $wp, $logger;

// Is this a different request?
if(isset($_POST['form_action']))
	{
	switch($_POST['form_action'])
  	{
  	case 'get_option_value':
    	$option_value = get_option($_POST['option_name']);
    	if($option_value !== false)
    		echo json_encode(array('option_name' => $_POST['option_name'], 'option_value' => $option_value));
    	else
    		echo '-1';
    	break;

  	default:
    	echo '-1';
    	break;
  	}
  exit;
	}

// Check if poll is set (also can be used to check for direct access)
if( isset($_POST['poll']) && wp_verify_nonce($_POST['spcheck'], 'submit')) {

	$logger->logVar($_POST, '$_POST');

	// Set our poll variables
	$pollID		= (int)$_POST['poll'];
	$simplyPoll	= new SimplyPoll();	
	$answer		= null;
	$other		= null;
	
	
	// A vote has been made
	if( isset($_POST['answer']) ) {
			
		$logger->log('The int `'.$_POST['answer'].'` has been accepted');
		
		$answer = $_POST['answer'];
		if($answer === '0') $other = $_POST['other']; // Set the other answer if 'other' was chosen
	
		// Check if we have the 'sptaken' cookie before trying to get data
		if(isset($_COOKIE['sptaken']))
			$taken	= $_COOKIE['sptaken'];
		else
			$taken	= null;

		$taken		= unserialize($taken);	// Unserialize $taken to get an array
		$taken[]	= $pollID;				// Add this poll's ID to the $taken array
		$taken		= serialize($taken);	// Serialize $taken array ready to be stored again
		
		setcookie('sptaken', $taken, time()+315569260, '/');

	} else {
		$logger->log('The no answer accepted');
	}

	// No back url has been set so treat it as a Javascript call
	if( !isset($_POST['backurl']) ) {
		
		$return = array(
			'answer'	=> $simplyPoll->submitPoll($pollID, $answer, $other), // This function will add the results
			'pollid'	=> $pollID
		);
		$json = json_encode($return);
		
		$logger->logVar($json, '$json');
		
		echo $json;

	} else {
		
		/**
		 * This block of code is pretty useless till I have a solution for none JS users
		 */
		$simplyPoll->submitPoll($pollID, $answer);
		
		$regex = '/(.[^\?]*)/';		
		$querystring = preg_replace($regex, '', $_POST['backurl']);

		if( $querystring ) {
			preg_match($regex, $_POST['backurl'], $matches);
			$url = $matches[0].$querystring.'&';
			
		} else {
			$url = $_POST['backurl'].'?';
		}
		
		$location = $url.'simply-poll-return='.$answer;

		header('Location: '.$location);

	}

} else {
	echo SP_DIRECT_ACCESS;
}