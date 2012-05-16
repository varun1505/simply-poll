<?php

class SimplyPoll {

	private	$pollData;
	private	$pollDB;		// Stores the DB class
	private $pollStrings;	// Store the custom strings


	/**
	 * Simply Poll construct
	 * Access the Simply Poll's database
	 * 
	 * @param bool $enque Set enqued files
	 */
	public function __construct($enque=true) {
		// Establish our DB class
		$this->pollDB = new SimplyPollDB();
	}
	
	
	/*************************************************************************/
	
	
	/**
	 * Poll Database
	 * Access the Simply Poll's database
	 * 
	 * @return object
	 */
	public function pollDB() {
		return $this->pollDB;
	}
	
	
	/*************************************************************************/
	
	
	/**
	 * Display Poll
	 * Gives the HTML for the poll to display on the front-end
	 * 
	 * @param array $args
	 * @return string
	 */
	public function displayPoll(array $args) {
		
		$limit = get_option('sp_limit');

		if( isset($args['id']) ) {
			$pollid		= $args['id'];
			$poll		= $this->grabPoll($pollid);
			
			if( isset($poll['question']) ) {
				$question	= stripcslashes($poll['question']);
				$answers	= $poll['answers'];
				$answersother = $poll['answersother'];
				$totalvotes = $poll['totalvotes'];
				
				foreach( $answers as $key => $answer ) {
					$answers[$key]['answer'] = stripcslashes($answer['answer']);
				}
				ob_start();

				$postFile = plugins_url(SP_SUBMIT, dirname(__FILE__));
				$thisPage = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

				$userCannotTakePoll = false;

				if(
					(
						$limit == 'yes' && isset($_COOKIE['sptaken']) && 
						in_array($args['id'], unserialize($_COOKIE['sptaken']))
					) || 
					isset($_GET['simply-poll-return'])  
				) {
					$userCannotTakePoll = true;
				}

				include(SP_DIR.SP_DISPLAY);
				$content = ob_get_clean();
				return $content;
			}
			
		}
		
	}
	
	
	/*************************************************************************/
	
	
	/**
	 * Submit Poll
	 * Passes back the poll results to return a JSON feed of responses. Can
	 * also just pass back previous results without passing an answer.
	 * 
	 * @param int $pollID
	 * @param int $answer
	 * @return int
	 */
	public function submitPoll($pollID, $answer=null, $other=null) {
		
		global $spAdmin, $logger;
	
		// The user has provided an answer
		if( isset($answer) ) {
			
			$poll = $this->grabPoll($pollID); // Grab the current results
			
			$totalVotes = 0;

			// Code to add a new answer if the enable other option is set and
			// a custom answer was given
			if($answer === '0' && !empty($other) && $poll['answersother'] === '1')
				{
				$newPoll = $poll;
				// Get the new question's ID
				$newKey	= false;
				// Check to see if this question already exists
				foreach($newPoll['answers'] as $key => $value)
					{
					if($other == $value['answer']) $newKey = $key;
					// While we're looping, decode any url entities
					$newPoll['answers'][$key]['answer'] = htmlspecialchars_decode($value['answer'], ENT_QUOTES);
					}
				// If a key isn't set, assume it's a new answer
				if(!$newKey)
					{
					$newKey = (count($newPoll['answers'])+1);
					// Add the new question to the poll object
					$newPoll['answers'][$newKey] = array('answer' => $other, 'vote' => 0);
					// Add the id to the poll object as 'polledit' so we know what to update
					$newPoll['polledit']	= $pollID;
					// Update the poll with the new answer
					$updatePoll = $spAdmin->setEdit($newPoll);
					if(!isset($updatePoll['return']['success']))
						{
						// If our update failed, set the key to 0 so this call fails
						$newKey = 0;
						}
					}
				// Set the key to the anser variable so the logic below continues to work
				$answer	= $newKey;
				// Set the poll back to our new poll variable to continue processing
				$poll = $newPoll;
				}
			
			// Verify answer is valid
			if(empty($poll['answers'][$answer]))
				{
				return null;
				}
			
			// Update the count of the answer
			$current = $poll['answers'][$answer]['vote'];
			++$current;
			$poll['answers'][$answer]['vote'] = $current;


			// Count the total votes
			foreach($poll['answers'] as $key => $thisAnswer){
				$totalVotes = $totalVotes + $thisAnswer['vote'];
			}
			
			
			$poll['totalvotes']	= $totalVotes;						// Update the total count
			$success			= $this->pollDB->setPollDB($poll);	// Push the results back to store
			$answer				= $poll['answers'][$answer];		// Provide feedback on answer
			
			$logger->logVar($answer, '$answer');
			
			return $answer;
			
		} else{
			return null;
		}
	}
	
	
	/*************************************************************************/


	/**
	 * Grab Poll
	 * Gets the current state of the the poll
	 *
	 * @param int $id
	 * @return array
	 */
	public function grabPoll($id=null) {
		
		$poll = $this->pollDB->getPollDB($id); // get the results from the the DB
		
		// If we set an ID then only return the single node
		if (isset($poll[0])) {
			$poll = $poll[0];
			$poll['answers'] = unserialize($poll['answers']);
		}
		
		return $poll;
	}
	
	
	/*************************************************************************/


	/**
	 * Grab String
	 * Pulls the stored string
	 *
	 * @param	string $name
	 * @return	string
	 */
	public function grabString($string) {

	}
	

}