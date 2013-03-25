jQuery(function() {

	var $ = jQuery;							// Because `$` is easier than using `jQuery`
	$('.sp-poll form').submit(formProcess);	// Access formProcess() when the poll is submitted
	if($('#polledit')) formAnswerRow_init(); // Alow dynamic row adding to the view/edit admin form

	/**
	 * Form Process
	 * Process through the form 
	 * 
	 * @param object e
	 */
	function formProcess(e) {
		
		e.preventDefault();
		
		var poll	= $('input[name=poll]').val(),
			answer	= $('input[name=answer]:checked').val(),
			other		= $('.sp-input-other').val(),
			elem		= $(this),
			div			= $(this).parent(),
			action	= $(this).attr('action');

		// Get the transition speed
		$.ajax({
			type:	'POST',
			url:	spAjax.url,
			data: {
						action:					'spAjaxSubmit',
						option_name:		'sp_transition_speed',
						form_action:		'get_option_value'
						},
			dataType: 'json',
			success: function(response)
				{
				if(response != '-1')
					{
      		transition_speed = parseInt(response.option_value);
      		elem.slideUp(transition_speed, function() {
						updatePoll(action, poll, answer, other);
					});
					}
				}
		});
	}

	/**
	 * Allow additional answers to be added dynamically
	 */
	function formAnswerRow_init() {
		
		// The form was initialized
		// Find the last answer in the form
		lastAnswer	= $('#polledit .pollanswers').last().attr('id');

		// If the last answer was found
		// Add the add/remove question buttons
		if(lastAnswer)
			{
			// How many questions do we have currently?
			var answerCount					= $('#polledit .pollanswers').length;

			// Remove all button containers first
			$('.pollanswers-buttons').remove();

			// Loop through each of the current answers and
			// add the relevent buttons to them (add/remove)
			$('#polledit .pollanswers').each(function(i){
				i++ // Add 1 to make sure out count is correct
				buttonRemove	= null;
				buttonAdd			= null;

				// As long as this is any answer after 1, add our buttons
				if(i > 1)
					{
					// Any answer after 2 always gets a remove button (2 can't ever be removed)
					if(i > 2)	buttonRemove = '<button id="pollanswers-remove-'+i+'" class="pollanswers-remove button-primary">-</button>';

					// If this is the last answer, add the 'add question' button
					if(i == answerCount)
						{
						buttonAdd = '<button id="pollanswers-add-'+i+'" class="pollanswers-add button-primary">+</button>';
						}

					// Combine the buttons in to the button container
					buttonContainer = '<div id="pollanswers-buttons-'+i+'" class="pollanswers-buttons">';
						if(buttonRemove !== null)	buttonContainer += buttonRemove;
						if(buttonAdd !== null) 		buttonContainer += buttonAdd;
					buttonContainer += '</div>';
					
					// Append the buttons container to the element
					$(this).after(buttonContainer);
					}
			});
			}
		
	}

	/**
	 * Add/Remove answer functionality for the admin
	 */
	$('.pollanswers-buttons .pollanswers-remove').live('click', function(e){
		e.preventDefault();

		// Remove the parent li of this button
		$(this).closest('li').remove();

		// Call the form init function again to setup new buttons
		formAnswerRow_init();

		return false;
	});
	$('.pollanswers-buttons .pollanswers-add').live('click', function(e){
		e.preventDefault();

		// How many questions do we have currently?
		var answerCount = $('#polledit .pollanswers').length;

		// Create the new li and input
		answerNew = '<li>'
				+'<input type="text" name="answers['+(answerCount+1)+'][answer]" size="50" value="" class="pollanswers" id="pollanswers-'+(answerCount+1)+'">'
			+'</li>';
		$('#polledit #answers ul').append(answerNew);

		// Call the form init function again to setup new buttons
		formAnswerRow_init();

		return false;
	});

	/**
	 * Update Poll
	 * Update the results from our AJAX query
	 * 
	 * @param string action
	 * @param int pollID
	 * @param int answer
	 */
	function updatePoll(action, pollID, answer, other) {
		
		var postData;

		if (answer > 0) {
			postData = {
				action:	'spAjaxSubmit',
				poll:	pollID,
				answer:	answer
			};

		} else {
			postData = {
				action:	'spAjaxSubmit',
				poll:	pollID,
				answer:	answer,
				other: other
			};
		}
		
		
		var ajax = $.ajax({
			type:		'POST',
			url:		spAjax.url,
			data:		postData,
			dataType:	'JSON',
			success:	displayResults,
			error:		function(e, textStatus, errorThrown) {
							console.log('An error occured with `updatePoll()`', ajax);
							console.log(textStatus, errorThrown, e);
						}
		});
	
	}

	/**
	 * Display Results
	 * Shows the results when requested
	 * 
	 * @param object data
	 */
	function displayResults(data) {

		var postData = {
				action: 'spAjaxResults',
				pollid: data.pollid
			},
		
			html = $.ajax({
				type:		'POST',
				async:		false,
				url:		spAjax.url,
				data:		postData,
				dataType:	'html',
				error:		function(e, textStatus, errorThrown) {
								console.log('An error occured with `displayResults()`');
								console.log(textStatus, errorThrown, e);
							}
			}).responseText,
		
			pollID = '#poll-'+data.pollid;
		
		$(pollID).append(html);
	}

});