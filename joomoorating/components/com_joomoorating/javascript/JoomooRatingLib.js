/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

/**
 * data members and functions to support joomoorating extension
 * ------------------------------------------------------------
 */
var JoomooRatingLib = {};         // declare our class name in the global namespace
function JoomooRatingLib () { };  // dummy constructor (singleton class)

/**
 * validate form before submitting it to the server
 * The system also validates data before storing it in the db, in check() which is in:
 *    administrator/components/com_joomoorating/tables/joomoorating.php
 */
JoomooRatingLib.validateRating = function ( ratingValue )
{
	if ( typeof(ratingValue) == 'number' )
	{
		if ( ratingValue < JoomooRatingLib.JOOMOO_RATING_MINIMUM )
		{
			var message = 'The specified rating (' + ratingValue + ') is too low.  ' +
				'It must be greater than or equal to ' + JoomooRatingLib.JOOMOO_RATING_MINIMUM + '.';
			alert( message );
			return false;
		}
		else if ( ratingValue > JoomooRatingLib.JOOMOO_RATING_MAXIMUM )
		{
			var message = 'The specified rating (' + ratingValue + ') is too high.  ' +
				'It must be less than or equal to ' + JoomooRatingLib.JOOMOO_RATING_MAXIMUM + '.';
			alert( message );
			return false;
		}
	}
	else
	{
		alert( 'The rating must be a number between ' + JOOMOO_RATING_MINIMUM + ' and ' + JOOMOO_RATING_MAXIMUM + ' inclusive.' );
		return false;
	}

	return true;
}

/*
 * We store the ratings so we always know which rating bar to use - normal or light
 */
JoomooRatingLib.ratingValue = [];

/**
 * event handler for mouseover a rating bar
 */
JoomooRatingLib.mouseoverRatingBar = function ( ratingInstance, ratingValue )
{
	var imgTagId = 'joomoorating_bar_' + ratingInstance + '_' + ratingValue;
	var yourvoteId = 'joomoorating_yourvote_' + ratingInstance;
	var yourvoteTextId = 'joomoorating_yourvote_text_' + ratingInstance;
	var yourvoteValueId = 'joomoorating_yourvote_value_' + ratingInstance;
	var newWidth = 0;
	var barImageFileName = JoomooRatingLib.imageFileRoot + '-normal-' + ratingValue + '.gif';

	if ( $(imgTagId) != null )
	{
		$(imgTagId).src = barImageFileName;
		JoomooRatingLib.saveImageWidth = $(imgTagId).width;
		newWidth = JoomooRatingLib.saveImageWidth + parseInt(JoomooRatingLib.hover_width_bump);
		$(imgTagId).width = newWidth;
	}

	if ( JoomooRatingLib.thisUserCanRate )
	{
		if ( typeof(JoomooRatingLib.ratingDescription) == 'object' && JoomooRatingLib.ratingDescription[ratingValue] )
		{
			if ( $(yourvoteTextId) != null )
			{
				$(yourvoteTextId).set( 'html', JoomooRatingLib.ratingDescription[ratingValue] + '</span>' );
			}
		}
		else
		{
			if ( $(yourvoteValueId) != null )
			{
				$(yourvoteValueId).set( 'html', ratingValue + '</span>' );
			}
		}
	}
	else
	{
		if ( $(yourvoteId) != null )
		{
			$(yourvoteId).set( 'html', JoomooRatingLib.JOOMOO_RATING_LOG_IN_TO_VOTE + '</span>' );
		}
	}
}
/**
 * event handler for mouseoout of a rating bar
 */
JoomooRatingLib.mouseoutRatingBar = function ( ratingInstance, ratingValue )
{
	var imgTagId = 'joomoorating_bar_' + ratingInstance + '_' + ratingValue;
	var yourvoteTextId = 'joomoorating_yourvote_text_' + ratingInstance;
	var yourvoteValueId = 'joomoorating_yourvote_value_' + ratingInstance;
	var barImageFileName = JoomooRatingLib._getBarImageFileName ( ratingInstance, ratingValue );

	if ( $(imgTagId) != null )
	{
		$(imgTagId).width = JoomooRatingLib.saveImageWidth;
		$(imgTagId).src = barImageFileName;
	}

	if ( $(yourvoteTextId) != null )
	{
		$(yourvoteTextId).set( 'html', 'Your vote: </span>' );
	}

	if ( $(yourvoteValueId) != null )
	{
		$(yourvoteValueId).set( 'html', '</span>' );
	}
}
/**
 * get the file name - normal if it's for the rating value else the light version
 */
JoomooRatingLib._getBarImageFileName = function ( ratingInstance, ratingValue )
{
	var barImageFileName;

	if ( ratingValue == JoomooRatingLib.ratingValue[ratingInstance] )
	{
		barImageFileName = JoomooRatingLib.imageFileRoot + '-normal-' + ratingValue + '.gif';
	}
	else
	{
		barImageFileName = JoomooRatingLib.imageFileRoot + '-light-' + ratingValue + '.gif';
	}

//	alert( 'ratingInstance = ' + ratingInstance + '; ratingValue = ' + ratingValue +
//		'; JoomooRatingLib.ratingValue[ratingInstance] = ' + JoomooRatingLib.ratingValue[ratingInstance] +
//		'; barImageFileName = ' + barImageFileName
//	);

	return barImageFileName
}
/**
 * event handler for mouse click on a rating bar
 */
JoomooRatingLib.clickRatingBar = function( ratingRowId, ratingInstance, ratingValue )
{
	var yourvoteId = 'joomoorating_yourvote_' + ratingInstance;
	var message;

	//	alert( 'JoomooRatingAjax.numberOfVotes[ratingInstance] = "' + JoomooRatingAjax.numberOfVotes[ratingInstance] + '"' );

	if ( JoomooRatingLib.thisUserCanRate )
	{
		if ( JoomooRatingAjax.okToVote(ratingInstance) )
		{
			JoomooRatingAjax.ajaxUpdateVoteInDb( ratingRowId, ratingInstance, ratingValue );
		}
		else
		{
			if ( $(yourvoteId) != null )
			{
				JoomooRatingAjax.max_consecutive_ratings == 1 ?
					message = "You've already voted!" :
					message = "You've already voted " + JoomooRatingAjax.numberOfVotes[ratingInstance] + ' times!';
				$(yourvoteId).set( 'html', '<span class="joomoobase_blink">' + message + '</span>' + '</span>' );
			}
		}
	}
	else
	{
		if ( $(yourvoteId) != null )
		{
			message = JoomooRatingLib.JOOMOO_RATING_LOG_IN_TO_VOTE;
			$(yourvoteId).set( 'html', '<span class="joomoobase_blink">' + message + '</span>' + '</span>' );
		}
	}
}
