/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  JoomooRating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

/**
 * functions to support using ajax for the joomoorating extension
 * --------------------------------------------------------------
 */
var JoomooRatingAjax = {};         // declare our class name in the global namespace
function JoomooRatingAjax () { };  // dummy constructor (singleton class)

//
// functions to support using ajax to store a rating in the database
// -----------------------------------------------------------------
//
//	JoomooRatingAjax._updateUrl = '/components/com_joomoobase/requests/helloWorld.php';  // for debugging
JoomooRatingAjax._updateUrl = '/components/com_joomoorating/requests/storeVote.php';

JoomooRatingAjax._updateData = {};             // data sent in the request
JoomooRatingAjax.needsFullCaption = [];     // when there's no votes yet we need the full caption

/**
 * event handler for when vote is successfully stored in DB
 */
JoomooRatingAjax.updatedSuccessfully = function ( responseText )
{
	//	alert ( 'responseText = ' + responseText );
	//	if ( $('response_text') != null ) { $('response_text').set( 'html', responseText + '<\/span>' ); }

	var message;
	var responsePieces = responseText.split( JoomooRatingAjax.RATING_RESPONSE_DELIMITER );
	var ratingInstance = responsePieces[1];
	var newRatingValueOneDecimal = responsePieces[2];
	var newVoteCount = responsePieces[3];
	var ajaxLogId = 'joomoorating_ajax_log_' + ratingInstance;

	if ( responseText.indexOf(JoomooRatingAjax.JOOMOO_RATING_SAVED_OK) == 0 )
	{
		message = responsePieces[0];
		//	if ( $(ajaxLogId) != null )
		//	{
		//		$(ajaxLogId).set( 'html', message + '<\/span>' );
		//	}
		JoomooRatingAjax._updateRatingValues( ratingInstance, newRatingValueOneDecimal, newVoteCount );
	}
	else if ( responseText.indexOf('Error') == 0 )   // known error, eg. vote out of range
	{
		alert( responseText );
		if ( $(ajaxLogId) != null )
		{
			$(ajaxLogId).set( 'html', 'Unable to process vote: ' + responseText + '.<\/span>' );
		}
	}
	else
	{
		if ( $(ajaxLogId) != null )
		{
			$(ajaxLogId).set( 'html', responseText + '<\/span>' );
		}
		alert( 'Sorry, some sort of error occurred.  You may want to try again later.' );
	}
}
/**
 * Error handler for when the request returns an error
 */
JoomooRatingAjax.updateError  = function ( status, statusText )
{
	if ( $('response_text') != null ) { $('response_text').set( 'html', 'Error ' + status + ': ' + statusText + '<\/span>' ); }
	alert( 'Error ' + status + ': ' + statusText );
}

/**
 * Get, setup, and send the request to store a vote
 */
JoomooRatingAjax.ajaxUpdateVoteInDb = function ( ratingRowId, ratingInstance, ratingValue )
{
	var myJoomooRequest;

	if ( JoomooRatingLib.validateRating(ratingValue) )
	{
		JoomooRatingAjax._updateData.ratingRowId = ratingRowId;
		JoomooRatingAjax._updateData.ratingInstance = ratingInstance;
		JoomooRatingAjax._updateData.ratingValue = ratingValue;
		myJoomooRequest = new JoomooRequest( JoomooRatingAjax._updateUrl, JoomooRatingAjax.updatedSuccessfully, JoomooRatingAjax.updateError );
		myJoomooRequest.sendPostRequest( JoomooRatingAjax._updateData );
	}

	return false;
}

/**
 * update the page after the vote's been stored successfully in DB
 */
JoomooRatingAjax._updateRatingValues = function ( ratingInstance, newRatingValueOneDecimal, newVoteCount )
{
	var ratingValueId = 'joomoorating_rating_value_' + ratingInstance;
	var voteCountId = 'joomoorating_vote_count_' + ratingInstance;
	var yourvoteThanksId = 'joomoorating_yourvote_thanks_' + ratingInstance;
	var imgTagId;

	var newRatingValue = Math.round( newRatingValueOneDecimal );
	var lightFileName;
	var normalFileName;
	var noVotesYet;

	JoomooRatingLib.ratingValue[ratingInstance] = newRatingValue;

	if ( $(ratingValueId) != null )
	{
		var newRatingValueText;
		JoomooRatingAjax.needsFullCaption[ratingInstance] ?
			newRatingValueText = JoomooRatingAjax.rating_label + ': ' + newRatingValueOneDecimal + '/' + JoomooRatingLib.JOOMOO_RATING_MAXIMUM :
			newRatingValueText = newRatingValueOneDecimal;
		$(ratingValueId).set('html', newRatingValueText + '</span>');
	}

	if ( $(voteCountId) != null )
	{
		var voteOrVotes;
		newVoteCount == 1 ? voteOrVotes = ' vote' : voteOrVotes = ' votes';
		$(voteCountId).set('html', newVoteCount + voteOrVotes + '</span>');
	}
	if ( $(yourvoteThanksId) )
	{
		$(yourvoteThanksId).set( 'html', 'Thanks!</span>' );
	}

	for ( var ratingSub = JoomooRatingLib.JOOMOO_RATING_MINIMUM; ratingSub <= JoomooRatingLib.JOOMOO_RATING_MAXIMUM; ratingSub++ )
	{
		imgTagId = 'joomoorating_bar_' + ratingInstance + '_' + ratingSub;
		if ( $(imgTagId) != null )
		{
			if ( ratingSub == newRatingValue )
			{
				normalFileName = JoomooRatingLib.imageFileRoot + '-normal-'  + ratingSub + '.gif';
				$(imgTagId).src = normalFileName;
			}
			else
			{
				lightFileName = JoomooRatingLib.imageFileRoot + '-light-'  + ratingSub + '.gif';
				$(imgTagId).src = lightFileName;
			}
		}
	}

	JoomooRatingAjax._logVote( ratingInstance );
	return;
}

/**
 * Logs a vote for this user for this rating instance
 */
JoomooRatingAjax.numberOfVotes = [];
JoomooRatingAjax._logVote = function ( ratingInstance )
{
//	alert( '_logVote: JoomooRatingAjax.numberOfVotes[ratingInstance] before = "' + JoomooRatingAjax.numberOfVotes[ratingInstance] + '"' );

	if ( typeof(JoomooRatingAjax.numberOfVotes[ratingInstance]) == 'number' &&
	     JoomooRatingAjax.numberOfVotes[ratingInstance] > 0 )
	{
		JoomooRatingAjax.numberOfVotes[ratingInstance]++;
	}
	else
	{
		JoomooRatingAjax.numberOfVotes[ratingInstance] = 1;
	}
}
/**
 * Checks to see whether user has already voted too many times for this instance
 * @return true if user has not exceeded limit else false this user can NOT vote
 */
JoomooRatingAjax.okToVote = function ( ratingInstance )
{
	var returnValue = true;

	if ( typeof(JoomooRatingAjax.numberOfVotes[ratingInstance]) == 'number' )
	{
		JoomooRatingAjax.numberOfVotes[ratingInstance] < JoomooRatingAjax.max_consecutive_ratings ?
			returnValue = true :
			returnValue = false;
	}
	return returnValue;
}

/**
 * ensconce the basic span tag for message received by server in a div tag
 */
JoomooRatingAjax.writeAjaxLog = function ( ratingInstance )
{
	document.write( '  <div class="joomoorating_ajax_log" id="joomoorating_ajax_log_' + ratingInstance + '">' );
	document.write( '   <center>' );
	JoomooRequest.writeAjaxLog();
	document.write( '   <center>' );
	document.write(    '</div>' );
}
